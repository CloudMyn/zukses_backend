<?php

namespace Database\Factories\Chat;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat\ConversationParticipant;
use App\Models\Chat\Conversation;
use App\Models\User;
use App\Models\ShopProfile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat\ConversationParticipant>
 */
class ConversationParticipantFactory extends Factory
{
    protected $model = ConversationParticipant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'user_id' => User::factory(),
            'shop_profile_id' => null, // We'll set this conditionally or leave as null
            'role' => $this->faker->randomElement(['participant', 'admin', 'agent', 'owner']),
            'joined_at' => \Illuminate\Support\Facades\Date::now(),
            'left_at' => null,
            'last_read_message_id' => null,
            'last_read_at' => null,
            'unread_count' => 0,
            'muted_until' => null,
            'is_blocked' => false,
            'preferences' => null,
        ];
    }
}