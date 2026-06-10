<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class AIProvider extends Model
{
    protected $table = 'ai_providers';

    protected $fillable = [
        'user_id', 'provider', 'name', 'api_key', 'api_url',
        'default_model', 'settings', 'is_active', 'is_default', 'last_used_at',
    ];

    protected $hidden = ['api_key'];

    protected $casts = [
        'settings'     => 'array',
        'is_active'    => 'boolean',
        'is_default'   => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function keys(): HasMany
    {
        return $this->hasMany(AIProviderKey::class, 'ai_provider_id');
    }

    /** Active keys sorted for failover: primary first, then fewest failures. */
    public function activeKeysForFailover(): HasMany
    {
        return $this->hasMany(AIProviderKey::class, 'ai_provider_id')
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->orderBy('fail_count')
            ->orderBy('last_failed_at');
    }

    public function setApiKeyAttribute(string $value): void
    {
        $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDecryptedApiKey(): ?string
    {
        if (!$this->attributes['api_key']) return null;
        try {
            return Crypt::decryptString($this->attributes['api_key']);
        } catch (\Exception) {
            return null;
        }
    }

    public function getModels(): array
    {
        return config("ai.providers.{$this->provider}.models", []);
    }

    public function getConfigAttribute(): array
    {
        return config("ai.providers.{$this->provider}", []);
    }

    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
