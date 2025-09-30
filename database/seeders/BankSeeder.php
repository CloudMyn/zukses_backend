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
            'name_bank' => 'BNI',
        ]);

        DB::table('banks')->insert([
            'name_bank' => 'BCA',
        ]);

        DB::table('banks')->insert([
            'name_bank' => 'Mandiri',
        ]);

        DB::table('banks')->insert([
            'name_bank' => 'BRI',
        ]);

        DB::table('banks')->insert([
            'name_bank' => 'BTN',
        ]);

        DB::table('banks')->insert([
            'name_bank' => 'Danamon',
        ]);

        DB::table('banks')->insert([
            'name_bank' => 'Mega',
        ]);

        DB::table('banks')->insert([
            'name_bank' => 'CIMB Niaga',
        ]);

        DB::table('banks')->insert([
            'name_bank' => 'CIMB Niaga Syariah',
        ]);
    }
}
