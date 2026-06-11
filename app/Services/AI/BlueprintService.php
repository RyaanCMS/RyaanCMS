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
 *
 * Genome Layer:  IntentEngine (0 tokens) + BlueprintGenomeEngine (0 tokens) pre-enrich
 *               the discovery prompt before the AI call — better accuracy, fewer tokens.
 */
class BlueprintService
{
    private IntentEngine $intentEngine;
    private BlueprintGenomeEngine $genomeEngine;

    public function __construct(
        ?IntentEngine $intentEngine = null,
        ?BlueprintGenomeEngine $genomeEngine = null
    ) {
        $this->intentEngine = $intentEngine ?? new IntentEngine();
        $this->genomeEngine = $genomeEngine ?? new BlueprintGenomeEngine();
    }

    /**
     * Run AI Discovery: convert free-text description into a structured blueprint.
     * Uses the smallest possible AI call — system prompt ~200 tokens, output ~300 tokens max.
     * Genome layer runs first (0 tokens) to pre-classify and enrich the prompt.
     */
    public function discover(string $description, $aiProvider): array
    {
        // ── Genome Phase (zero AI tokens) ──────────────────────────────────
        $intent      = $this->intentEngine->detect($description);
        $genome      = $this->genomeEngine->assemble($intent);
        $genomeCtx   = $genome['prompt_context'] ?? '';

        // Prepend genome context to the user message for better AI classification
        $enrichedDescription = $genomeCtx
            ? $genomeCtx . "\n\nUser request: " . $description
            : $description;

        $messages = [
            ['role' => 'system',  'content' => $this->discoverySystemPrompt()],
            ['role' => 'user',    'content' => $enrichedDescription],
        ];

        $response = $aiProvider->chat($messages, ['max_tokens' => 1000]);
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
            'business_domain'   => '',
            'business_model'    => 'b2c',
            'blueprint_level'   => 'professional',
            'requested_outputs' => [],
            'platform'          => ['web'],
            'users'             => ['admin', 'user'],
            'features'          => [],
            'modules'           => [],
            'pages'             => [],
            'sections'          => [],
            'forms'             => [],
            'workflows'         => [],
            'reports'           => [],
            'dashboards'        => [],
            'entities'          => [],
            'integrations'      => [],
            'automations'       => [],
            'ai_rules'          => [],
            'deployment_profile'=> '',
            'priority_entities' => [],
        ], $blueprint);

        $blueprint['app_type'] = $this->normalizeAppType((string) $blueprint['app_type'], $description);
        $blueprint['business_domain'] = $blueprint['business_domain'] ?: $blueprint['industry'];
        $blueprint['requested_outputs'] = $this->normalizeOutputModes($blueprint['requested_outputs'] ?? [], $description, $blueprint);
        $blueprint['blueprint_level'] = $this->inferMaturityLevel($description, (string) $blueprint['blueprint_level']);
        $blueprint['deployment_profile'] = $blueprint['deployment_profile'] ?: $this->inferDeploymentProfile($blueprint);
        $blueprint['pages'] = $this->ensureList($blueprint['pages'], $this->defaultPages($blueprint));
        $blueprint['sections'] = $this->ensureList($blueprint['sections'], $this->defaultSections($blueprint));
        $blueprint['forms'] = $this->ensureList($blueprint['forms'], $this->defaultForms($blueprint));
        $blueprint['dashboards'] = $this->ensureList($blueprint['dashboards'], $this->defaultDashboards($blueprint));
        $blueprint['reports'] = $this->ensureList($blueprint['reports'], $this->defaultReports($blueprint));

        // Enrich: match domain packs and merge their default entities
        $blueprint['matched_packs']      = $this->matchDomainPacks($blueprint);
        $blueprint['features']           = $this->mergePackFeatures($blueprint);
        $blueprint['suggested_entities'] = $this->suggestEntities($blueprint);
        $blueprint['tokens_used']        = $response['tokens_used'] ?? 0;

        // ── Genome enrichment (zero additional tokens) ────────────────────
        // Merge genome-derived modules/roles/workflows/integrations/KPIs
        // without overwriting what the AI returned.
        $blueprint['genome']         = $this->genomeEngine->toArray($intent);
        $blueprint['genome_intent']  = [
            'action'     => $intent['action'],
            'industries' => $intent['industries'],
            'confidence' => $intent['confidence'],
        ];

        // Fill missing modules from genome (AI didn't always list all)
        if (empty($blueprint['modules'])) {
            $blueprint['modules'] = $genome['modules'] ?? [];
        }

        // Merge integrations
        $aiIntegrations     = $blueprint['integrations'] ?? [];
        $genomeIntegrations = $genome['integrations']    ?? [];
        $blueprint['integrations'] = array_values(array_unique(array_merge($aiIntegrations, $genomeIntegrations)));

        // Merge workflows
        $aiWorkflows     = $blueprint['workflows'] ?? [];
        $genomeWorkflows = $genome['workflows']    ?? [];
        $blueprint['workflows'] = array_values(array_unique(array_merge($aiWorkflows, $genomeWorkflows)));

        // Attach KPIs from genome (not in original blueprint schema)
        $blueprint['kpis']  = $genome['kpis']  ?? [];
        $blueprint['roles'] = array_values(array_unique(array_merge(
            $blueprint['users'] ?? [],
            $genome['roles']    ?? []
        )));

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
        $outputs  = $this->summarizeList($bp['requested_outputs'] ?? []);
        $pages    = $this->summarizeList($bp['pages'] ?? []);
        $forms    = $this->summarizeList($bp['forms'] ?? []);
        $reports  = $this->summarizeList($bp['reports'] ?? []);
        $flows    = $this->summarizeList($bp['workflows'] ?? []);
        $domain   = $bp['business_domain'] ?? $bp['industry'] ?? '';
        $level    = $bp['blueprint_level'] ?? 'professional';

        return <<<CTX
