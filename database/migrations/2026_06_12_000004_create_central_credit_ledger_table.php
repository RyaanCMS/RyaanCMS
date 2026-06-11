<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('central_credit_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('license_key', 40)->index();
            $table->string('domain', 300)->nullable();
            $table->enum('type', ['topup', 'usage', 'bonus', 'refund', 'adjustment', 'expire']);
            $table->bigInteger('amount');                   // positive = add, negative = deduct
            $table->unsignedBigInteger('balance_after')->default(0);
            $table->string('description', 300)->nullable();
            $table->string('reference', 200)->nullable();   // project_id, invoice_id, etc.
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index(['license_key', 'recorded_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('central_credit_ledger');
    }
};
