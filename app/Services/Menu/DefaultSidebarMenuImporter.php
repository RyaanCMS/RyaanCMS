<?php

namespace App\Services\Menu;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;

class DefaultSidebarMenuImporter
{
    private const USER_MENU_CATEGORY = 'user_topbar';
    private const DEVELOPER_MENU_CATEGORY = 'developer_menu';

    public function ensureForUser(User $user): void
    {
        MenuCategory::ensureDefaultsForUser($user->id);

        $userMenu = $this->ensureMenu($user, 'User Menu', self::USER_MENU_CATEGORY, ['user_sidebar', 'sidebar']);
        $this->seedUserSidebar($userMenu, $user);

        $developerMenu = $this->ensureMenu($user, 'Developer Menu', self::DEVELOPER_MENU_CATEGORY, ['developer_sidebar']);
        $this->seedDeveloperSidebar($developerMenu, $user);
    }

    private function ensureMenu(User $user, string $name, string $category, array $legacyCategories = []): Menu
    {
        $slug = "system-{$category}-{$user->id}";
        $legacySlugs = collect($legacyCategories)
            ->map(fn (string $legacyCategory) => "system-{$legacyCategory}-{$user->id}")
            ->all();

        $menu = Menu::where('user_id', $user->id)
            ->whereIn('slug', [$slug, ...$legacySlugs])
            ->orderByRaw('CASE WHEN slug = ? THEN 0 ELSE 1 END', [$slug])
            ->first();

        if (!$menu) {
            $menu = Menu::where('user_id', $user->id)
                ->whereIn('category', $legacyCategories)
                ->where('name', 'like', '%Sidebar%')
                ->first();
        }

        if ($menu) {
            $menu->name = $name;
            $menu->category = $category;
            $menu->is_active = true;

            $slugTaken = Menu::where('slug', $slug)
                ->where('id', '<>', $menu->id)
                ->exists();

            if (!$slugTaken) {
                $menu->slug = $slug;
            }

            $menu->save();

            return $menu;
        }

        return Menu::create([
            'user_id' => $user->id,
            'slug' => $slug,
            'name' => $name,
            'category' => $category,
            'is_active' => true,
        ]);
    }

    private function seedUserSidebar(Menu $menu, User $user): void
    {
        $this->createItem($menu, null, [
            'label' => 'Dashboard',
            'url' => route('dashboard'),
            'icon' => 'M3 12h18M3 6h18M3 18h18',
            'order' => 10,
        ]);

        $myApps = $this->createItem($menu, null, [
            'label' => 'My Apps',
            'url' => route('projects.index'),
            'icon' => 'M3 7h18v12H3z',
            'order' => 20,
        ]);

        $projects = Project::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get(['id', 'name']);

        foreach ($projects as $index => $project) {
            $this->createItem($menu, $myApps, [
                'label' => Str::limit($project->name, 40, ''),
                'url' => route('builder.show', $project),
                'icon' => 'M4 6h16v12H4z',
                'order' => 100 + $index,
            ]);
        }
    }

    private function seedDeveloperSidebar(Menu $menu, User $user): void
    {
        $this->createItem($menu, null, [
            'label' => 'Marketplace',
            'url' => route('marketplace.index'),
            'icon' => 'M5 9h14l1 12H4L5 9z',
            'order' => 10,
        ]);

        $this->createItem($menu, null, [
            'label' => 'AI Knowledge',
            'url' => route('wisdom.index'),
            'icon' => 'M12 3v18M3 12h18',
            'order' => 20,
        ]);

        $menus = $this->createItem($menu, null, [
            'label' => 'Menus',
            'url' => route('menus.index'),
            'icon' => 'M4 6h16M4 12h16M4 18h7',
            'order' => 30,
        ]);

        $this->createItem($menu, $menus, [
            'label' => 'Menu Management',
            'url' => route('menus.index'),
            'icon' => 'M4 6h16M4 12h16M4 18h7',
            'order' => 10,
        ]);

        $this->createItem($menu, $menus, [
            'label' => 'Menu Categories',
            'url' => route('menu-categories.index'),
            'icon' => 'M7 7h10M7 12h10M7 17h10',
            'order' => 20,
        ]);

        $this->createItem($menu, null, [
            'label' => 'Settings',
            'url' => route('settings.index'),
            'icon' => 'M12 8a4 4 0 100 8 4 4 0 000-8z',
            'order' => 40,
        ]);

        if ($user->isAdmin()) {
            $this->createItem($menu, null, [
                'label' => 'Admin Panel',
                'url' => route('marketplace.admin.panel'),
                'icon' => 'M12 3l8 4v5c0 5-3 8-8 9-5-1-8-4-8-9V7z',
                'order' => 50,
            ]);
        }
    }

    private function createItem(Menu $menu, ?MenuItem $parent, array $data): MenuItem
    {
        $item = MenuItem::firstOrNew([
            'menu_id' => $menu->id,
            'parent_id' => $parent?->id,
            'label' => $data['label'],
        ]);

        $item->fill([
            'url' => $data['url'],
            'icon' => $data['icon'],
            'target' => '_self',
            'order' => $data['order'],
            'is_active' => true,
        ]);
        $item->save();

        return $item;
    }
}
