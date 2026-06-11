<?php

namespace App\Services\AI;

/**
 * BlueprintGenomeEngine — Blueprint Genome System, Phase 2
 *
 * Takes the structured intent from IntentEngine and assembles a rich blueprint context
 * by combining genome libraries (industries, functions) — ZERO AI tokens.
 *
 * Pipeline:
 *   Intent → Industry Genome → Function Genomes → Merged Blueprint Context
 *
 * The assembled context is then:
 *   1. Injected into BlueprintService::discover() to reduce AI call tokens
 *   2. Passed to KnowledgeBaseService as enriched domain context
 *   3. Used directly by CodeGeneratorService for zero-AI module scaffolding
 */
class BlueprintGenomeEngine
{
    private array $industries;
    private array $functions;

    public function __construct()
    {
        $this->industries = require config_path('genome/industries.php');
        $this->functions  = require config_path('genome/functions.php');
    }

    /**
     * Assemble a full blueprint genome from a detected intent.
     *
     * @param  array  $intent  Output of IntentEngine::detect()
     * @return array  Rich genome context ready for injection
     */
    public function assemble(array $intent): array
    {
        $industryIds  = $intent['industries']  ?? [];
        $featureIds   = $intent['features']    ?? [];
        $action       = $intent['action']      ?? 'build';
        $complexity   = $intent['complexity']  ?? 'medium';

        // 1. Load genome data for each matched industry
        $industryGenomes = $this->loadIndustryGenomes($industryIds);

        // 2. Collect all required functions from industries + explicit feature hints
        $allFunctionIds = $this->collectFunctionIds($industryGenomes, $featureIds);

        // 3. Load genome data for each function
        $functionGenomes = $this->loadFunctionGenomes($allFunctionIds);

        // 4. Merge and deduplicate
        $merged = $this->mergeGenomes($industryGenomes, $functionGenomes);

        // 5. Scale adjustments
        $merged = $this->applyComplexityFilter($merged, $complexity);

        return [
            'action'              => $action,
            'complexity'          => $complexity,
            'confidence'          => $intent['confidence'] ?? 0.5,
            'primary_industry'    => $industryIds[0] ?? null,
            'industries'          => $industryIds,
            'sectors'             => $this->extractSectors($industryGenomes),
            'modules'             => $merged['modules'],
            'workflows'           => $merged['workflows'],
            'roles'               => $merged['roles'],
            'integrations'        => $merged['integrations'],
            'kpis'                => $merged['kpis'],
            'entities'            => $this->deriveEntities($merged['modules']),
            'suggested_features'  => $allFunctionIds,
            'function_details'    => $functionGenomes,
            'industry_details'    => $industryGenomes,
            'prompt_context'      => $this->buildPromptContext($intent, $merged, $industryGenomes),
        ];
    }

    /**
     * Build a compact AI prompt context string from the genome.
     * Replaces large system-prompt knowledge sections with structured genome data.
     * Typical size: ~500-800 tokens (vs 3000+ for static prompts).
     */
    public function buildPromptContext(array $intent, array $merged, array $industryGenomes): string
    {
        $primaryIndustry  = $intent['industries'][0] ?? 'general';
        $industryLabel    = $industryGenomes[$primaryIndustry]['label'] ?? ucfirst($primaryIndustry);
        $action           = $intent['action'] ?? 'build';
        $complexity       = $intent['complexity'] ?? 'medium';

        $modules      = implode(', ', array_slice($merged['modules'], 0, 20));
        $workflows    = implode(', ', array_slice($merged['workflows'], 0, 10));
        $roles        = implode(', ', array_unique(array_slice($merged['roles'], 0, 10)));
        $integrations = implode(', ', array_slice($merged['integrations'], 0, 8));
        $kpis         = implode(', ', array_slice($merged['kpis'], 0, 6));

        $allIndustries = implode(' + ', array_map(
            fn($id) => $industryGenomes[$id]['label'] ?? $id,
            $intent['industries'] ?? [$primaryIndustry]
        ));

        return <<<GENOME
[GENOME CONTEXT — {$allIndustries} — {$complexity} scale]
Action     : {$action}
Industry   : {$allIndustries}
Modules    : {$modules}
Workflows  : {$workflows}
User Roles : {$roles}
Integrations: {$integrations}
KPIs       : {$kpis}
[END GENOME]
GENOME;
    }

    /**
     * Quick module list for a given industry ID — used by module installer.
     */
    public function modulesForIndustry(string $industryId): array
    {
        $genome = $this->loadIndustryGenomes([$industryId]);
        $functionIds = $this->collectFunctionIds($genome, []);
        $functionGenomes = $this->loadFunctionGenomes($functionIds);
        $merged = $this->mergeGenomes($genome, $functionGenomes);
        return array_unique($merged['modules']);
    }

