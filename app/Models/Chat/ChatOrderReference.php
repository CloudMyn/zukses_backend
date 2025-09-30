<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class ChatOrderReference extends Model
{
    use HasFactory;

    protected $table = 'chat_order_references';

    protected $fillable = [
        'message_id',
        'order_id',
        'marketplace_order_id',
        'snapshot',
    ];

    protected $casts = [
        'snapshot' => 'array',
    ];

    // Relationships
    public function message()
    {
        return $this->belongsTo(ChatMessage::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}