<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Courier;
use Illuminate\Support\Str;

class ShippingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $couriers = [
            'gosend' => [
                'name' => 'Gosend', 
                'logo_url' => '/image/gosend 1.png', 
                'services' => ['Gosend Sameday', 'Gosend Instant']
            ],
            'jnt' => [
                'name' => 'J&T', 
                'logo_url' => '/image/gosend 2.png', 
                'services' => ['J&T NextDay', 'J&T SameDay', 'J&T Regular']
            ],
            'jne' => [
                'name' => 'JNE', 
                'logo_url' => '/image/gosend 3.png', 
                'services' => ['JNE Trucking', 'JNE Regular', 'JNE Yes', 'JNE Oke']
            ],
            'sicepat' => [
                'name' => 'Si Cepat Logistik', 
                'logo_url' => '/image/gosend 4.png', 
                'services' => ['SiCepat BEST', 'SiCepat GOKIL', 'SiCepat SIUNTUNG']
            ],
            'anteraja' => [
                'name' => 'Anteraja', 
                'logo_url' => '/image/gosend 5.png', 
                'services' => ['Anteraja Regular', 'Anteraja Next Day', 'Anteraja Same Day']
            ],
            'pos' => [
                'name' => 'POS Indonesia', 
                'logo_url' => '/image/gosend 6.png', 
                'services' => ['POS Regular', 'POS Express']
            ],
            'paxel' => [
                'name' => 'Paxel', 
                'logo_url' => '/image/gosend 7.png', 
                'services' => ['Paxel Sameday', 'Paxel Big', 'Paxel Instant']
            ],
        ];

        foreach ($couriers as $code => $data) {
            // Membuat entri untuk perusahaan kurir
            $courier = Courier::create([
                'code' => $code,
                'name' => $data['name'],
                'logo_url' => $data['logo_url'],
            ]);

            // Membuat entri untuk setiap layanan dari kurir tersebut
            foreach ($data['services'] as $serviceName) {
                // Membuat kode layanan yang sederhana (cth: "Gosend Sameday" -> "SAMEDAY")
                $serviceCode = Str::upper(last(explode(' ', $serviceName)));
                
                $courier->services()->create([
                    'code' => $serviceCode,
                    'name' => $serviceName,
                ]);
            }
        }
    }
}
