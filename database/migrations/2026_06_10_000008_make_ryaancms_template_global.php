<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEFAULT_TEMPLATE = 'template.ryaancms';

    public function up(): void
    {
        $now = now();

        DB::table('project_modules')
            ->where('module_key', self::DEFAULT_TEMPLATE)
            ->delete();

        DB::table('settings')->updateOrInsert(
            ['user_id' => null, 'group' => 'system', 'key' => 'public_site_template_key'],
            [
                'value' => self::DEFAULT_TEMPLATE,
                'type' => 'string',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('settings')
            ->whereNull('user_id')
            ->where('group', 'system')
            ->where('key', 'public_site_project_id')
            ->delete();
    }

    public function down(): void
    {
        DB::table('settings')
            ->whereNull('user_id')
            ->where('group', 'system')
            ->where('key', 'public_site_template_key')
            ->delete();
    }
};
