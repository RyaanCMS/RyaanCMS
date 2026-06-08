<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kb_prompt_cache', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 32)->unique()->comment('md5(provider|normalized_prompt)');
            $table->string('prompt_summary', 255)->comment('First 250 chars for display');
            $table->string('provider', 50)->default('default');
            $table->longText('response')->comment('JSON-encoded cached response array');
            $table->unsignedInteger('tokens_saved')->default(0)->comment('Tokens the original call cost');
            $table->unsignedInteger('hit_count')->default(0)->comment('How many times this cache entry was served');
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index(['provider', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_prompt_cache');
    }
};
