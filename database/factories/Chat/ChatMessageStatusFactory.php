<?php

namespace Database\Factories\Chat;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat\ChatMessageStatus;
use App\Models\Chat\ChatMessage;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat\ChatMessageStatus>
 */
class ChatMessageStatusFactory extends Factory
{
    protected $model = ChatMessageStatus::class;

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
            'status' => $this->faker->randomElement(['sent', 'delivered', 'read', 'failed']),
            'status_at' => \Illuminate\Support\Facades\Date::now(),
            'device_info' => null,
        ];
    }
}