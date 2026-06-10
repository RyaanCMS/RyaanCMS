<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MenuCategory extends Model
{
    protected $fillable = [
        'user_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'color',
        'is_active',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'display_name',
    ];

    public const DEFAULTS = [
        ['name' => 'User',      'slug' => 'user',      'description' => 'Menus shown in the User section of the sidebar.',      'color' => '#6366f1', 'sort_order' => 1],
        ['name' => 'Developer', 'slug' => 'developer', 'description' => 'Menus shown in the Developer section of the sidebar.', 'color' => '#ec4899', 'sort_order' => 2],
        ['name' => 'Admin Menu', 'slug' => 'admin_sidebar', 'description' => 'Menus shown in the dashboard sidebar.', 'color' => '#7c3aed', 'sort_order' => 10],
        ['name' => 'Header Navigation', 'slug' => 'header', 'description' => 'Header menus for public pages.', 'color' => '#1d4ed8', 'sort_order' => 30],
        ['name' => 'Footer Navigation', 'slug' => 'footer', 'description' => 'Footer menus for public pages.', 'color' => '#15803d', 'sort_order' => 40],
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
        foreach (self::DEFAULTS as $default) {
            self::firstOrCreate(
                ['user_id' => $userId, 'slug' => $default['slug']],
                [
                    ...$default,
                    'is_active' => true,
                    'is_system' => true,
                ]
            );
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->parent ? "{$this->parent->name} / {$this->name}" : $this->name;
    }
}
