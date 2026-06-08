<?php

namespace App\Services\AI;

/**
 * Picks a random design variant on every generation call.
 * Injected into system prompts so each build looks visually distinct.
 *
 * Covers: color palette, layout, card style, typography, button style, nav pattern.
 */
class DesignVariantService
{
    // ── Color Palettes ────────────────────────────────────────────────────────
    private const PALETTES = [
        'midnight_indigo' => [
            'name'        => 'Midnight Indigo',
            'bg'          => '#0f1117',
            'surface'     => '#1a1f2e',
            'border'      => '#2d3454',
            'accent'      => '#6366f1',
            'accent_dark' => '#4f46e5',
            'text_1'      => '#f1f5f9',
            'text_2'      => '#94a3b8',
            'text_3'      => '#475569',
            'success'     => '#22c55e',
            'danger'      => '#ef4444',
            'mode'        => 'dark',
        ],
        'ocean_deep' => [
            'name'        => 'Ocean Deep',
            'bg'          => '#040d1a',
            'surface'     => '#071528',
            'border'      => '#0f2d4a',
            'accent'      => '#0ea5e9',
            'accent_dark' => '#0284c7',
            'text_1'      => '#e0f2fe',
            'text_2'      => '#7dd3fc',
            'text_3'      => '#38bdf8',
            'success'     => '#34d399',
            'danger'      => '#f87171',
            'mode'        => 'dark',
        ],
        'emerald_forest' => [
            'name'        => 'Emerald Forest',
            'bg'          => '#030d09',
            'surface'     => '#071a0f',
            'border'      => '#134225',
            'accent'      => '#10b981',
            'accent_dark' => '#059669',
            'text_1'      => '#ecfdf5',
            'text_2'      => '#a7f3d0',
            'text_3'      => '#6ee7b7',
            'success'     => '#34d399',
            'danger'      => '#f87171',
            'mode'        => 'dark',
        ],
        'crimson_dark' => [
            'name'        => 'Crimson Dark',
            'bg'          => '#0d0609',
            'surface'     => '#1a0c10',
            'border'      => '#3d1520',
            'accent'      => '#f43f5e',
            'accent_dark' => '#e11d48',
            'text_1'      => '#fff1f2',
            'text_2'      => '#fda4af',
            'text_3'      => '#fb7185',
            'success'     => '#34d399',
            'danger'      => '#fb7185',
            'mode'        => 'dark',
        ],
        'purple_haze' => [
            'name'        => 'Purple Haze',
            'bg'          => '#0c0712',
            'surface'     => '#160e24',
            'border'      => '#2e1a4a',
            'accent'      => '#a855f7',
            'accent_dark' => '#9333ea',
            'text_1'      => '#faf5ff',
            'text_2'      => '#d8b4fe',
            'text_3'      => '#c084fc',
            'success'     => '#34d399',
            'danger'      => '#f87171',
            'mode'        => 'dark',
        ],
        'amber_warm' => [
            'name'        => 'Amber Warm',
            'bg'          => '#fffbf0',
            'surface'     => '#ffffff',
            'border'      => '#fde68a',
            'accent'      => '#d97706',
            'accent_dark' => '#b45309',
            'text_1'      => '#1c1917',
            'text_2'      => '#57534e',
            'text_3'      => '#a8a29e',
            'success'     => '#16a34a',
            'danger'      => '#dc2626',
            'mode'        => 'light',
        ],
        'clean_white' => [
            'name'        => 'Clean White',
            'bg'          => '#f8fafc',
            'surface'     => '#ffffff',
            'border'      => '#e2e8f0',
            'accent'      => '#3b82f6',
            'accent_dark' => '#2563eb',
            'text_1'      => '#0f172a',
            'text_2'      => '#475569',
            'text_3'      => '#94a3b8',
            'success'     => '#16a34a',
            'danger'      => '#dc2626',
            'mode'        => 'light',
        ],
        'rose_gold' => [
            'name'        => 'Rose Gold',
            'bg'          => '#fff5f5',
            'surface'     => '#ffffff',
            'border'      => '#fecdd3',
            'accent'      => '#e11d48',
            'accent_dark' => '#be123c',
            'text_1'      => '#1f0f12',
            'text_2'      => '#6b7280',
            'text_3'      => '#9ca3af',
            'success'     => '#16a34a',
            'danger'      => '#dc2626',
            'mode'        => 'light',
        ],
        'slate_pro' => [
            'name'        => 'Slate Pro',
            'bg'          => '#0f172a',
            'surface'     => '#1e293b',
            'border'      => '#334155',
            'accent'      => '#38bdf8',
            'accent_dark' => '#0284c7',
            'text_1'      => '#f1f5f9',
            'text_2'      => '#94a3b8',
            'text_3'      => '#64748b',
            'success'     => '#4ade80',
            'danger'      => '#f87171',
            'mode'        => 'dark',
        ],
        'zinc_modern' => [
            'name'        => 'Zinc Modern',
            'bg'          => '#18181b',
            'surface'     => '#27272a',
            'border'      => '#3f3f46',
            'accent'      => '#f4f4f5',
            'accent_dark' => '#e4e4e7',
            'text_1'      => '#fafafa',
            'text_2'      => '#a1a1aa',
            'text_3'      => '#71717a',
            'success'     => '#4ade80',
            'danger'      => '#f87171',
            'mode'        => 'dark',
        ],
        'cyberpunk' => [
            'name'        => 'Cyberpunk',
            'bg'          => '#05050f',
            'surface'     => '#0a0a1a',
            'border'      => '#1a1a3a',
            'accent'      => '#facc15',
            'accent_dark' => '#eab308',
            'text_1'      => '#fef9c3',
            'text_2'      => '#a3e635',
            'text_3'      => '#4ade80',
            'success'     => '#a3e635',
            'danger'      => '#f43f5e',
            'mode'        => 'dark',
        ],
        'nordic_frost' => [
            'name'        => 'Nordic Frost',
            'bg'          => '#eceff4',
            'surface'     => '#ffffff',
            'border'      => '#d8dee9',
            'accent'      => '#5e81ac',
            'accent_dark' => '#4c6d91',
            'text_1'      => '#2e3440',
            'text_2'      => '#4c566a',
            'text_3'      => '#7a8899',
            'success'     => '#a3be8c',
            'danger'      => '#bf616a',
            'mode'        => 'light',
        ],
    ];

