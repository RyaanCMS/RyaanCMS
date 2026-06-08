<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIConversation extends Model
{
    protected $table = 'ai_conversations';

    protected $fillable = [
        'user_id', 'project_id', 'title', 'provider',
        'model', 'total_tokens', 'message_count', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function messages()
    {
        return $this->hasMany(AIMessage::class, 'conversation_id')->orderBy('created_at');
    }

    public function latestMessage()
    {
        return $this->hasOne(AIMessage::class, 'conversation_id')->latest();
    }

    public function addMessage(string $role, string $content, array $meta = []): AIMessage
    {
        $message = $this->messages()->create([
            'user_id'      => $this->user_id,
            'role'         => $role,
            'content'      => $content,
            'model'        => $meta['model'] ?? $this->model,
            'tokens_used'  => $meta['tokens_used'] ?? 0,
            'response_time_ms' => $meta['response_time_ms'] ?? null,
            'generated_files'  => $meta['generated_files'] ?? null,
        ]);

        $this->increment('message_count');
        $this->increment('total_tokens', $meta['tokens_used'] ?? 0);

        if (!$this->title && $role === 'user') {
            $this->update(['title' => substr($content, 0, 80)]);
        }

        return $message;
    }

    public function getMessagesForAPI(): array
    {
        $rows = $this->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->get();

        $result = [];

        foreach ($rows as $m) {
            $content = $m->content ?? '';

            // Drop empty messages — Claude API rejects empty content strings
            if (trim($content) === '') {
                continue;
            }

            // Large assistant messages are full code-generation JSON (50k–100k chars).
            // Sending them back inflates context and can exceed the 200k-token limit.
            // Replace them with a compact summary so the AI knows what was already built.
            if ($m->role === 'assistant' && strlen($content) > 3000) {
                // Prefer the structured summary field
                if (preg_match('/"summary"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $content, $match)) {
                    $content = '[Code generated. Summary: ' . $match[1] . ']';
                } else {
                    // Strip JSON blocks and keep surrounding prose
                    $stripped = preg_replace('/```json[\s\S]*?```/i', '', $content);
                    $stripped = preg_replace('/\{[\s\S]*?"files"\s*:[\s\S]*/s', '', $stripped);
                    $stripped = trim($stripped);
                    $content  = $stripped ?: '[Code was generated and saved to project files]';
                    if (strlen($content) > 3000) {
                        $content = substr($content, 0, 3000) . '…';
                    }
                }
            }

            // Final guard — must be non-empty
            if (trim($content) === '') {
                $content = $m->role === 'assistant' ? '[Code was generated]' : '(previous request)';
            }

            $result[] = ['role' => $m->role, 'content' => $content];
        }

        // Remove consecutive same-role messages (keeps the latest one in each run).
        // These happen when a request errors after the user message was saved but
        // before an assistant reply was stored — two user turns in a row → 400.
        $deduped = [];
        foreach ($result as $msg) {
            if (!empty($deduped) && end($deduped)['role'] === $msg['role']) {
                array_pop($deduped); // drop the earlier duplicate
            }
            $deduped[] = $msg;
        }

        // Keep only the last 8 messages (4 turns) — enough for follow-up context
        // without ballooning the payload on long conversations
        return array_slice($deduped, -8);
    }
}
