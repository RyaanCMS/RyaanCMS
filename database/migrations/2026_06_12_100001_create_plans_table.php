<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();        // free_trial, starter, pro, enterprise
            $table->string('name');
            $table->integer('price_monthly')->default(0); // cents (0 = free)
            $table->string('currency', 3)->default('USD');
            $table->integer('credits_per_month')->default(0);
            $table->integer('max_projects')->default(1);  // -1 = unlimited
            $table->json('features')->nullable();
            $table->integer('trial_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default plans
        $now = now();
        DB::table('plans')->insert([
            [
                'key'              => 'free_trial',
                'name'             => 'Free Trial',
                'price_monthly'    => 0,
                'currency'         => 'USD',
                'credits_per_month'=> 100,
                'max_projects'     => 2,
                'features'         => json_encode(['ai_builder', 'blueprint', 'basic_kb']),
                'trial_days'       => 14,
                'is_active'        => true,
                'sort_order'       => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'key'              => 'starter',
                'name'             => 'Starter',
                'price_monthly'    => 1900,  // $19/mo
                'currency'         => 'USD',
                'credits_per_month'=> 500,
                'max_projects'     => 5,
                'features'         => json_encode(['ai_builder', 'blueprint', 'basic_kb', 'premium_kb']),
                'trial_days'       => 0,
                'is_active'        => true,
                'sort_order'       => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'key'              => 'pro',
                'name'             => 'Pro',
                'price_monthly'    => 4900,  // $49/mo
                'currency'         => 'USD',
                'credits_per_month'=> 2000,
                'max_projects'     => -1,
                'features'         => json_encode(['ai_builder', 'blueprint', 'basic_kb', 'premium_kb', 'pipeline_agents', 'senior_dev_wisdom']),
                'trial_days'       => 0,
                'is_active'        => true,
                'sort_order'       => 2,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'key'              => 'enterprise',
                'name'             => 'Enterprise',
                'price_monthly'    => 14900, // $149/mo
                'currency'         => 'USD',
                'credits_per_month'=> 10000,
                'max_projects'     => -1,
                'features'         => json_encode(['ai_builder', 'blueprint', 'basic_kb', 'premium_kb', 'pipeline_agents', 'senior_dev_wisdom', 'priority_routing', 'advanced_arch', 'white_label']),
                'trial_days'       => 0,
                'is_active'        => true,
                'sort_order'       => 3,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
