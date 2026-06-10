<?php

namespace App\Services\Template;

class TemplateRegistry
{
    public function all(): array
    {
        return [
            'template.ryaancms' => [
                'key'         => 'template.ryaancms',
                'name'        => 'RyaanCMS',
                'description' => 'Default RyaanCMS public landing page with product, domain, marketplace, and download sections.',
                'category'    => 'RyaanCMS',
                'type'        => 'template',
                'is_global'   => true,
                'icon'        => 'R',
                'color'       => '#6366f1',
                'view'        => 'templates.ryaancms',
                'tags'        => ['ryaancms', 'default', 'landing', 'ai builder', 'cms'],
            ],
            'template.restaurant' => [
                'key'         => 'template.restaurant',
                'name'        => 'La Bella Cucina',
                'description' => 'Elegant restaurant website with menu, gallery, and reservation system',
                'category'    => 'Restaurant & Food',
                'type'        => 'template',
                'icon'        => '🍽️',
                'color'       => '#8b2500',
                'view'        => 'templates.restaurant',
                'tags'        => ['restaurant', 'food', 'menu', 'cafe', 'dining'],
            ],
            'template.ecommerce' => [
                'key'         => 'template.ecommerce',
                'name'        => 'StyleHub Store',
                'description' => 'Modern e-commerce template with product listings, categories, and newsletter',
                'category'    => 'E-commerce & Retail',
                'type'        => 'template',
                'icon'        => '🛒',
                'color'       => '#e11d48',
                'view'        => 'templates.ecommerce',
                'tags'        => ['shop', 'ecommerce', 'store', 'retail', 'fashion'],
            ],
            'template.portfolio' => [
                'key'         => 'template.portfolio',
                'name'        => 'Portfolio Pro',
                'description' => 'Clean portfolio for designers, developers, and creative professionals',
                'category'    => 'Portfolio & Personal',
                'type'        => 'template',
                'icon'        => '🎨',
                'color'       => '#7c3aed',
                'view'        => 'templates.portfolio',
                'tags'        => ['portfolio', 'freelancer', 'designer', 'developer', 'personal'],
            ],
            'template.saas' => [
                'key'         => 'template.saas',
                'name'        => 'DataFlow SaaS',
                'description' => 'High-converting SaaS landing page with features, pricing, and testimonials',
                'category'    => 'SaaS & Software',
                'type'        => 'template',
                'icon'        => '🚀',
                'color'       => '#2563eb',
                'view'        => 'templates.saas',
                'tags'        => ['saas', 'software', 'startup', 'app', 'product'],
            ],
            'template.agency' => [
                'key'         => 'template.agency',
                'name'        => 'PixelCraft Agency',
                'description' => 'Bold creative agency template with portfolio, services, and team sections',
                'category'    => 'Agency & Creative',
                'type'        => 'template',
                'icon'        => '⚡',
                'color'       => '#18181b',
                'view'        => 'templates.agency',
                'tags'        => ['agency', 'creative', 'design', 'studio', 'marketing'],
            ],
        ];
    }

    public function get(string $key): ?array
    {
        return $this->all()[$key] ?? null;
    }

    public function getView(string $key): ?string
    {
        return $this->all()[$key]['view'] ?? null;
    }

    public function byCategory(): array
    {
        $grouped = [];
        foreach ($this->all() as $template) {
            $grouped[$template['category']][] = $template;
        }
        return $grouped;
    }
}
