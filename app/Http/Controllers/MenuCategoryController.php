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
            ->with('parent:id,user_id,parent_id,name,slug,color')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $menuCounts = Menu::where('user_id', Auth::id())
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $categories = $categories->map(function (MenuCategory $category) use ($menuCounts) {
            $category->menus_count = (int) ($menuCounts[$category->slug] ?? 0);
            $category->parent_name = $category->parent?->name;
            $category->parent_slug = $category->parent?->slug;
            return $category;
        });

        return view('menu_categories.index', compact('categories'));
    }

    // Lightweight AJAX endpoint — create a category and return the new option for the dropdown
    public function quickStore(Request $request): \Illuminate\Http\JsonResponse
    {
        MenuCategory::ensureDefaultsForUser(Auth::id());

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $slug = \Illuminate\Support\Str::slug($data['name']);
        $base = $slug ?: 'category';
        $i    = 0;
        while (MenuCategory::where('user_id', Auth::id())->where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        $category = MenuCategory::create([
            'user_id'   => Auth::id(),
            'name'      => $data['name'],
            'slug'      => $slug,
            'color'     => $data['color'] ?? '#6366f1',
            'is_active' => true,
            'is_system' => false,
        ]);

        return response()->json([
            'success'  => true,
            'category' => [
                'slug'  => $category->slug,
                'label' => $category->name,
                'color' => $category->color,
            ],
        ]);
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
            'parent_id' => $this->parentRules(),
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
            'parent_id' => $this->parentRules($menuCategory),
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

    private function parentRules(?MenuCategory $menuCategory = null): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('menu_categories', 'id')->where('user_id', Auth::id()),
            function (string $attribute, mixed $value, \Closure $fail) use ($menuCategory) {
                if (!$value || !$menuCategory) {
                    return;
                }

                if ((int) $value === $menuCategory->id) {
                    $fail('A menu category cannot be its own parent.');
                    return;
                }

                if ($this->wouldCreateParentLoop($menuCategory, (int) $value)) {
                    $fail('Select a top-level or sibling category as the parent.');
                }
            },
        ];
    }

    private function wouldCreateParentLoop(MenuCategory $menuCategory, int $parentId): bool
    {
        $next = MenuCategory::where('user_id', Auth::id())->find($parentId);
        $seen = [];

        while ($next) {
            if ($next->id === $menuCategory->id) {
                return true;
            }

            if (!$next->parent_id || in_array($next->parent_id, $seen, true)) {
                return false;
            }

            $seen[] = $next->id;
            $next = MenuCategory::where('user_id', Auth::id())->find($next->parent_id);
        }

        return false;
    }
}
