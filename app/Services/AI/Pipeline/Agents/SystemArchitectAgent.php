<?php

namespace App\Services\AI\Pipeline\Agents;

use App\Models\Project;
use App\Services\AI\Pipeline\BaseAgent;

class SystemArchitectAgent extends BaseAgent
{
    public function getName(): string        { return 'System Architect'; }
    public function getIndex(): int          { return 4; }
    public function getIcon(): string        { return '🏗️'; }
    public function getDescription(): string { return 'Designing folder structure and API patterns'; }

    public function run(array $context, Project $project, callable $saveFile, callable $emit): array
    {
        $modules  = json_encode($context['product']['modules'] ?? [], JSON_PRETTY_PRINT);
        $entities = implode(', ', $context['frs']['main_entities'] ?? []);

        $systemPrompt = <<<SYS
You are a Laravel System Architect. {$this->stackContext()}

Design the complete file architecture for this application.

RULES:
- Models go in app/Models/
- Controllers go in app/Http/Controllers/
- Views go in resources/views/ (NOT in layouts/ subdirectory — put layout in resources/views/components/)
- Migrations go in database/migrations/ with timestamp prefix
- Seeders go in database/seeders/
- Form requests go in app/Http/Requests/

OUTPUT: Respond ONLY with a JSON object in this exact format:
{
  "conventions": {
    "model_namespace": "App\\Models",
    "controller_suffix": "Controller",
    "view_prefix": "resources/views",
    "layout_file": "resources/views/components/app-layout.blade.php"
  },
  "file_map": {
    "migrations": ["database/migrations/2026_06_07_000001_create_users_table.php"],
    "models": ["app/Models/User.php", "app/Models/Product.php"],
    "controllers": ["app/Http/Controllers/ProductController.php"],
    "requests": ["app/Http/Requests/StoreProductRequest.php"],
    "views": ["resources/views/dashboard.blade.php", "resources/views/products/index.blade.php"],
    "seeders": ["database/seeders/ProductSeeder.php"]
  },
  "api_patterns": ["RESTful resource routes", "Route model binding", "Policy authorization"],
  "auth_middleware": "auth",
  "route_file": "routes/web.php"
}
SYS;

        $userPrompt = "Entities: {$entities}\nModules:\n{$modules}\n\nApp: {$context['prompt']}";

        $emit(['type' => 'chunk', 'agent_index' => $this->getIndex(), 'text' => 'Designing system architecture...']);

        $raw          = $this->chat($systemPrompt, $userPrompt, 3000);
        $architecture = $this->extractJson($raw) ?? [
            'conventions' => [
                'model_namespace' => 'App\\Models',
                'controller_suffix' => 'Controller',
                'view_prefix' => 'resources/views',
                'layout_file' => 'resources/views/components/app-layout.blade.php',
            ],
            'file_map'    => [],
            'api_patterns' => ['RESTful resource routes', 'Route model binding'],
            'auth_middleware' => 'auth',
            'route_file'  => 'routes/web.php',
        ];

        return ['architecture' => $architecture];
    }
}
