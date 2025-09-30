<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class ChatProductReference extends Model
{
    use HasFactory;

    protected $table = 'chat_product_references';

    protected $fillable = [
        'message_id',
        'product_id',
        'marketplace_product_id',
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}