<?php

namespace Database\Factories\Chat;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat\ChatMessageAttachment;
use App\Models\Chat\ChatMessage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat\ChatMessageAttachment>
 */
class ChatMessageAttachmentFactory extends Factory
{
    protected $model = ChatMessageAttachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_id' => ChatMessage::factory(),
            'type' => $this->faker->randomElement(['image', 'video', 'audio', 'file', 'sticker']),
            'url' => $this->faker->imageUrl(),
            'filename' => $this->faker->word() . '.jpg',
            'content_type' => 'image/jpeg',
            'size_bytes' => $this->faker->numberBetween(1000, 5000000),
            'metadata' => null,
        ];
    }
}