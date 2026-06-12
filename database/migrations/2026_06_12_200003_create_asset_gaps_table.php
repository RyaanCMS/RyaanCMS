<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_gaps', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 64)->index();
            $table->string('requirement', 128);
            $table->string('gap_type', 32)->default('new');        // new | extension
            $table->text('description')->nullable();
            $table->string('base_asset', 128)->nullable();         // which asset to extend
            $table->string('priority', 16)->default('medium');     // critical | high | medium | low
            $table->unsignedInteger('demand_count')->default(1);   // how many times requested
            $table->timestamps();

            $table->unique(['domain', 'requirement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_gaps');
    }
};
