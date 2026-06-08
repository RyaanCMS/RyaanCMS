<?php

namespace App\Services\AI\Pipeline\Agents;

use App\Models\Project;
use App\Services\AI\Pipeline\BaseAgent;

class DatabaseAgent extends BaseAgent
{
    public function getName(): string        { return 'Database Agent'; }
    public function getIndex(): int          { return 5; }
    public function getIcon(): string        { return '🗄️'; }
    public function getDescription(): string { return 'Generating migrations and seed data'; }

    public function run(array $context, Project $project, callable $saveFile, callable $emit): array
    {
        $entities = implode(', ', $context['frs']['main_entities'] ?? ['User']);
        $modules  = json_encode($context['product']['modules'] ?? [], JSON_PRETTY_PRINT);

        $systemPrompt = <<<SYS
You are a Laravel Database Engineer. {$this->stackContext()}

Generate complete migration files and seeders for a Laravel 11 application.

RULES:
- Migration filenames: database/migrations/YYYY_MM_DD_HHMMSS_create_{table}_table.php
  Use dates starting from 2026_06_07_000001 incrementing by 1
- Always include id(), timestamps() in every table
- Use proper foreign keys with constrained()->cascadeOnDelete()
- Seeders go in database/seeders/ — include realistic sample data
- Always include a DatabaseSeeder.php that calls all other seeders
- Do NOT recreate the users table (it exists in Laravel by default)

OUTPUT: Respond ONLY with a JSON object in this exact format:
{
  "files": [
    {
      "path": "database/migrations/2026_06_07_000001_create_products_table.php",
      "content": "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration {\n    public function up(): void\n    {\n        Schema::create('products', function (Blueprint \\$table) {\n            \\$table->id();\n            \\$table->string('name');\n            \\$table->text('description')->nullable();\n            \\$table->decimal('price', 10, 2)->default(0);\n            \\$table->enum('status', ['active', 'inactive'])->default('active');\n            \\$table->foreignId('user_id')->constrained()->cascadeOnDelete();\n            \\$table->timestamps();\n        });\n    }\n    public function down(): void { Schema::dropIfExists('products'); }\n};"
    },
    {
      "path": "database/seeders/ProductSeeder.php",
      "content": "<?php\n\nnamespace Database\\Seeders;\n\nuse App\\Models\\Product;\nuse Illuminate\\Database\\Seeder;\n\nclass ProductSeeder extends Seeder {\n    public function run(): void {\n        Product::factory(10)->create();\n    }\n}"
    }
  ]
}
SYS;

        $userPrompt = "Entities to create tables for: {$entities}\n\nModules:\n{$modules}\n\nApp description: {$context['prompt']}";

        $emit(['type' => 'chunk', 'agent_index' => $this->getIndex(), 'text' => 'Generating database migrations...']);

        $raw    = $this->chat($systemPrompt, $userPrompt, 8000);
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

        return ['db_files' => $savedPaths];
    }
}
