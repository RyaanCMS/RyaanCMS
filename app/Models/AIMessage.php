<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIMessage extends Model
{
    protected $table = 'ai_messages';

    protected $fillable = [
        'conversation_id', 'user_id', 'role', 'content',
        'generated_files', 'tokens_used', 'model', 'response_time_ms',
    ];

    protected $casts = [
        'generated_files' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(AIConversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
