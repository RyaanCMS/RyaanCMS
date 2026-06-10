<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $userId = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id')
            ?? DB::table('users')->orderBy('id')->value('id');

        if (!$userId) {
            return;
        }

        $now = now();
        $project = DB::table('projects')->where('slug', 'core-cms')->first();

        if (!$project) {
            $projectId = DB::table('projects')->insertGetId([
                'user_id' => $userId,
                'name' => 'Core CMS',
                'slug' => 'core-cms',
                'description' => 'Main website project for the RyaanCMS root domain.',
                'type' => 'static',
                'framework' => 'blade',
                'status' => 'active',
                'tech_stack' => json_encode(['Laravel', 'Blade', 'Tailwind CSS', 'Alpine.js']),
                'settings' => json_encode(['is_core_cms' => true, 'build_type' => 'website']),
                'github_branch' => 'main',
                'storage_used' => 0,
                'ai_tokens_used' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $projectId = $project->id;
            $settings = json_decode($project->settings ?? '[]', true) ?: [];
            $settings['is_core_cms'] = true;
            $settings['build_type'] = $settings['build_type'] ?? 'website';

            DB::table('projects')->where('id', $projectId)->update([
                'name' => 'Core CMS',
                'description' => $project->description ?: 'Main website project for the RyaanCMS root domain.',
                'settings' => json_encode($settings),
                'updated_at' => $now,
            ]);
        }

        DB::table('project_files')->updateOrInsert(
            ['project_id' => $projectId, 'path' => 'README.md'],
            [
                'name' => 'README.md',
                'type' => 'file',
                'mime_type' => 'text/markdown',
                'extension' => 'md',
                'content' => "# Core CMS\n\nThis project controls the main RyaanCMS website. Activate any website template on this project to publish it on the root domain.",
                'size' => 0,
                'is_binary' => false,
                'disk_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        $projectId = DB::table('projects')->where('slug', 'core-cms')->value('id');

        if (!$projectId) {
            return;
        }

        DB::table('project_files')->where('project_id', $projectId)->delete();
        DB::table('project_modules')->where('project_id', $projectId)->delete();
        DB::table('projects')->where('id', $projectId)->delete();
    }
};
