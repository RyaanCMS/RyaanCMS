<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class AIProviderKey extends Model
{
    protected $table = 'ai_provider_keys';

    protected $fillable = [
        'ai_provider_id', 'user_id', 'label',
        'api_key', 'is_primary', 'is_active',
        'fail_count', 'last_failed_at', 'last_used_at',
    ];

    protected $hidden = ['api_key'];

    protected $casts = [
        'is_primary'     => 'boolean',
        'is_active'      => 'boolean',
        'fail_count'     => 'integer',
        'last_failed_at' => 'datetime',
        'last_used_at'   => 'datetime',
    ];

    public function aiProvider(): BelongsTo
    {
        return $this->belongsTo(AIProvider::class);
    }

    public function setApiKeyAttribute(string $value): void
    {
        $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDecryptedApiKey(): ?string
    {
        if (empty($this->attributes['api_key'])) return null;
        try {
            return Crypt::decryptString($this->attributes['api_key']);
        } catch (\Exception) {
            return null;
        }
    }

    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public function recordFailure(): void
    {
        $this->increment('fail_count');
        $this->update(['last_failed_at' => now()]);
    }

    public function resetFailures(): void
    {
        $this->update(['fail_count' => 0, 'last_failed_at' => null]);
    }
}
