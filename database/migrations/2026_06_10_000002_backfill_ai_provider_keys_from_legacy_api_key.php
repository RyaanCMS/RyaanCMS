<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_providers') || !Schema::hasTable('ai_provider_keys')) {
            return;
        }

        DB::table('ai_providers')
            ->select('id', 'user_id', 'api_key', 'created_at', 'updated_at')
            ->whereNotNull('api_key')
            ->where('api_key', '<>', '')
            ->orderBy('id')
            ->chunkById(100, function ($providers) {
                foreach ($providers as $provider) {
                    $hasKeys = DB::table('ai_provider_keys')
                        ->where('ai_provider_id', $provider->id)
                        ->exists();

                    if ($hasKeys) {
                        continue;
                    }

                    DB::table('ai_provider_keys')->insert([
                        'ai_provider_id' => $provider->id,
                        'user_id'        => $provider->user_id,
                        'label'          => 'Primary Key',
                        'api_key'        => $provider->api_key,
                        'is_primary'     => true,
                        'is_active'      => true,
                        'fail_count'     => 0,
                        'last_failed_at' => null,
                        'last_used_at'   => null,
                        'created_at'     => $provider->created_at ?? now(),
                        'updated_at'     => $provider->updated_at ?? now(),
                    ]);
                }
            }, 'id');
    }

    public function down(): void
    {
    }
};
