<?php

namespace Database\Factories\Chat;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\Conversation;
use App\Models\User;
use App\Models\ShopProfile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat\ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    protected $model = ChatMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_user_id' => User::factory(),
            'sender_shop_profile_id' => null, // We'll set this conditionally or leave as null
            'content' => $this->faker->sentence(),
            'content_type' => $this->faker->randomElement(['text', 'system', 'template', 'product_card', 'order_card']),
            'metadata' => null,
            'parent_message_id' => null,
            'reply_to_message_id' => null,
            'edited_at' => null,
            'is_deleted' => false,
            'deleted_at' => null,
        ];
    }
}