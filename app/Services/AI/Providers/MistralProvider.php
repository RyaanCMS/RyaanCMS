<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MistralProvider implements AIProviderInterface
{
    protected Client $client;
    protected string $apiKey;
    protected string $model;
    protected int $maxTokens;

    public function __construct(array $config)
    {
        $this->apiKey    = $config['api_key'] ?? '';
        $this->model     = $config['default_model'] ?? 'mistral-large-latest';
        $this->maxTokens = $config['max_tokens'] ?? 4096;

        $this->client = new Client([
            'base_uri' => $config['api_url'] ?? 'https://api.mistral.ai/v1',
            'timeout'  => 120,
            'headers'  => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type'  => 'application/json',
            ],
        ]);
    }

    public function chat(array $messages, array $options = []): array
    {
        $startTime = microtime(true);

        try {
            $response = $this->client->post('/v1/chat/completions', [
                'json' => [
                    'model'      => $options['model'] ?? $this->model,
                    'messages'   => $messages,
                    'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'content'       => $data['choices'][0]['message']['content'] ?? '',
                'tokens_used'   => $data['usage']['total_tokens'] ?? 0,
                'model'         => $data['model'] ?? $this->model,
                'response_time' => (int) ((microtime(true) - $startTime) * 1000),
                'raw'           => $data,
            ];
        } catch (RequestException $e) {
            $body = $e->getResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : [];
            throw new \RuntimeException(
                'Mistral API Error: '.($body['message'] ?? $e->getMessage())
            );
        }
    }

    public function stream(array $messages, callable $callback, array $options = []): void
    {
        try {
            $response = $this->client->post('/v1/chat/completions', [
                'json'   => [
                    'model'      => $options['model'] ?? $this->model,
                    'messages'   => $messages,
                    'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
                    'stream'     => true,
                ],
                'stream' => true,
            ]);
        } catch (RequestException $e) {
            $body   = $e->getResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : [];
            $apiMsg = $body['message'] ?? $body['error']['message'] ?? $e->getMessage();
            $status = $e->getResponse()?->getStatusCode();
            if ($status === 401) throw new \RuntimeException('Invalid Mistral API key. Please update it in Settings → AI Providers.');
            if ($status === 429) throw new \RuntimeException('Mistral rate limit reached. Please wait and try again.');
            throw new \RuntimeException('Mistral API Error: ' . $apiMsg);
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

                $data    = json_decode($jsonStr, true);
                $content = $data['choices'][0]['delta']['content'] ?? '';
                if ($content) $callback($content);
            }
        }
    }

    public function getModels(): array
    {
        return config('ai.providers.mistral.models', []);
    }

    public function getName(): string
    {
        return 'Mistral AI';
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
