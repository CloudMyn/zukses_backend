<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ShopProfile;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Seeder;


class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the first shop profile and category for seeding
        $shopProfile = ShopProfile::first();
        $category = ProductCategory::first();

        // If no shop profile or category exists, create them
        if (!$shopProfile) {
            // First create a user for the shop profile
            $user = User::factory()->create([
                'name' => 'Shop Owner',
                'email' => 'shop@example.com',
                'password' => bcrypt('password'),
                'role' => 'seller',
            ]);

            $shopProfile = ShopProfile::create([
                'user_id' => $user->id,
                'shop_name' => 'Seeder Shop',
                'full_name' => 'Shop Owner',
                'nik' => '1234567890123456',
                'ktp_url' => 'https://example.com/ktp.jpg',
                'selfie_url' => 'https://example.com/selfie.jpg',
                'description' => 'Seeder shop for testing',
            ]);
        }

        if (!$category) {
            $category = ProductCategory::create([
                'name' => 'Digital Products',
            ]);
        }

        $data = [
            'seller_id' => $shopProfile->id,
            'category_id' => $category->id,
            'name' => "UNDANGAN_ONLINE",
            'desc' => "Undangan Digital Online",
            'seller_id' => 1,
            'category_id' => 1,
            'sku' => "UND001",
            'price' => 100000,
            'stock' => 100,
            'min_purchase' => 1,
            'max_purchase' => 10,
            'is_used' => false,
            'status' => 1, // 1 for PUBLISHED status
            'is_cod_enabled' => true,
        ];

        Product::create($data);
    }
}
