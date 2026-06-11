<?php

namespace Database\Seeders;

use App\Models\LocalQuestionPack;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Migrates questions from config/kb/question_intelligence.php
 * into the local_question_packs table.
 * Safe to run multiple times (uses updateOrCreate on slug).
 */
class LocalQuestionPackSeeder extends Seeder
{
    public function run(): void
    {
        $kb = config('kb.question_intelligence');

        if (empty($kb['by_domain'])) {
            $this->command->warn('[LocalQuestionPackSeeder] No domains found in question_intelligence config.');
            return;
        }

        foreach ($kb['by_domain'] as $domain => $questions) {
            $slug = 'builtin-' . Str::slug($domain) . '-global';

            LocalQuestionPack::updateOrCreate(
                ['slug' => $slug],
                [
                    'central_pack_uuid' => null,                  // locally seeded
                    'name'              => Str::title(str_replace('_', ' ', $domain)) . ' Questions',
                    'industry'          => $domain,
                    'blueprint_slug'    => $domain,
                    'country'           => 'ALL',
                    'language'          => 'en',
                    'version'           => '1.0.0',
                    'status'            => 'active',
                    'visibility'        => 'public',
                    'is_premium'        => false,
                    'questions_json'    => $questions,
                    'rules_json'        => ['max_questions' => 5, 'rule' => $kb['rule'] ?? ''],
                    'metadata'          => ['source' => 'builtin', 'domain' => $domain],
                    'synced_at'         => null,
                    'license_status'    => 'free',
                ]
            );

            $this->command->info("  ✓ {$slug} (" . count($questions) . " questions)");
        }

        $this->command->info('[LocalQuestionPackSeeder] Done.');
    }
}
