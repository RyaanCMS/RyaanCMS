<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Services\Menu\DefaultSidebarMenuImporter;
use App\Models\MenuItem;
use App\Models\MarketplaceInstallation;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index()
    {
        app(DefaultSidebarMenuImporter::class)->ensureForUser(Auth::user());
        $menuCategories = $this->menuCategories();
        $categoryOptions = $this->categoryOptions($menuCategories);
        $menus = Menu::where('user_id', Auth::id())->latest()->get();
        return view('menus.index', compact('menus', 'menuCategories', 'categoryOptions'));
    }

    public function create()
    {
        $menuCategories = $this->menuCategories();
        return view('menus.create', compact('menuCategories'));
    }

    public function store(Request $request)
    {
        $this->menuCategories();

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'category'   => ['required', Rule::exists('menu_categories', 'slug')->where('user_id', Auth::id())],
            'url'        => ['nullable', 'string', 'max:500'],
            'icon'       => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['user_id'] = Auth::id();
        $data['slug']    = Str::slug($data['name'] . '-' . uniqid());

        $menu = Menu::create($data);

        return redirect()->route('menus.index')->with('success', 'Menu created.');
    }

    public function edit(Menu $menu)
    {
        $this->checkOwner($menu);
        $menu->load(['items.children', 'items.installation.item']);

        $installations = MarketplaceInstallation::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('item')
            ->get();

        $projects = Project::where('user_id', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'deployment_url']);

        $allItems = $menu->allItems()->with(['installation.item', 'children'])->whereNull('parent_id')->orderBy('order')->get();
        $tableItems = $menu->allItems()
            ->with(['parent', 'installation.item'])
            ->orderBy('order')
            ->orderBy('label')
            ->get();
        $menuCategories = $this->menuCategories();

        // Build categorised quick-link list for the dropdown
        $siteLinks = [];

        // System pages
        $systemPages = [
            ['label' => 'Home',        'url' => url('/')],
            ['label' => 'Dashboard',   'url' => route('dashboard')],
            ['label' => 'Login',       'url' => route('login')],
            ['label' => 'Register',    'url' => route('register')],
            ['label' => 'Marketplace', 'url' => route('marketplace.index')],
            ['label' => 'Menus',       'url' => route('menus.index')],
            ['label' => 'Settings',    'url' => route('settings.index')],
            ['label' => 'Terms',       'url' => route('terms')],
            ['label' => 'Privacy',     'url' => route('privacy')],
        ];
        if (!empty($systemPages)) {
            $siteLinks['System Pages'] = $systemPages;
        }

        // User's builder projects — preview URL opens full-page app, builder URL opens editor
        if ($projects->isNotEmpty()) {
            $previewLinks = [];
            $builderLinks = [];
            foreach ($projects as $p) {
                $previewLinks[] = [
                    'label' => $p->name,
                    'url'   => $p->deployment_url ?: route('builder.preview', $p),
                ];
                $builderLinks[] = [
                    'label' => $p->name . ' (Builder)',
                    'url'   => route('builder.show', $p),
                ];
            }
            $siteLinks['Your Projects'] = $previewLinks;
            $siteLinks['Project Builders'] = $builderLinks;
        }

        // Installed marketplace apps
        if ($installations->isNotEmpty()) {
            $siteLinks['Installed Apps'] = $installations->map(fn($i) => [
                'label' => $i->item->name ?? 'App #' . $i->id,
                'url'   => url('/marketplace/' . $i->marketplace_item_id),
            ])->toArray();
        }

        return view('menus.edit', compact('menu', 'installations', 'allItems', 'tableItems', 'siteLinks', 'menuCategories'));
    }

    public function update(Request $request, Menu $menu)
    {
        $this->checkOwner($menu);
        $this->menuCategories();

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'category'   => ['required', Rule::exists('menu_categories', 'slug')->where('user_id', Auth::id())],
            'url'        => ['nullable', 'string', 'max:500'],
            'icon'       => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active'  => ['boolean'],
        ]);
        $menu->update($data);
        return back()->with('success', 'Menu updated.');
    }

    public function destroy(Menu $menu)
    {
        $this->checkOwner($menu);
        $menu->delete();
        return redirect()->route('menus.index')->with('success', 'Menu deleted.');
    }

    public function itemsData(Menu $menu): \Illuminate\Http\JsonResponse
    {
        $this->checkOwner($menu);
        return response()->json([
            'success' => true,
            'menu'    => ['id' => $menu->id, 'name' => $menu->name],
            'items'   => $this->getItemsArray($menu),
        ]);
    }

    public function storeItem(Request $request, Menu $menu)
    {
        $this->checkOwner($menu);
        $data = $request->validate([
            'label'     => ['required', 'string', 'max:100'],
            'url'       => ['nullable', 'string', 'max:500'],
            'target'    => ['nullable', 'in:_self,_blank'],
            'parent_id' => ['nullable', 'exists:menu_items,id'],
        ]);
        $this->checkParentBelongsToMenu($menu, $data['parent_id'] ?? null);

        $data['menu_id'] = $menu->id;
        $data['order']   = MenuItem::where('menu_id', $menu->id)->max('order') + 1;

        MenuItem::create($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'items' => $this->getItemsArray($menu)]);
        }
        return back()->with('success', 'Menu item added.');
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $item)
    {
        $this->checkOwner($menu);
        $this->checkItemBelongsToMenu($menu, $item);

        $data = $request->validate([
            'label'     => ['required', 'string', 'max:100'],
            'url'       => ['nullable', 'string', 'max:500'],
            'target'    => ['nullable', 'in:_self,_blank'],
            'parent_id' => ['nullable', 'exists:menu_items,id'],
            'is_active' => ['boolean'],
        ]);
        $this->checkParentBelongsToMenu($menu, $data['parent_id'] ?? null, $item);

        $item->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'items' => $this->getItemsArray($menu)]);
        }
        return back()->with('success', 'Menu item updated.');
    }

    public function destroyItem(Request $request, Menu $menu, MenuItem $item)
    {
        $this->checkOwner($menu);
        $this->checkItemBelongsToMenu($menu, $item);
        $item->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'items' => $this->getItemsArray($menu)]);
        }
        return back()->with('success', 'Menu item deleted.');
    }

    private function getItemsArray(Menu $menu): array
    {
        return $menu->allItems()
            ->with('parent:id,label')
            ->orderBy('order')
            ->orderBy('label')
            ->get()
            ->map(fn($i) => [
                'id'           => $i->id,
                'label'        => $i->label,
                'url'          => $i->url ?? '',
                'target'       => $i->target ?? '_self',
                'parent_id'    => $i->parent_id,
                'parent_label' => $i->parent?->label,
                'is_active'    => (bool) $i->is_active,
            ])
            ->toArray();
    }

    public function reorderItems(Request $request, Menu $menu)
    {
        $this->checkOwner($menu);
        $request->validate(['items' => ['required', 'array']]);
        foreach ($request->items as $order => $id) {
            MenuItem::where('id', $id)->where('menu_id', $menu->id)->update(['order' => $order]);
        }
        return response()->json(['success' => true]);
    }

    private function checkOwner(Menu $menu): void
    {
        if ($menu->user_id !== Auth::id()) abort(403);
    }

    private function menuCategories()
    {
        MenuCategory::ensureDefaultsForUser(Auth::id());

        return MenuCategory::where('user_id', Auth::id())
            ->with('parent:id,user_id,parent_id,name,slug')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function categoryOptions($menuCategories): array
    {
        return $menuCategories
            ->map(fn (MenuCategory $category) => [
                'slug' => $category->slug,
                'name' => $category->name,
                'label' => $category->display_name,
                'parent_id' => $category->parent_id,
                'parent_name' => $category->parent?->name,
                'description' => $category->description,
                'color' => $category->color,
                'is_active' => $category->is_active,
                'is_system' => $category->is_system,
            ])
            ->values()
            ->toArray();
    }

    private function checkItemBelongsToMenu(Menu $menu, MenuItem $item): void
    {
        if ($item->menu_id !== $menu->id) abort(404);
    }

    private function checkParentBelongsToMenu(Menu $menu, mixed $parentId, ?MenuItem $item = null): void
    {
        if (empty($parentId)) return;

        if ($item && (int) $parentId === $item->id) abort(422);

        $parentExists = MenuItem::where('id', $parentId)
            ->where('menu_id', $menu->id)
            ->exists();

        if (!$parentExists) abort(422);
    }
}
