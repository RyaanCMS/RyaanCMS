<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEFAULT_TEMPLATE = 'template.ryaancms';

    public function up(): void
    {
        $projectId = DB::table('settings')
            ->whereNull('user_id')
            ->where('group', 'system')
            ->where('key', 'public_site_project_id')
            ->value('value');

        $projectId = $projectId
            ? DB::table('projects')->where('id', (int) $projectId)->value('id')
            : null;

        $projectId ??= DB::table('projects')->orderBy('id')->value('id');

        if (!$projectId) {
            return;
        }

        $now = now();

        DB::table('project_modules')
            ->where('project_id', $projectId)
            ->where('module_key', 'like', 'template.%')
            ->update(['status' => 'installed', 'updated_at' => $now]);

        DB::table('project_modules')->updateOrInsert(
            ['project_id' => $projectId, 'module_key' => self::DEFAULT_TEMPLATE],
            [
                'status' => 'active',
                'options' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['user_id' => null, 'group' => 'system', 'key' => 'public_site_project_id'],
            [
                'value' => (string) $projectId,
                'type' => 'integer',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('project_modules')
            ->where('module_key', self::DEFAULT_TEMPLATE)
            ->update(['status' => 'installed', 'updated_at' => now()]);
    }
};
