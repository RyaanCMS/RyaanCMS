<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbPromptCache extends Model
{
    protected $table = 'kb_prompt_cache';

    protected $fillable = [
        'hash',
        'prompt_summary',
        'provider',
        'response',
        'tokens_saved',
        'hit_count',
        'expires_at',
    ];

    protected $casts = [
        'tokens_saved' => 'integer',
        'hit_count'    => 'integer',
        'expires_at'   => 'datetime',
    ];
}
