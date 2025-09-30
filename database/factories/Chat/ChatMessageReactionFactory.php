<?php

namespace Database\Factories\Chat;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat\ChatMessageReaction;
use App\Models\Chat\ChatMessage;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat\ChatMessageReaction>
 */
class ChatMessageReactionFactory extends Factory
{
    protected $model = ChatMessageReaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_id' => ChatMessage::factory(),
            'user_id' => User::factory(),
            'reaction' => $this->faker->randomElement(['👍', '❤️', '😂', '😮', '😢', '👏']),
            'reacted_at' => \Illuminate\Support\Facades\Date::now(),
        ];
    }
}