<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_generation_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 64)->index();
            $table->string('extension', 64)->nullable();
            $table->unsignedTinyInteger('coverage_pct')->default(0);  // 0–100
            $table->unsignedSmallInteger('assets_reused')->default(0);
            $table->unsignedSmallInteger('gaps_count')->default(0);
            $table->boolean('ai_needed')->default(false)->index();
            $table->unsignedSmallInteger('hours_saved')->default(0);   // estimated dev hours
            $table->json('composed_from')->nullable();                  // multi-domain composition list
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_generation_metrics');
    }
};
