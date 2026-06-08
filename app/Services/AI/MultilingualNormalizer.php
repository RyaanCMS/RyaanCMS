<?php

namespace App\Services\AI;

/**
 * Normalizes multilingual mixed prompts to English before keyword scoring and AI processing.
 *
 * Strategy:
 *   1. Phrase-level replacements — context-safe, applied first (longest match wins)
 *   2. Contextual regex patterns — ambiguous words normalized only in safe surrounding context
 *   3. Unambiguous word replacements — only words that never appear in English programming
 *
 * Covers: Bangla (romanized), Hindi (romanized), Spanish, French, Portuguese,
 *         Turkish, Indonesian/Malay, German, Italian.
 *
 * Safety rule: short words or words that exist in English are NEVER replaced without
 * surrounding context (e.g., "hole" only normalizes as "when" after a tech verb).
 */
class MultilingualNormalizer
{
    /**
     * Phrase-level replacements — applied first, longest phrase has priority.
     * Each phrase is matched case-insensitively with word-boundary guards.
     */
    private array $phrases = [
        // ── Bangla romanized — action phrases ───────────────────────────────
        'a redirect korbay'     => 'redirect to',
        'a redirect korbe'      => 'redirect to',
        'a redirect korbo'      => 'redirect to',
        'redirect korbay'       => 'redirect to',
        'redirect korbe'        => 'redirect to',
        'page a pathao'         => 'redirect to page',
        'page e pathao'         => 'redirect to page',
        'page a niye jao'       => 'navigate to page',
        // ── Bangla — time/condition phrases ─────────────────────────────────
        'er pore'               => 'after',
        'r pore'                => 'after',
        'er age'                => 'before',
        'r age'                 => 'before',
        'er jonno'              => 'for',
        'r jonno'               => 'for',
        'er moddhe'             => 'inside',
        'r moddhe'              => 'inside',
        'mone hoy'              => 'I think',
        // ── Hindi romanized — common phrases ────────────────────────────────
        'ke liye'               => 'for',
        'ke baad'               => 'after',
        'ke pehle'              => 'before',
        'se pehle'              => 'before',
        'jab bhi'               => 'whenever',
        'tab tak'               => 'until',
        'is ke baad'            => 'after this',
        // ── Turkish ─────────────────────────────────────────────────────────
        'ana sayfa'             => 'home page',
        'giris sayfasi'         => 'login page',
        'giriş sayfası'         => 'login page',
        'cikis sayfasi'         => 'logout page',
        'çıkış sayfası'         => 'logout page',
        // ── French ──────────────────────────────────────────────────────────
        'tableau de bord'       => 'dashboard',
        "page d'accueil"        => 'home page',
        'page d accueil'        => 'home page',
        'se connecter'          => 'login',
        'se deconnecter'        => 'logout',
        'se déconnecter'        => 'logout',
        // ── German ──────────────────────────────────────────────────────────
        'nach dem login'        => 'after login',
        'nach dem logout'       => 'after logout',
        // ── Indonesian/Malay ─────────────────────────────────────────────────
        'halaman utama'         => 'home page',
        'halaman beranda'       => 'home page',
        'masuk ke'              => 'redirect to',
        // ── Vietnamese ───────────────────────────────────────────────────────
        'trang chủ'             => 'home page',
        'trang chú'             => 'home page',
        'đăng nhập'             => 'login',
        'đăng xuất'             => 'logout',
        'chuyển hướng'          => 'redirect',
        'cập nhật'              => 'update',
    ];

    /**
     * Contextual regex patterns — only replace when surrounding words make intent clear.
     * These handle words that ALSO exist in English (like "hole") but only in specific combos.
     */
    private array $contextualPatterns = [
        // Bangla: "[tech-verb] hole" → "[tech-verb] when"
        // "logout hole" = "after logout" / "when logout"
        '/\b(login|logout|register|submit|click|save|delete|upload|install|pay)\s+hole\b/i'
            => '$1 when',

        // Bangla: "page a" / "page e" at end of phrase or before verb = just "page"
        '/\b(page|screen|modal|form|view)\s+[ae]\s+(redirect|jao|jabe|navigate|korbay|korbe)/i'
            => '$1 redirect to',

        // Bangla: "X a redirect" → "redirect to X"
        '/\b(\w+)\s+[ae]\s+redirect\b/i'
            => 'redirect to $1',

        // Hindi: "login ke baad" pattern already covered by phrases,
        // but also handle "X ke baad" for other verbs
        '/\b(\w+)\s+ke\s+baad\b/i'
            => 'after $1',

        // Spanish: "después de X" → "after X"
        '/\bdespués\s+de\s+(\w+)/iu'
            => 'after $1',

        // German: "nach dem X" → "after X" (generic)
        '/\bnach\s+dem\s+(\w+)/iu'
            => 'after $1',
    ];

