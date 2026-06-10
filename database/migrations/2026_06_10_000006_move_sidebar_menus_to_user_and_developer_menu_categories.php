<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const USER_MENU = [
        'name' => 'User Menu',
        'slug' => 'user_topbar',
        'description' => 'Built-in user menus and submenus.',
        'color' => '#6366f1',
        'sort_order' => 5,
    ];

    private const DEVELOPER_MENU = [
        'name' => 'Developer Menu',
        'slug' => 'developer_menu',
        'description' => 'Built-in developer menus and submenus.',
        'color' => '#ec4899',
        'sort_order' => 6,
    ];

    public function up(): void
    {
        $now = now();

        foreach (DB::table('users')->pluck('id') as $userId) {
            $this->ensureCategory($userId, self::USER_MENU, $now);
            $this->ensureCategory($userId, self::DEVELOPER_MENU, $now);

            DB::table('menus')
                ->where('user_id', $userId)
                ->whereIn('category', ['user_sidebar', 'sidebar'])
                ->update(['category' => self::USER_MENU['slug'], 'updated_at' => $now]);

            DB::table('menus')
                ->where('user_id', $userId)
                ->where('category', 'developer_sidebar')
                ->update(['category' => self::DEVELOPER_MENU['slug'], 'updated_at' => $now]);

            $this->renameSystemMenu($userId, 'system-user_sidebar-'.$userId, 'system-user_topbar-'.$userId, self::USER_MENU, $now);
            $this->renameSystemMenu($userId, 'system-developer_sidebar-'.$userId, 'system-developer_menu-'.$userId, self::DEVELOPER_MENU, $now);

            DB::table('menu_categories')
                ->where('user_id', $userId)
                ->whereIn('slug', ['user_sidebar', 'developer_sidebar', 'sidebar'])
                ->delete();
        }
    }

    public function down(): void
    {
        $now = now();

        foreach (DB::table('users')->pluck('id') as $userId) {
            $this->ensureCategory($userId, [
                'name' => 'User Sidebar',
                'slug' => 'user_sidebar',
                'description' => 'Built-in user sidebar menus and submenus.',
                'color' => '#6366f1',
                'sort_order' => 5,
            ], $now);

            $this->ensureCategory($userId, [
                'name' => 'Developer Sidebar',
                'slug' => 'developer_sidebar',
                'description' => 'Built-in developer sidebar menus and submenus.',
                'color' => '#ec4899',
                'sort_order' => 6,
            ], $now);

            $this->renameSystemMenu($userId, 'system-user_topbar-'.$userId, 'system-user_sidebar-'.$userId, [
                'name' => 'User Sidebar',
                'slug' => 'user_sidebar',
            ], $now);

            $this->renameSystemMenu($userId, 'system-developer_menu-'.$userId, 'system-developer_sidebar-'.$userId, [
                'name' => 'Developer Sidebar',
                'slug' => 'developer_sidebar',
            ], $now);
        }
    }

    private function ensureCategory(int $userId, array $category, mixed $now): void
    {
        $exists = DB::table('menu_categories')
            ->where('user_id', $userId)
            ->where('slug', $category['slug'])
            ->exists();

        if ($exists) {
            DB::table('menu_categories')
                ->where('user_id', $userId)
                ->where('slug', $category['slug'])
                ->update([
                    'name' => $category['name'],
                    'description' => $category['description'] ?? null,
                    'is_active' => true,
                    'is_system' => true,
                    'sort_order' => $category['sort_order'] ?? 0,
                    'updated_at' => $now,
                ]);

            return;
        }

        DB::table('menu_categories')->insert([
            'user_id' => $userId,
            'parent_id' => null,
            'name' => $category['name'],
            'slug' => $category['slug'],
            'description' => $category['description'] ?? null,
            'color' => $category['color'] ?? '#475569',
            'is_active' => true,
            'is_system' => true,
            'sort_order' => $category['sort_order'] ?? 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function renameSystemMenu(int $userId, string $oldSlug, string $newSlug, array $category, mixed $now): void
    {
        $menu = DB::table('menus')
            ->where('user_id', $userId)
            ->whereIn('slug', [$oldSlug, $newSlug])
            ->orderByRaw("CASE WHEN slug = ? THEN 0 ELSE 1 END", [$oldSlug])
            ->first();

        if (!$menu) {
            return;
        }

        $updates = [
            'name' => $category['name'],
            'category' => $category['slug'],
            'is_active' => true,
            'updated_at' => $now,
        ];

        $slugTaken = DB::table('menus')
            ->where('slug', $newSlug)
            ->where('id', '<>', $menu->id)
            ->exists();

        if (!$slugTaken) {
            $updates['slug'] = $newSlug;
        }

        DB::table('menus')->where('id', $menu->id)->update($updates);
    }
};
