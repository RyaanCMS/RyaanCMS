<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RyaanCMS Core Settings
    |--------------------------------------------------------------------------
    */

    'version' => '1.0.0',

    'max_projects' => env('RYAAN_MAX_PROJECTS', 10),

    'enable_marketplace' => env('RYAAN_ENABLE_MARKETPLACE', true),

    'enable_deployment' => env('RYAAN_ENABLE_DEPLOYMENT', true),

    /*
    |--------------------------------------------------------------------------
    | Project Types
    |--------------------------------------------------------------------------
    */
    'project_types' => [
        'laravel'   => ['label' => 'Laravel App',    'icon' => 'laravel',   'color' => 'red'],
        'react'     => ['label' => 'React App',      'icon' => 'react',     'color' => 'blue'],
        'nextjs'    => ['label' => 'Next.js App',    'icon' => 'nextjs',    'color' => 'gray'],
        'static'    => ['label' => 'Static Website', 'icon' => 'html',      'color' => 'orange'],
        'saas'      => ['label' => 'SaaS Platform',  'icon' => 'saas',      'color' => 'purple'],
        'ecommerce' => ['label' => 'eCommerce',       'icon' => 'shop',      'color' => 'green'],
        'crm'       => ['label' => 'CRM System',     'icon' => 'crm',       'color' => 'yellow'],
        'erp'       => ['label' => 'ERP System',     'icon' => 'erp',       'color' => 'indigo'],
        'api'       => ['label' => 'REST API',       'icon' => 'api',       'color' => 'teal'],
        'mobile'    => ['label' => 'Mobile App',     'icon' => 'mobile',    'color' => 'pink'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Templates
    |--------------------------------------------------------------------------
    */
    'templates' => [
        'restaurant'   => 'Restaurant Management System',
        'clinic'       => 'Clinic & Healthcare',
        'ecommerce'    => 'eCommerce Platform',
        'real_estate'  => 'Real Estate Platform',
        'school'       => 'School Management',
        'hotel'        => 'Hotel Management',
        'pharmacy'     => 'Pharmacy Management',
        'gym'          => 'Gym & Fitness',
        'salon'        => 'Salon & Spa',
        'logistics'    => 'Logistics & Courier',
    ],

    /*
    |--------------------------------------------------------------------------
    | Marketplace Categories
    |--------------------------------------------------------------------------
    */
    'marketplace_categories' => [
        'ecommerce'   => 'eCommerce',
        'crm'         => 'CRM',
        'hrm'         => 'HRM & Payroll',
        'accounting'  => 'Accounting',
        'ai_agents'   => 'AI Agents',
        'integrations'=> 'Integrations',
        'themes'      => 'Themes',
        'plugins'     => 'Plugins',
        'templates'   => 'Templates',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Deployment Targets
    |--------------------------------------------------------------------------
    */
    'deployment_targets' => [
        'shared' => 'Shared Hosting (FTP/SFTP)',
        'vps'    => 'VPS / Dedicated Server (SSH)',
        'docker' => 'Docker Container',
        'cpanel' => 'cPanel Hosting',
    ],

];
