<?php

namespace App\Http\Controllers;

use App\Models\AIProvider;
use App\Models\AIProviderKey;
use App\Models\Setting;
use App\Services\AI\AIManager;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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

                return [
                    'provider'          => $key,
                    'name'              => $saved?->name ?? ($provider['name'] ?? ucfirst(str_replace('_', ' ', $key))),
                    'category'          => $category,
                    'category_label'    => $categoryTitles[$category] ?? ucfirst($category),
                    'configured'        => (bool) ($saved && $saved->is_active),
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
}
