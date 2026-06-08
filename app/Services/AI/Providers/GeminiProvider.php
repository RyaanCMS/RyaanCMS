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

            return [
                'content'       => $data['candidates'][0]['content']['parts'][0]['text'] ?? '',
                'tokens_used'   => ($data['usageMetadata']['totalTokenCount'] ?? 0),
                'model'         => $model,
                'response_time' => (int) ((microtime(true) - $startTime) * 1000),
                'raw'           => $data,
            ];
        } catch (RequestException $e) {
            $body = $e->getResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : [];
            throw new \RuntimeException(
                'Gemini API Error: '.($body['error']['message'] ?? $e->getMessage())
            );
        }
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
            $body   = $e->getResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : [];
            $apiMsg = $body['error']['message'] ?? $e->getMessage();
            $status = $e->getResponse()?->getStatusCode();
            if ($status === 401 || $status === 403) throw new \RuntimeException('Invalid Gemini API key. Please update it in Settings → AI Providers.');
            if ($status === 429) throw new \RuntimeException('Gemini rate limit reached. Please wait and try again.');
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
}
