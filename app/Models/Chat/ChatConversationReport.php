<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ShopProfile;

class ChatConversationReport extends Model
{
    use HasFactory;

    protected $table = 'chat_conversation_reports';

    protected $fillable = [
        'conversation_id',
        'reporter_id',
        'reason',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}