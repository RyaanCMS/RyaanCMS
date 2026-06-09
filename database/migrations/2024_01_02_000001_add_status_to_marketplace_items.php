<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_items', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('is_published');
            // pending | approved | rejected
            $table->text('rejection_reason')->nullable()->after('status');
            $table->string('package_file')->nullable()->after('rejection_reason');
            $table->string('demo_url_submission')->nullable()->after('package_file');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_items', function (Blueprint $table) {
            $table->dropColumn(['status', 'rejection_reason', 'package_file', 'demo_url_submission']);
        });
    }
};
