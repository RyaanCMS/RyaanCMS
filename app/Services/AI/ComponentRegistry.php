<?php

namespace App\Services\AI;

/**
 * Component Registry — zero-AI component retrieval.
 *
 * Routes standard UI requests away from AI entirely:
 * data table, modal form, stats cards, empty state, etc.
 * Each component returns battle-tested Blade code instantly.
 *
 * Routing check: before calling AI, run matchComponent($prompt).
 * If a match is found, insert it directly — 0 tokens spent.
 */
class ComponentRegistry
{
    private array $components;

    /** Keywords that trigger component lookup before AI */
    private array $triggerKeywords = [
        'stats card'       => 'stats_card_row',
        'kpi card'         => 'stats_card_row',
        'dashboard card'   => 'stats_card_row',
        'data table'       => 'data_table',
        'sortable table'   => 'data_table',
        'list table'       => 'data_table',
        'modal form'       => 'modal_form',
        'create modal'     => 'modal_form',
        'add modal'        => 'modal_form',
        'notification bell'=> 'notification_bell',
        'notification icon'=> 'notification_bell',
        'breadcrumb'       => 'breadcrumb',
        'empty state'      => 'empty_state',
        'no data'          => 'empty_state',
        'alert banner'     => 'alert_banner',
        'flash message'    => 'alert_banner',
        'search filter'    => 'search_filter_bar',
        'filter bar'       => 'search_filter_bar',
        'file upload'      => 'file_upload_zone',
        'drag drop'        => 'file_upload_zone',
        'upload zone'      => 'file_upload_zone',
        'pagination'       => 'pagination_links',
        'inline edit'      => 'inline_edit_field',
        'click to edit'    => 'inline_edit_field',
    ];

    public function __construct()
    {
        $this->components = config('components', []);
    }

    /** All components, optionally filtered by category */
    public function all(string $category = ''): array
    {
        if (!$category) return $this->components;

        return array_filter(
            $this->components,
            fn($c) => ($c['category'] ?? '') === $category
        );
    }

    /** Get a single component by key */
    public function get(string $key): ?array
    {
        return $this->components[$key] ?? null;
    }

    /** Check if a prompt maps to a known component — returns key or null */
    public function matchComponent(string $prompt): ?string
    {
        $lower = strtolower($prompt);
        foreach ($this->triggerKeywords as $phrase => $key) {
            if (str_contains($lower, $phrase)) {
                return isset($this->components[$key]) ? $key : null;
            }
        }
        return null;
    }

    /** Get the Blade template for a component key */
    public function template(string $key): ?string
    {
        return $this->components[$key]['template'] ?? null;
    }

    /** Get all unique categories */
    public function categories(): array
    {
        return array_unique(array_column(array_values($this->components), 'category'));
    }

    /** How many tokens this component saves vs. asking AI */
    public function tokensSaved(string $key): int
    {
        return $this->components[$key]['tokens_saved'] ?? 500;
    }

    /** Total tokens saved across all components (for stats display) */
    public function totalTokensSaved(): int
    {
        return array_sum(array_column(array_values($this->components), 'tokens_saved'));
    }

    /** Search components by label, description, or tags */
    public function search(string $query): array
    {
        $lower = strtolower($query);
        return array_filter($this->components, function ($c) use ($lower) {
            if (str_contains(strtolower($c['label'] ?? ''), $lower)) return true;
            if (str_contains(strtolower($c['description'] ?? ''), $lower)) return true;
            foreach ($c['tags'] ?? [] as $tag) {
                if (str_contains(strtolower($tag), $lower)) return true;
            }
            return false;
        });
    }
}
