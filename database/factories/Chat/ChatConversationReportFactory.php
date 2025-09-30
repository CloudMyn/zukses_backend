<?php

namespace Database\Factories\Chat;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat\ChatConversationReport;
use App\Models\Chat\Conversation;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat\ChatConversationReport>
 */
class ChatConversationReportFactory extends Factory
{
    protected $model = ChatConversationReport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'reporter_id' => User::factory(),
            'reason' => $this->faker->sentence(),
            'metadata' => null,
        ];
    }
}