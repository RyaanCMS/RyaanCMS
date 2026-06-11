<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Anonymized prompt intent telemetry — no raw prompts stored, ever.
        // Only structural signals: domain, entities, language, country.
        Schema::create('central_telemetry', function (Blueprint $table) {
            $table->id();
            $table->string('domain_hash', 64)->index();     // SHA-256 of domain — no PII
            $table->string('license_key', 40)->nullable()->index();
            $table->string('intent_domain', 100)->nullable(); // healthcare|ecommerce|hrm…
            $table->json('entities')->nullable();            // ["patient","appointment","billing"]
            $table->json('functions')->nullable();           // ["crud","auth","reporting"]
            $table->string('language', 10)->nullable();      // en|bn|hi|ar…
            $table->string('country', 5)->nullable();        // BD|US|IN…
            $table->string('generation_type', 40)->nullable();// blueprint|ai_step3|feature
            $table->string('app_version', 20)->nullable();
            $table->boolean('used_ai')->default(false);
            $table->timestamp('recorded_at')->useCurrent();

            $table->index(['intent_domain', 'recorded_at']);
            $table->index(['language', 'country']);
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('central_telemetry');
    }
};
