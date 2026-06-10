<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->string('description')->nullable();
            $table->string('color', 7)->default('#475569');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'is_active', 'sort_order']);
        });

        $defaults = [
            ['name' => 'Admin Menu', 'slug' => 'admin_sidebar', 'description' => 'Menus shown in the dashboard sidebar.', 'color' => '#7c3aed', 'sort_order' => 10],
            ['name' => 'User Menu', 'slug' => 'user_topbar', 'description' => 'Menus shown in the dashboard top bar.', 'color' => '#b45309', 'sort_order' => 20],
            ['name' => 'Header Navigation', 'slug' => 'header', 'description' => 'Header menus for public pages.', 'color' => '#1d4ed8', 'sort_order' => 30],
            ['name' => 'Footer Navigation', 'slug' => 'footer', 'description' => 'Footer menus for public pages.', 'color' => '#15803d', 'sort_order' => 40],
            ['name' => 'Sidebar Navigation', 'slug' => 'sidebar', 'description' => 'Legacy sidebar menus.', 'color' => '#6d28d9', 'sort_order' => 50],
            ['name' => 'Custom Menu', 'slug' => 'custom', 'description' => 'General purpose custom menus.', 'color' => '#475569', 'sort_order' => 60],
        ];

        $now = now();
        foreach (DB::table('users')->pluck('id') as $userId) {
            foreach ($defaults as $default) {
                DB::table('menu_categories')->insert([
                    'user_id' => $userId,
                    'name' => $default['name'],
                    'slug' => $default['slug'],
                    'description' => $default['description'],
                    'color' => $default['color'],
                    'is_active' => true,
                    'is_system' => true,
                    'sort_order' => $default['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_categories');
    }
};
