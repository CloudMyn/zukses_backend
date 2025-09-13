<?php

namespace Database\Seeders;

use App\Models\Product;
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

        $data = [
            'name' => "UNDANGAN_ONLINE",
            'image' => "",
            'type' => "DIGITAL",
            'url' => "",
            'is_active' => 1,
        ];

        Product::create($data);
    }
}
