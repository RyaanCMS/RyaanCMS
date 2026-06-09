<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('update_history', function (Blueprint $table) {
            $table->id();
            $table->string('version', 30);
            $table->string('type', 20)->default('core'); // core | plugin | template
            $table->string('package_key', 100)->nullable(); // plugin/template key
            $table->enum('status', ['downloading','extracting','applying','migrating','success','failed'])
                  ->default('downloading');
            $table->text('changelog')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'package_key']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('update_history');
    }
};
