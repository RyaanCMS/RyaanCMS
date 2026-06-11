<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['group' => 'general',    'key' => 'site_name',        'value' => 'RyaanCMS',               'type' => 'string'],
            ['group' => 'general',    'key' => 'site_tagline',     'value' => 'AI-Powered App Builder',  'type' => 'string'],
            ['group' => 'general',    'key' => 'allow_registration','value' => 'true',                   'type' => 'boolean'],
            ['group' => 'general',    'key' => 'default_ai',       'value' => 'claude',                  'type' => 'string'],
            ['group' => 'appearance', 'key' => 'theme',            'value' => 'light',                   'type' => 'string'],
            ['group' => 'appearance', 'key' => 'primary_color',    'value' => '#6366f1',                 'type' => 'string'],
            ['group' => 'branding',   'key' => 'logo_path',        'value' => '',                        'type' => 'string'],
            ['group' => 'branding',   'key' => 'favicon_path',     'value' => '',                        'type' => 'string'],
            ['group' => 'branding',   'key' => 'font_family',      'value' => 'Inter',                   'type' => 'string'],
            ['group' => 'branding',   'key' => 'primary_color',    'value' => '#6366f1',                 'type' => 'string'],
            ['group' => 'branding',   'key' => 'site_name',        'value' => 'RyaanCMS',               'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['user_id' => null, 'group' => $setting['group'], 'key' => $setting['key']],
                array_merge($setting, ['user_id' => null, 'updated_at' => now(), 'created_at' => now()])
            );
        }
    }
}
