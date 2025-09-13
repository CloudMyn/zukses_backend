<?php

namespace Database\Seeders;

use App\Models\MasterStatus;
use Illuminate\Database\Seeder;

class MasterStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            [
                'name' => 'Belum Bayar',
                'desc' => 'Transaksi belum dibayarkan',
            ],
            [
                'name' => 'Sedang Dikemas',
                'desc' => 'Barang sedang dikemas',
            ],
            [
                'name' => 'Dikirim',
                'desc' => 'Barang sudah dikirim',
            ],
            [
                'name' => 'Selesai',
                'desc' => 'Barang selesai dikirim',
            ],
            [
                'name' => 'Dibatalkan',
                'desc' => 'Transaksi dibatalkan',
            ],
            [
                'name' => 'Pengembalian Barang',
                'desc' => 'Pembeli melakukan pengembalian barang',
            ],
        ];

        foreach ($statuses as $status) {
            MasterStatus::create($status);
        }
    }
}
