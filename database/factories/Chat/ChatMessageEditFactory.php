<?php

namespace Database\Factories\Chat;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat\ChatMessageEdit;
use App\Models\Chat\ChatMessage;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat\ChatMessageEdit>
 */
class ChatMessageEditFactory extends Factory
{
    protected $model = ChatMessageEdit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_id' => ChatMessage::factory(),
            'editor_id' => User::factory(),
            'previous_content' => $this->faker->sentence(),
            'edit_reason' => $this->faker->sentence(3),
            'edited_at' => \Illuminate\Support\Facades\Date::now(),
        ];
    }
}