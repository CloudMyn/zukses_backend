<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ShopProfile;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'owner_user_id',
        'owner_shop_profile_id',
        'metadata',
        'last_message_id',
        'last_message_at',
        'is_open',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_message_at' => 'datetime',
    ];

    // Relationships
    public function ownerUser()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function ownerShopProfile()
    {
        return $this->belongsTo(ShopProfile::class, 'owner_shop_profile_id');
    }

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function lastMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'last_message_id');
    }

    public function reports()
    {
        return $this->hasMany(ChatConversationReport::class);
    }
}