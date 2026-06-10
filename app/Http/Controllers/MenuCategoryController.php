<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Services\Menu\DefaultSidebarMenuImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MenuCategoryController extends Controller
{
    public function index()
    {
        app(DefaultSidebarMenuImporter::class)->ensureForUser(Auth::user());

        $categories = MenuCategory::where('user_id', Auth::id())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $menuCounts = Menu::where('user_id', Auth::id())
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $categories = $categories->map(function (MenuCategory $category) use ($menuCounts) {
            $category->menus_count = (int) ($menuCounts[$category->slug] ?? 0);
            return $category;
        });

        return view('menu_categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        MenuCategory::ensureDefaultsForUser(Auth::id());
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('name')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('menu_categories', 'slug')->where('user_id', Auth::id()),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['user_id'] = Auth::id();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_system'] = false;

        MenuCategory::create($data);

        return redirect()->route('menu-categories.index')->with('success', 'Menu category created.');
    }

    public function update(Request $request, MenuCategory $menuCategory)
    {
        $this->checkOwner($menuCategory);

        if (!$menuCategory->is_system) {
            $request->merge([
                'slug' => Str::slug($request->input('slug') ?: $request->input('name')),
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                $menuCategory->is_system ? 'nullable' : 'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('menu_categories', 'slug')
                    ->where('user_id', Auth::id())
                    ->ignore($menuCategory->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldSlug = $menuCategory->slug;
        if ($menuCategory->is_system) {
            $data['slug'] = $oldSlug;
        }
        $data['is_active'] = $request->boolean('is_active');

        $menuCategory->update($data);

        if ($oldSlug !== $menuCategory->slug) {
            Menu::where('user_id', Auth::id())
                ->where('category', $oldSlug)
                ->update(['category' => $menuCategory->slug]);
        }

        return back()->with('success', 'Menu category updated.');
    }

    public function destroy(MenuCategory $menuCategory)
    {
        $this->checkOwner($menuCategory);

        if ($menuCategory->is_system) {
            return back()->with('error', 'System menu categories cannot be deleted.');
        }

        $menusUsingCategory = Menu::where('user_id', Auth::id())
            ->where('category', $menuCategory->slug)
            ->count();

        if ($menusUsingCategory > 0) {
            return back()->with('error', 'Move menus out of this category before deleting it.');
        }

        $menuCategory->delete();

        return back()->with('success', 'Menu category deleted.');
    }

    private function checkOwner(MenuCategory $menuCategory): void
    {
        abort_if($menuCategory->user_id !== Auth::id(), 403);
    }
}
