<?php

namespace App\Services\AI;

use App\Models\AIProvider as AIProviderModel;
use App\Models\User;
use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Providers\ClaudeProvider;
use App\Services\AI\Providers\GeminiProvider;
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
                $instance = $this->makeFromModel($savedProvider);
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
                return $this->makeFromModel($defaultProvider);
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
            ->map(fn (AIProviderModel $provider) => [
                'provider' => $provider->provider,
                'name'     => $provider->name,
                'model'    => $provider->default_model,
                'driver'   => $this->makeFromModel($provider),
            ])
            ->filter(fn (array $candidate) => $candidate['driver']->isConfigured())
            ->values()
            ->all();
    }

    public function makeFromModel(AIProviderModel $model): AIProviderInterface
    {
        $config = config("ai.providers.{$model->provider}", []);
        $config['api_key']       = $model->getDecryptedApiKey() ?? $config['api_key'] ?? '';
        $config['default_model'] = $model->default_model ?? $config['default_model'];

        if ($model->api_url) {
            $config['api_url'] = $model->api_url;
        }

        $model->markAsUsed();
        return $this->makeDriver($model->provider, $config);
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
