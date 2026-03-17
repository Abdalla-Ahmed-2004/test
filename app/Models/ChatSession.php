<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'messages',
    ];

    protected $casts = [
        'messages' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Append a new message to the session.
     */
    public function appendMessage(string $role, string $content): void
    {
        $messages = $this->messages ?? [];
        $messages[] = [
            'role' => $role,
            'content' => $content,
            'at' => now()->toISOString(),
        ];
        $this->update(['messages' => $messages]);
    }
}
