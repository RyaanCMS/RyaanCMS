<?php

namespace App\Services\AI\Pipeline;

/**
 * Tracks fix attempts and their outcomes across build-loop retries.
 *
 * For each error (identified by a stable signature), we record:
 *  - what fix was attempted
 *  - whether it succeeded (error disappeared next round) or failed (still present)
 *
 * The Debugger agent uses this to:
 *  1. Avoid repeating fixes that already failed
 *  2. Know which files/patterns are "stuck" and need a full rewrite
 *  3. Escalate strategy after 2+ failed attempts on the same error
 */
class FixMemory
{
    /** @var array<string, array> keyed by error signature */
    private array $records = [];

    public function __construct(array $persisted = [])
    {
        $this->records = $persisted;
    }

    // ── Recording ─────────────────────────────────────────────────────────────

    /**
     * Record that a fix was attempted for a set of errors in this retry.
     * Call BEFORE the debugger runs.
     */
    public function recordAttempt(int $retryIndex, array $errors, string $fixSummary): void
    {
        foreach ($errors as $err) {
            $sig = $this->signature($err);
            if (!isset($this->records[$sig])) {
                $this->records[$sig] = [
                    'file'       => $err['file'] ?? '',
                    'type'       => $err['type'] ?? '',
                    'message'    => $err['message'] ?? '',
                    'first_seen' => $retryIndex,
                    'seen_count' => 0,
                    'attempts'   => [],
                ];
            }
            $this->records[$sig]['seen_count']++;
            $this->records[$sig]['attempts'][] = [
                'retry'       => $retryIndex,
                'fix_summary' => $fixSummary,
                'outcome'     => 'pending',
            ];
        }
    }

    /**
     * After the debugger runs, compare previous errors to new errors.
     * Mark each fix attempt as 'resolved' or 'failed'.
     *
     * @param array $errorsBefore  errors that existed before the fix
     * @param array $errorsAfter   errors remaining after the fix
     */
    public function recordOutcomes(array $errorsBefore, array $errorsAfter): void
    {
        $remainingSigs = array_map([$this, 'signature'], $errorsAfter);

        foreach ($errorsBefore as $err) {
            $sig = $this->signature($err);
            if (!isset($this->records[$sig])) continue;

            // Find the most recent pending attempt
            $attempts = &$this->records[$sig]['attempts'];
            foreach (array_reverse(array_keys($attempts)) as $i) {
                if ($attempts[$i]['outcome'] === 'pending') {
                    $attempts[$i]['outcome'] = in_array($sig, $remainingSigs) ? 'failed' : 'resolved';
                    break;
                }
            }
        }
    }

    // ── Querying ──────────────────────────────────────────────────────────────

    /**
     * Return errors that are "stuck" — seen 2+ times with all fixes having failed.
     */
    public function stuckErrors(): array
    {
        $stuck = [];
        foreach ($this->records as $sig => $rec) {
            if ($rec['seen_count'] < 2) continue;

            $allFailed = !empty($rec['attempts']) && collect($rec['attempts'])
                ->filter(fn($a) => $a['outcome'] !== 'pending')
                ->every(fn($a) => $a['outcome'] === 'failed');

            if ($allFailed) {
                $stuck[] = $rec + ['signature' => $sig, 'failed_fixes' => $this->failedFixSummaries($rec)];
            }
        }
        return $stuck;
    }

    /**
     * Return a list of fix summaries that have already failed for a given error,
     * so the debugger can avoid repeating them.
     */
    public function failedFixesFor(array $err): array
    {
        $sig = $this->signature($err);
        if (!isset($this->records[$sig])) return [];

        return collect($this->records[$sig]['attempts'])
            ->where('outcome', 'failed')
            ->pluck('fix_summary')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Recommended strategy for a given error based on failure history.
     * patch → rewrite → redesign
     */
    public function strategyFor(array $err): string
    {
        $sig = $this->signature($err);
        if (!isset($this->records[$sig])) return 'patch';

        $failCount = collect($this->records[$sig]['attempts'])
            ->where('outcome', 'failed')
            ->count();

        return match(true) {
            $failCount >= 3 => 'redesign',
            $failCount >= 1 => 'rewrite',
            default         => 'patch',
        };
    }

    /**
     * Build a compact summary block to inject into the DebuggerAgent's prompt.
     */
    public function toPromptContext(array $currentErrors): string
    {
        if (empty($this->records)) return '';

        $lines = ["FIX MEMORY (learn from these — do not repeat failed fixes):\n"];

        foreach ($currentErrors as $err) {
            $sig       = $this->signature($err);
            $rec       = $this->records[$sig] ?? null;
            if (!$rec || empty($rec['attempts'])) continue;

            $failedFixes = $this->failedFixesFor($err);
            $strategy    = $this->strategyFor($err);

            $lines[] = "Error: [{$err['type']}] {$err['file']}: {$err['message']}";
            $lines[] = "  Seen {$rec['seen_count']} time(s). Strategy: {$strategy}";

            if (!empty($failedFixes)) {
                $lines[] = '  Already tried (FAILED — do NOT repeat):';
                foreach ($failedFixes as $fix) {
                    $lines[] = "    ✗ {$fix}";
                }
            }

            $resolved = collect($rec['attempts'])->where('outcome', 'resolved')->pluck('fix_summary')->first();
            if ($resolved) {
                $lines[] = "  Previously resolved by: ✓ {$resolved}";
            }

            $lines[] = '';
        }

        $stuck = $this->stuckErrors();
        if (!empty($stuck)) {
            $lines[] = "STUCK ERRORS (all previous fixes failed — use a completely different approach):";
            foreach ($stuck as $s) {
                $lines[] = "  ⚠ [{$s['type']}] {$s['file']}: {$s['message']}";
                $lines[] = "    Recommended: full file REWRITE, not a patch";
            }
        }

        return implode("\n", $lines);
    }

    public function toArray(): array
    {
        return $this->records;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function signature(array $err): string
    {
        // Stable key: file + error type + first 80 chars of message
        return md5(($err['file'] ?? '') . '|' . ($err['type'] ?? '') . '|' . mb_substr($err['message'] ?? '', 0, 80));
    }

    private function failedFixSummaries(array $rec): array
    {
        return collect($rec['attempts'])
            ->where('outcome', 'failed')
            ->pluck('fix_summary')
            ->filter()
            ->values()
            ->all();
    }
}
