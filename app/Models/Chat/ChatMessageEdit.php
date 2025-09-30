<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ChatMessageEdit extends Model
{
    use HasFactory;

    protected $table = 'chat_message_edits';

    protected $fillable = [
        'message_id',
        'editor_id',
        'previous_content',
        'edit_reason',
        'edited_at',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    // Relationships
    public function message()
    {
        return $this->belongsTo(ChatMessage::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}