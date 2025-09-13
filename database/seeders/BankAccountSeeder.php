<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        for ($i=0; $i <= 10; $i++) {
            $data = [
                'bank_name' => 'BNI',
                'bank_code' => '009',
                'account_number' => $faker->creditCardNumber,
                'account_name' => $faker->name,
                'is_active' => 1,
            ];
    
            BankAccount::create($data);
        }
    }
}
