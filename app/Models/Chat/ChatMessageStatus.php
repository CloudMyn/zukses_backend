<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ChatMessageStatus extends Model
{
    use HasFactory;

    protected $table = 'chat_message_statuses';

    protected $fillable = [
        'message_id',
        'user_id',
        'status',
        'status_at',
        'device_info',
    ];

    protected $casts = [
        'device_info' => 'array',
        'status_at' => 'datetime',
    ];

    // Relationships
    public function message()
    {
        return $this->belongsTo(ChatMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}