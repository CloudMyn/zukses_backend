<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(ProductSeeder::class);   
        // $this->call(BankSeeder::class);
        $this->call(MasterDistrictSeeder::class);
        $this->call(MasterStatusSeeder::class);
        $this->call(ProductCategorySeeder::class);
        $this->call(SellerSeeder::class);
        $this->call(BuyerSeeder::class);
        $this->call([
            ShippingSeeder::class,
        ]);
    }
}
