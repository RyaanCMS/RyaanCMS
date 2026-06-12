<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solution_memory', function (Blueprint $table) {
            $table->id();
            $table->char('fingerprint', 32)->unique()->index();         // md5 of normalized prompt
            $table->string('domain', 64)->index();
            $table->string('prompt_sample', 255);                       // first 255 chars for human readability
            $table->unsignedTinyInteger('coverage')->default(0);        // coverage at time of recording
            $table->json('assembly_plan')->nullable();                  // the plan that worked
            $table->unsignedInteger('reuse_count')->default(0)->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solution_memory');
    }
};