    /**
     * Quick integrations list for one or more industries.
     */
    public function integrationsForIndustries(array $industryIds): array
    {
        $genome = $this->loadIndustryGenomes($industryIds);
        $all = [];
        foreach ($genome as $g) {
            $all = array_merge($all, $g['integrations'] ?? []);
        }
        return array_unique($all);
    }

    /**
     * Return genome context as a compact array for blueprint JSON storage.
     */
    public function toArray(array $intent): array
    {
        $assembled = $this->assemble($intent);
        return [
            'genome_version'   => '1.0',
            'primary_industry' => $assembled['primary_industry'],
            'sectors'          => $assembled['sectors'],
            'modules'          => $assembled['modules'],
            'roles'            => $assembled['roles'],
            'workflows'        => $assembled['workflows'],
            'integrations'     => $assembled['integrations'],
            'kpis'             => $assembled['kpis'],
            'complexity'       => $assembled['complexity'],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private assembly helpers
    // ──────────────────────────────────────────────────────────────────────

    private function loadIndustryGenomes(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            if (isset($this->industries[$id])) {
                $result[$id] = $this->industries[$id];
            }
        }
        return $result;
    }

    private function loadFunctionGenomes(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            if (isset($this->functions[$id])) {
                $result[$id] = $this->functions[$id];
            }
        }
        return $result;
    }

    /** Collect all function IDs from industry genomes + explicit feature hints */
    private function collectFunctionIds(array $industryGenomes, array $explicitFeatures): array
    {
        $ids = $explicitFeatures;
        foreach ($industryGenomes as $genome) {
            $ids = array_merge($ids, $genome['functions'] ?? []);
        }
        return array_unique($ids);
    }

    /** Merge all genome arrays into deduplicated flat lists */
    private function mergeGenomes(array $industryGenomes, array $functionGenomes): array
    {
        $modules      = [];
        $workflows    = [];
        $roles        = [];
        $integrations = [];
        $kpis         = [];

        // From industries
        foreach ($industryGenomes as $genome) {
            $workflows    = array_merge($workflows,    $genome['workflows']    ?? []);
            $roles        = array_merge($roles,        $genome['roles']        ?? []);
            $integrations = array_merge($integrations, $genome['integrations'] ?? []);
            $kpis         = array_merge($kpis,         $genome['kpis']         ?? []);
        }

        // From functions
        foreach ($functionGenomes as $genome) {
            $modules   = array_merge($modules,   $genome['modules']   ?? []);
            $workflows = array_merge($workflows, $genome['workflows'] ?? []);
            $roles     = array_merge($roles,     $genome['roles']     ?? []);
            $kpis      = array_merge($kpis,      $genome['kpis']      ?? []);
        }

        return [
            'modules'      => array_values(array_unique($modules)),
            'workflows'    => array_values(array_unique($workflows)),
            'roles'        => array_values(array_unique($roles)),
            'integrations' => array_values(array_unique($integrations)),
            'kpis'         => array_values(array_unique($kpis)),
        ];
    }

    /** Trim lists based on complexity to avoid over-specifying for small apps */
    private function applyComplexityFilter(array $merged, string $complexity): array
    {
        $limits = match ($complexity) {
            'small'  => ['modules' => 8,  'workflows' => 5,  'roles' => 4,  'integrations' => 3,  'kpis' => 4],
            'medium' => ['modules' => 15, 'workflows' => 10, 'roles' => 7,  'integrations' => 6,  'kpis' => 6],
            'large'  => ['modules' => 30, 'workflows' => 20, 'roles' => 12, 'integrations' => 12, 'kpis' => 10],
            default  => ['modules' => 15, 'workflows' => 10, 'roles' => 7,  'integrations' => 6,  'kpis' => 6],
        };

        foreach ($limits as $key => $max) {
            $merged[$key] = array_slice($merged[$key], 0, $max);
        }

        return $merged;
    }

    private function extractSectors(array $industryGenomes): array
    {
        $sectors = [];
        foreach ($industryGenomes as $genome) {
            $s = $genome['sector'] ?? null;
            if ($s && !in_array($s, $sectors, true)) {
                $sectors[] = $s;
            }
        }
        return $sectors;
    }

    /**
     * Derive entity names from module names.
     * "Order" → ['id', 'created_at', 'updated_at'] base entity stub.
     */
    private function deriveEntities(array $modules): array
    {
        $entities = [];
        foreach ($modules as $module) {
            $entities[] = [
                'name'  => $module,
                'table' => $this->toSnakeCase($module) . 's',
            ];
        }
        return $entities;
    }

    private function toSnakeCase(string $s): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $s));
    }
}
