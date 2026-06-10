<?php

namespace App\Services\AI;

use App\Models\AIProvider as AIProviderModel;
use App\Models\User;
use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Providers\ClaudeProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\KeyFailoverProvider;
use App\Services\AI\Providers\MistralProvider;
use App\Services\AI\Providers\OllamaProvider;
use App\Services\AI\Providers\OpenAIProvider;
use Illuminate\Support\Facades\Auth;

class AIManager
{
    protected array $providers = [];

    protected array $driverMap = [
        'claude'     => ClaudeProvider::class,
        'openai'     => OpenAIProvider::class,
        'gemini'     => GeminiProvider::class,
        'mistral'    => MistralProvider::class,
        'ollama'     => OllamaProvider::class,
        // OpenAI-compatible providers — reuse OpenAIProvider
        'groq'       => OpenAIProvider::class,
        'grok'       => OpenAIProvider::class,
        'deepseek'   => OpenAIProvider::class,
        'perplexity' => OpenAIProvider::class,
        'openrouter' => OpenAIProvider::class,
        'together'   => OpenAIProvider::class,
        'fireworks'  => OpenAIProvider::class,
        'cerebras'   => OpenAIProvider::class,
    ];

    public function provider(?string $name = null, ?User $user = null): AIProviderInterface
    {
        $user ??= Auth::user();

        // Try to load from user's saved providers
        if ($user && $name) {
            $savedProvider = $user->aiProviders()
                ->where('provider', $name)
                ->where('is_active', true)
                ->first();

            if ($savedProvider) {
                if (!isset($this->driverMap[$savedProvider->provider])) {
                    throw new \RuntimeException("No driver found for AI provider [{$savedProvider->provider}].");
                }

                $instance = $this->makeFailoverProvider($savedProvider);
                if ($instance->isConfigured()) {
                    return $instance;
                }
                // Record exists but the key is missing (e.g. was never saved or APP_KEY changed).
                // Mark it inactive so it stops appearing as "connected" and give a clear message.
                $savedProvider->update(['is_active' => false]);
                throw new \RuntimeException(
                    "The \"{$savedProvider->name}\" provider has no saved credentials. " .
                    "Please go to Settings → AI Providers, click Update next to it, and paste your API Key."
                );
            }
        }

        // Try user's default provider
        if ($user && !$name) {
            $defaultProvider = $user->defaultAIProvider;
            if ($defaultProvider) {
                return $this->makeFailoverProvider($defaultProvider);
            }
        }

        // Fall back to system config — only for providers that don't need a user key
        $name ??= config('ai.default', 'claude');
        $instance = $this->makeFromConfig($name);

        if (!$instance->isConfigured()) {
            throw new \RuntimeException(
                "The \"{$name}\" provider is not configured. Please add your credentials in Settings → AI Providers."
            );
        }

        return $instance;
    }

