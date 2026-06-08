<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ClaudeProvider implements AIProviderInterface
{
    protected Client $client;
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;
    protected int $maxTokens;

    public function __construct(array $config)
    {
        $this->apiKey    = $config['api_key'] ?? '';
        $this->apiUrl    = $config['api_url'] ?? 'https://api.anthropic.com/v1';
        $this->model     = $config['default_model'] ?? 'claude-sonnet-4-6';
        $this->maxTokens = $config['max_tokens'] ?? 8192;

        $this->client = new Client([
            'base_uri'        => $this->apiUrl,
            'timeout'         => 180,
            'connect_timeout' => 15,
            'headers'         => [
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'anthropic-beta'    => 'prompt-caching-2024-07-31', // cache system prompt → 10% cost on cache hits
                'content-type'      => 'application/json',
            ],
        ]);
    }

    public function chat(array $messages, array $options = []): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Claude API key is not configured. Please add your API key in Settings → AI Providers.');
        }

        $startTime = microtime(true);

        // Separate system from user/assistant messages
        $systemMessage = null;
        $chatMessages  = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemMessage = $msg['content'];
            } else {
                $chatMessages[] = $msg;
            }
        }

        $payload = [
            'model'      => $options['model'] ?? $this->model,
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'messages'   => $chatMessages,
        ];

        if ($systemMessage) {
            // Prompt caching: wrap system prompt in cache_control block.
            // After first call, Anthropic caches this for 5 min → subsequent calls cost 10% of normal input tokens.
            $payload['system'] = [[
                'type'          => 'text',
                'text'          => $systemMessage,
                'cache_control' => ['type' => 'ephemeral'],
            ]];
        }

        try {
            $response = $this->client->post('/v1/messages', ['json' => $payload]);
            $data     = json_decode($response->getBody()->getContents(), true);

            $inputTokens      = $data['usage']['input_tokens'] ?? 0;
            $outputTokens     = $data['usage']['output_tokens'] ?? 0;
            $cacheWriteTokens = $data['usage']['cache_creation_input_tokens'] ?? 0;
            $cacheReadTokens  = $data['usage']['cache_read_input_tokens'] ?? 0;
            // Cache reads cost 10% — count them at full value for usage display, but log savings
            $tokensUsed = $inputTokens + $outputTokens + $cacheWriteTokens + (int)($cacheReadTokens * 0.1);

            return [
                'content'             => $data['content'][0]['text'] ?? '',
                'tokens_used'         => $tokensUsed,
                'model'               => $data['model'] ?? $this->model,
                'stop_reason'         => $data['stop_reason'] ?? 'end_turn',
                'response_time'       => (int) ((microtime(true) - $startTime) * 1000),
                'cache_write_tokens'  => $cacheWriteTokens,
                'cache_read_tokens'   => $cacheReadTokens,
                'raw'                 => $data,
            ];
        } catch (RequestException $e) {
            $msg = $e->getMessage();

            if (str_contains($msg, 'timed out') || str_contains($msg, 'Operation timed out') || str_contains($msg, 'cURL error 28')) {
                throw new \RuntimeException('Claude API timed out. Check your internet connection and try again with a simpler prompt. If this persists, verify your API key in Settings → AI Providers.');
            }
            if (str_contains($msg, 'cURL error 6') || str_contains($msg, 'Could not resolve host')) {
                throw new \RuntimeException('Cannot reach Claude API (api.anthropic.com). Please check your server\'s internet connection.');
            }

            $body = $e->getResponse()
                ? json_decode($e->getResponse()->getBody()->getContents(), true)
                : [];
            $apiMsg = $body['error']['message'] ?? $msg;

            if ($e->getResponse()?->getStatusCode() === 401) {
                throw new \RuntimeException('Invalid Claude API key. Please update your API key in Settings → AI Providers.');
            }
            if ($e->getResponse()?->getStatusCode() === 429) {
                throw new \RuntimeException('Claude API rate limit reached. Please wait a moment and try again.');
            }
            if ($e->getResponse()?->getStatusCode() === 400) {
                throw new \RuntimeException('Claude API rejected the request: ' . $apiMsg . ' — This usually means the conversation history is invalid. Try starting a New Chat.');
            }

            throw new \RuntimeException('Claude API Error: ' . $apiMsg);
        }
    }

    public function stream(array $messages, callable $callback, array $options = []): void
    {
        $systemMessage = null;
        $chatMessages  = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemMessage = $msg['content'];
            } else {
                $chatMessages[] = $msg;
            }
        }

        $payload = [
            'model'      => $options['model'] ?? $this->model,
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'messages'   => $chatMessages,
            'stream'     => true,
        ];

        if ($systemMessage) {
            $payload['system'] = [[
                'type'          => 'text',
                'text'          => $systemMessage,
                'cache_control' => ['type' => 'ephemeral'],
            ]];
        }

        try {
            $response = $this->client->post('/v1/messages', [
                'json'   => $payload,
                'stream' => true,
            ]);
        } catch (RequestException $e) {
            $body   = $e->getResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : [];
            $apiMsg = $body['error']['message'] ?? $e->getMessage();
            $status = $e->getResponse()?->getStatusCode();
            if ($status === 401) throw new \RuntimeException('Invalid Claude API key. Please update it in Settings → AI Providers.');
            if ($status === 429) throw new \RuntimeException('Claude API rate limit reached. Please wait a moment and try again.');
            if ($status === 400) throw new \RuntimeException('Claude API rejected the request: ' . $apiMsg . ' — Try starting a New Chat.');
            throw new \RuntimeException('Claude API Error: ' . $apiMsg);
        }

        // Buffer-based SSE parsing — reads in chunks, processes complete lines.
        // The naive read(1024) approach splits SSE lines at chunk boundaries,
        // causing JSON parse failures. This buffers until a full line is found.
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

                if (isset($data['delta']['text'])) {
                    $callback($data['delta']['text']);
                }
            }
        }
    }

    public function getModels(): array
    {
        return config('ai.providers.claude.models', []);
    }

    public function getName(): string
    {
        return 'Anthropic Claude';
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
