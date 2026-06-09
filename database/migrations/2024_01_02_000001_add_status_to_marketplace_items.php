<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_items', function (Blueprint $table) {
            if (!Schema::hasColumn('marketplace_items', 'status')) {
                $table->string('status')->default('pending')->after('is_published');
            }
            if (!Schema::hasColumn('marketplace_items', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('marketplace_items', 'demo_url_submission')) {
                $table->string('demo_url_submission')->nullable()->after('rejection_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_items', function (Blueprint $table) {
            $cols = array_filter(
                ['status', 'rejection_reason', 'demo_url_submission'],
                fn($c) => Schema::hasColumn('marketplace_items', $c)
            );
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
