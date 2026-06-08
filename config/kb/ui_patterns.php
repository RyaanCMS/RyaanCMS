<?php
/**
 * UI/UX Patterns Knowledge Base
 * Senior developers understand UX. Level 4: Product Knowledge
 */
return [

    // ── Dashboard Layouts ─────────────────────────────────────────────────────
    'dashboard_layouts' => [
        'kpi_cards'   => 'Top row: 3–4 metric cards (Total Revenue, New Orders, Active Users, Pending Tasks)',
        'chart_row'   => 'Below KPIs: Bar/Line chart (trend) + Doughnut (breakdown)',
        'table_row'   => 'Recent activity table: last 10 records with quick actions',
        'sidebar'     => 'Left sidebar: icon + label navigation. Collapsible on mobile.',
        'breadcrumbs' => 'Always show current location: Dashboard / Orders / #1234',
        'mobile_first'=> 'Cards stack vertically on mobile. Bottom navigation on mobile.',
    ],

    // ── Navigation Patterns ────────────────────────────────────────────────────
    'navigation' => [
        'sidebar_admin' => [
            'structure' => 'Logo → User profile pill → Nav groups with icons → Settings at bottom',
            'groups'    => 'Group related items: Core, Reports, Settings, Admin',
            'active'    => 'Highlight active item. Show sub-menu only when parent active.',
            'badge'     => 'Show count badge on items needing attention (Orders: 5)',
        ],
        'top_nav' => [
            'use_for'   => 'Marketing/public pages. Less suitable for admin apps.',
            'mobile'    => 'Hamburger menu → full-screen overlay on mobile',
        ],
        'mobile_bottom_nav' => [
            'max_items' => '5 items maximum',
            'icons'     => 'Always pair icon + short label',
            'use_for'   => 'Mobile app, SaaS with mobile-first requirement',
        ],
    ],

    // ── Data Table Patterns ────────────────────────────────────────────────────
    'data_tables' => [
        'features'       => ['Sort by column', 'Filter by status/date', 'Global search', 'Bulk select', 'Pagination', 'Export (CSV/Excel)'],
        'row_actions'    => ['View (eye icon)', 'Edit (pencil)', 'Delete (trash — confirmation required)'],
        'bulk_actions'   => ['Delete selected', 'Export selected', 'Change status'],
        'empty_state'    => 'Show illustration + "No records found" + primary action button',
        'loading_state'  => 'Skeleton loader rows (not spinner overlay)',
        'mobile_table'   => 'Convert to card list view on mobile. Show only essential columns.',
        'sticky_header'  => 'Sticky column headers when table > viewport height',
    ],

    // ── Form UX Patterns ──────────────────────────────────────────────────────
    'forms' => [
        'inline_validation' => 'Validate on blur, not on submit only. Show error below field.',
        'required_fields'   => 'Mark required with *. Explain at top "* required field".',
        'auto_save'         => 'For long forms: auto-save draft every 30s. "Saved X minutes ago"',
        'multi_step'        => 'Break forms > 8 fields into steps. Show progress bar. Allow back navigation.',
        'loading_button'    => 'Disable submit button + show spinner on submit. Prevent double submit.',
        'confirmation'      => 'For destructive actions: modal confirm. For irreversible: type name to confirm.',
        'file_upload'       => 'Drag-and-drop zone + click to browse. Show progress bar. Preview images.',
        'select_search'     => 'Use searchable select (select2/tom-select) for options > 10.',
        'date_picker'       => 'Use date range picker for reports. Show calendar, not text input.',
    ],

    // ── Empty States ──────────────────────────────────────────────────────────
    'empty_states' => [
        'rule'     => 'NEVER show a blank page. Always show empty state.',
        'elements' => ['Icon/illustration', 'Clear explanation (what this section is for)', 'Primary action button'],
        'examples' => [
            'orders'   => '"No orders yet" → "Create First Order" button',
            'products' => '"Your catalog is empty" → "Add Product" button',
            'search'   => '"No results for X" → "Clear filters" or "Try different keywords"',
        ],
    ],

    // ── Color & Status Patterns ────────────────────────────────────────────────
    'status_colors' => [
        'success_active_paid'    => 'green (#22c55e bg, #166534 text)',
        'warning_pending_review' => 'yellow (#eab308 bg, #713f12 text)',
        'error_failed_cancelled' => 'red (#ef4444 bg, #991b1b text)',
        'info_draft_processing'  => 'blue (#3b82f6 bg, #1e40af text)',
        'neutral_inactive'       => 'gray (#9ca3af bg, #374151 text)',
        'rule'                   => 'Consistent color coding across all status badges in the app',
    ],

    // ── Charts & Analytics ────────────────────────────────────────────────────
    'charts' => [
        'revenue_trend'    => 'Line chart: daily/weekly/monthly toggle. Compare to previous period.',
        'category_split'   => 'Doughnut or horizontal bar for % breakdown',
        'comparison'       => 'Grouped bar for period-over-period comparison',
        'funnel'           => 'Funnel chart for conversion stages (lead → deal → won)',
        'geographic'       => 'Map chart for regional distribution',
        'library'          => 'Chart.js (lightweight) or ApexCharts (feature-rich)',
        'loading'          => 'Show skeleton while data loads. Never empty chart area.',
    ],

    // ── Responsive Design Rules ────────────────────────────────────────────────
    'responsive' => [
        'breakpoints' => ['sm: 640px', 'md: 768px', 'lg: 1024px', 'xl: 1280px'],
        'mobile_first'=> 'Write mobile CSS first, add lg: overrides',
        'touch_targets'=> 'Minimum 44×44px tap target on mobile',
        'table_mobile' => 'Hide non-essential columns. Provide horizontal scroll or card view.',
        'font_sizes'   => 'Base 14–16px. Minimum 12px for labels. Heading scale 1.25 ratio.',
    ],

    // ── Notification & Feedback ────────────────────────────────────────────────
    'feedback_patterns' => [
        'toast'         => '3s auto-dismiss for success. 5s for errors. Top-right corner.',
        'inline_error'  => 'Form field errors below the field.',
        'page_alert'    => 'Full-width banner for important system messages.',
        'loading'       => 'Skeleton > spinner > nothing. Never freeze UI.',
        'progress'      => 'Progress bar for bulk operations. Show percentage + ETA.',
        'confirmation'  => 'Modal dialog for destructive actions. Red button for confirm.',
    ],

    // ── Print & Export ────────────────────────────────────────────────────────
    'print_export' => [
        'invoice_pdf'  => 'Company logo, details, line items table, total. A4 format.',
        'csv_export'   => 'Include headers. Date format: YYYY-MM-DD. UTF-8 BOM for Excel.',
        'excel_export' => 'Package: maatwebsite/excel. Freeze top row. Auto-fit columns.',
        'print_css'    => 'Hide sidebar, nav, buttons. Show page-break hints.',
    ],

];
