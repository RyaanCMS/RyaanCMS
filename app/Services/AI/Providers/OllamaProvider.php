<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class OllamaProvider implements AIProviderInterface
{
    protected Client $client;
    protected string $host;
    protected string $model;
    protected int $maxTokens;

    public function __construct(array $config)
    {
        $this->host      = rtrim($config['api_url'] ?? 'http://localhost:11434', '/');
        $this->model     = $config['default_model'] ?? 'llama3.2';
        $this->maxTokens = $config['max_tokens'] ?? 4096;

        $this->client = new Client([
            'base_uri' => $this->host,
            'timeout'  => 300,
            'headers'  => ['Content-Type' => 'application/json'],
        ]);
    }

    public function chat(array $messages, array $options = []): array
    {
        $startTime = microtime(true);

        try {
            $response = $this->client->post('/api/chat', [
                'json' => [
                    'model'    => $options['model'] ?? $this->model,
                    'messages' => $messages,
                    'stream'   => false,
                    'options'  => ['num_predict' => $options['max_tokens'] ?? $this->maxTokens],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'content'       => $data['message']['content'] ?? '',
                'tokens_used'   => ($data['prompt_eval_count'] ?? 0) + ($data['eval_count'] ?? 0),
                'model'         => $data['model'] ?? $this->model,
                'response_time' => (int) ((microtime(true) - $startTime) * 1000),
                'raw'           => $data,
            ];
        } catch (RequestException $e) {
            throw new \RuntimeException(
                'Ollama API Error: '.$e->getMessage().'. Is Ollama running at '.$this->host.'?'
            );
        }
    }

    public function stream(array $messages, callable $callback, array $options = []): void
    {
        try {
            $response = $this->client->post('/api/chat', [
                'json'   => [
                    'model'    => $options['model'] ?? $this->model,
                    'messages' => $messages,
                    'stream'   => true,
                    'options'  => ['num_predict' => $options['max_tokens'] ?? $this->maxTokens],
                ],
                'stream' => true,
            ]);
        } catch (RequestException $e) {
            throw new \RuntimeException(
                'Ollama API Error: ' . $e->getMessage() . '. Is Ollama running at ' . $this->host . '?'
            );
        }

        // Buffer-based NDJSON parsing — prevents JSON errors when read() splits across lines
        $buffer = '';
        $body   = $response->getBody();

        while (!$body->eof()) {
            $buffer .= $body->read(2048);

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line   = rtrim(substr($buffer, 0, $pos), "\r");
                $buffer = substr($buffer, $pos + 1);

                if (!$line) continue;
                $data = json_decode($line, true);
                if (!is_array($data)) continue;

                $content = $data['message']['content'] ?? '';
                if ($content) $callback($content);
                if ($data['done'] ?? false) break 2;
            }
        }
    }

    public function getModels(): array
    {
        try {
            $response = $this->client->get('/api/tags');
            $data     = json_decode($response->getBody()->getContents(), true);
            $models   = [];
            foreach ($data['models'] ?? [] as $model) {
                $name           = $model['name'];
                $models[$name]  = $name.' ('.round($model['size'] / 1073741824, 1).' GB)';
            }
            return $models ?: config('ai.providers.ollama.models', []);
        } catch (\Exception) {
            return config('ai.providers.ollama.models', []);
        }
    }

    public function getName(): string
    {
        return 'Ollama (Local LLM)';
    }

    public function getDefaultModel(): string
    {
        return $this->model;
    }

    public function isConfigured(): bool
    {
        try {
            $this->client->get('/api/tags', ['timeout' => 3]);
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
