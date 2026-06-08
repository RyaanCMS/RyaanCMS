<?php

namespace App\Services\AI\Pipeline\Agents;

use App\Models\Project;
use App\Services\AI\Pipeline\BaseAgent;

class BackendAgent extends BaseAgent
{
    public function getName(): string        { return 'Backend Agent'; }
    public function getIndex(): int          { return 6; }
    public function getIcon(): string        { return '⚙️'; }
    public function getDescription(): string { return 'Building controllers, models, routes, and RBAC'; }

    public function run(array $context, Project $project, callable $saveFile, callable $emit): array
    {
        $frsJson      = json_encode($context['frs']     ?? [], JSON_PRETTY_PRINT);
        $modulesJson  = json_encode($context['product']['modules'] ?? [], JSON_PRETTY_PRINT);
        $roles        = implode(', ', array_keys($context['product']['role_permissions'] ?? ['admin' => ['*'], 'user' => []]));
        $dbFiles      = implode(', ', $context['db_files'] ?? []);

        $systemPrompt = <<<SYS
You are a Senior Laravel Backend Developer. {$this->stackContext()}

Generate complete, working Laravel 11 backend code.

RULES:
- Models: use HasFactory, define \$fillable, \$casts, all relationships
- Controllers: resourceful methods (index, create, store, show, edit, update, destroy)
  - index: paginate(15), pass to view
  - store/update: use FormRequest for validation
  - Wrap writes in try/catch, return back()->with() messages
- Routes: generate a routes/generated.php file (do NOT write to routes/web.php — it is protected)
  - Include: Route::middleware(['auth'])->group(function() { ... })
  - Use Route::resource() for CRUD
  - Add a dashboard route and auth routes (login, register, logout)
- Requests: create StoreXxxRequest and UpdateXxxRequest with proper validation rules
- Auth: Use Laravel's built-in Auth facade — middleware('auth') on protected routes
- RBAC: Use a simple \$user->role field (string column on users: 'admin' or 'user')
  - Add a helper isAdmin(): bool on User model
  - Gate::define in AuthServiceProvider OR inline checks in controllers

OUTPUT: Respond ONLY with a JSON object:
{
  "files": [
    {"path": "app/Models/Product.php", "content": "..."},
    {"path": "app/Http/Controllers/ProductController.php", "content": "..."},
    {"path": "app/Http/Requests/StoreProductRequest.php", "content": "..."},
    {"path": "routes/generated.php", "content": "..."}
  ]
}
SYS;

        $userPrompt = <<<PROMPT
FRS: {$frsJson}

Modules: {$modulesJson}

User roles: {$roles}

Already generated DB files (reference only — do NOT regenerate): {$dbFiles}

App description: {$context['prompt']}

Generate ALL models, controllers, form requests, and a routes/generated.php file.
PROMPT;

        $emit(['type' => 'chunk', 'agent_index' => $this->getIndex(), 'text' => 'Building backend code...']);

        $raw    = $this->chat($systemPrompt, $userPrompt, 12000);
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

        return ['backend_files' => $savedPaths];
    }
}
