<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_item_id')->constrained()->cascadeOnDelete();
            $table->uuid('license_key')->unique();
            $table->string('domain');
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'marketplace_item_id', 'domain'], 'marketplace_purchases_user_item_domain_unique');
            $table->index(['marketplace_item_id', 'domain']);
        });

        Schema::table('marketplace_installations', function (Blueprint $table) {
            if (!Schema::hasColumn('marketplace_installations', 'marketplace_purchase_id')) {
                $table->foreignId('marketplace_purchase_id')
                    ->nullable()
                    ->after('marketplace_item_id')
                    ->constrained('marketplace_purchases')
                    ->nullOnDelete();
            }
        });

        $installations = DB::table('marketplace_installations')
            ->whereNotNull('license_key')
            ->whereNotNull('domain')
            ->orderBy('id')
            ->get();

        foreach ($installations as $installation) {
            $purchase = DB::table('marketplace_purchases')
                ->where('license_key', $installation->license_key)
                ->orWhere(function ($query) use ($installation) {
                    $query->where('user_id', $installation->user_id)
                        ->where('marketplace_item_id', $installation->marketplace_item_id)
                        ->where('domain', $installation->domain);
                })
                ->first();

            $purchaseId = $purchase?->id ?? DB::table('marketplace_purchases')->insertGetId([
                'user_id'             => $installation->user_id,
                'marketplace_item_id' => $installation->marketplace_item_id,
                'license_key'         => $installation->license_key,
                'domain'              => $installation->domain,
                'purchased_at'        => $installation->activated_at ?? $installation->created_at,
                'created_at'          => $installation->created_at,
                'updated_at'          => $installation->updated_at,
            ]);

            DB::table('marketplace_installations')
                ->where('id', $installation->id)
                ->update([
                    'marketplace_purchase_id' => $purchaseId,
                    'license_key'             => $purchase?->license_key ?? $installation->license_key,
                ]);
        }

        try {
            Schema::table('marketplace_installations', function (Blueprint $table) {
                $table->dropUnique('marketplace_installations_license_key_unique');
            });
        } catch (Throwable $e) {
            // Older databases may not have the generated unique index name.
        }

        Schema::table('marketplace_installations', function (Blueprint $table) {
            $table->unique(['project_id', 'marketplace_item_id'], 'marketplace_installations_project_item_unique');
            $table->index(['user_id', 'marketplace_item_id', 'domain'], 'marketplace_installations_user_item_domain_index');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_installations', function (Blueprint $table) {
            $table->dropIndex('marketplace_installations_user_item_domain_index');
            $table->dropUnique('marketplace_installations_project_item_unique');
            $table->dropConstrainedForeignId('marketplace_purchase_id');
        });

        Schema::dropIfExists('marketplace_purchases');

        Schema::table('marketplace_installations', function (Blueprint $table) {
            $table->unique('license_key', 'marketplace_installations_license_key_unique');
        });
    }
};
