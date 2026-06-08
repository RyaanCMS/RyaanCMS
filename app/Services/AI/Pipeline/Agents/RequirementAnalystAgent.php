<?php

namespace App\Services\AI\Pipeline\Agents;

use App\Models\Project;
use App\Services\AI\Pipeline\BaseAgent;

class RequirementAnalystAgent extends BaseAgent
{
    public function getName(): string        { return 'Requirement Analyst'; }
    public function getIndex(): int          { return 1; }
    public function getIcon(): string        { return '📋'; }
    public function getDescription(): string { return 'Converting prompt into FRS/SRS specification'; }

    public function run(array $context, Project $project, callable $saveFile, callable $emit): array
    {
        $systemPrompt = <<<SYS
You are a Senior Software Requirements Analyst. {$this->stackContext()}

Analyze the user's request and produce a complete Functional Requirements Specification (FRS).

OUTPUT: Respond ONLY with a JSON object (no markdown, no explanation) in this exact format:
{
  "app_name": "Short descriptive app name",
  "summary": "1-2 sentence description",
  "functional_requirements": ["Users can...", "Admins can..."],
  "non_functional_requirements": ["Responsive design", "Secure authentication"],
  "user_roles": ["admin", "user"],
  "main_entities": ["User", "Product", "Order"],
  "key_workflows": ["User registers → verifies → logs in", "Admin creates product → user orders"],
  "pages_needed": ["login", "register", "dashboard", "products/index", "products/create"],
  "auth_required": true,
  "has_admin_panel": true
}
SYS;

        $userPrompt = "Build this application:\n\n" . $context['prompt'];

        $emit(['type' => 'chunk', 'agent_index' => $this->getIndex(), 'text' => 'Analyzing requirements...']);

        $raw = $this->chat($systemPrompt, $userPrompt, 2000);
        $frs = $this->extractJson($raw) ?? [
            'app_name'                    => $project->name,
            'summary'                     => $context['prompt'],
            'functional_requirements'     => ['CRUD operations for main entities'],
            'non_functional_requirements' => ['Responsive', 'Secure'],
            'user_roles'                  => ['admin', 'user'],
            'main_entities'               => ['User'],
            'key_workflows'               => [],
            'pages_needed'                => ['dashboard', 'login', 'register'],
            'auth_required'               => true,
            'has_admin_panel'             => true,
        ];

        return ['frs' => $frs];
    }
}
