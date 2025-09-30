<?php

namespace Database\Factories\Chat;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat\Conversation;
use App\Models\User;
use App\Models\ShopProfile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat\Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['private', 'group', 'order_support', 'product_support', 'system']),
            'title' => $this->faker->sentence(),
            'owner_user_id' => User::factory(),
            'owner_shop_profile_id' => null, // Nullable field
            'metadata' => null,
            'last_message_id' => null,
            'last_message_at' => null,
            'is_open' => true,
        ];
    }
}