    // ── Card Styles ───────────────────────────────────────────────────────────
    private const CARD_STYLES = [
        'glass'       => 'Glassmorphism: backdrop-blur, semi-transparent background (rgba with 0.05–0.15 opacity), subtle border with rgba white/10.',
        'flat'        => 'Flat Minimal: solid background, no shadow, 1px solid border, sharp or slightly rounded corners.',
        'elevated'    => 'Elevated: white/dark solid background, strong box-shadow (0 4px 24px rgba(0,0,0,0.18)), no visible border.',
        'bordered'    => 'Bordered Heavy: transparent background, 2px solid accent-color border, sharp corners.',
        'soft_shadow' => 'Soft Shadow: solid background, very subtle shadow (0 2px 8px rgba(0,0,0,0.08)), large border-radius (16px+).',
        'neumorphic'  => 'Neumorphic: background same as page bg, dual inset/outset shadows to create depth illusion, very subtle.',
        'gradient'    => 'Gradient Surface: linear-gradient background using accent color at 5–10% opacity, glowing accent border.',
        'brutalist'   => 'Brutalist: 3px solid black/white border offset with a drop-shadow (4px 4px 0px accent), flat fill.',
    ];

    // ── Layout Patterns ───────────────────────────────────────────────────────
    private const LAYOUTS = [
        'sidebar_left'    => 'Fixed left sidebar (240px) + scrollable main content area. Sidebar has logo, nav links with icons.',
        'sidebar_icons'   => 'Collapsed icon-only sidebar (64px) + main area. Hover expands to show labels.',
        'top_nav'         => 'Sticky top navigation bar (60px) + full-width content below. No sidebar.',
        'dual_panel'      => 'Left sidebar (220px) + top breadcrumb bar + content. Two levels of navigation.',
        'dashboard_hero'  => 'Top hero stat bar + card grid below. Minimal sidebar or tabs on left.',
        'minimal_center'  => 'No sidebar. Content centered max-width 1100px. Top nav. Clean whitespace.',
        'tab_layout'      => 'Top navigation + horizontal tab bar below it + tabbed content sections.',
        'split_layout'    => 'Left 40% list panel + right 60% detail panel. Good for CRM/email-style apps.',
    ];

    // ── Button Styles ─────────────────────────────────────────────────────────
    private const BUTTON_STYLES = [
        'rounded_fill'  => 'Solid filled background, border-radius 8px, no border.',
        'pill'          => 'Fully pill-shaped (border-radius 9999px), solid fill.',
        'sharp_outline' => 'Transparent background, 2px solid border, sharp corners (border-radius 4px).',
        'ghost'         => 'Transparent background, no border, text color only. Hover shows light bg tint.',
        'gradient_btn'  => 'Linear gradient background from accent to accent-dark, no border, border-radius 8px.',
        'underline'     => 'No background, no border, just text with animated underline on hover.',
    ];

