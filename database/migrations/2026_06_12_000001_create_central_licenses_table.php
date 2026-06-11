<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('central_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('license_key', 40)->unique();   // RYAAN-XXXX-XXXX-XXXX-XXXX
            $table->string('plan', 20)->default('starter'); // free|starter|pro|enterprise
            $table->string('email', 200)->nullable();
            $table->string('name', 200)->nullable();
            $table->string('domain', 300)->nullable();      // primary domain bound to license
            $table->enum('status', ['active', 'suspended', 'expired', 'trial'])->default('active');
            $table->unsignedBigInteger('credits_included')->default(0);
            $table->unsignedBigInteger('credits_used')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('issued_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();               // extra plan features, overrides
            $table->timestamps();

            $table->index('status');
            $table->index('plan');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('central_licenses');
    }
};
