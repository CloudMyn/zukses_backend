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
            'desc' => "Undangan Digital Online",
            'seller_id' => 1,
            'category_id' => 1,
            'sku' => "UND001",
            'price' => 100000,
            'stock' => 100,
            'min_purchase' => 1,
            'max_purchase' => 10,
            'is_used' => false,
        ];

        Product::create($data);
    }
}
