<?php

namespace App\Services\AI\Pipeline\Agents;

use App\Models\Project;
use App\Services\AI\Pipeline\BaseAgent;

class TestingAgent extends BaseAgent
{
    public function getName(): string        { return 'Testing Agent'; }
    public function getIndex(): int          { return 8; }
    public function getIcon(): string        { return '🧪'; }
    public function getDescription(): string { return 'Generating test scenarios and feature tests'; }

    public function run(array $context, Project $project, callable $saveFile, callable $emit): array
    {
        $appName     = $context['frs']['app_name'] ?? $project->name;
        $workflows   = implode("\n- ", $context['frs']['key_workflows'] ?? ['User logs in', 'User views dashboard']);
        $modules     = collect($context['product']['modules'] ?? [])->pluck('name')->implode(', ');
        $backendFiles = implode(', ', $context['backend_files'] ?? []);

        $systemPrompt = <<<SYS
You are a QA Engineer specializing in Laravel testing. {$this->stackContext()}

Generate Laravel Feature tests using PHPUnit.

RULES:
- Use tests/Feature/ directory
- Use RefreshDatabase trait
- Test authentication flows
- Test CRUD for each major module
- Use actingAs(\$user) for authenticated tests
- Test both happy path and validation failures
- Generate a validation_report.json summarizing what was tested

OUTPUT: Respond ONLY with a JSON object:
{
  "files": [
    {
      "path": "tests/Feature/AuthTest.php",
      "content": "<?php\n\nnamespace Tests\\Feature;\n\nuse App\\Models\\User;\nuse Illuminate\\Foundation\\Testing\\RefreshDatabase;\nuse Tests\\TestCase;\n\nclass AuthTest extends TestCase {\n    use RefreshDatabase;\n    \n    public function test_user_can_login(): void {\n        \$user = User::factory()->create();\n        \$response = \$this->post('/login', ['email' => \$user->email, 'password' => 'password']);\n        \$response->assertRedirect();\n    }\n}"
    },
    {
      "path": "tests/Feature/validation_report.json",
      "content": "{\"tested_flows\": [], \"coverage\": \"basic\"}"
    }
  ],
  "test_summary": {
    "total_tests": 5,
    "flows_tested": ["login", "register", "CRUD"],
    "coverage": "basic"
  }
}
SYS;

        $userPrompt = <<<PROMPT
App: {$appName}
Modules: {$modules}
Key workflows:
- {$workflows}

Backend files generated: {$backendFiles}

Generate Feature tests covering: auth flows, CRUD operations for each module, and a validation_report.json.
PROMPT;

        $emit(['type' => 'chunk', 'agent_index' => $this->getIndex(), 'text' => 'Writing test scenarios...']);

        $raw    = $this->chat($systemPrompt, $userPrompt, 6000);
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

        return [
            'test_files'   => $savedPaths,
            'test_summary' => $parsed['test_summary'] ?? ['total_tests' => 0, 'coverage' => 'none'],
        ];
    }
}
