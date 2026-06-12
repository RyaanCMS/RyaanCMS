<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_code')->unique();
            $table->string('license_token')->nullable();
            $table->string('domain');
            $table->string('product_id')->nullable();
            $table->string('product_name')->nullable();
            $table->string('buyer_email')->nullable();
            $table->string('status')->default('inactive'); // active | inactive | suspended | expired
            $table->json('meta')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
