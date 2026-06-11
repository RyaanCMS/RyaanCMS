<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class GeminiProvider implements AIProviderInterface
{
    protected Client $client;
    protected string $apiKey;
    protected string $model;
    protected int $maxTokens;
    protected string $apiUrl;

    public function __construct(array $config)
    {
        $this->apiKey    = $config['api_key'] ?? '';
        $this->apiUrl    = $config['api_url'] ?? 'https://generativelanguage.googleapis.com/v1beta';
        $this->model     = $config['default_model'] ?? 'gemini-1.5-pro';
        $this->maxTokens = $config['max_tokens'] ?? 8192;

        $this->client = new Client(['timeout' => 120]);
    }

    public function chat(array $messages, array $options = []): array
    {
        $startTime = microtime(true);
        $model     = $options['model'] ?? $this->model;

        // Convert messages to Gemini format
        $contents      = [];
        $systemMessage = null;

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemMessage = $msg['content'];
                continue;
            }
            $contents[] = [
                'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $payload = [
            'contents'         => $contents,
            'generationConfig' => ['maxOutputTokens' => $options['max_tokens'] ?? $this->maxTokens],
            // Relax safety thresholds — security/auth code in prompts triggers false positives
            'safetySettings'   => self::codeSafetySettings(),
        ];

        if ($systemMessage) {
            $payload['systemInstruction'] = ['parts' => [['text' => $systemMessage]]];
        }

        try {
            $response = $this->client->post(
                "{$this->apiUrl}/models/{$model}:generateContent?key={$this->apiKey}",
                ['json' => $payload]
            );

            $data = json_decode($response->getBody()->getContents(), true);

            // Prompt-level block (before any output generated)
            if (!empty($data['promptFeedback']['blockReason'])) {
                throw new \RuntimeException('Gemini blocked this prompt (' . $data['promptFeedback']['blockReason'] . '). Try rephrasing or splitting into smaller requests.');
            }

            // Candidate-level safety block (200 OK but finishReason = SAFETY)
            $finishReason = $data['candidates'][0]['finishReason'] ?? '';
            if ($finishReason === 'SAFETY') {
                throw new \RuntimeException('Gemini flagged the generated output as unsafe. Try rephrasing your prompt or removing security-sensitive keywords.');
            }

            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if ($text === '' && !in_array($finishReason, ['STOP', 'MAX_TOKENS', ''])) {
                throw new \RuntimeException('Gemini returned empty output (reason: ' . $finishReason . '). Please try again.');
            }

            return [
                'content'       => $text,
                'tokens_used'   => ($data['usageMetadata']['totalTokenCount'] ?? 0),
                'model'         => $model,
                'response_time' => (int) ((microtime(true) - $startTime) * 1000),
                'raw'           => $data,
            ];
        } catch (RequestException $e) {
            $rawBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '';
            $body    = json_decode($rawBody, true) ?? [];
            $errItem = is_array($body) && isset($body[0]) ? $body[0] : $body;
            $apiMsg  = $errItem['error']['message'] ?? $e->getMessage();
            $status  = $e->getResponse()?->getStatusCode();

            $isContentFilter = str_contains($apiMsg, 'content filtering')
                || str_contains($apiMsg, 'Output blocked')
                || str_contains($rawBody, 'content filtering')
                || str_contains($rawBody, 'Output blocked');

            // Content-filter retry: Gemini's content-filtering-policy is independent of
            // safetySettings — BLOCK_NONE doesn't affect it. We try 3 strategies:
            //   1) Same model, no systemInstruction (the trigger is usually the system prompt)
            //   2) Flash model + systemInstruction (Flash is far less strict)
            //   3) Flash model, no systemInstruction (maximum compatibility)
            if ($status === 400 && $isContentFilter) {
                $result = $this->retryAfterContentFilter($model, $payload, $systemMessage, $startTime);
                if ($result !== null) return $result;
                throw new \RuntimeException('Gemini content policy blocked this request. Switch to gemini-2.0-flash or use OpenAI/Claude for complex code generation.');
            }

            if ($status === 401 || $status === 403) throw new \RuntimeException('Invalid Gemini API key. Please update it in Settings → AI Providers.');
            if ($status === 429) throw new \RuntimeException('Gemini rate limit reached. Please wait and try again.');
            throw new \RuntimeException('Gemini API Error: ' . $apiMsg);
        }
    }

    /**
     * Three-tier content-filter retry strategy.
     * Returns a response array on success, null if all attempts fail.
     */
    private function retryAfterContentFilter(
        string $model,
        array $payload,
        ?string $systemMessage,
        float $startTime
    ): ?array {
        $attempts = [];

        // Attempt 1: same model, move system instruction into user message
        if ($systemMessage !== null) {
            $p = $payload;
            unset($p['systemInstruction']);
            array_unshift($p['contents'], [
                'role'  => 'user',
                'parts' => [['text' => '[Context] ' . mb_substr($systemMessage, 0, 2000)]],
            ]);
            $attempts[] = [$model, $p];
        }

        // Attempts 2 & 3: Flash model (less strict policy) — with and without systemInstruction
        $flash = $this->flashFallbackModel($model);
        if ($flash) {
            $attempts[] = [$flash, $payload];                        // Flash + system instruction
            if ($systemMessage !== null) {
                $p2 = $payload;
                unset($p2['systemInstruction']);
                array_unshift($p2['contents'], [
                    'role'  => 'user',
                    'parts' => [['text' => '[Context] ' . mb_substr($systemMessage, 0, 2000)]],
                ]);
                $attempts[] = [$flash, $p2];                         // Flash, no system instruction
            }
        }

        foreach ($attempts as [$attemptModel, $attemptPayload]) {
            try {
                $r    = $this->client->post("{$this->apiUrl}/models/{$attemptModel}:generateContent?key={$this->apiKey}", ['json' => $attemptPayload]);
                $d    = json_decode($r->getBody()->getContents(), true);
                $text = $d['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if ($text !== '') {
                    return [
                        'content'       => $text,
                        'tokens_used'   => $d['usageMetadata']['totalTokenCount'] ?? 0,
                        'model'         => $attemptModel,
                        'response_time' => (int) ((microtime(true) - $startTime) * 1000),
                        'raw'           => $d,
                    ];
                }
            } catch (\Throwable) {}
        }

        return null;
    }

    /**
     * Returns a Flash model to use as content-filter fallback, or null if already on Flash.
     * Flash models apply a much more permissive content-filtering-policy for code generation.
     */
    private function flashFallbackModel(string $currentModel): ?string
    {
        // Already on a flash model — no further model fallback
        if (str_contains(strtolower($currentModel), 'flash')) {
            return null;
        }
        // Prefer 2.0 flash; fall back to 1.5 flash
        return 'gemini-2.0-flash-exp';
    }

    public function stream(array $messages, callable $callback, array $options = []): void
    {
        $model         = $options['model'] ?? $this->model;
        $systemMessage = null;
        $contents      = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemMessage = $msg['content'];
                continue;
            }
            $contents[] = [
                'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $payload = [
            'contents'         => $contents,
            'generationConfig' => ['maxOutputTokens' => $options['max_tokens'] ?? $this->maxTokens],
            'safetySettings'   => self::codeSafetySettings(),
        ];

        if ($systemMessage) {
            $payload['systemInstruction'] = ['parts' => [['text' => $systemMessage]]];
        }

        try {
            $response = $this->client->post(
                "{$this->apiUrl}/models/{$model}:streamGenerateContent?alt=sse&key={$this->apiKey}",
                ['json' => $payload, 'stream' => true]
            );
        } catch (RequestException $e) {
            $rawBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '';
            $body    = json_decode($rawBody, true) ?? [];
            // Gemini SSE endpoint wraps errors in a JSON array; generateContent uses a plain object
            $errItem = is_array($body) && isset($body[0]) ? $body[0] : $body;
            $apiMsg  = $errItem['error']['message'] ?? $e->getMessage();
            $status  = $e->getResponse()?->getStatusCode();

            if ($status === 401 || $status === 403) throw new \RuntimeException('Invalid Gemini API key. Please update it in Settings → AI Providers.');
            if ($status === 429) throw new \RuntimeException('Gemini rate limit reached. Please wait and try again.');

            // Content filter — detected either via parsed body or raw body string
            $isContentFilter = str_contains($apiMsg, 'content filtering')
                || str_contains($apiMsg, 'Output blocked')
                || str_contains($rawBody, 'content filtering')
                || str_contains($rawBody, 'Output blocked');

            if ($status === 400 && $isContentFilter) {
                // Fall back to chat() which runs the full 3-tier content-filter retry
                $startTime = microtime(true);
                $result = $this->retryAfterContentFilter($model, $payload, $systemMessage, $startTime);
                if ($result !== null && !empty($result['content'])) {
                    $callback($result['content']);
                    return;
                }
                throw new \RuntimeException('Gemini content policy blocked this request. Switch to gemini-2.0-flash or use OpenAI/Claude for complex code generation.');
            }

            throw new \RuntimeException('Gemini API Error: ' . $apiMsg);
        }

        // Buffer-based SSE parsing — prevents JSON errors when read() splits across lines
        $buffer = '';
        $body   = $response->getBody();

        while (!$body->eof()) {
            $buffer .= $body->read(2048);

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line   = rtrim(substr($buffer, 0, $pos), "\r");
                $buffer = substr($buffer, $pos + 1);

                if (!str_starts_with($line, 'data: ')) continue;
                $jsonStr = trim(substr($line, 6));
                if ($jsonStr === '' || $jsonStr === '[DONE]') continue;

                $data = json_decode($jsonStr, true);
                if (!is_array($data)) continue;

                // Error embedded in stream body (200 OK but error JSON inside SSE data)
                if (isset($data['error'])) {
                    $errMsg = $data['error']['message'] ?? 'Unknown error';
                    if (str_contains($errMsg, 'content filtering') || str_contains($errMsg, 'Output blocked')) {
                        throw new \RuntimeException('Gemini blocked this request due to content policy. Try rephrasing or breaking your prompt into smaller parts.');
                    }
                    throw new \RuntimeException('Gemini API Error: ' . $errMsg);
                }

                // Check for safety block mid-stream
                $finishReason = $data['candidates'][0]['finishReason'] ?? '';
                if ($finishReason === 'SAFETY') {
                    throw new \RuntimeException('Gemini flagged the output as unsafe mid-generation. Try rephrasing your prompt.');
                }

                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if ($text) $callback($text);
            }
        }
    }

    public function getModels(): array
    {
        return config('ai.providers.gemini.models', []);
    }

    public function getName(): string
    {
        return 'Google Gemini';
    }

    public function getDefaultModel(): string
    {
        return $this->model;
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    private static function codeSafetySettings(): array
    {
        // BLOCK_NONE: disable all safety filtering for code generation.
        // Code prompts (auth systems, SQL, security patterns, injection prevention)
        // trigger false positives even at BLOCK_ONLY_HIGH — BLOCK_NONE is required.
        return [
            ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH',       'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
        ];
    }
}
