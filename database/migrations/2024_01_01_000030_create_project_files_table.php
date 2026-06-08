<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('path'); // relative path within project
            $table->string('type')->default('file'); // file, directory
            $table->string('mime_type')->nullable();
            $table->string('extension')->nullable();
            $table->longText('content')->nullable();
            $table->bigInteger('size')->default(0);
            $table->boolean('is_binary')->default(false);
            $table->string('disk_path')->nullable(); // actual storage path
            $table->timestamps();

            $table->unique(['project_id', 'path']);
            $table->index(['project_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_files');
    }
};
