<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const RENAMES = [
        ['old_slug' => 'user_topbar',   'new_slug' => 'user',      'name' => 'User',      'color' => '#6366f1', 'sort_order' => 1],
        ['old_slug' => 'developer_menu','new_slug' => 'developer', 'name' => 'Developer', 'color' => '#ec4899', 'sort_order' => 2],
    ];

    public function up(): void
    {
        $now = now();

        foreach (DB::table('users')->pluck('id') as $userId) {
            foreach (self::RENAMES as $r) {
                $exists = DB::table('menu_categories')
                    ->where('user_id', $userId)->where('slug', $r['new_slug'])->exists();

                if ($exists) {
                    // already migrated — just make sure the name/sort_order are correct
                    DB::table('menu_categories')
                        ->where('user_id', $userId)->where('slug', $r['new_slug'])
                        ->update(['name' => $r['name'], 'sort_order' => $r['sort_order'], 'updated_at' => $now]);
                } else {
                    $old = DB::table('menu_categories')
                        ->where('user_id', $userId)->where('slug', $r['old_slug'])->first();

                    if ($old) {
                        DB::table('menu_categories')->where('id', $old->id)
                            ->update(['slug' => $r['new_slug'], 'name' => $r['name'], 'sort_order' => $r['sort_order'], 'updated_at' => $now]);
                    } else {
                        DB::table('menu_categories')->insert([
                            'user_id'    => $userId,
                            'parent_id'  => null,
                            'name'       => $r['name'],
                            'slug'       => $r['new_slug'],
                            'description'=> null,
                            'color'      => $r['color'],
                            'is_active'  => true,
                            'is_system'  => true,
                            'sort_order' => $r['sort_order'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }

                // Re-point menus from old slug → new slug
                DB::table('menus')
                    ->where('user_id', $userId)->where('category', $r['old_slug'])
                    ->update(['category' => $r['new_slug'], 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        $now = now();

        foreach (DB::table('users')->pluck('id') as $userId) {
            foreach (self::RENAMES as $r) {
                DB::table('menu_categories')
                    ->where('user_id', $userId)->where('slug', $r['new_slug'])
                    ->update(['slug' => $r['old_slug'], 'name' => ucwords(str_replace('_', ' ', $r['old_slug'])), 'updated_at' => $now]);

                DB::table('menus')
                    ->where('user_id', $userId)->where('category', $r['new_slug'])
                    ->update(['category' => $r['old_slug'], 'updated_at' => $now]);
            }
        }
    }
};