    public function providerFallbackCandidates(?string $name = null, ?User $user = null): array
    {
        $user ??= Auth::user();
        $candidates = collect();

        if ($user) {
            $savedProviders = $user->aiProviders()
                ->where('is_active', true)
                ->get();

            if ($name) {
                $selected = $savedProviders->firstWhere('provider', $name);
                if ($selected) {
                    $candidates->push($selected);
                }
            }

            $default = $savedProviders->firstWhere('is_default', true);
            if ($default && !$candidates->contains('id', $default->id)) {
                $candidates->push($default);
            }

            foreach ($savedProviders->sortByDesc('last_used_at') as $savedProvider) {
                if (!$candidates->contains('id', $savedProvider->id)) {
                    $candidates->push($savedProvider);
                }
            }
        }

        if ($candidates->isEmpty()) {
            $configName = $name ?: config('ai.default', 'claude');
            return [[
                'provider' => $configName,
                'name'     => config("ai.providers.{$configName}.name", ucfirst($configName)),
                'model'    => null,
                'driver'   => $this->makeFromConfig($configName),
            ]];
        }

        return $candidates
            ->map(function (AIProviderModel $provider) {
                if (!isset($this->driverMap[$provider->provider])) {
                    return null;
                }

                $driver = $this->makeFailoverProvider($provider);
                if (!$driver->isConfigured()) {
                    return null;
                }

                return [
                    'provider'     => $provider->provider,
                    'name'         => $provider->name,
                    'model'        => $provider->default_model,
                    'model_record' => $provider,
                    'driver'       => $driver,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function makeFailoverProvider(AIProviderModel $model): AIProviderInterface
    {
        return new KeyFailoverProvider($this, $model);
    }

    public function makeFromModel(AIProviderModel $model): AIProviderInterface
    {
        $config = config("ai.providers.{$model->provider}", []);
        $config['default_model'] = $model->default_model ?? ($config['default_model'] ?? null);

        if ($model->api_url) {
            $config['api_url'] = $model->api_url;
        }

        // Prefer the primary key from ai_provider_keys; fall back to ai_providers.api_key
        $primaryKey = $model->activeKeysForFailover()->first();
        if ($primaryKey) {
            $config['api_key'] = $primaryKey->getDecryptedApiKey() ?? '';
            $primaryKey->markAsUsed();
        } else {
            $config['api_key'] = $model->getDecryptedApiKey() ?? $config['api_key'] ?? '';
            $model->markAsUsed();
        }

        return $this->makeDriver($model->provider, $config);
    }

    /**
     * Try an AI call with automatic key failover.
     * Rotates through all active keys when a key-related error occurs.
     */
    public function withKeyFallback(AIProviderModel $model, callable $call, ?callable $canRetry = null): mixed
    {
        $activeKeys = $model->activeKeysForFailover()->get();
        $baseConfig = config("ai.providers.{$model->provider}", []);
        $baseConfig['default_model'] = $model->default_model ?? ($baseConfig['default_model'] ?? null);
        if ($model->api_url) {
            $baseConfig['api_url'] = $model->api_url;
        }

        // Build ordered list: managed keys first, then fall back to legacy api_providers.api_key
        $keyAttempts = $activeKeys->map(fn($k) => ['key_model' => $k, 'api_key' => $k->getDecryptedApiKey()])->all();

        if (empty($keyAttempts)) {
            $legacyKey = $model->getDecryptedApiKey();
            $keyAttempts = [['key_model' => null, 'api_key' => $legacyKey]];
        }

        $lastException = null;

        foreach ($keyAttempts as $attempt) {
            if (empty($attempt['api_key'])) continue;

            try {
                $config = array_merge($baseConfig, ['api_key' => $attempt['api_key']]);
                $provider = $this->makeDriver($model->provider, $config);
                $result = $call($provider);

                if ($attempt['key_model']) {
                    $attempt['key_model']->resetFailures();
                    $attempt['key_model']->markAsUsed();
                } else {
                    $model->markAsUsed();
                }

                return $result;
            } catch (\Throwable $e) {
                $lastException = $e;

                if ($this->isKeyRelatedError($e)) {
                    if ($canRetry && !$canRetry($e)) {
                        throw $e;
                    }

                    if ($attempt['key_model']) {
                        $attempt['key_model']->recordFailure();
                    }
                    continue;
                }

                throw $e;
            }
        }

        throw $lastException ?? new \RuntimeException(
            "All API keys for {$model->name} are unavailable or exhausted."
        );
    }

    public function isKeyRelatedError(\Throwable $e): bool
    {
        $msg = strtolower($e->getMessage());
        $phrases = [
            'invalid api key', 'incorrect api key', 'quota exceeded',
            'insufficient_quota', 'insufficient credit', 'rate limit exceeded',
            'authentication', 'unauthorized', 'api_key_invalid',
            'credits exhausted', 'billing', 'payment required',
            'no credit', 'no credits', 'credit exhausted', 'balance exhausted',
            'insufficient balance', 'billing hard limit', 'usage limit',
            'too many requests', 'resource exhausted', 'resource_exhausted',
            'rate_limit_exceeded', 'rate limit reached',
        ];
        foreach ($phrases as $phrase) {
            if (str_contains($msg, $phrase)) return true;
        }
        $code = method_exists($e, 'getCode') ? $e->getCode() : 0;
        return in_array($code, [401, 402, 403, 429]);
    }

    public function makeFromConfig(string $name): AIProviderInterface
    {
        $config = config("ai.providers.{$name}");

        if (!$config) {
            throw new \InvalidArgumentException("AI provider [{$name}] is not configured.");
        }

        return $this->makeDriver($name, $config);
    }

    protected function makeDriver(string $name, array $config): AIProviderInterface
    {
        $driverClass = $this->driverMap[$name] ?? null;

        if (!$driverClass) {
            throw new \InvalidArgumentException("No driver found for AI provider [{$name}].");
        }

        return new $driverClass($config);
    }

    public function getAvailableProviders(): array
    {
        return array_keys($this->driverMap);
    }

    public function getProviderConfig(string $name): array
    {
        return config("ai.providers.{$name}", []);
    }
}
