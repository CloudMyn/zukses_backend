<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@special-moment.info',
            'password' => '$2y$10$j/L14t7Ac1pIrQ1U9xDwSe.cTvG3S.ejglOMBemMVJ4QyBp/pHfi6',
            'role' => 'admin',
            'whatsapp' => '081254130919',
        ]);
        DB::table('tokens')->insert([
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvdjEvYXV0aC9yZWdpc3RlciIsImlhdCI6MTcyNjI5NjU1MiwiZXhwIjoxNzI2OTAxMzUyLCJuYmYiOjE3MjYyOTY1NTIsImp0aSI6IndYN09MYWpHaGgzczltWVkiLCJzdWIiOiIyIiwicHJ2IjoiZjY0ZDQ4YTZjZWM3YmRmYTdmYmY4OTk0NTRiNDg4YjNlNDYyNTIwYSIsImlkIjoyLCJlbWFpbCI6ImFkbWluQGludml0YXRpb24uY29tIn0.qfLUwMv-gNCGIvGBZhEkh2E5tMHtbUESMMPqQTDoG38',
            'email' => 'admin@special-moment.info',
            'is_active' => 1
        ]);
    }
}
