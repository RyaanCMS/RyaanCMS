<?php

namespace App\Services\AI\Pipeline\Contracts;

use App\Models\Project;

interface AgentInterface
{
    public function getName(): string;
    public function getIndex(): int;
    public function getIcon(): string;
    public function getDescription(): string;

    /**
     * Run this agent.
     *
     * @param  array    $context   Accumulated pipeline context (read from previous agents)
     * @param  Project  $project   The project being built
     * @param  callable $saveFile  fn(Project, string $path, string $content): ?array
     * @param  callable $emit      fn(array $event): void  — SSE emitter
     * @return array               Partial context to merge (only the keys this agent adds/updates)
     */
    public function run(array $context, Project $project, callable $saveFile, callable $emit): array;
}
