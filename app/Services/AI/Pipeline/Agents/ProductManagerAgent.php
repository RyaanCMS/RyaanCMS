<?php

namespace App\Services\AI\Pipeline\Agents;

use App\Models\Project;
use App\Services\AI\Pipeline\BaseAgent;

class ProductManagerAgent extends BaseAgent
{
    public function getName(): string        { return 'Product Manager'; }
    public function getIndex(): int          { return 2; }
    public function getIcon(): string        { return '🗂️'; }
    public function getDescription(): string { return 'Defining modules, user stories, and navigation'; }

    public function run(array $context, Project $project, callable $saveFile, callable $emit): array
    {
        $frs = $context['frs'] ?? [];
        $frsJson = json_encode($frs, JSON_PRETTY_PRINT);

        $systemPrompt = <<<SYS
You are a Product Manager. {$this->stackContext()}

Based on the FRS, define the product structure.

OUTPUT: Respond ONLY with a JSON object in this exact format:
{
  "modules": [
    {
      "name": "Products",
      "route_prefix": "products",
      "controller": "ProductController",
      "model": "Product",
      "operations": ["index", "create", "store", "edit", "update", "destroy", "show"],
      "fields": ["name", "description", "price", "status"]
    }
  ],
  "navigation": [
    {"label": "Dashboard", "route": "dashboard", "icon": "home"},
    {"label": "Products", "route": "products.index", "icon": "box"}
  ],
  "user_stories": [
    "As admin, I can manage all products",
    "As user, I can view product catalog"
  ],
  "role_permissions": {
    "admin": ["*"],
    "user": ["products.index", "products.show", "dashboard"]
  }
}
SYS;

        $userPrompt = "FRS:\n{$frsJson}\n\nDefine the product structure for: {$context['prompt']}";

        $emit(['type' => 'chunk', 'agent_index' => $this->getIndex(), 'text' => 'Defining modules and user stories...']);

        $raw     = $this->chat($systemPrompt, $userPrompt, 3000);
        $product = $this->extractJson($raw) ?? [
            'modules'          => [],
            'navigation'       => [['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home']],
            'user_stories'     => [],
            'role_permissions' => ['admin' => ['*'], 'user' => ['dashboard']],
        ];

        return ['product' => $product];
    }
}