PROJECT BLUEPRINT:
  App Type   : {$bp['app_type']}
  Name       : {$bp['name']}
  Domain     : {$domain}
  Level      : {$level}
  Outputs    : {$outputs}
  Platform   : {$this->join($bp['platform'] ?? [])}
  Users      : {$this->join($bp['users'] ?? [])}
  Features   : {$features}
  Domain Packs: {$packs}
  Pages      : {$pages}
  Forms      : {$forms}
  Workflows  : {$flows}
  Reports    : {$reports}
  Entities   :
{$entities}

Build exactly what the blueprint defines. Do not expose blueprint, routing, token, or cost mechanics to the user.
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
        $domain   = strtolower($blueprint['business_domain'] ?? '');
        $haystack = $appType . ' ' . $industry . ' ' . $domain . ' ' . implode(' ', $features);

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

    private function normalizeAppType(string $appType, string $description): string
    {
        $key = strtolower(trim(str_replace([' ', '-'], '_', $appType)));
        $packs = config('domain_packs', []);

        $aliases = [
            'clinic' => 'hospital',
            'diagnostic' => 'hospital',
            'diagnostic_center' => 'hospital',
            'dental' => 'hospital',
            'dental_clinic' => 'hospital',
            'education' => 'school',
            'college' => 'school',
            'university' => 'school',
            'school_erp' => 'school',
            'online_course' => 'lms',
            'course' => 'lms',
            'shop' => 'ecommerce',
            'store' => 'ecommerce',
            'multi_vendor' => 'marketplace',
            'ngo' => 'ngo_charity',
            'charity' => 'ngo_charity',
            'nonprofit' => 'ngo_charity',
            'agency' => 'project_management',
            'property_crm' => 'real_estate',
            'real_estate_crm' => 'real_estate',
            'rental_management' => 'property_rental',
            'hotel' => 'hotel_hospitality',
            'hospitality' => 'hotel_hospitality',
            'news' => 'news_media',
            'blog' => 'news_media',
            'telehealth' => 'telemedicine',
            'virtual_clinic' => 'telemedicine',
        ];

        if (isset($aliases[$key])) return $aliases[$key];
        if (isset($packs[$key])) return $key;

        $haystack = strtolower($key . ' ' . $description);
        $keywordAliases = [
            'hospital' => ['hospital', 'clinic', 'diagnostic', 'dental', 'patient', 'doctor', 'medical'],
            'pharmacy' => ['pharmacy', 'medicine', 'drug store'],
            'school' => ['school', 'college', 'university', 'student', 'teacher'],
            'restaurant' => ['restaurant', 'cafe', 'food delivery', 'kitchen'],
            'ecommerce' => ['ecommerce', 'e-commerce', 'online store', 'shop'],
            'marketplace' => ['marketplace', 'multi vendor', 'two-sided'],
            'real_estate' => ['real estate', 'property sale', 'broker'],
            'property_rental' => ['rental', 'tenant', 'lease'],
            'hotel_hospitality' => ['hotel', 'resort', 'hospitality'],
            'ngo_charity' => ['ngo', 'charity', 'donation', 'beneficiary'],
            'project_management' => ['agency', 'client portal', 'project management'],
        ];

        foreach ($keywordAliases as $pack => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($haystack, $keyword) && isset($packs[$pack])) {
                    return $pack;
                }
            }
        }

        return $key ?: 'custom';
    }

    private function normalizeOutputModes(mixed $modes, string $description, array $blueprint): array
    {
        $modes = is_array($modes) ? $modes : [$modes];
        $normalized = [];
        $map = [
            'app' => 'application',
            'application' => 'application',
            'software' => 'application',
            'system' => 'application',
            'website' => 'website',
            'site' => 'website',
            'homepage' => 'website',
            'home_page' => 'website',
            'landing' => 'landing_page',
            'landing_page' => 'landing_page',
            'portal' => 'portal',
            'dashboard' => 'dashboard',
            'admin' => 'dashboard',
            'panel' => 'dashboard',
            'api' => 'api',
        ];

        foreach ($modes as $mode) {
            $key = strtolower(trim(str_replace([' ', '-'], '_', (string) $mode)));
            if (isset($map[$key])) $normalized[] = $map[$key];
        }

        $lower = strtolower($description);
        $onlyLanding = (str_contains($lower, 'only') || str_contains($lower, 'just')) && str_contains($lower, 'landing');
        $onlyWebsite = (str_contains($lower, 'only') || str_contains($lower, 'just')) && (str_contains($lower, 'website') || str_contains($lower, 'site'));

        if ($onlyLanding) return ['landing_page'];
        if ($onlyWebsite) return ['website', 'landing_page'];

        if (str_contains($lower, 'landing')) $normalized[] = 'landing_page';
        if (str_contains($lower, 'website') || str_contains($lower, 'site') || str_contains($lower, 'homepage')) {
            $normalized[] = 'website';
            $normalized[] = 'landing_page';
        }
        if (str_contains($lower, 'portal')) $normalized[] = 'portal';
        if (str_contains($lower, 'dashboard') || str_contains($lower, 'admin panel')) $normalized[] = 'dashboard';
        if (str_contains($lower, 'api')) $normalized[] = 'api';

        $applicationSignals = [
            'app', 'application', 'system', 'management', 'platform', 'crm', 'erp',
            'saas', 'booking', 'inventory', 'pos', 'software',
        ];
        foreach ($applicationSignals as $signal) {
            if (str_contains($lower, $signal)) {
                array_push($normalized, 'application', 'dashboard', 'portal', 'website', 'landing_page');
                break;
            }
        }

        if (empty($normalized)) {
            $normalized = ['application', 'dashboard', 'website', 'landing_page'];
        }

        $order = ['application', 'dashboard', 'portal', 'website', 'landing_page', 'api'];
        return array_values(array_intersect($order, array_unique($normalized)));
    }

    private function inferMaturityLevel(string $description, string $level): string
    {
        $level = strtolower(trim($level));
        if (in_array($level, ['basic', 'professional', 'enterprise'], true)) return $level;

        $lower = strtolower($description);
        if (str_contains($lower, 'enterprise') || str_contains($lower, 'multi branch') || str_contains($lower, 'multi-branch')) {
            return 'enterprise';
        }
        if (str_contains($lower, 'basic') || str_contains($lower, 'simple') || str_contains($lower, 'mvp')) {
            return 'basic';
        }

        return 'professional';
    }

    private function inferDeploymentProfile(array $blueprint): string
    {
        $outputs = $blueprint['requested_outputs'] ?? [];
        if ($outputs === ['landing_page'] || $outputs === ['website', 'landing_page']) {
            return 'static_or_laravel_public_site';
        }
        if (($blueprint['app_type'] ?? '') === 'saas' || ($blueprint['business_model'] ?? '') === 'saas') {
            return 'multi_tenant_laravel_saas';
        }

        return 'laravel_business_application';
    }

    private function ensureList(mixed $value, array $fallback): array
    {
        if (!is_array($value)) {
            $value = array_filter([(string) $value]);
        }

        $value = array_values(array_filter($value, fn($item) => is_array($item) || trim((string) $item) !== ''));

        return empty($value) ? $fallback : $value;
    }

    private function defaultPages(array $blueprint): array
    {
        $outputs = $blueprint['requested_outputs'] ?? [];
        $pages = [];

        if (in_array('landing_page', $outputs, true)) $pages[] = 'Landing page';
        if (in_array('website', $outputs, true)) {
            $pages = array_merge($pages, ['Home', 'About', 'Services', 'Pricing or packages', 'Contact']);
        }
        if (in_array('application', $outputs, true)) {
            $pages = array_merge($pages, ['Login', 'Register', 'Dashboard', 'Module list/create/edit/show pages']);
        }
        if (in_array('portal', $outputs, true)) $pages[] = 'User portal';
        if (in_array('dashboard', $outputs, true)) $pages[] = 'Analytics dashboard';

        return array_values(array_unique($pages));
    }

    private function defaultSections(array $blueprint): array
    {
        $outputs = $blueprint['requested_outputs'] ?? [];
        if (!array_intersect($outputs, ['website', 'landing_page'])) return [];

        return ['navigation', 'hero', 'services', 'features', 'how_it_works', 'social_proof', 'testimonials', 'faq', 'contact', 'footer'];
    }

    private function defaultForms(array $blueprint): array
    {
        $outputs = $blueprint['requested_outputs'] ?? [];
        $forms = [];

        if (array_intersect($outputs, ['website', 'landing_page'])) $forms[] = 'Contact or lead capture form';
        if (in_array('application', $outputs, true)) $forms[] = 'Create and edit forms for every business entity';
        if (in_array(($blueprint['app_type'] ?? ''), ['hospital', 'booking', 'telemedicine', 'hotel_hospitality'], true)) {
            $forms[] = 'Appointment or booking request form';
        }

        return array_values(array_unique($forms));
    }

    private function defaultDashboards(array $blueprint): array
    {
        $outputs = $blueprint['requested_outputs'] ?? [];
        if (!array_intersect($outputs, ['application', 'dashboard'])) return [];

        return ['Executive KPI dashboard', 'Recent activity dashboard', 'Operational status dashboard'];
    }

    private function defaultReports(array $blueprint): array
    {
        $outputs = $blueprint['requested_outputs'] ?? [];
        if (!array_intersect($outputs, ['application', 'dashboard'])) return [];

        return ['Daily summary report', 'Status distribution report', 'Exportable records report'];
    }

    private function mergePackFeatures(array $blueprint): array
    {
        $features = $blueprint['features'] ?? [];
        $packs = config('domain_packs', []);

        foreach ($blueprint['matched_packs'] ?? [] as $packKey) {
            foreach ($packs[$packKey]['features'] ?? [] as $feature) {
                $features[] = $feature;
            }
        }

        return array_values(array_unique(array_filter($features)));
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
        return $this->summarizeList($arr);
    }

    private function summarizeList(array $items): string
    {
        $summary = array_map(function ($item) {
            if (is_array($item)) {
                return (string) ($item['name'] ?? $item['title'] ?? $item['label'] ?? json_encode($item));
            }

            return (string) $item;
        }, $items);

        return implode(', ', array_values(array_filter($summary)));
    }

    /**
     * Ultra-compact discovery system prompt (~200 tokens).
     * AI ONLY extracts structure — no code, no prose.
     */
    private function discoverySystemPrompt(): string
    {
        return <<<'PROMPT'
You are a software requirements analyst for RyaanCMS, an AI Business Operating System Builder.
Extract a structured business OS blueprint from the user's normal-language request.
Infer the hidden business plan, but never mention internal routing, cost, tokens, or blueprint mechanics to the user.
Return ONLY valid JSON — no prose, no markdown, just the JSON object:

{
  "app_type": "crm|erp|marketplace|booking|lms|hospital|restaurant|ecommerce|saas|hrm|accounting|school|inventory|pharmacy|real_estate|hotel_hospitality|ngo_charity|project_management|custom",
  "name": "Short app name",
  "industry": "industry",
  "business_domain": "specific business domain",
  "business_model": "b2b|b2c|marketplace|saas|internal",
  "blueprint_level": "basic|professional|enterprise",
  "requested_outputs": ["application","dashboard","portal","website","landing_page"],
  "platform": ["web","mobile"],
  "users": ["admin","customer","vendor"],
  "features": ["feature1","feature2"],
  "modules": ["module1","module2"],
  "pages": ["page1","page2"],
  "sections": ["hero","services","features","faq","contact"],
  "forms": ["form1","form2"],
  "workflows": ["workflow1","workflow2"],
  "reports": ["report1","report2"],
  "dashboards": ["dashboard1"],
  "entities": [
    {
      "name": "EntityName",
      "fields": [
        {"name":"field_name","type":"string|integer|decimal|boolean|text|date|datetime|enum|json","required":true,"label":"Human Label","options":["for enum only"]}
      ]
    }
  ],
  "integrations": ["stripe","maps"],
  "automations": ["automation1"],
  "ai_rules": ["ask only for missing high-risk details","generate missing gaps only"],
  "deployment_profile": "static_or_laravel_public_site|laravel_business_application|multi_tenant_laravel_saas",
  "priority_entities": ["Entity1"]
}
PROMPT;
    }
}