    /**
     * Pure word-level replacements.
     * ONLY include words that NEVER appear in standard English programming vocabulary.
     * Words 5+ chars preferred; short words require very high confidence.
     */
    private array $words = [
        // ── Bangla romanized (high confidence — clearly not English) ─────────
        'korbay'    => 'should',
        'korbe'     => 'should',
        'korbo'     => 'should',
        'korle'     => 'when',   // conditional: "login korle" = "when login done"
        'korte'     => 'to',
        'hobar'     => 'of becoming',
        'lagbe'     => 'needs to',
        'lagbay'    => 'needs to',
        'lagche'    => 'needs',
        'theke'     => 'from',
        'somosto'   => 'all',
        'dekhao'    => 'show',
        'dekhai'    => 'show',
        'hatao'     => 'remove',
        'hatai'     => 'remove',
        'pathao'    => 'send',
        'banao'     => 'create',
        'banai'     => 'create',
        'chhoto'    => 'small',
        'dorkar'    => 'need',
        'keno'      => 'why',
        'kivabe'    => 'how',
        'jabo'      => 'navigate to',
        'jabe'      => 'will navigate',
        'asbe'      => 'will come',
        'chayi'     => 'want',
        'hobe'      => 'will be',
        'hoye'      => 'becomes',
        'bollo'     => 'said',
        'pelam'     => 'got',
        'niye'      => 'about',
        'korchi'    => 'doing',
        // ── Hindi romanized ──────────────────────────────────────────────────
        'karo'      => 'do',
        'karna'     => 'to do',
        'kijiye'    => 'please do',
        'dikhao'    => 'show',
        'lagao'     => 'apply',
        'bhejo'     => 'send',
        'daalo'     => 'add',
        'chhodo'    => 'remove',
        'chahiye'   => 'need',
        'seedha'    => 'directly',
        'nahi'      => 'not',
        // ── Spanish ─────────────────────────────────────────────────────────
        'crear'     => 'create',
        'agregar'   => 'add',
        'añadir'    => 'add',
        'eliminar'  => 'delete',
        'mostrar'   => 'show',
        'cuando'    => 'when',
        'después'   => 'after',
        'desde'     => 'from',
        'todos'     => 'all',
        'redirigir' => 'redirect',
        'redirige'  => 'redirect',
        'actualizar'=> 'update',
        'cambiar'   => 'change',
        'arreglar'  => 'fix',
        'construir' => 'build',
        'enviar'    => 'send',
        'inicio'    => 'home',
        'página'    => 'page',
        'pagina'    => 'page',
        // ── French ──────────────────────────────────────────────────────────
        'créer'     => 'create',
        'creer'     => 'create',
        'ajouter'   => 'add',
        'supprimer' => 'delete',
        'effacer'   => 'delete',
        'afficher'  => 'show',
        'montrer'   => 'show',
        'quand'     => 'when',
        'après'     => 'after',
        'apres'     => 'after',
        'avant'     => 'before',
        'depuis'    => 'from',
        'rediriger' => 'redirect',
        'corriger'  => 'fix',
        'réparer'   => 'fix',
        'reparer'   => 'fix',
        'construire'=> 'build',
        'envoyer'   => 'send',
        'accueil'   => 'home',
        // ── Portuguese ──────────────────────────────────────────────────────
        'criar'     => 'create',
        'adicionar' => 'add',
        'remover'   => 'remove',
        'apagar'    => 'delete',
        'deletar'   => 'delete',
        'quando'    => 'when',
        'depois'    => 'after',
        'redirecionar' => 'redirect',
        'atualizar' => 'update',
        'corrigir'  => 'fix',
        'enviar'    => 'send',
        'início'    => 'home',
        // ── Turkish ─────────────────────────────────────────────────────────
        'oluştur'   => 'create',
        'olustur'   => 'create',
        'ekle'      => 'add',
        'göster'    => 'show',
        'goster'    => 'show',
        'yönlendir' => 'redirect',
        'yonlendir' => 'redirect',
        'güncelle'  => 'update',
        'guncelle'  => 'update',
        'düzelt'    => 'fix',
        'duzelt'    => 'fix',
        'sayfa'     => 'page',
        'anasayfa'  => 'home',
        'giris'     => 'login',
        'giriş'     => 'login',
        'cikis'     => 'logout',
        'çıkış'     => 'logout',
        // ── Indonesian/Malay ─────────────────────────────────────────────────
        'bikin'     => 'create',
        'tambah'    => 'add',
        'tambahkan' => 'add',
        'hapus'     => 'delete',
        'hilangkan' => 'remove',
        'tampilkan' => 'show',
        'tunjukkan' => 'show',
        'ketika'    => 'when',
        'setelah'   => 'after',
        'sebelum'   => 'before',
        'untuk'     => 'for',
        'dengan'    => 'with',
        'tanpa'     => 'without',
        'tidak'     => 'not',
        'jangan'    => "don't",
        'semua'     => 'all',
        'halaman'   => 'page',
        'beranda'   => 'home',
        'perbarui'  => 'update',
        'perbaiki'  => 'fix',
        // ── German ──────────────────────────────────────────────────────────
        'erstellen' => 'create',
        'hinzufügen'=> 'add',
        'hinzufugen'=> 'add',
        'löschen'   => 'delete',
        'loschen'   => 'delete',
        'entfernen' => 'remove',
        'anzeigen'  => 'show',
        'umleiten'  => 'redirect',
        'aktualisieren' => 'update',
        'reparieren'=> 'fix',
        'weiterleiten' => 'redirect',
        'startseite'=> 'home',
        'zur'       => 'to',   // German "to the"
        'zum'       => 'to',   // German "to the" (masculine)
        // ── Italian ─────────────────────────────────────────────────────────
        'creare'    => 'create',
        'aggiungere'=> 'add',
        'eliminare' => 'delete',
        'rimuovere' => 'remove',
        'mostrare'  => 'show',
        'reindirizzare' => 'redirect',
        'aggiornare'=> 'update',
        'correggere'=> 'fix',
        'costruire' => 'build',
        'inviare'   => 'send',
        // ── Vietnamese (romanized) ───────────────────────────────────────────
        'tạo'       => 'create',
        'thêm'      => 'add',
        'xóa'       => 'delete',
        'trang'     => 'page',
        'sửa'       => 'fix',
    ];

