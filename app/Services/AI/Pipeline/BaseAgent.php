<?php

namespace App\Services\AI\Pipeline;

use App\Models\User;
use App\Services\AI\AIManager;
use App\Services\AI\Pipeline\Contracts\AgentInterface;

abstract class BaseAgent implements AgentInterface
{
    public function __construct(
        protected AIManager $aiManager,
        protected ?string   $providerName = null,
        protected ?string   $model        = null,
        protected ?User     $user         = null,
    ) {}

    /**
     * Call AI synchronously (non-streaming). Returns the raw text response.
     */
    protected function chat(string $systemPrompt, string $userPrompt, int $maxTokens = 4000): string
    {
        $candidates = $this->aiManager->providerFallbackCandidates($this->providerName, $this->user);

        if (empty($candidates)) {
            throw new \RuntimeException('No AI provider configured for pipeline agent: ' . $this->getName());
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $userPrompt],
        ];

        $options = ['max_tokens' => $maxTokens];
        if ($this->model) {
            $options['model'] = $this->model;
        }

        $lastError = null;
        foreach ($candidates as $candidate) {
            try {
                $response = $candidate['driver']->chat($messages, $options);
                return $response['content'] ?? '';
            } catch (\Throwable $e) {
                $lastError = $e;
            }
        }

        throw $lastError ?? new \RuntimeException('All AI providers failed for agent: ' . $this->getName());
    }

    /**
     * Extract JSON from AI response. Handles markdown code blocks and bare JSON.
     */
    protected function extractJson(string $content): ?array
    {
        // Strip markdown code fences
        if (preg_match('/```(?:json)?\s*([\s\S]+?)\s*```/i', $content, $m)) {
            $decoded = json_decode(trim($m[1]), true);
            if (is_array($decoded)) return $decoded;
        }

        // Try direct parse
        $decoded = json_decode(trim($content), true);
        if (is_array($decoded)) return $decoded;

        // Find outermost JSON object or array
        if (preg_match('/(\{[\s\S]*\}|\[[\s\S]*\])/s', $content, $m)) {
            $decoded = json_decode($m[1], true);
            if (is_array($decoded)) return $decoded;
        }

        return null;
    }

    /**
     * Shared stack tech description injected into every agent's system prompt.
     */
    protected function stackContext(): string
    {
        return 'Tech stack: Laravel 11, PHP 8.2+, MySQL, Blade templates, Alpine.js, Tailwind CSS (CDN), dark theme UI.';
    }
}
