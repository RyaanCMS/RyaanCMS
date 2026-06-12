<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pipeline_run_id')->nullable()->constrained('pipeline_runs')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('domain', 64)->default('general');

            // Dimension scores (0-100)
            $table->unsignedTinyInteger('ui_score')->default(0);
            $table->unsignedTinyInteger('backend_score')->default(0);
            $table->unsignedTinyInteger('security_score')->default(0);
            $table->unsignedTinyInteger('performance_score')->default(0);
            $table->unsignedTinyInteger('overall_score')->default(0);

            $table->string('grade', 4)->default('C');

            $table->json('strengths')->nullable();
            $table->json('issues')->nullable();
            $table->json('recommendations')->nullable();

            $table->unsignedInteger('total_files')->default(0);
            $table->unsignedInteger('tokens_used')->default(0);

            $table->timestamps();

            $table->index(['project_id', 'created_at']);
            $table->index(['user_id', 'overall_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_scores');
    }
};
