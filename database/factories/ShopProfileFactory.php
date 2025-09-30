<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ShopProfile;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShopProfile>
 */
class ShopProfileFactory extends Factory
{
    protected $model = ShopProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'shop_name' => $this->faker->company(),
            'full_name' => $this->faker->name(),
            'nik' => $this->faker->numerify('################'),
            'ktp_url' => $this->faker->imageUrl(),
            'selfie_url' => $this->faker->imageUrl(),
            'description' => $this->faker->paragraph(),
            'logo_url' => $this->faker->imageUrl(),
        ];
    }
}