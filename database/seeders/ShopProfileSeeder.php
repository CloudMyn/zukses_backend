<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('shop_profiles')->insert([
            'user_id' => 2, // admin user id
            'shop_name' => 'admin',
            'description' => 'Admin Shop',
            'logo_url' => null,
            'full_name' => 'admin',
            'nik' => '1234567890123456',
            'ktp_url' => null,
            'selfie_url' => null,
        ]);
    }
}