<?php

namespace App\Http\Controllers;

use App\Models\AIProvider;
use App\Models\AIProviderKey;
use App\Models\Setting;
use App\Models\User;
use App\Services\AI\AIManager;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function __construct(protected AIManager $aiManager) {}

    public function index()
    {
        $user          = Auth::user();
        $providerQuery = $user->aiProviders();

        if (Schema::hasTable('ai_provider_keys')) {
            $providerQuery->with(['keys' => function ($query) {
                $query->orderByDesc('is_primary')->orderBy('fail_count');
            }]);
        }

        $aiProviders   = $providerQuery->get();
        $allProviders  = config('ai.providers', []);
        $providerRows  = $this->buildAIProviderRows($allProviders, $aiProviders);
        $userSettings  = $user->settings()->get()->groupBy('group');

        return view('settings.index', compact('user', 'aiProviders', 'allProviders', 'providerRows', 'userSettings'));
    }

    private function buildAIProviderRows(array $allProviders, Collection $aiProviders): Collection
    {
        $providerLinks = $this->providerLinks();
        $categoryTitles = [
            'text'  => 'Text Generation',
            'voice' => 'Voice & Audio',
            'local' => 'Local / Self-Hosted',
        ];
        $savedByProvider = $aiProviders->keyBy('provider');

        return collect(array_keys($allProviders))
            ->merge($savedByProvider->keys())
            ->unique()
            ->map(function (string $key) use ($allProviders, $savedByProvider, $providerLinks, $categoryTitles) {
                /** @var AIProvider|null $saved */
                $saved = $savedByProvider->get($key);
                $provider = $allProviders[$key] ?? [];
                $category = $provider['category'] ?? 'text';
                $configuredModels = collect($provider['models'] ?? []);

                if ($configuredModels->isEmpty() && $saved?->default_model) {
                    $configuredModels = collect([$saved->default_model => $saved->default_model]);
                }

                $models = $configuredModels
                    ->map(fn ($name, $modelKey) => ['key' => $modelKey, 'name' => $name])
                    ->values();
                $savedKeys = $this->formatProviderKeys($saved);
                $hasCredential = $this->providerHasCredential($saved);
                $keyCount = count($savedKeys) ?: ($hasCredential ? 1 : 0);

                $hasKey     = $this->providerHasCredential($saved);
                $isActive   = (bool) ($saved?->is_active);
                $configured = $hasKey && $isActive;

                return [
                    'provider'          => $key,
                    'name'              => $saved?->name ?? ($provider['name'] ?? ucfirst(str_replace('_', ' ', $key))),
                    'category'          => $category,
                    'category_label'    => $categoryTitles[$category] ?? ucfirst($category),
                    'has_key'           => $hasKey,
                    'is_active'         => $isActive,
                    'configured'        => $configured,
                    'provider_id'       => $saved?->id,
                    'api_url'           => $saved?->api_url ?? ($provider['api_url'] ?? ''),
                    'default_model'     => $saved?->default_model ?? ($provider['default_model'] ?? ''),
                    'is_default'        => (bool) ($saved?->is_default),
                    'last_used_at'      => optional($saved?->last_used_at)->toISOString(),
                    'updated_at'        => optional($saved?->updated_at)->toISOString(),
                    'models'            => $models,
                    'model_count'       => $models->count(),
                    'requires_endpoint' => !empty($provider['requires_endpoint']) || $key === 'ollama' || (bool) ($saved?->api_url && empty($provider)),
                    'api_url_label'     => $key === 'ollama' ? 'Ollama Host URL' : ($key === 'bedrock' ? 'AWS Region' : 'Endpoint URL'),
                    'external_url'      => $providerLinks[$key]['url'] ?? null,
                    'external_label'    => $providerLinks[$key]['label'] ?? null,
                    'keys'              => $savedKeys,
                    'key_count'         => $keyCount,
                ];
            })
            ->values();
    }

    private function formatProviderKeys(?AIProvider $provider): array
    {
        if (!$provider || !$provider->relationLoaded('keys')) {
            return [];
        }

        return $provider->keys
            ->map(fn (AIProviderKey $key) => [
                'id'           => $key->id,
                'label'        => $key->label,
                'is_primary'   => (bool) $key->is_primary,
                'is_active'    => (bool) $key->is_active,
                'fail_count'   => (int) $key->fail_count,
                'last_used_at' => optional($key->last_used_at)->toISOString(),
            ])
            ->values()
            ->toArray();
    }

    private function providerHasCredential(?AIProvider $provider): bool
    {
        if (!$provider) {
            return false;
        }

        if ($provider->relationLoaded('keys') && $provider->keys->contains(fn (AIProviderKey $key) => $key->is_active)) {
            return true;
        }

        return $provider->getDecryptedApiKey() !== null;
    }

    private function providerLinks(): array
    {
        return [
            'claude'      => ['url' => 'https://console.anthropic.com/settings/keys', 'label' => 'console.anthropic.com'],
            'openai'      => ['url' => 'https://platform.openai.com/api-keys', 'label' => 'platform.openai.com'],
            'gemini'      => ['url' => 'https://aistudio.google.com/app/apikey', 'label' => 'aistudio.google.com'],
            'mistral'     => ['url' => 'https://console.mistral.ai/api-keys', 'label' => 'console.mistral.ai'],
            'grok'        => ['url' => 'https://console.x.ai/', 'label' => 'console.x.ai'],
            'deepseek'    => ['url' => 'https://platform.deepseek.com/api-keys', 'label' => 'platform.deepseek.com'],
            'groq'        => ['url' => 'https://console.groq.com/keys', 'label' => 'console.groq.com'],
            'cohere'      => ['url' => 'https://dashboard.cohere.com/api-keys', 'label' => 'dashboard.cohere.com'],
            'perplexity'  => ['url' => 'https://www.perplexity.ai/settings/api', 'label' => 'perplexity.ai'],
            'openrouter'  => ['url' => 'https://openrouter.ai/keys', 'label' => 'openrouter.ai'],
            'together'    => ['url' => 'https://api.together.ai/settings/api-keys', 'label' => 'api.together.ai'],
            'huggingface' => ['url' => 'https://huggingface.co/settings/tokens', 'label' => 'huggingface.co'],
            'azure'       => ['url' => 'https://portal.azure.com/', 'label' => 'portal.azure.com'],
            'bedrock'     => ['url' => 'https://aws.amazon.com/bedrock/', 'label' => 'aws.amazon.com/bedrock'],
            'replicate'   => ['url' => 'https://replicate.com/account/api-tokens', 'label' => 'replicate.com'],
            'fireworks'   => ['url' => 'https://fireworks.ai/account/api-keys', 'label' => 'fireworks.ai'],
            'cerebras'    => ['url' => 'https://cloud.cerebras.ai/', 'label' => 'cloud.cerebras.ai'],
            'ai21'        => ['url' => 'https://studio.ai21.com/account/api-key', 'label' => 'studio.ai21.com'],
            'sambanova'   => ['url' => 'https://cloud.sambanova.ai/apis', 'label' => 'cloud.sambanova.ai'],
            'elevenlabs'  => ['url' => 'https://elevenlabs.io/app/settings/api-keys', 'label' => 'elevenlabs.io'],
            'ollama'      => ['url' => 'https://ollama.com/download', 'label' => 'ollama.com'],
        ];
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email,'.$user->id],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username,'.$user->id],
        ]);

        $user->update($request->only('name', 'email', 'username'));

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', 'min:8'],
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        Auth::user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function saveSystemConfig(Request $request)
    {
        $userId = Auth::id();
        Setting::set('system.show_dashboard_menu',    $request->boolean('show_dashboard_menu')    ? '1' : '0', 'boolean', $userId);
        Setting::set('system.show_dashboard_sidebar', $request->boolean('show_dashboard_sidebar') ? '1' : '0', 'boolean', $userId);
        Setting::set('system.sidebar_auto_hide',      $request->boolean('sidebar_auto_hide')      ? '1' : '0', 'boolean', $userId);
        Setting::set('ai_builder.auto_docs',          $request->boolean('ai_builder_auto_docs')   ? '1' : '0', 'boolean', $userId);
        if ($request->has('public_site_project_id')) {
            Setting::set('system.public_site_project_id', (string)(int)$request->input('public_site_project_id', 0), 'string', null);
        }

        return response()->json(['success' => true]);
    }

    // ── AI Provider key management ──────────────────────────────────────────────

    public function addProviderKey(Request $request, AIProvider $aiProvider)
    {
        abort_if($aiProvider->user_id !== Auth::id(), 403);

        $request->validate([
            'label'   => ['nullable', 'string', 'max:100'],
            'api_key' => ['required', 'string'],
        ]);

        $keyNumber  = $aiProvider->keys()->count() + 1;
        $isPrimary  = $keyNumber === 1;

        $key = $aiProvider->keys()->create([
            'user_id'    => Auth::id(),
            'label'      => $request->input('label') ?: "Key {$keyNumber}",
            'api_key'    => $request->input('api_key'),
            'is_primary' => $isPrimary,
            'is_active'  => true,
        ]);

        return response()->json([
            'success' => true,
            'key'     => [
                'id'          => $key->id,
                'label'       => $key->label,
                'is_primary'  => $key->is_primary,
                'is_active'   => $key->is_active,
                'fail_count'  => 0,
                'last_used_at' => null,
            ],
        ]);
    }

    public function deleteProviderKey(AIProvider $aiProvider, AIProviderKey $key)
    {
        abort_if($aiProvider->user_id !== Auth::id() || $key->ai_provider_id !== $aiProvider->id, 403);

        $wasPrimary = $key->is_primary;
        $key->delete();

        if ($wasPrimary) {
            $aiProvider->keys()->where('is_active', true)->first()?->update(['is_primary' => true]);
        }

        return response()->json(['success' => true]);
    }

    public function setPrimaryProviderKey(AIProvider $aiProvider, AIProviderKey $key)
    {
        abort_if($aiProvider->user_id !== Auth::id() || $key->ai_provider_id !== $aiProvider->id, 403);

        $aiProvider->keys()->update(['is_primary' => false]);
        $key->update(['is_primary' => true]);

        return response()->json(['success' => true]);
    }

    public function toggleProviderKey(AIProvider $aiProvider, AIProviderKey $key)
    {
        abort_if($aiProvider->user_id !== Auth::id() || $key->ai_provider_id !== $aiProvider->id, 403);

        $key->update(['is_active' => !$key->is_active]);

        return response()->json(['success' => true, 'is_active' => $key->is_active]);
    }

    // AI Provider methods

    public function saveAIProvider(Request $request)
    {
        $request->validate([
            'provider'      => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(config('ai.providers', [])))],
            'api_key'       => ['nullable', 'string'],
            'api_url'       => ['nullable', 'string', 'max:500'],
            'default_model' => ['nullable', 'string'],
        ]);

        $providerConfig = config("ai.providers.{$request->provider}");
        $name           = $providerConfig['name'] ?? ucfirst($request->provider);

        // Check whether a key already exists for this provider
        $existing = AIProvider::where('user_id', Auth::id())
            ->where('provider', $request->provider)
            ->when(Schema::hasTable('ai_provider_keys'), fn ($query) => $query->with('keys'))
            ->first();
        $hasExistingKey = $this->providerHasCredential($existing);

        // Reject the save if no key is supplied and none is stored yet
        if (!$request->filled('api_key') && !$hasExistingKey) {
            return response()->json([
                'success' => false,
                'message' => "Please enter your API Key — the field cannot be empty.",
            ], 422);
        }

        $provider = AIProvider::updateOrCreate(
            ['user_id' => Auth::id(), 'provider' => $request->provider],
            [
                'name'          => $name,
                'api_url'       => $request->api_url,
                'default_model' => $request->default_model ?? $providerConfig['default_model'] ?? null,
                'is_active'     => true,
            ]
        );

        // Only overwrite key if a new one was supplied
        if ($request->filled('api_key')) {
            $provider->api_key = $request->api_key;
            $provider->save();

            // Sync the new key into ai_provider_keys as the primary key
            if (Schema::hasTable('ai_provider_keys')) {
                $primaryKey = $provider->keys()->where('is_primary', true)->first();
                if ($primaryKey) {
                    $primaryKey->api_key = $request->api_key;
                    $primaryKey->fail_count = 0;
                    $primaryKey->last_failed_at = null;
                    $primaryKey->save();
                } else {
                    $provider->keys()->create([
                        'user_id'    => Auth::id(),
                        'label'      => 'Primary Key',
                        'api_key'    => $request->api_key,
                        'is_primary' => true,
                        'is_active'  => true,
                    ]);
                }
            }
        }

        // Set as default if requested or if it's the first provider
        if ($request->boolean('set_default') || AIProvider::where('user_id', Auth::id())->count() === 1) {
            AIProvider::where('user_id', Auth::id())->update(['is_default' => false]);
            $provider->update(['is_default' => true]);
        }

        if (Schema::hasTable('ai_provider_keys')) {
            $provider->load(['keys' => function ($query) {
                $query->orderByDesc('is_primary')->orderBy('fail_count');
            }]);
        }

        $keys = $this->formatProviderKeys($provider);

        if ($request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'message'     => $name . ' connected successfully!',
                'provider_id' => $provider->id,
                'updated_at'  => optional($provider->updated_at)->toISOString(),
                'keys'        => $keys,
                'key_count'   => count($keys) ?: ($this->providerHasCredential($provider) ? 1 : 0),
            ]);
        }

        return back()->with('success', $name.' API key saved successfully.');
    }

    public function deleteAIProvider(AIProvider $aiProvider)
    {
        if ($aiProvider->user_id !== Auth::id()) abort(403);

        $aiProvider->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'AI provider removed.');
    }

    public function testAIProvider(Request $request)
    {
        $request->validate([
            'provider' => ['required', 'string'],
        ]);

        try {
            $provider = $this->aiManager->provider($request->provider, Auth::user());

            if (!$provider->isConfigured()) {
                return response()->json(['success' => false, 'message' => 'Provider is not configured. Please add your API key.']);
            }

            $result = $provider->chat([
                ['role' => 'user', 'content' => 'Say "RyaanCMS connection test successful!" and nothing else.']
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['content'],
                'model'   => $result['model'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();

        $preferences = $request->only(['theme', 'default_ai_provider', 'default_model', 'language']);

        foreach ($preferences as $key => $value) {
            Setting::set("appearance.{$key}", $value, 'string', $user->id);
        }

        return back()->with('success', 'Preferences saved.');
    }

    public function saveBranding(Request $request)
    {
        $request->validate([
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font_family'   => ['nullable', 'string', 'max:60'],
        ]);

        $userId = Auth::id();

        Setting::set('branding.primary_color', $request->input('primary_color', '#6366f1'), 'string', $userId);
        Setting::set('branding.font_family',   $request->input('font_family', 'Poppins'),   'string', $userId);

        return back()->with('success', 'Branding settings saved.');
    }

    public function uploadBrandingAsset(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:logo,favicon'],
            'file' => ['required', 'file', 'mimes:png,jpg,jpeg,gif,ico,svg,webp', 'max:2048'],
        ]);

        $userId = Auth::id();
        $type   = $request->input('type');
        $path   = $request->file('file')->store("branding/{$userId}", 'public');

        Setting::set("branding.{$type}_path", $path, 'string', $userId);

        return back()->with('success', ucfirst($type).' uploaded successfully.');
    }

    public function saveBrandingGlobal(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $request->validate([
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font_family'   => ['nullable', 'string', 'max:60'],
        ]);

        Setting::set('branding.primary_color', $request->input('primary_color', '#6366f1'), 'string', null);
        Setting::set('branding.font_family',   $request->input('font_family', 'Poppins'),   'string', null);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Branding saved as site default.']);
        }

        return back()->with('success', 'Branding saved as site default.');
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'file', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
        ]);

        $user = Auth::user();
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store("avatars/{$user->id}", 'public');
        $user->update(['avatar' => $path]);

        return back()->with('success', 'Avatar updated successfully.');
    }

    public function toggleAIProviderActive(AIProvider $aiProvider)
    {
        abort_if($aiProvider->user_id !== Auth::id(), 403);

        $aiProvider->update(['is_active' => !$aiProvider->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $aiProvider->is_active,
        ]);
    }

    // ── Team CRUD ────────────────────────────────────────────────────────────

    public function teamIndex()
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $members = User::where('id', '!=', Auth::id())
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'username', 'role', 'avatar', 'is_active', 'created_at'])
            ->map(fn($u) => array_merge($u->toArray(), ['avatar_url' => $u->avatar_url]));

        return response()->json($members);
    }

    public function storeTeamMember(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', 'in:admin,developer,user'],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'username' => $request->input('username'),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => $user->name.' added to team.',
            'member'  => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'username'   => $user->username,
                'role'       => $user->role,
                'is_active'  => $user->is_active,
                'avatar_url' => $user->avatar_url,
                'created_at' => $user->created_at->toISOString(),
            ],
        ]);
    }

    public function updateTeamMember(Request $request, User $user)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        abort_if($user->id === Auth::id(), 422, 'Cannot modify your own account here.');

        $request->validate([
            'name'      => ['nullable', 'string', 'max:255'],
            'email'     => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'username'  => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'password'  => ['nullable', 'string', 'min:8'],
            'role'      => ['nullable', 'in:admin,developer,user'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = array_filter([
            'name'      => $request->input('name'),
            'email'     => $request->input('email'),
            'username'  => $request->input('username'),
            'password'  => $request->filled('password') ? Hash::make($request->password) : null,
            'role'      => $request->input('role'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : null,
        ], fn($v) => $v !== null);

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => $user->name.' updated.',
            'member'  => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'username'   => $user->username,
                'role'       => $user->role,
                'is_active'  => $user->is_active,
                'avatar_url' => $user->avatar_url,
                'created_at' => $user->created_at?->toISOString(),
            ],
        ]);
    }

    public function destroyTeamMember(User $user)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        abort_if($user->id === Auth::id(), 422, 'Cannot delete your own account here.');

        $name = $user->name;
        $user->delete();

        return response()->json(['success' => true, 'message' => $name.' removed from team.']);
    }

    // ── Integrations ─────────────────────────────────────────────────────────

    public function saveIntegration(Request $request)
    {
        $type = $request->input('type');
        $allowed = ['github', 'webhook', 'slack', 'sendgrid', 'google_analytics', 'smtp'];
        abort_unless(in_array($type, $allowed), 422, 'Unknown integration type.');

        $userId = Auth::id();

        match ($type) {
            'github' => [
                Setting::set('integration.github_token',   $request->input('github_token', ''),   'string', $userId),
                Setting::set('integration.github_org',     $request->input('github_org', ''),     'string', $userId),
            ],
            'webhook' => [
                Setting::set('integration.webhook_url',    $request->input('webhook_url', ''),    'string', $userId),
                Setting::set('integration.webhook_secret', $request->input('webhook_secret', ''), 'string', $userId),
            ],
            'slack' => [
                Setting::set('integration.slack_webhook',  $request->input('slack_webhook', ''),  'string', $userId),
                Setting::set('integration.slack_channel',  $request->input('slack_channel', '#general'), 'string', $userId),
            ],
            'sendgrid' => [
                Setting::set('integration.sendgrid_key',   $request->input('sendgrid_key', ''),   'string', $userId),
                Setting::set('integration.sendgrid_from',  $request->input('sendgrid_from', ''),  'string', $userId),
            ],
            'google_analytics' => [
                Setting::set('integration.ga_id',          $request->input('ga_id', ''),          'string', $userId),
            ],
            'smtp' => [
                Setting::set('integration.smtp_host',     $request->input('smtp_host', ''),     'string', $userId),
                Setting::set('integration.smtp_port',     $request->input('smtp_port', '587'),  'string', $userId),
                Setting::set('integration.smtp_user',     $request->input('smtp_user', ''),     'string', $userId),
                Setting::set('integration.smtp_password', $request->input('smtp_password', ''), 'string', $userId),
                Setting::set('integration.smtp_from',     $request->input('smtp_from', ''),     'string', $userId),
            ],
            default => null,
        };

        return response()->json(['success' => true, 'message' => ucfirst(str_replace('_', ' ', $type)).' settings saved.']);
    }

    public function testIntegration(Request $request)
    {
        $type  = $request->input('type');
        $userId = Auth::id();

        try {
            switch ($type) {
                case 'webhook': {
                    $url = Setting::get('integration.webhook_url', '', $userId);
                    if (!$url) return response()->json(['success' => false, 'message' => 'No webhook URL configured.']);
                    $secret = Setting::get('integration.webhook_secret', '', $userId);
                    $payload = json_encode(['event' => 'test', 'source' => 'RyaanCMS', 'timestamp' => now()->toISOString()]);
                    $sig = $secret ? hash_hmac('sha256', $payload, $secret) : null;
                    $headers = ['Content-Type' => 'application/json', 'X-RyaanCMS-Event' => 'test'];
                    if ($sig) $headers['X-RyaanCMS-Signature'] = 'sha256='.$sig;
                    $response = \Illuminate\Support\Facades\Http::withHeaders($headers)->timeout(8)->post($url, json_decode($payload, true));
                    return response()->json([
                        'success' => $response->successful(),
                        'message' => $response->successful() ? 'Webhook delivered successfully (HTTP '.$response->status().').' : 'Webhook failed: HTTP '.$response->status().'.',
                    ]);
                }
                case 'slack': {
                    $webhook = Setting::get('integration.slack_webhook', '', $userId);
                    if (!$webhook) return response()->json(['success' => false, 'message' => 'No Slack webhook URL configured.']);
                    $response = \Illuminate\Support\Facades\Http::timeout(8)->post($webhook, [
                        'text' => ':white_check_mark: *RyaanCMS test message* — your Slack integration is working!',
                    ]);
                    return response()->json([
                        'success' => $response->successful(),
                        'message' => $response->successful() ? 'Test message sent to Slack!' : 'Slack error: '.$response->body(),
                    ]);
                }
                case 'github': {
                    $token = Setting::get('integration.github_token', '', $userId);
                    if (!$token) return response()->json(['success' => false, 'message' => 'No GitHub token configured.']);
                    $response = \Illuminate\Support\Facades\Http::withToken($token)->timeout(8)->get('https://api.github.com/user');
                    if ($response->successful()) {
                        $login = $response->json('login');
                        return response()->json(['success' => true, 'message' => "Connected as @{$login}."]);
                    }
                    return response()->json(['success' => false, 'message' => 'GitHub auth failed: '.($response->json('message') ?? 'Invalid token.')]);
                }
                default:
                    return response()->json(['success' => false, 'message' => 'Test not supported for this integration.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()]);
        }
    }
}
