<?php

namespace Database\Seeders;

use App\Models\MarketplaceItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) return;

        $items = [
            [
                'name'         => 'eCommerce Pro',
                'slug'         => 'ecommerce-pro',
                'description'  => 'Complete eCommerce solution with product management, cart, orders, and payment gateway integration.',
                'category'     => 'ecommerce',
                'type'         => 'module',
                'price'        => 0.00,
                'is_free'      => true,
                'is_published' => true,
                'is_featured'  => true,
                'version'      => '1.0.0',
                'tags'         => json_encode(['ecommerce', 'shop', 'cart', 'payment']),
            ],
            [
                'name'         => 'CRM Module',
                'slug'         => 'crm-module',
                'description'  => 'Customer relationship management with leads, contacts, deals, and activity tracking.',
                'category'     => 'crm',
                'type'         => 'module',
                'price'        => 0.00,
                'is_free'      => true,
                'is_published' => true,
                'is_featured'  => true,
                'version'      => '1.0.0',
                'tags'         => json_encode(['crm', 'leads', 'sales', 'contacts']),
            ],
            [
                'name'         => 'AI Chatbot Agent',
                'slug'         => 'ai-chatbot-agent',
                'description'  => 'Intelligent chatbot agent powered by AI for customer support and lead generation.',
                'category'     => 'ai_agents',
                'type'         => 'agent',
                'price'        => 0.00,
                'is_free'      => true,
                'is_published' => true,
                'is_featured'  => true,
                'version'      => '1.0.0',
                'tags'         => json_encode(['ai', 'chatbot', 'support', 'automation']),
            ],
            [
                'name'         => 'HRM & Payroll',
                'slug'         => 'hrm-payroll',
                'description'  => 'Human resource management with employee profiles, attendance, leaves, and payroll.',
                'category'     => 'hrm',
                'type'         => 'module',
                'price'        => 0.00,
                'is_free'      => true,
                'is_published' => true,
                'is_featured'  => false,
                'version'      => '1.0.0',
                'tags'         => json_encode(['hrm', 'payroll', 'employees', 'hr']),
            ],
            [
                'name'         => 'Dark Pro Theme',
                'slug'         => 'dark-pro-theme',
                'description'  => 'Modern dark professional theme with multiple color schemes and layout options.',
                'category'     => 'themes',
                'type'         => 'theme',
                'price'        => 0.00,
                'is_free'      => true,
                'is_published' => true,
                'is_featured'  => false,
                'version'      => '1.0.0',
                'tags'         => json_encode(['theme', 'dark', 'modern', 'responsive']),
            ],
        ];

        foreach ($items as $item) {
            MarketplaceItem::updateOrCreate(
                ['slug' => $item['slug']],
                array_merge($item, [
                    'user_id'      => $admin->id,
                    'published_at' => now(),
                ])
            );
        }
    }
}
