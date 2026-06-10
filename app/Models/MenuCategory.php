<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MenuCategory extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'color',
        'is_active',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    public const DEFAULTS = [
        ['name' => 'Admin Menu', 'slug' => 'admin_sidebar', 'description' => 'Menus shown in the dashboard sidebar.', 'color' => '#7c3aed', 'sort_order' => 10],
        ['name' => 'User Menu', 'slug' => 'user_topbar', 'description' => 'Menus shown in the dashboard top bar.', 'color' => '#b45309', 'sort_order' => 20],
        ['name' => 'Header Navigation', 'slug' => 'header', 'description' => 'Header menus for public pages.', 'color' => '#1d4ed8', 'sort_order' => 30],
        ['name' => 'Footer Navigation', 'slug' => 'footer', 'description' => 'Footer menus for public pages.', 'color' => '#15803d', 'sort_order' => 40],
        ['name' => 'Sidebar Navigation', 'slug' => 'sidebar', 'description' => 'Legacy sidebar menus.', 'color' => '#6d28d9', 'sort_order' => 50],
        ['name' => 'Custom Menu', 'slug' => 'custom', 'description' => 'General purpose custom menus.', 'color' => '#475569', 'sort_order' => 60],
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public static function ensureDefaultsForUser(int $userId): void
    {
        if (self::where('user_id', $userId)->exists()) {
            return;
        }

        foreach (self::DEFAULTS as $default) {
            self::create([
                ...$default,
                'user_id' => $userId,
                'is_active' => true,
                'is_system' => true,
            ]);
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