    /**
     * Normalize a multilingual/mixed-language prompt to English.
     * Returns the normalized string; identical to input if nothing was normalized.
     */
    public function normalize(string $prompt): string
    {
        $result = $prompt;

        // Step 1: Phrase-level (longest phrases first, case-insensitive)
        $sortedPhrases = $this->phrases;
        uksort($sortedPhrases, static fn($a, $b) => strlen($b) - strlen($a));

        foreach ($sortedPhrases as $phrase => $replacement) {
            $pattern = '/(?<![a-zA-ZÀ-ÖØ-öø-ÿ])' . preg_quote($phrase, '/') . '(?![a-zA-ZÀ-ÖØ-öø-ÿ])/iu';
            $result  = preg_replace($pattern, $replacement, $result);
        }

        // Step 2: Contextual patterns (ambiguous words with safe surrounding context)
        foreach ($this->contextualPatterns as $pattern => $replacement) {
            $result = preg_replace($pattern, $replacement, $result);
        }

        // Step 3: Unambiguous word replacements (word boundaries, Unicode-aware)
        $sortedWords = $this->words;
        uksort($sortedWords, static fn($a, $b) => strlen($b) - strlen($a)); // longest first

        foreach ($sortedWords as $word => $replacement) {
            $escaped = preg_quote($word, '/');
            $pattern = '/(?<![a-zA-ZÀ-ÖØ-öø-ÿ])' . $escaped . '(?![a-zA-ZÀ-ÖØ-öø-ÿ])/iu';
            $result  = preg_replace($pattern, $replacement, $result);
        }

        // Step 4: Clean up extra whitespace
        $result = preg_replace('/\s{2,}/', ' ', trim($result));

        return $result;
    }

    /**
     * Enrich a prompt by appending a normalized English translation when the prompt
     * contains non-English words. The AI sees both versions, preserving nuance.
     */
    public function enrichPrompt(string $prompt): string
    {
        $normalized = $this->normalize($prompt);

        // Only append if normalization actually changed something meaningful
        if ($normalized === $prompt || similar_text($normalized, $prompt) / max(strlen($prompt), 1) > 0.95) {
            return $prompt;
        }

        return $prompt . "\n[Normalized intent: " . $normalized . "]";
    }
}
