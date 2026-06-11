<?php

namespace App\Services\Menu;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\User;
use Illuminate\Support\Str;

class DefaultSidebarMenuImporter
{
    private const USER_MENU_CATEGORY      = 'user';
    private const DEVELOPER_MENU_CATEGORY = 'developer';

    public function ensureForUser(User $user): void
    {
        MenuCategory::ensureDefaultsForUser($user->id);
        $this->seedDeveloperMenus($user);
        // USER category is intentionally left empty — admins populate it via Menus UI
    }

    private function seedDeveloperMenus(User $user): void
    {
        $items = [
            ['name' => 'Dashboard',        'url' => route('dashboard'),               'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'sort_order' => 10],
            ['name' => 'My Apps',          'url' => route('projects.index'),          'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'sort_order' => 20],
            ['name' => 'AI Builder',       'url' => route('projects.index'),          'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4', 'sort_order' => 30],
            ['name' => 'Marketplace',      'url' => route('marketplace.index'),       'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'sort_order' => 40],
            ['name' => 'Templates',        'url' => route('marketplace.templates'),   'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z', 'sort_order' => 50],
            ['name' => 'AI Knowledge',     'url' => route('wisdom.index'),            'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'sort_order' => 60],
            ['name' => 'Menus',            'url' => route('menus.index'),             'icon' => 'M4 6h16M4 12h16M4 18h7', 'sort_order' => 70],
            ['name' => 'Menu Categories',  'url' => route('menu-categories.index'),   'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z', 'sort_order' => 80],
            ['name' => 'Settings',         'url' => route('settings.index'),          'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'sort_order' => 90],
        ];

        // Only create menus that do not yet exist — never delete user-customised menus.
        // A menu is identified by its slug (user-scoped). If a user deleted a menu,
        // it stays deleted. New items added here are created for existing users on
        // next page load.
        foreach ($items as $item) {
            $slug = 'dev-' . Str::slug($item['name']) . '-' . $user->id;
            Menu::firstOrCreate(
                ['slug' => $slug, 'user_id' => $user->id],
                array_merge($item, [
                    'user_id'   => $user->id,
                    'slug'      => $slug,
                    'category'  => self::DEVELOPER_MENU_CATEGORY,
                    'is_active' => true,
                ])
            );
        }
    }
}
