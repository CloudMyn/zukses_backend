<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('banks')->insert([
            'bank_code' => '009',
            'bank_name' => 'BNI',
        ]);

        DB::table('banks')->insert([
            'bank_code' => '014',
            'bank_name' => 'BCA',
        ]);

        DB::table('banks')->insert([
            'bank_code' => '008',
            'bank_name' => 'Mandiri',
        ]);
        
        DB::table('banks')->insert([
            'bank_code' => '002',
            'bank_name' => 'BRI',
        ]);
        
        DB::table('banks')->insert([
            'bank_code' => '200',
            'bank_name' => 'BTN',
        ]);

        DB::table('banks')->insert([
            'bank_code' => '011',
            'bank_name' => 'Danamon',
        ]);

        DB::table('banks')->insert([
            'bank_code' => '426',
            'bank_name' => 'Mega',
        ]);

        DB::table('banks')->insert([
            'bank_code' => '022',
            'bank_name' => 'CIMB Niaga',
        ]);

        DB::table('banks')->insert([
            'bank_code' => '022',
            'bank_name' => 'CIMB Niaga Syariah',
        ]);
    }
}
