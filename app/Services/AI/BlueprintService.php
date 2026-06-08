<?php

namespace App\Services\AI;

use App\Models\Project;

/**
 * BlueprintService — Phase 1 & 2 of Blueprint-Driven Development
 *
 * Phase 1 (Discovery): User says "Create Uber for Laundry" → tiny AI call (~300 tokens)
 *                      → returns structured blueprint JSON. No code generated.
 *
 * Phase 2 (Matching):  Blueprint app_type + features → matched domain packs.
 *                      Domain packs supply pre-defined entity schemas (zero AI cost).
 */
class BlueprintService
{
    /**
     * Run AI Discovery: convert free-text description into a structured blueprint.
     * Uses the smallest possible AI call — system prompt ~200 tokens, output ~300 tokens max.
     * This replaces the "generate everything" 8 000-token prompt with a planning-only call.
     */
    public function discover(string $description, $aiProvider): array
    {
        $messages = [
            ['role' => 'system',  'content' => $this->discoverySystemPrompt()],
            ['role' => 'user',    'content' => $description],
        ];

        $response = $aiProvider->chat($messages, ['max_tokens' => 600]);
        $content  = $response['content'] ?? '{}';

        // Strip markdown fences if the model wrapped the JSON
        $content = preg_replace('/^```(?:json)?\s*/m', '', $content);
        $content = preg_replace('/\s*```\s*$/m', '', $content);
        $content = trim($content);

        // Extract the first {...} block
        if (!str_starts_with($content, '{')) {
            preg_match('/\{.*\}/s', $content, $m);
            $content = $m[0] ?? '{}';
        }

        $blueprint = json_decode($content, true);
        if (!is_array($blueprint)) {
            $blueprint = ['app_type' => 'custom', 'name' => 'My App', 'entities' => []];
        }

        // Ensure required keys exist
        $blueprint = array_merge([
            'app_type'          => 'custom',
            'name'              => 'My Application',
            'industry'          => '',
            'business_model'    => 'b2c',
            'platform'          => ['web'],
            'users'             => ['admin', 'user'],
            'features'          => [],
            'entities'          => [],
            'integrations'      => [],
            'priority_entities' => [],
        ], $blueprint);

        // Enrich: match domain packs and merge their default entities
        $blueprint['matched_packs']    = $this->matchDomainPacks($blueprint);
        $blueprint['suggested_entities'] = $this->suggestEntities($blueprint);
        $blueprint['tokens_used']      = $response['tokens_used'] ?? 0;

        return $blueprint;
    }

    /**
     * Store blueprint as JSON on the project.
     */
    public function store(Project $project, array $blueprint): void
    {
        $project->update(['blueprint' => $blueprint]);
    }

    /**
     * Retrieve project blueprint.
     */
    public function get(Project $project): ?array
    {
        return $project->blueprint;
    }

    /**
     * Return the condensed blueprint summary to inject into AI code-gen prompts.
     * Much cheaper than injecting all project files — gives AI the architectural context.
     */
    public function toPromptContext(Project $project): string
    {
        $bp = $this->get($project);
        if (!$bp) return '';

        $entities = collect($bp['entities'] ?? [])
            ->map(fn($e) => '  • ' . ($e['name'] ?? ''))
            ->implode("\n");

        $features = implode(', ', $bp['features'] ?? []);
        $packs    = implode(', ', $bp['matched_packs'] ?? []);

        return <<<CTX
PROJECT BLUEPRINT:
  App Type   : {$bp['app_type']}
  Name       : {$bp['name']}
  Platform   : {$this->join($bp['platform'] ?? [])}
  Users      : {$this->join($bp['users'] ?? [])}
  Features   : {$features}
  Domain Packs: {$packs}
  Entities   :
{$entities}

Build exactly what the blueprint defines. Do not invent extra entities or features.
CTX;
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Match blueprint fields against domain pack trigger keywords.
     */
    private function matchDomainPacks(array $blueprint): array
    {
        $packs    = config('domain_packs', []);
        $appType  = strtolower($blueprint['app_type'] ?? '');
        $features = array_map('strtolower', $blueprint['features'] ?? []);
        $industry = strtolower($blueprint['industry'] ?? '');
        $haystack = $appType . ' ' . $industry . ' ' . implode(' ', $features);

        $matched = [];

        // Exact type match
        if (isset($packs[$appType])) {
            $matched[] = $appType;
        }

        // Keyword matching across all packs
        foreach ($packs as $key => $pack) {
            if (in_array($key, $matched)) continue;
            foreach ($pack['trigger_keywords'] ?? [] as $kw) {
                if (str_contains($haystack, $kw)) {
                    $matched[] = $key;
                    break;
                }
            }
        }

        return array_unique(array_slice($matched, 0, 3));
    }

    /**
     * Suggest entities by merging blueprint entities + domain pack defaults,
     * de-duplicating by name.
     */
    private function suggestEntities(array $blueprint): array
    {
        $packs      = config('domain_packs', []);
        $existing   = collect($blueprint['entities'] ?? [])->keyBy(fn($e) => strtolower($e['name'] ?? ''));
        $suggestions = $existing->toArray();

        foreach ($blueprint['matched_packs'] ?? [] as $packKey) {
            $pack = $packs[$packKey] ?? null;
            if (!$pack) continue;
            foreach ($pack['entities'] ?? [] as $entity) {
                $key = strtolower($entity['name'] ?? '');
                if (!isset($suggestions[$key])) {
                    $suggestions[$key] = $entity;
                }
            }
        }

        return array_values($suggestions);
    }

    private function join(array $arr): string
    {
        return implode(', ', $arr);
    }

    /**
     * Ultra-compact discovery system prompt (~200 tokens).
     * AI ONLY extracts structure — no code, no prose.
     */
    private function discoverySystemPrompt(): string
    {
        return <<<'PROMPT'
You are a software requirements analyst. Extract a structured blueprint from the user's description.
Return ONLY valid JSON — no prose, no markdown, just the JSON object:

{
  "app_type": "crm|erp|marketplace|booking|lms|hospital|restaurant|ecommerce|saas|hrm|accounting|school|inventory|custom",
  "name": "Short app name",
  "industry": "industry",
  "business_model": "b2b|b2c|marketplace|saas|internal",
  "platform": ["web","mobile"],
  "users": ["admin","customer","vendor"],
  "features": ["feature1","feature2"],
  "entities": [
    {
      "name": "EntityName",
      "fields": [
        {"name":"field_name","type":"string|integer|decimal|boolean|text|date|datetime|enum|json","required":true,"label":"Human Label","options":["for enum only"]}
      ]
    }
  ],
  "integrations": ["stripe","maps"],
  "priority_entities": ["Entity1"]
}
PROMPT;
    }
}
