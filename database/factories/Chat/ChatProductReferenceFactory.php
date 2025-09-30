<?php

namespace Database\Factories\Chat;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat\ChatProductReference;
use App\Models\Chat\ChatMessage;
use App\Models\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat\ChatProductReference>
 */
class ChatProductReferenceFactory extends Factory
{
    protected $model = ChatProductReference::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_id' => ChatMessage::factory(),
            'product_id' => Product::factory(),
            'marketplace_product_id' => null,
            'snapshot' => null,
        ];
    }
}