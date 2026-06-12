<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_intelligence_registry', function (Blueprint $table) {
            $table->id();

            // Asset identity
            $table->string('asset_type', 32)->index();    // workflow, module, blueprint, error_fix, etc.
            $table->string('domain', 64)->index();        // hospital, lms, ecommerce, etc.
            $table->string('extension', 64)->nullable();  // veterinary, dental, etc.
            $table->string('name', 255);                  // human-readable asset name
            $table->char('fingerprint', 32)->unique()->index(); // deduplication key

            // Structural intelligence (no PII, no customer data)
            $table->json('dependencies')->nullable();     // ['billing', 'inventory']
            $table->json('tags')->nullable();             // ['multi-tenant', 'approval', 'hipaa']
            $table->json('business_rules')->nullable();   // implied rules from the prompt

            // Privacy-safe prompt summary (max 200 chars, PII stripped)
            $table->string('prompt_summary', 200)->nullable();

            // Quality signals
            $table->unsignedTinyInteger('confidence')->default(50);        // 0–100: domain coverage confidence
            $table->unsignedTinyInteger('extraction_quality')->default(0); // 0–100: quality of this extraction
            $table->unsignedInteger('seen_count')->default(1)->index();    // how many times we've seen this

            // Export lifecycle
            $table->boolean('export_ready')->default(false)->index(); // quality >= 70 → ready to export
            $table->boolean('is_validated')->default(false);          // validated by RGIN cloud
            $table->boolean('is_exported')->default(false)->index();  // already uploaded to RGIN cloud

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_intelligence_registry');
    }
};
