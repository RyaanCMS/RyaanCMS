<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('module_key');                                          // auth, rbac, payments ...
            $table->enum('status', ['installed', 'active', 'disabled'])->default('installed');
            $table->json('options')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'module_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_modules');
    }
};
