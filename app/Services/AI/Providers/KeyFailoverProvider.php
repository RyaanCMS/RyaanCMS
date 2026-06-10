<?php

namespace App\Services\AI\Providers;

use App\Models\AIProvider as AIProviderModel;
use App\Services\AI\AIManager;
use App\Services\AI\Contracts\AIProviderInterface;

class KeyFailoverProvider implements AIProviderInterface
{
    public function __construct(
        protected AIManager $manager,
        protected AIProviderModel $model,
    ) {}

    public function chat(array $messages, array $options = []): array
    {
        return $this->manager->withKeyFallback(
            $this->model,
            fn (AIProviderInterface $provider) => $provider->chat($messages, $options)
        );
    }

    public function stream(array $messages, callable $callback, array $options = []): void
    {
        $emitted = false;

        $this->manager->withKeyFallback(
            $this->model,
            function (AIProviderInterface $provider) use ($messages, $callback, $options, &$emitted) {
                $provider->stream($messages, function (string $chunk) use ($callback, &$emitted) {
                    $emitted = true;
                    $callback($chunk);
                }, $options);
            },
            fn () => !$emitted
        );
    }

    public function getModels(): array
    {
        return config("ai.providers.{$this->model->provider}.models", []);
    }

    public function getName(): string
    {
        return $this->model->name;
    }

    public function getDefaultModel(): string
    {
        return $this->model->default_model
            ?? config("ai.providers.{$this->model->provider}.default_model", '');
    }

    public function isConfigured(): bool
    {
        if (!in_array($this->model->provider, $this->manager->getAvailableProviders(), true)) {
            return false;
        }

        if ($this->model->activeKeysForFailover()
            ->get()
            ->contains(fn ($key) => filled($key->getDecryptedApiKey()))) {
            return true;
        }

        return filled($this->model->getDecryptedApiKey());
    }
}
