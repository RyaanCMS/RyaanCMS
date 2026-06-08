<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['user_id', 'group', 'key', 'value', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getParsedValueAttribute(): mixed
    {
        return match($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'json'    => json_decode($this->value, true),
            default   => $this->value,
        };
    }

    public static function get(string $key, mixed $default = null, ?int $userId = null): mixed
    {
        [$group, $settingKey] = explode('.', $key, 2) + [null, $key];

        $query = static::where('group', $group)->where('key', $settingKey);

        if ($userId) {
            $setting = (clone $query)->where('user_id', $userId)->first()
                ?? $query->whereNull('user_id')->first();
        } else {
            $setting = $query->whereNull('user_id')->first();
        }

        return $setting ? $setting->parsed_value : $default;
    }

    public static function set(string $key, mixed $value, string $type = 'string', ?int $userId = null): void
    {
        [$group, $settingKey] = explode('.', $key, 2) + [null, $key];

        static::updateOrCreate(
            ['user_id' => $userId, 'group' => $group, 'key' => $settingKey],
            ['value' => is_array($value) ? json_encode($value) : (string) $value, 'type' => $type]
        );
    }
}
