<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outcome_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pipeline_run_id')->nullable()->constrained('pipeline_runs')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('domain', 64)->default('general');

            // Build statistics
            $table->unsignedInteger('files_generated')->default(0);
            $table->unsignedInteger('tokens_used')->default(0);
            $table->decimal('ai_cost_estimate', 10, 6)->default(0);
            $table->decimal('ai_cost_saved', 10, 6)->default(0);
            $table->unsignedInteger('build_time_seconds')->default(0);
            $table->unsignedTinyInteger('quality_score')->nullable();
            $table->string('quality_grade', 4)->nullable();

            // Source tracking — what was reused vs generated
            $table->string('blueprint_source', 32)->default('ai_discovery');
            $table->json('modules_used')->nullable();
            $table->json('components_reused')->nullable();
            $table->json('rules_applied')->nullable();
            $table->json('confidence_scores')->nullable();
            $table->boolean('ai_was_used')->default(true);

            // Business outcome metrics (filled in by user or automated tracking)
            $table->decimal('revenue_impact', 15, 2)->nullable()->comment('Estimated revenue increase');
            $table->decimal('cost_reduction', 15, 2)->nullable()->comment('Estimated cost saved');
            $table->decimal('time_saved_hours', 8, 2)->nullable()->comment('Dev hours saved');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'created_at']);
            $table->index(['user_id', 'domain']);
            $table->index('blueprint_source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outcome_records');
    }
};
