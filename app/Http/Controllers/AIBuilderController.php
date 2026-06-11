<?php

namespace App\Http\Controllers;

use App\Models\AIConversation;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectModule;
use App\Services\AI\AIManager;
use App\Services\AI\BlueprintService;
use App\Services\AI\CodeGeneratorService;
use App\Services\AI\ComponentRegistry;
use App\Services\AI\MetadataCrudGenerator;
use App\Services\Template\TemplateRegistry;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class AIBuilderController extends Controller
{
    private const MAX_ATTACHED_TEXT_CHARS = 12000;
    private const MAX_IMAGE_BYTES_FOR_PROMPT = 4194304;
    private const MAX_PROJECT_FILE_CHARS = 1048576;
    private const MAX_TEMPLATE_SOURCE_CHARS = 12000;

    public function __construct(
        protected AIManager        $aiManager,
        protected CodeGeneratorService $codeGenerator,
        protected BlueprintService $blueprintService,
        protected MetadataCrudGenerator $crudGenerator,
        protected ComponentRegistry $componentRegistry,
        protected TemplateRegistry $templateRegistry,
    ) {}

    public function show(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $files       = $project->files()->orderBy('type', 'desc')->orderBy('path')->get();
        $providers   = Auth::user()->aiProviders()->where('is_active', true)->get();
        $conversations = $project->conversations()->limit(20)->get();
        $aiProviders = config('ai.providers');
        $templates   = $this->builderTemplates($project);
        $requestedTemplateKey = (string) $request->query('template');
        $selectedTemplateKey = isset($templates[$requestedTemplateKey]) ? $requestedTemplateKey : null;

        // Load or create active conversation
        $conversation = $project->conversations()->latest()->first()
            ?? AIConversation::create([
                'user_id'    => Auth::id(),
                'project_id' => $project->id,
                'provider'   => Auth::user()->getSetting('ai', 'default_provider', 'claude'),
                'title'      => 'New Conversation',
            ]);

        $messages = $conversation->messages()->get();

        return view('builder.show', compact(
            'project', 'files', 'providers', 'conversations',
            'conversation', 'messages', 'aiProviders', 'templates', 'selectedTemplateKey'
        ));
    }

    public function chat(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'message'         => ['nullable', 'string', 'max:20000'],
            'conversation_id' => ['nullable', 'exists:ai_conversations,id'],
            'provider'        => ['nullable', 'string'],
            'model'           => ['nullable', 'string'],
            'template_key'    => ['nullable', 'string', Rule::in(array_keys($this->builderTemplates($project)))],
            'url'             => ['nullable', 'url', 'max:500'],
            'files'           => ['nullable', 'array', 'max:10'],
            'files.*'         => ['nullable', 'file', 'max:10240'],
        ]);

        $provider = $request->input('provider', 'claude');

        // Build enriched prompt from attachments + URL
        $userMessage  = trim($request->message ?? '');
        $contextParts = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $extracted = $this->extractFileContent($file);
                if ($extracted) {
                    $contextParts[] = $extracted;
                }
            }
        }

        if ($request->url) {
            $fetched = $this->fetchUrlContent($request->url);
            if ($fetched) {
                $contextParts[] = $fetched;
            }
        }

        $templateContext = $this->templatePromptContext($project, $request->input('template_key'));
        if ($templateContext) {
            $contextParts[] = $templateContext;
        }

        $prompt = empty($contextParts)
            ? $userMessage
            : implode("\n\n", $contextParts) . "\n\n---\n\nUser request: " . ($userMessage ?: 'Please analyze the content above and help me build accordingly.');

        if (empty(trim($prompt))) {
            return response()->json(['success' => false, 'error' => 'Message cannot be empty.'], 422);
        }

        // Get or create conversation
        if ($request->conversation_id) {
            $conversation = AIConversation::where('id', $request->conversation_id)
                ->where('user_id', Auth::id())
                ->where('project_id', $project->id)
                ->firstOrFail();
            $conversation->update(['provider' => $provider, 'model' => $request->model ?? $conversation->model]);
        } else {
            $conversation = AIConversation::create([
                'user_id'    => Auth::id(),
                'project_id' => $project->id,
                'provider'   => $provider,
                'model'      => $request->model,
            ]);
        }

        try {
            $result = $this->codeGenerator->generate(
                prompt:       $prompt,
                project:      $project,
                user:         Auth::user(),
                conversation: $conversation,
                provider:     $provider,
                model:        $request->model,
                visiblePrompt: $this->visiblePromptWithTemplate($project, $userMessage, $request->input('template_key')),
            );

            return response()->json([
                'success'         => true,
                'message'         => $result['message'],
                'files_generated' => $result['files_generated'],
                'tokens_used'     => $result['tokens_used'],
                'tokens_saved'    => $result['tokens_saved'] ?? 0,
                'cache_hit'       => $result['cache_hit'] ?? false,
                'model'           => $result['model'],
                'conversation_id' => $conversation->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function extractFileContent(UploadedFile $file): string
    {
        $name = $file->getClientOriginalName();
        $ext  = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType() ?? '';
        $path = $file->getRealPath();
        $size = $file->getSize() ?? 0;

        // Images — encode as base64 data URI so vision-capable models can see them
        if (str_starts_with($mime, 'image/')) {
            if ($size > self::MAX_IMAGE_BYTES_FOR_PROMPT) {
                return "[IMAGE: {$name}]\nImage omitted because it is larger than 4 MB. Please upload a smaller image or describe the key details.";
            }

            $b64 = base64_encode(file_get_contents($path));
            return "[IMAGE: {$name}]\ndata:{$mime};base64,{$b64}";
        }

        // Plain text / code files — read directly
        $textExts = ['txt','md','mdx','html','htm','php','js','ts','jsx','tsx','css','scss','sass',
                     'json','xml','yaml','yml','toml','csv','sql','py','java','rb','go','rs',
                     'sh','bat','env','ini','conf','log','svg'];
        if (in_array($ext, $textExts) || str_starts_with($mime, 'text/')) {
            $content = file_get_contents($path);
            $content = $this->limitPromptText($content, self::MAX_ATTACHED_TEXT_CHARS);
            return "[FILE: {$name}]\n```{$ext}\n{$content}\n```";
        }

        // PDF — basic readable text extraction
        if ($ext === 'pdf' || $mime === 'application/pdf') {
            $raw = file_get_contents($path);
            preg_match_all('/\(([^\)]{3,500})\)/', $raw, $m);
            $text = implode(' ', array_filter($m[1], fn($s) => mb_detect_encoding($s, 'UTF-8', true) && !preg_match('/[\x00-\x08\x0b-\x0c\x0e-\x1f]/', $s)));
            $text = mb_substr(preg_replace('/\s+/', ' ', trim($text)), 0, 8000);
            return "[PDF: {$name}]\n{$text}";
        }

        // DOCX / XLSX / PPTX — extract XML text from the zip container
        if (in_array($ext, ['docx', 'xlsx', 'pptx', 'odt', 'ods', 'odp'])) {
            $zip = new ZipArchive();
            if ($zip->open($path) === true) {
                $xmlFile = match($ext) {
                    'xlsx','ods' => 'xl/sharedStrings.xml',
                    'pptx','odp' => 'ppt/slides/slide1.xml',
                    default      => 'word/document.xml',
                };
                $xml = $zip->getFromName($xmlFile) ?: '';
                $zip->close();
                $text = mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags($xml))), 0, 8000);
                return '[' . strtoupper($ext) . ": {$name}]\n{$text}";
            }
        }

        // DOC (legacy binary Word) — extract printable strings
        if ($ext === 'doc') {
            $raw  = file_get_contents($path);
            preg_match_all('/[\x20-\x7E]{4,}/', $raw, $m);
            $text = mb_substr(implode(' ', $m[0]), 0, 6000);
            return "[DOC: {$name}]\n{$text}";
        }

        // Unrecognised binary
        return "[ATTACHED FILE: {$name} ({$mime}) — binary content, describe its purpose in your request]";
    }

    private function limitPromptText(string $content, int $maxChars): string
    {
        if (mb_strlen($content) <= $maxChars) {
            return $content;
        }

        return mb_substr($content, 0, $maxChars)
            . "\n\n[Content truncated for speed and security. Upload a smaller file or ask about a specific section if needed.]";
    }

    private function fetchUrlContent(string $url): string
    {
        $scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?: '');
        if (!in_array($scheme, ['http', 'https'], true)) {
            return "[URL: {$url}]\nAccess denied - only HTTP and HTTPS URLs are allowed.";
        }

        // SSRF protection: block internal/private network addresses
        $host = parse_url($url, PHP_URL_HOST);
        if ($host) {
            $ip = gethostbyname($host);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return "[URL: {$url}]\nAccess denied — private/internal addresses are not allowed.";
            }
        }

        try {
            $response = Http::timeout(12)
                ->withHeaders(['User-Agent' => 'RyaanCMS-AI-Builder/1.0 (content reader)'])
                ->get($url);

            if (! $response->successful()) {
                return "[URL: {$url}]\nFailed to fetch — HTTP {$response->status()}";
            }

            $html = $response->body();

            // Page title
            preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $titleM);
            $title = html_entity_decode(strip_tags($titleM[1] ?? ''), ENT_QUOTES | ENT_HTML5);

            // Strip boilerplate tags then convert to text
            $html = preg_replace('/<(script|style|nav|footer|header|aside|noscript)[^>]*>.*?<\/\1>/is', ' ', $html);
            $html = preg_replace('/<[^>]+>/', ' ', $html);
            $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5);
            $text = mb_substr(trim(preg_replace('/\s+/', ' ', $html)), 0, 8000);

            return "[WEBSITE: {$url}]\nTitle: {$title}\n\n{$text}";
        } catch (\Exception $e) {
            return "[URL: {$url}]\nFailed to fetch — " . $e->getMessage();
        }
    }

    private function builderTemplates(Project $project): array
    {
        return array_replace($this->templateRegistry->all(), $this->uploadedTemplatesForBuilder($project));
    }

    private function uploadedTemplatesForBuilder(Project $project): array
    {
        $templates = [];
        $projectIds = Auth::user()?->projects()->pluck('id') ?? collect([$project->id]);
        if ($projectIds->isEmpty()) {
            return [];
        }

        $modules = ProjectModule::whereIn('project_id', $projectIds)
            ->where('module_key', 'like', 'template.uploaded.%')
            ->latest('updated_at')
            ->get();

        foreach ($modules as $module) {
            if (isset($templates[$module->module_key])) {
                continue;
            }

            $template = $this->uploadedTemplateMetaForBuilder($module);
            if ($template) {
                $templates[$module->module_key] = $template;
            }
        }

        return $templates;
    }

    private function uploadedTemplateMetaForBuilder(ProjectModule $module): ?array
    {
        if (!data_get($module->options, 'uploaded_template')) {
            return null;
        }

        $sourcePath = data_get($module->options, 'source_path');
        if (!$sourcePath || !Storage::disk('local')->exists($sourcePath)) {
            return null;
        }

        $manifest = data_get($module->options, 'manifest', []);
        if (!is_array($manifest)) {
            $manifest = [];
        }

        return [
            'key' => $module->module_key,
            'name' => (string) ($manifest['name'] ?? 'Uploaded Template'),
            'description' => (string) ($manifest['description'] ?? 'Uploaded custom website template.'),
            'category' => (string) ($manifest['category'] ?? 'Uploaded Template'),
            'type' => 'template',
            'icon' => (string) ($manifest['icon'] ?? 'T'),
            'color' => $this->normalizeUploadedColor($manifest['color'] ?? '#6366f1'),
            'view' => null,
            'source_path' => $sourcePath,
            'tags' => $this->normalizeUploadedTags($manifest['tags'] ?? []),
            'version' => (string) ($manifest['version'] ?? '1.0.0'),
            'is_uploaded' => true,
            'is_global' => false,
        ];
    }

    private function normalizeUploadedTags(mixed $tags): array
    {
        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }

        if (!is_array($tags)) {
            $tags = [];
        }

        $normalized = array_values(array_filter(array_map(
            fn($tag) => trim((string) $tag),
            $tags
        )));

        return $normalized ?: ['uploaded', 'template'];
    }

    private function normalizeUploadedColor(mixed $color): string
    {
        $color = trim((string) $color);

        return preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color)
            ? $color
            : '#6366f1';
    }

    private function templatePromptContext(Project $project, ?string $templateKey): ?string
    {
        if (!$templateKey) {
            return null;
        }

        $template = $this->builderTemplates($project)[$templateKey] ?? null;
        if (!$template) {
            return null;
        }

        $source = $this->templateSource($template);
        $tags = implode(', ', $template['tags'] ?? []);
        $slug = str_replace('template.', '', $templateKey);

        return <<<PROMPT
RYAAN TEMPLATE CUSTOMIZATION CONTEXT
Selected template key: {$templateKey}
Template name: {$template['name']}
Category: {$template['category']}
Description: {$template['description']}
Tags: {$tags}

Instructions:
- Use this template as the design and content base for the user's customization prompt.
- Keep the original structure and quality unless the user asks for a larger redesign.
- Always generate or update `preview.html` so the Builder preview shows the customized template immediately.
- If a reusable Blade template is useful, save it as `resources/views/templates/custom-{$slug}.blade.php`.
- Do not edit RyaanCMS platform files or the built-in template source directly.

Current template source:
```blade
{$source}
```
PROMPT;
    }

    private function templateSource(array $template): string
    {
        $sourcePath = $template['source_path'] ?? null;
        if ($sourcePath && Storage::disk('local')->exists($sourcePath)) {
            return $this->limitPromptText(Storage::disk('local')->get($sourcePath), self::MAX_TEMPLATE_SOURCE_CHARS);
        }

        $view = $template['view'] ?? '';
        $path = resource_path('views/' . str_replace('.', '/', $view) . '.blade.php');

        if (!is_file($path)) {
            return '[Template source file not found.]';
        }

        $source = file_get_contents($path) ?: '';

        if (preg_match("/@include\\(['\"]([^'\"]+)['\"]\\)/", $source, $match)) {
            $includedPath = resource_path('views/' . str_replace('.', '/', $match[1]) . '.blade.php');
            if (is_file($includedPath)) {
                $included = file_get_contents($includedPath) ?: '';
                $source .= "\n\n[Included view: {$match[1]}]\n" . $included;
            }
        }

        return $this->limitPromptText($source, self::MAX_TEMPLATE_SOURCE_CHARS);
    }

    private function templateCodeFiles(array $template, string $templateKey): array
    {
        $sourcePath = $template['source_path'] ?? null;
        if ($sourcePath && Storage::disk('local')->exists($sourcePath)) {
            $name = Str::slug($template['name'] ?? 'uploaded-template') ?: 'uploaded-template';

            return [[
                'id' => 'template:' . $templateKey . ':source',
                'type' => 'file',
                'name' => $name . '.blade.php',
                'path' => 'template-source/' . $name . '.blade.php',
                'extension' => 'php',
                'content' => Storage::disk('local')->get($sourcePath),
                'read_only' => true,
                'template_source' => true,
            ]];
        }

        $view = $template['view'] ?? '';
        if (!$view) {
            return [];
        }

        $files = [];
        $seen = [];
        $this->collectTemplateViewFiles($view, $templateKey, $files, $seen);

        return array_values($files);
    }

    private function collectTemplateViewFiles(string $view, string $templateKey, array &$files, array &$seen): void
    {
        if ($view === '' || isset($seen[$view])) {
            return;
        }

        $seen[$view] = true;
        $relativePath = str_replace('.', '/', $view) . '.blade.php';
        $path = resource_path('views/' . $relativePath);

        if (!is_file($path)) {
            return;
        }

        $content = file_get_contents($path) ?: '';
        $files[$view] = [
            'id' => 'template:' . $templateKey . ':' . $view,
            'type' => 'file',
            'name' => basename($path),
            'path' => 'resources/views/' . $relativePath,
            'extension' => 'php',
            'content' => $content,
            'read_only' => true,
            'template_source' => true,
        ];

        preg_match_all("/@(include|includeIf|extends|component)\\s*\\(\\s*['\"]([^'\"]+)['\"]/", $content, $matches);
        foreach ($matches[2] ?? [] as $nestedView) {
            if (str_starts_with($nestedView, 'templates.')) {
                $this->collectTemplateViewFiles($nestedView, $templateKey, $files, $seen);
            }
        }
    }

    private function visiblePromptWithTemplate(Project $project, string $message, ?string $templateKey): string
    {
        $template = $templateKey ? ($this->builderTemplates($project)[$templateKey] ?? null) : null;
        if (!$template) {
            return $message;
        }

        $request = trim($message) ?: 'Customize this template.';

        return "Customize template: {$template['name']}\n\n{$request}";
    }

    public function streamChat(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'message'         => ['required', 'string', 'max:20000'],
            'conversation_id' => ['nullable', 'exists:ai_conversations,id'],
            'provider'        => ['nullable', 'string'],
            'model'           => ['nullable', 'string'],
            'template_key'    => ['nullable', 'string', Rule::in(array_keys($this->builderTemplates($project)))],
        ]);

        $provider = $request->input('provider', 'claude');

        $conversation = $request->conversation_id
            ? AIConversation::where('id', $request->conversation_id)
                            ->where('user_id', Auth::id())
                            ->where('project_id', $project->id)
                            ->firstOrFail()
            : AIConversation::create([
                'user_id'    => Auth::id(),
                'project_id' => $project->id,
                'provider'   => $provider,
                'model'      => $request->model,
            ]);

        // Keep conversation provider in sync with what the user selected
        if ($conversation->provider !== $provider) {
            $conversation->update(['provider' => $provider]);
        }

        // Capture user before entering the stream closure — Auth::user() can return
        // null inside response()->stream() on some session drivers.
        $user = Auth::user();

        $templateContext = $this->templatePromptContext($project, $request->input('template_key'));
        $userMessage = trim($request->message);
        $prompt = $templateContext
            ? $templateContext . "\n\n---\n\nUser request: " . $userMessage
            : $userMessage;
        $visiblePrompt = $this->visiblePromptWithTemplate($project, $userMessage, $request->input('template_key'));

        return response()->stream(function () use ($request, $project, $conversation, $provider, $user, $prompt, $visiblePrompt) {
            $emit = function (array $event) {
                echo 'data: ' . json_encode($event) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
            };

            try {
                $this->codeGenerator->streamGenerate(
                    prompt:       $prompt,
                    project:      $project,
                    user:         $user,
                    conversation: $conversation,
                    onEvent:      $emit,
                    provider:     $provider,
                    model:        $request->model,
                    visiblePrompt: $visiblePrompt,
                );
            } catch (\Throwable $e) {
                $emit(['type' => 'error', 'message' => $e->getMessage()]);
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    public function getFile(Project $project, ProjectFile $file)
    {
        $this->authorize('view', $project);
        $this->ensureFileBelongsToProject($project, $file);

        return response()->json([
            'id'       => $file->id,
            'name'     => $file->name,
            'path'     => $file->path,
            'content'  => $file->content,
            'language' => $file->language,
        ]);
    }

    public function saveFile(Request $request, Project $project, ProjectFile $file)
    {
        $this->authorize('update', $project);
        $this->ensureFileBelongsToProject($project, $file);

        $request->validate(['content' => ['required', 'string', 'max:' . self::MAX_PROJECT_FILE_CHARS]]);

        $file->update([
            'content' => $request->content,
            'size'    => strlen($request->content),
        ]);

        return response()->json(['success' => true]);
    }

    public function createFile(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'path' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:file,directory'],
        ]);

        $path      = $this->normalizeProjectPath($request->path);
        $name      = basename($path);
        $extension = $request->type === 'file' ? pathinfo($name, PATHINFO_EXTENSION) : null;

        if ($project->files()->where('path', $path)->exists()) {
            throw ValidationException::withMessages([
                'path' => 'A project file already exists at this path.',
            ]);
        }

        $file = ProjectFile::create([
            'project_id' => $project->id,
            'name'       => $name,
            'path'       => $path,
            'type'       => $request->type,
            'extension'  => $extension,
            'content'    => '',
            'size'       => 0,
        ]);

        return response()->json(['success' => true, 'file' => $file]);
    }

    public function deleteFile(Project $project, ProjectFile $file)
    {
        $this->authorize('update', $project);
        $this->ensureFileBelongsToProject($project, $file);

        $file->delete();

        return response()->json(['success' => true]);
    }

    private function ensureFileBelongsToProject(Project $project, ProjectFile $file): void
    {
        abort_unless((int) $file->project_id === (int) $project->id, 404);
    }

    private function normalizeProjectPath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        $path = ltrim($path, '/');

        if (
            $path === ''
            || preg_match('/^[A-Za-z]:/', $path)
            || str_contains($path, "\0")
            || preg_match('/[\x00-\x1F]/', $path)
            || preg_match('#(^|/)\.\.(/|$)#', $path)
        ) {
            throw ValidationException::withMessages([
                'path' => 'Enter a safe relative project path.',
            ]);
        }

        return $path;
    }

    public function previewHtml(Project $project)
    {
        $this->authorize('view', $project);

        // Priority order: preview.html → any .html → welcome/index blade (stripped)
        $file = $project->files()->where('name', 'preview.html')->where('type', 'file')->first()
            ?? $project->files()->where('extension', 'html')->where('type', 'file')->first()
            ?? $project->files()->where('extension', 'htm')->where('type', 'file')->first();

        if ($file && $file->content) {
            return response($file->content, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        // No standalone HTML — return a helpful notice listing the generated files
        $files        = $project->files()->where('type', 'file')->orderBy('path')->get();
        $fileCount    = $files->count();
        $fileRows     = $files->map(fn($f) => '<li>' . e($f->path ?: $f->name) . '</li>')->implode('');
        $filesSection = $fileRows
            ? '<div class="files-label">Generated files</div><ul>' . $fileRows . '</ul>'
            : '';

        $html = <<<HTML
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Preview</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,sans-serif;background:#f9fafb;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}
.card{background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:28px 32px;max-width:480px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.06)}
.badge{display:inline-flex;align-items:center;gap:5px;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;border-radius:20px;padding:3px 12px;font-size:11px;font-weight:600;margin-bottom:16px}
h2{font-size:18px;font-weight:700;color:#111827;margin-bottom:6px}
.sub{font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:18px}
.files-label{font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;margin-bottom:8px}
ul{list-style:none;max-height:240px;overflow-y:auto;border:1px solid #f3f4f6;border-radius:8px;padding:8px 12px}
li{font-family:monospace;font-size:11px;color:#374151;padding:3px 0;border-bottom:1px solid #f9fafb}
li:last-child{border-bottom:none}
.tip{margin-top:18px;padding:13px 15px;background:#f5f3ff;border-radius:10px;border:1px solid #e0e7ff;font-size:12px;color:#4338ca;line-height:1.6}
</style></head>
<body><div class="card">
<div class="badge">🖥️ Laravel / PHP App</div>
<h2>{$fileCount} file(s) generated successfully</h2>
<div class="sub">This is a server-side Laravel app — it needs a PHP server to run.<br>To see a visual preview, ask the AI to generate a <strong>preview.html</strong>.</div>
{$filesSection}
<div class="tip">💡 <strong>Try asking:</strong> "Now generate a standalone preview.html showing the main dashboard UI with sample data using Tailwind CDN (no PHP required)"</div>
</div></body></html>
HTML;

        return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function templatePreview(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $templates = $this->builderTemplates($project);
        $request->validate([
            'template' => ['required', 'string', Rule::in(array_keys($templates))],
        ]);

        $templateKey = (string) $request->query('template');
        $template = $templates[$templateKey] ?? null;
        abort_if(!$template, 404);

        $sourcePath = $template['source_path'] ?? null;
        if ($sourcePath) {
            abort_unless(Storage::disk('local')->exists($sourcePath), 404);

            return response(Blade::render(Storage::disk('local')->get($sourcePath), [
                'project' => $project,
                'template' => $template,
            ]), 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        $view = $template['view'] ?? null;
        abort_unless($view && view()->exists($view), 404);

        return response()
            ->view($view, ['project' => $project, 'template' => $template])
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    public function templateFiles(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $templates = $this->builderTemplates($project);
        $request->validate([
            'template' => ['required', 'string', Rule::in(array_keys($templates))],
        ]);

        $templateKey = (string) $request->query('template');
        $template = $templates[$templateKey] ?? null;
        abort_if(!$template, 404);

        return response()->json([
            'success' => true,
            'files' => $this->templateCodeFiles($template, $templateKey),
        ]);
    }

    public function newConversation(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $conversation = AIConversation::create([
            'user_id'    => Auth::id(),
            'project_id' => $project->id,
            'provider'   => $request->input('provider', 'claude'),
            'title'      => 'New Conversation',
        ]);

        return response()->json([
            'success'      => true,
            'conversation' => $conversation,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Blueprint-Driven Development endpoints
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Phase 1 — Discovery Engine
     *
     * Library-first: 95% of common business types are resolved from the pre-built
     * blueprint library with zero AI tokens — no API key required.
     * Only falls through to an AI call for truly novel/unknown business types.
     */
    public function discover(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $request->validate(['description' => ['required', 'string', 'max:2000']]);

        $description = $request->input('description');

        try {
            // Step 1: Library lookup — 0 AI tokens, no API key needed
            $blueprint = $this->blueprintService->tryLibrary($description);

            // Step 2: Fall back to AI only when library has no match
            if (!$blueprint) {
                $aiProvider = $this->aiManager->provider(
                    $request->input('provider'),
                    Auth::user()
                );
                $blueprint = $this->blueprintService->discover($description, $aiProvider);
            }

            $this->blueprintService->store($project, $blueprint);

            return response()->json([
                'success'     => true,
                'blueprint'   => $blueprint,
                'tokens_used' => $blueprint['tokens_used'] ?? 0,
                'source'      => $blueprint['source'] ?? 'ai_discovery',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * Return the project's current blueprint (no AI call).
     */
    public function getBlueprint(Project $project)
    {
        $this->authorize('view', $project);

        $blueprint = $this->blueprintService->get($project);
        $packs     = config('domain_packs', []);

        return response()->json([
            'success'      => true,
            'blueprint'    => $blueprint,
            'domain_packs' => collect($packs)->map(fn($p, $k) => [
                'key'  => $k,
                'name' => $p['name'],
                'icon' => $p['icon'] ?? '📦',
            ])->values(),
        ]);
    }

    /**
     * Phase 7 — Metadata-Driven CRUD Generation
     * Generates migration, model, controller, and views from entity metadata.
     * ZERO AI cost — pure deterministic PHP generation.
     */
    public function generateCrud(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $request->validate([
            'entity'        => ['required', 'array'],
            'entity.name'   => ['required', 'string', 'regex:/^[A-Za-z][A-Za-z0-9]*$/'],
            'entity.fields' => ['required', 'array', 'min:1'],
            'entity.fields.*.name' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_]*$/'],
            'entity.fields.*.label' => ['nullable', 'string', 'max:80'],
            'entity.fields.*.type' => ['nullable', 'string', 'in:string,text,integer,decimal,boolean,date,datetime,json,enum,foreignId,email'],
            'entity.fields.*.required' => ['nullable', 'boolean'],
            'entity.fields.*.options' => ['nullable', 'array', 'max:20'],
            'entity.fields.*.options.*' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9 _-]+$/'],
        ]);

        try {
            $entity    = $request->input('entity');
            $generated = $this->crudGenerator->generate($entity);

            // Save each file to the project
            $saved = [];
            foreach ($generated as $fileData) {
                $existing = $project->files()->where('path', $fileData['path'])->first();
                if ($existing) {
                    $existing->update(['content' => $fileData['content']]);
                    $saved[] = $existing->fresh();
                } else {
                    $file = $project->files()->create([
                        'path'    => $fileData['path'],
                        'name'    => basename($fileData['path']),
                        'type'    => 'file',
                        'content' => $fileData['content'],
                    ]);
                    $saved[] = $file;
                }
            }

            return response()->json([
                'success'        => true,
                'files_generated'=> $saved,
                'tokens_used'    => 0,
                'message'        => count($saved) . ' files generated for ' . $entity['name'] . ' (no AI tokens used)',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * Return all domain packs with their entity definitions.
     */
    public function domainPacks()
    {
        $packs = Cache::remember('builder:domain-packs', 600, fn() => config('domain_packs', []));

        return response()->json([
            'success' => true,
            'packs'   => $packs,
        ]);
    }

    /**
     * Component Registry — return all pre-built components (zero AI tokens).
     * Optional: filter by category or search query.
     */
    public function components(Request $request)
    {
        $search   = $request->input('search', '');
        $category = $request->input('category', '');
        $cacheKey = 'builder:components:' . md5($category . '|' . $search);

        $components = Cache::remember($cacheKey, 300, function () use ($search, $category) {
            return $search
                ? $this->componentRegistry->search($search)
                : $this->componentRegistry->all($category);
        });

        $categories = Cache::remember(
            'builder:component-categories',
            600,
            fn() => $this->componentRegistry->categories()
        );
        $totalTokensSaved = Cache::remember(
            'builder:component-total-tokens-saved',
            600,
            fn() => $this->componentRegistry->totalTokensSaved()
        );

        return response()->json([
            'success'       => true,
            'components'    => array_map(function ($key, $c) {
                return [
                    'key'          => $key,
                    'label'        => $c['label'],
                    'category'     => $c['category'],
                    'description'  => $c['description'],
                    'tags'         => $c['tags'] ?? [],
                    'tokens_saved' => $c['tokens_saved'] ?? 0,
                    'preview'      => $c['preview'] ?? '',
                    'template'     => $c['template'],
                ];
            }, array_keys($components), array_values($components)),
            'categories'    => $categories,
            'total_tokens_saved' => $totalTokensSaved,
        ]);
    }

    /**
     * Insert a specific component template as a chat message response.
     * Bypasses AI entirely — instant, zero-cost.
     */
    public function insertComponent(Request $request, Project $project, string $key)
    {
        $this->authorize('update', $project);

        $component = $this->componentRegistry->get($key);
        if (!$component) {
            return response()->json(['success' => false, 'error' => 'Component not found'], 404);
        }

        $saved   = $this->componentRegistry->tokensSaved($key);
        $message = "⚡ **{$component['label']}** inserted from Component Registry — {$saved} tokens saved.\n\n"
                 . "```blade\n{$component['template']}\n```\n\n"
                 . "_Customise the field names, route targets, and variable names to match your models._";

        return response()->json([
            'success'      => true,
            'message'      => "**{$component['label']}** is ready.\n\n```blade\n{$component['template']}\n```\n\n_Customize the field names, route targets, and variable names to match your models._",
            'component'    => $key,
            'tokens_saved' => $saved,
        ]);
    }

    /**
     * Smart Questions Engine — return context-aware questions for a project's domain.
     * Helps users discover what they need before building, saving revision AI calls.
     */
    public function smartQuestions(Project $project)
    {
        $this->authorize('view', $project);

        $domain  = $project->type ?? 'general';
        $rawQs   = config("kb.question_intelligence.by_domain.{$domain}", []);

        // Fall back to general
        if (empty($rawQs)) {
            $rawQs = config('kb.question_intelligence.by_domain.general', []);
        }

        // Extract just the question text (the 'q' field) for the UI
        $questions = array_filter(array_map(fn($item) => $item['q'] ?? null, $rawQs));

        // Scope-triggered questions
        $scopeTriggers = array_values(config('kb.question_intelligence.triggered_by_scope', []));

        return response()->json([
            'success'        => true,
            'domain'         => $domain,
            'questions'      => array_values($questions),
            'scope_triggers' => $scopeTriggers,
            'tip'            => config('kb.question_intelligence.rule', ''),
        ]);
    }
}
