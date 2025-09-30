<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ShopProfile;

class ConversationParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'shop_profile_id',
        'role',
        'joined_at',
        'left_at',
        'last_read_message_id',
        'last_read_at',
        'unread_count',
        'muted_until',
        'is_blocked',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'last_read_at' => 'datetime',
        'muted_until' => 'datetime',
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shopProfile()
    {
        return $this->belongsTo(ShopProfile::class);
    }

    public function lastReadMessage()
    {
        return $this->belongsTo(Message::class, 'last_read_message_id');
    }
}