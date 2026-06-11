<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Local copy of packs synced from Central Registry ─────────────
        Schema::create('local_question_packs', function (Blueprint $table) {
            $table->id();
            $table->uuid('central_pack_uuid')->nullable()->index();   // null = locally seeded
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('industry');
            $table->string('blueprint_slug')->nullable();
            $table->string('country', 10)->default('ALL');            // BD, IN, ALL …
            $table->string('language', 10)->default('en');
            $table->string('version')->default('1.0.0');
            $table->string('status')->default('active');              // active|deprecated
            $table->string('visibility')->default('public');          // public|premium
            $table->boolean('is_premium')->default(false);
            $table->json('questions_json')->nullable();               // array of question objects
            $table->json('rules_json')->nullable();                   // optional rules array
            $table->json('metadata')->nullable();                     // extra fields (icon, desc…)
            $table->timestamp('synced_at')->nullable();               // last synced from central
            $table->string('license_status')->default('free');        // free|licensed|expired
            $table->timestamps();

            $table->index(['industry', 'country', 'language']);
            $table->index(['blueprint_slug', 'country']);
        });

        // ── Sync audit log ────────────────────────────────────────────────
        Schema::create('registry_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('registry_url');
            $table->string('action');                                  // sync|verify|download|check
            $table->integer('packs_checked')->default(0);
            $table->integer('packs_updated')->default(0);
            $table->integer('packs_skipped')->default(0);
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->json('details')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamp('synced_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_question_packs');
        Schema::dropIfExists('registry_sync_logs');
    }
};
