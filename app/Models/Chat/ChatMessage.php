<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ShopProfile;
use App\Models\Product;
use App\Models\Order;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';

    protected $fillable = [
        'conversation_id',
        'sender_user_id',
        'sender_shop_profile_id',
        'content',
        'content_type',
        'metadata',
        'parent_message_id',
        'reply_to_message_id',
        'edited_at',
        'is_deleted',
        'deleted_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'edited_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function senderShopProfile()
    {
        return $this->belongsTo(ShopProfile::class, 'sender_shop_profile_id');
    }

    public function parentMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'parent_message_id');
    }

    public function replyToMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_message_id');
    }

    public function attachments()
    {
        return $this->hasMany(ChatMessageAttachment::class, 'message_id');
    }

    public function statuses()
    {
        return $this->hasMany(ChatMessageStatus::class, 'message_id');
    }

    public function reactions()
    {
        return $this->hasMany(ChatMessageReaction::class, 'message_id');
    }

    public function edits()
    {
        return $this->hasMany(ChatMessageEdit::class, 'message_id');
    }

    public function productReferences()
    {
        return $this->hasMany(ChatProductReference::class, 'message_id');
    }

    public function orderReferences()
    {
        return $this->hasMany(ChatOrderReference::class, 'message_id');
    }
}