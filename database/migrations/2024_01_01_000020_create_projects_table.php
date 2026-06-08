<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type')->default('laravel'); // laravel, react, nextjs, static, etc.
            $table->string('framework')->nullable();
            $table->string('status')->default('active'); // active, archived, deploying, error
            $table->string('thumbnail')->nullable();
            $table->json('tech_stack')->nullable();
            $table->json('settings')->nullable();
            $table->json('env_variables')->nullable();
            $table->string('github_repo')->nullable();
            $table->string('github_branch')->default('main');
            $table->string('deployment_url')->nullable();
            $table->string('deployment_target')->nullable();
            $table->timestamp('last_deployed_at')->nullable();
            $table->bigInteger('storage_used')->default(0); // bytes
            $table->integer('ai_tokens_used')->default(0);
            $table->timestamps();
        });

        Schema::create('project_collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('editor'); // viewer, editor, admin
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_collaborators');
        Schema::dropIfExists('projects');
    }
};
