<?php

namespace App\Services\AI\Contracts;

interface AIProviderInterface
{
    public function chat(array $messages, array $options = []): array;

    public function stream(array $messages, callable $callback, array $options = []): void;

    public function getModels(): array;

    public function getName(): string;

    public function getDefaultModel(): string;

    public function isConfigured(): bool;
}
