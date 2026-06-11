<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('central_installs', function (Blueprint $table) {
            $table->id();
            $table->string('license_key', 40)->nullable()->index();
            $table->string('domain', 300);
            $table->string('app_version', 20)->nullable();
            $table->string('php_version', 20)->nullable();
            $table->string('laravel_version', 20)->nullable();
            $table->unsignedInteger('user_count')->default(1);
            $table->unsignedInteger('project_count')->default(0);
            $table->unsignedBigInteger('prompts_today')->default(0);
            $table->unsignedBigInteger('prompts_total')->default(0);
            $table->ipAddress('last_ip')->nullable();
            $table->timestamp('first_seen')->useCurrent();
            $table->timestamp('last_ping')->nullable();
            $table->timestamps();

            $table->unique('domain');
            $table->index('app_version');
            $table->index('last_ping');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('central_installs');
    }
};
