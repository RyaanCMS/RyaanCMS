<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // License + package fields on installations
        Schema::table('marketplace_installations', function (Blueprint $table) {
            $table->uuid('license_key')->nullable()->unique()->after('status');
            $table->string('domain')->nullable()->after('license_key');
            $table->timestamp('activated_at')->nullable()->after('domain');
            $table->string('package_path')->nullable()->after('activated_at');
        });

        // Developer packaging + menu metadata on items
        Schema::table('marketplace_items', function (Blueprint $table) {
            $table->json('menu_items')->nullable()->after('tags');   // [{label,icon_path,color,route}]
            $table->string('icon')->nullable()->after('menu_items'); // emoji or short token
            $table->string('icon_color')->nullable()->after('icon');
            $table->string('package_file')->nullable()->after('download_url'); // stored zip path
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_installations', function (Blueprint $table) {
            $table->dropColumn(['license_key', 'domain', 'activated_at', 'package_path']);
        });
        Schema::table('marketplace_items', function (Blueprint $table) {
            $table->dropColumn(['menu_items', 'icon', 'icon_color', 'package_file']);
        });
    }
};
