<?php

namespace Database\Seeders;

use Database\Seeders\Chat\ChatDemoSeeder;
use Database\Seeders\Chat\ChatSeeder;
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
        // $this->call(BankSeeder::class);

        $this->call(UsersSeeder::class);
        // $this->call(MasterDistrictSeeder::class);
        $this->call(MasterStatusSeeder::class);
        $this->call(ProductCategorySeeder::class);
        $this->call(SellerSeeder::class);
        $this->call(ShopProfileSeeder::class);
        $this->call(ShippingSeeder::class);
        $this->call(BuyerSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call([
            // ShippingSeeder::class,
            ChatSeeder::class,
            ChatDemoSeeder::class
        ]);
    }
}
