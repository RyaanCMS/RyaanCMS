<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Soft deletes on projects
        Schema::table('projects', function (Blueprint $table) {
            $table->softDeletes();
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });

        // Missing indexes on ai_conversations
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index(['project_id', 'created_at']);
        });

        // Missing indexes on project_files
        Schema::table('project_files', function (Blueprint $table) {
            $table->index(['project_id', 'created_at']);
        });

        // Missing indexes on marketplace_items
        Schema::table('marketplace_items', function (Blueprint $table) {
            $table->index(['is_published', 'created_at']);
        });

        // Missing indexes on pipeline_runs
        Schema::table('pipeline_runs', function (Blueprint $table) {
            $table->index(['project_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });

        // Missing indexes on project_wisdom
        if (Schema::hasTable('project_wisdom')) {
            Schema::table('project_wisdom', function (Blueprint $table) {
                $table->index(['user_id', 'domain']);
                $table->index(['confidence', 'reuse_count']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['project_id', 'created_at']);
        });

        Schema::table('project_files', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'created_at']);
        });

        Schema::table('marketplace_items', function (Blueprint $table) {
            $table->dropIndex(['is_published', 'created_at']);
        });

        Schema::table('pipeline_runs', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'status']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        if (Schema::hasTable('project_wisdom')) {
            Schema::table('project_wisdom', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'domain']);
                $table->dropIndex(['confidence', 'reuse_count']);
            });
        }
    }
};