    // ── Typography ────────────────────────────────────────────────────────────
    private const TYPOGRAPHY = [
        'inter'       => ['heading' => 'Inter, sans-serif', 'body' => 'Inter, sans-serif',      'mono' => 'JetBrains Mono, monospace'],
        'geist'       => ['heading' => 'Geist, sans-serif', 'body' => 'Geist, sans-serif',       'mono' => 'Geist Mono, monospace'],
        'plus_jakarta'=> ['heading' => 'Plus Jakarta Sans, sans-serif', 'body' => 'Inter, sans-serif', 'mono' => 'Fira Code, monospace'],
        'syne'        => ['heading' => 'Syne, sans-serif',  'body' => 'DM Sans, sans-serif',     'mono' => 'JetBrains Mono, monospace'],
        'space_grotesk'=> ['heading'=> 'Space Grotesk, sans-serif', 'body' => 'Inter, sans-serif', 'mono' => 'Space Mono, monospace'],
        'outfit'      => ['heading' => 'Outfit, sans-serif', 'body' => 'Outfit, sans-serif',     'mono' => 'Fira Code, monospace'],
        'manrope'     => ['heading' => 'Manrope, sans-serif','body' => 'Manrope, sans-serif',    'mono' => 'JetBrains Mono, monospace'],
    ];

    // ── Animation Style ───────────────────────────────────────────────────────
    private const ANIMATIONS = [
        'subtle'     => 'Subtle: transition-all duration-200, hover:-translate-y-0.5 on cards.',
        'smooth'     => 'Smooth: transition-all duration-300, scale-105 on card hover, fade-in on load.',
        'snappy'     => 'Snappy: transition-all duration-100, instant feedback, no translate effects.',
        'bouncy'     => 'Bouncy: transition-all duration-300 ease-bounce, scale hover effects.',
        'none'       => 'No animations. Pure static layout, no transitions.',
    ];

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Pick a random design variant. Returns both the spec array and a prompt block.
     */
    public function pick(?int $seed = null): array
    {
        if ($seed !== null) mt_srand($seed);

        $palette    = $this->randomValue(self::PALETTES);
        $cardStyle  = $this->randomKey(self::CARD_STYLES);
        $layout     = $this->randomKey(self::LAYOUTS);
        $buttonStyle= $this->randomKey(self::BUTTON_STYLES);
        $typography = $this->randomValue(self::TYPOGRAPHY);
        $animation  = $this->randomKey(self::ANIMATIONS);

        return [
            'palette'      => $palette,
            'card_style'   => $cardStyle,
            'layout'       => $layout,
            'button_style' => $buttonStyle,
            'typography'   => $typography,
            'animation'    => $animation,
            'prompt_block' => $this->buildPromptBlock($palette, $cardStyle, $layout, $buttonStyle, $typography, $animation),
        ];
    }

    /**
     * Build the prompt injection block for the AI system prompt.
     */
    public function buildPromptBlock(
        array  $palette,
        string $cardStyle,
        string $layout,
        string $buttonStyle,
        array  $typography,
        string $animation
    ): string {
        $mode       = $palette['mode'];
        $paletteName= $palette['name'];

        return <<<DSBLOCK
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
DESIGN SYSTEM — USE EXACTLY AS SPECIFIED
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Theme Mode: {$mode} ({$paletteName})

Color Palette (use these hex values — do NOT substitute):
  Page background : {$palette['bg']}
  Surface/card bg : {$palette['surface']}
  Border color    : {$palette['border']}
  Accent primary  : {$palette['accent']}
  Accent dark     : {$palette['accent_dark']}
  Text primary    : {$palette['text_1']}
  Text secondary  : {$palette['text_2']}
  Text muted      : {$palette['text_3']}
  Success         : {$palette['success']}
  Danger/Error    : {$palette['danger']}

Layout Pattern: {$layout}
  → {$this->describe(self::LAYOUTS, $layout)}

Card Style: {$cardStyle}
  → {$this->describe(self::CARD_STYLES, $cardStyle)}

Button Style: {$buttonStyle}
  → {$this->describe(self::BUTTON_STYLES, $buttonStyle)}

Typography:
  Headings : {$typography['heading']}
  Body     : {$typography['body']}
  Mono     : {$typography['mono']}
  (Load from Google Fonts CDN if not system font)

Animation: {$animation}
  → {$this->describe(self::ANIMATIONS, $animation)}

MANDATORY RULES:
1. Every file you generate MUST follow this exact design system.
2. Apply palette colors via inline style="" attributes (Tailwind CDN won't have custom hex values).
3. Do NOT use generic gray/blue Tailwind defaults — use the palette hex values above.
4. Every page must feel cohesive — same card style, same button style, consistent spacing.
5. preview.html must showcase the chosen design prominently.
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
DSBLOCK;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function randomValue(array $arr): array|string
    {
        $keys = array_keys($arr);
        return $arr[$keys[array_rand($keys)]];
    }

    private function randomKey(array $arr): string
    {
        $keys = array_keys($arr);
        return $keys[array_rand($keys)];
    }

    private function describe(array $arr, string $key): string
    {
        return $arr[$key] ?? '';
    }
}
