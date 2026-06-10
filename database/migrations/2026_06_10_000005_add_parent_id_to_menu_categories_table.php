<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('user_id')
                ->constrained('menu_categories')
                ->nullOnDelete();

            $table->index(['user_id', 'parent_id', 'sort_order'], 'menu_categories_user_parent_sort_index');
        });
    }

    public function down(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex('menu_categories_user_parent_sort_index');
            $table->dropColumn('parent_id');
        });
    }
};
