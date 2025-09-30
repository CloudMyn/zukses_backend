<?php

namespace Database\Factories\Chat;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat\ChatOrderReference;
use App\Models\Chat\ChatMessage;
use App\Models\Order;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat\ChatOrderReference>
 */
class ChatOrderReferenceFactory extends Factory
{
    protected $model = ChatOrderReference::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_id' => ChatMessage::factory(),
            'order_id' => Order::factory(),
            'marketplace_order_id' => null,
            'snapshot' => null,
        ];
    }
}