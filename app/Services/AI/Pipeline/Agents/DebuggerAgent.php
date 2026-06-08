<?php

namespace App\Services\AI\Pipeline\Agents;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Services\AI\Pipeline\BaseAgent;

class DebuggerAgent extends BaseAgent
{
    public function getName(): string        { return 'Debugger Agent'; }
    public function getIndex(): int          { return 9; }
    public function getIcon(): string        { return '🔧'; }
    public function getDescription(): string { return 'Auto-detecting and fixing code errors'; }

    public function run(array $context, Project $project, callable $saveFile, callable $emit): array
    {
        $errors          = $context['current_validation_errors'] ?? [];
        $retryNum        = $context['retry_count'] ?? 0;
        $fixMemoryBlock  = $context['fix_memory_context'] ?? '';
        $stuckErrors     = $context['stuck_errors'] ?? [];

        if (empty($errors)) {
            $emit(['type' => 'chunk', 'agent_index' => $this->getIndex(), 'text' => 'No errors found — skipping debugger.']);
            return ['debugger_fixes' => []];
        }

        // Determine dominant strategy: if any error is stuck → rewrite; else use per-error strategy
        $strategies   = array_unique(array_column(array_map(
            fn($e) => ['s' => $e['strategy'] ?? 'patch'], $errors
        ), 's'));
        $dominantStrat = in_array('redesign', $strategies) ? 'redesign'
                       : (in_array('rewrite', $strategies) ? 'rewrite' : 'patch');

        // Load actual file contents for files with errors
        $fileContents = '';
        $errorFiles   = array_unique(array_column($errors, 'file'));
        foreach (array_slice($errorFiles, 0, 8) as $filePath) {
            $pf = ProjectFile::where('project_id', $project->id)->where('path', $filePath)->first();
            if ($pf) {
                $snippet = mb_substr($pf->content, 0, 3000);
                $fileContents .= "\n--- {$filePath} ---\n{$snippet}\n";
            }
        }

        // Build per-error strategy hints
        $errorsText = '';
        foreach ($errors as $err) {
            $strat = $err['strategy'] ?? 'patch';
            $failed = implode('; ', $err['failed_fixes'] ?? []);
            $errorsText .= "File: {$err['file']} | Type: {$err['type']} | Strategy: {$strat}\n";
            $errorsText .= "Error: {$err['message']}\n";
            if ($failed) $errorsText .= "Already tried (FAILED): {$failed}\n";
            $errorsText .= "\n";
        }

        $stuckBlock = '';
        if (!empty($stuckErrors)) {
            $stuckBlock = "\nSTUCK ERRORS — all previous patches failed on these. You MUST do a full file rewrite:\n";
            foreach ($stuckErrors as $s) {
                $stuckBlock .= "  ⚠ {$s['file']}: {$s['message']}\n";
            }
        }

        $strategyGuide = match($dominantStrat) {
            'redesign' => "STRATEGY: REDESIGN — previous rewrites also failed. Completely re-architect the file from scratch with a simpler, more direct implementation.",
            'rewrite'  => "STRATEGY: REWRITE — patches have already failed. Output full file replacements (\"files\" array), not patches.",
            default    => "STRATEGY: PATCH — use surgical search/replace patches where possible.",
        };

        $systemPrompt = <<<SYS
You are a Senior Laravel Debugger with memory of past fix attempts. {$this->stackContext()}

This is retry #{$retryNum}. You have access to a fix-memory log showing what was tried before and whether it worked.

{$strategyGuide}

RULES:
- Study the FIX MEMORY carefully — never repeat a fix marked as FAILED
- For PATCH: include 2-3 lines of surrounding context in "search" for uniqueness
- For REWRITE: output the entire corrected file in "files"
- For REDESIGN: completely rewrite with a simpler approach (fewer classes, inline logic)
- Fix PHP syntax errors, missing namespace/use statements, brace mismatches, missing class declarations
- Each fix_applied entry must describe WHAT you changed and WHY (for the memory log)

OUTPUT: Respond ONLY with a JSON object:
{
  "analysis": "Root cause and chosen approach",
  "patches": [
    {
      "path": "app/Models/Product.php",
      "search": "use Illuminate\\\\Database\\\\Eloquent\\\\Model;\n\nclass Product",
      "replace": "use Illuminate\\\\Database\\\\Eloquent\\\\Model;\nuse Illuminate\\\\Database\\\\Eloquent\\\\Factories\\\\HasFactory;\n\nclass Product"
    }
  ],
  "files": [
    {"path": "app/Http/Controllers/ProductController.php", "content": "...full file..."}
  ],
  "fixes_applied": ["Added HasFactory trait import — patch strategy", "Rewrote ProductController — rewrite strategy"]
}
SYS;

        $userPrompt = <<<PROMPT
CURRENT ERRORS (retry #{$retryNum} | strategy: {$dominantStrat}):
{$errorsText}{$stuckBlock}

{$fixMemoryBlock}

RELEVANT FILE CONTENTS:
{$fileContents}

Fix all errors using the {$dominantStrat} strategy. Do not repeat any previously failed fix.
PROMPT;

        $emit(['type' => 'chunk', 'agent_index' => $this->getIndex(), 'text' => "Fixing " . count($errors) . " error(s) (attempt #{$retryNum})..."]);

        $raw    = $this->chat($systemPrompt, $userPrompt, 8000);
        $parsed = $this->extractJson($raw);

        $fixedPaths = [];

        // Apply patches
        foreach ($parsed['patches'] ?? [] as $patch) {
            if (empty($patch['path'])) continue;
            $pf = ProjectFile::where('project_id', $project->id)->where('path', $patch['path'])->first();
            if ($pf && !empty($patch['search']) && str_contains($pf->content, $patch['search'])) {
                $newContent = str_replace($patch['search'], $patch['replace'] ?? '', $pf->content);
                $saved = $saveFile($project, $patch['path'], $newContent);
                if ($saved) {
                    $fixedPaths[] = $saved['path'];
                    $emit(['type' => 'file_saved', 'agent_index' => $this->getIndex(), 'path' => $saved['path'], 'action' => 'patched']);
                }
            }
        }

        // Apply full file replacements
        foreach ($parsed['files'] ?? [] as $file) {
            if (empty($file['path']) || empty($file['content'])) continue;
            $saved = $saveFile($project, $file['path'], $file['content']);
            if ($saved) {
                $fixedPaths[] = $saved['path'];
                $emit(['type' => 'file_saved', 'agent_index' => $this->getIndex(), 'path' => $saved['path'], 'action' => 'rewritten']);
            }
        }

        return [
            'debugger_fixes' => array_merge($context['debugger_fixes'] ?? [], [
                'retry'         => $retryNum,
                'analysis'      => $parsed['analysis'] ?? '',
                'fixes_applied' => $parsed['fixes_applied'] ?? [],
                'files_fixed'   => $fixedPaths,
            ]),
        ];
    }
}
