<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // developer/seller
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('long_description')->nullable();
            $table->string('category'); // ecommerce, crm, ai_agents, themes, plugins
            $table->string('type'); // plugin, theme, template, agent, module
            $table->string('version')->default('1.0.0');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->boolean('is_free')->default(true);
            $table->string('thumbnail')->nullable();
            $table->json('screenshots')->nullable();
            $table->json('tags')->nullable();
            $table->json('compatibility')->nullable(); // Laravel versions, etc.
            $table->string('download_url')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('documentation_url')->nullable();
            $table->integer('downloads')->default(0);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('rating_count')->default(0);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('marketplace_installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_item_id')->constrained()->cascadeOnDelete();
            $table->string('version');
            $table->string('status')->default('installed'); // installed, active, disabled, error
            $table->timestamps();
        });

        Schema::create('marketplace_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_item_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating');
            $table->text('review')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_reviews');
        Schema::dropIfExists('marketplace_installations');
        Schema::dropIfExists('marketplace_items');
    }
};
