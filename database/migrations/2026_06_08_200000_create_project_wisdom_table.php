<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Intelligence Ledger — organizational memory that compounds with every project.
 * Stores lessons, decisions, autopsies, and rule candidates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_wisdom', function (Blueprint $table) {
            // Identity
            $table->uuid('id')->primary();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Classification
            $table->string('domain', 50)->default('general')->index();
            $table->string('entry_type', 30)->default('lesson')
                  ->comment('lesson|decision|outcome|autopsy|rule_candidate');

            // Content
            $table->text('lesson')->nullable()->comment('The core insight or decision recorded');
            $table->text('what_went_right')->nullable();
            $table->text('what_went_wrong')->nullable();
            $table->text('recommendation')->nullable()->comment('Actionable advice for future projects');
            $table->text('outcome')->nullable()->comment('What actually happened (closes the loop)');

            // Intelligence scoring
            $table->decimal('confidence', 4, 2)->default(0.70)->comment('0.00–1.00 confidence score');
            $table->json('tags')->nullable()->comment('["security", "performance", "ecommerce", ...]');

            // Rule promotion
            $table->boolean('is_rule_candidate')->default(false)->index();
            $table->string('rule_trigger', 500)->nullable()
                  ->comment('IF condition that should trigger this rule');

            // Economics
            $table->decimal('ai_cost_estimate', 8, 4)->nullable()
                  ->comment('Estimated AI cost this decision saved (USD)');
            $table->decimal('ai_cost_saved', 8, 4)->nullable()
                  ->comment('Actual AI cost saved by reusing this wisdom');

            // Reuse tracking
            $table->unsignedInteger('reuse_count')->default(0)->index();

            $table->timestamps();

            // Indexes for common queries
            $table->index(['domain', 'confidence']);
            $table->index(['domain', 'is_rule_candidate']);
            $table->index(['entry_type', 'domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_wisdom');
    }
};
