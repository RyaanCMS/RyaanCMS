<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->string('url', 500)->nullable()->after('category');
            $table->string('icon', 500)->nullable()->after('url');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['url', 'icon', 'sort_order']);
        });
    }
};
