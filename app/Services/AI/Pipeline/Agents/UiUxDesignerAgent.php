<?php

namespace App\Services\AI\Pipeline\Agents;

use App\Models\Project;
use App\Services\AI\DesignVariantService;
use App\Services\AI\Pipeline\BaseAgent;

class UiUxDesignerAgent extends BaseAgent
{
    public function getName(): string        { return 'UI/UX Designer'; }
    public function getIndex(): int          { return 7; }
    public function getIcon(): string        { return '🎨'; }
    public function getDescription(): string { return 'Creating views, layouts, and interactive preview'; }

    public function run(array $context, Project $project, callable $saveFile, callable $emit): array
    {
        $appName    = $context['frs']['app_name'] ?? $project->name;
        $modules    = json_encode($context['product']['modules'] ?? [], JSON_PRETTY_PRINT);
        $navigation = json_encode($context['product']['navigation'] ?? [], JSON_PRETTY_PRINT);
        $pages      = implode(', ', $context['frs']['pages_needed'] ?? ['dashboard', 'login', 'register']);

        // Pick a fresh random design variant for this pipeline run
        $design       = (new DesignVariantService())->pick();
        $designBlock  = $design['prompt_block'];

        $systemPrompt = <<<SYS
You are a Senior UI/UX Designer and Laravel Blade developer. {$this->stackContext()}

Generate beautiful Blade views using Tailwind CSS (CDN) and Alpine.js.

{$designBlock}

RULES:
- Apply the DESIGN SYSTEM above exactly — use the specified hex colors, layout, card style, button style.
- Layout file: resources/views/components/app-layout.blade.php
  - Structure matches the specified Layout Pattern
  - Use the palette bg/surface/border/text hex values via inline style=""
  - Alpine.js x-data for sidebar/menu toggle
- Auth views: resources/views/auth/login.blade.php, auth/register.blade.php
  - Standalone pages (no layout component), centered card matching the card style
- Dashboard: resources/views/dashboard.blade.php
  - Use <x-app-layout> component
  - Show stat cards using the specified card style
- Module views (for each module in the modules list):
  - resources/views/{route_prefix}/index.blade.php (table with pagination, create button, delete form)
  - resources/views/{route_prefix}/create.blade.php (form with all fields)
  - resources/views/{route_prefix}/edit.blade.php (form pre-filled via \$model)
- preview.html: A STANDALONE single-file HTML app (no server needed)
  - Uses Tailwind CDN, Alpine.js CDN
  - Showcases the chosen design system prominently
  - Must have <html>, <head>, <body>, </html> tags
  - Must define window.app = function() { return { ... } } for Alpine

OUTPUT: Respond ONLY with a JSON object:
{
  "files": [
    {"path": "resources/views/components/app-layout.blade.php", "content": "..."},
    {"path": "resources/views/auth/login.blade.php", "content": "..."},
    {"path": "resources/views/auth/register.blade.php", "content": "..."},
    {"path": "resources/views/dashboard.blade.php", "content": "..."},
    {"path": "resources/views/{module}/index.blade.php", "content": "..."},
    {"path": "preview.html", "content": "<!DOCTYPE html>..."}
  ]
}
SYS;

        $userPrompt = <<<PROMPT
App name: {$appName}
Pages needed: {$pages}

Navigation: {$navigation}

Modules: {$modules}

App description: {$context['prompt']}

Generate the layout, auth views, dashboard, all module CRUD views, and preview.html.
Keep views clean, professional, and dark-themed.
PROMPT;

        $emit(['type' => 'chunk', 'agent_index' => $this->getIndex(), 'text' => 'Designing UI components and views...']);

        $raw    = $this->chat($systemPrompt, $userPrompt, 14000);
        $parsed = $this->extractJson($raw);
        $files  = $parsed['files'] ?? [];

        $savedPaths = [];
        foreach ($files as $file) {
            if (empty($file['path']) || empty($file['content'])) continue;
            $saved = $saveFile($project, $file['path'], $file['content']);
            if ($saved) {
                $savedPaths[] = $saved['path'];
                $emit(['type' => 'file_saved', 'agent_index' => $this->getIndex(), 'path' => $saved['path']]);
            }
        }

        return ['ui_files' => $savedPaths];
    }
}
