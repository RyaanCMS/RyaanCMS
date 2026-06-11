<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'username',
        'role', 'github_id', 'github_token', 'meta', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token', 'github_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'meta'              => 'array',
        'is_active'         => 'boolean',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function aiProviders()
    {
        return $this->hasMany(AIProvider::class);
    }

    public function defaultAIProvider()
    {
        return $this->hasOne(AIProvider::class)->where('is_default', true)->where('is_active', true);
    }

    public function conversations()
    {
        return $this->hasMany(AIConversation::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function marketplaceItems()
    {
        return $this->hasMany(MarketplaceItem::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getSetting(string $group, string $key, mixed $default = null): mixed
    {
        $setting = $this->settings()
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        if (!$setting) {
            // Fall back to global setting
            $setting = Setting::whereNull('user_id')
                ->where('group', $group)
                ->where('key', $key)
                ->first();
        }

        return $setting ? $setting->parsed_value : $default;
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/'.$this->avatar);
        }
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=6366f1&color=fff';
    }

    public function creditBalance()
    {
        return $this->hasOne(CreditBalance::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function aiUsageLogs()
    {
        return $this->hasMany(AiUsageLog::class);
    }
}
