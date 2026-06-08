<?php

namespace App\Services\AI\Pipeline\Agents;

use App\Models\Project;
use App\Services\AI\Pipeline\BaseAgent;

class TaskPlannerAgent extends BaseAgent
{
    public function getName(): string        { return 'Task Planner'; }
    public function getIndex(): int          { return 3; }
    public function getIcon(): string        { return '📅'; }
    public function getDescription(): string { return 'Creating structured task plan with build order'; }

    public function run(array $context, Project $project, callable $saveFile, callable $emit): array
    {
        $modules  = collect($context['product']['modules'] ?? [])->pluck('name')->implode(', ');
        $entities = implode(', ', $context['frs']['main_entities'] ?? []);

        $systemPrompt = <<<SYS
You are a Technical Task Planner. {$this->stackContext()}

Create a concrete build plan. Be specific — name the actual files.

OUTPUT: Respond ONLY with a JSON object in this exact format:
{
  "build_order": ["migrations", "models", "controllers", "requests", "views", "routes", "seeders"],
  "phases": [
    {
      "name": "Database",
      "agent": "DatabaseAgent",
      "tasks": ["Create users migration", "Create products migration", "Create UserSeeder", "Create ProductSeeder"]
    },
    {
      "name": "Backend",
      "agent": "BackendAgent",
      "tasks": ["Create Product model", "Create ProductController", "Create StoreProductRequest"]
    },
    {
      "name": "Frontend",
      "agent": "UiUxDesigner",
      "tasks": ["Create app layout", "Create dashboard view", "Create products/index view", "Create products/create view", "Create preview.html"]
    }
  ],
  "estimated_files": 25
}
SYS;

        $userPrompt = "Modules: {$modules}\nEntities: {$entities}\nApp: {$context['prompt']}";

        $emit(['type' => 'chunk', 'agent_index' => $this->getIndex(), 'text' => 'Planning build tasks...']);

        $raw      = $this->chat($systemPrompt, $userPrompt, 2000);
        $taskPlan = $this->extractJson($raw) ?? [
            'build_order'     => ['migrations', 'models', 'controllers', 'views', 'routes'],
            'phases'          => [],
            'estimated_files' => 20,
        ];

        return ['task_plan' => $taskPlan];
    }
}
