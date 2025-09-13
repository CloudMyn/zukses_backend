<?php

namespace Database\Seeders;

use App\Models\MasterCity;
use App\Models\MasterPostalCode;
use App\Models\MasterProvince;
use App\Models\MasterSubdistrict;
use App\Models\MasterSubdistrictPolygon;
use Illuminate\Database\Seeder;

class MasterDistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $districts = include storage_path('app/data/districts.php');
        $district_polygons = include storage_path('app/data/subdistrict_polygons.php');
        $postal_codes = include storage_path('app/data/postal_codes.php');

        $province_id = [];
        $city_id = [];
        $subdistrict_id = [];
        $subdistrict_name = [];
        $subdistrict_id_by_code_polygons = [];

        foreach ($districts as $district) {
            $code = $district[0];
            $name = $district[1];

            $data = [
                'name' => $name,
            ];
            
            $dot_count = substr_count($code, '.');

            if ($dot_count === 0) {
                $id = MasterProvince::create($data)->id;
                $province_id[$code] = $id;
            } else if ($dot_count === 1) {
                $province_code = substr($code, 0, 2);
                $data['province_id'] = $province_id[$province_code];
                $id = MasterCity::create($data)->id;
                $city_id[$code] = $id;
            } else {
                $city_code = substr($code, 0, 5);
                $data['city_id'] = $city_id[$city_code];
                $id = MasterSubdistrict::create($data)->id;
                $subdistrict_id[$code] = $id;
                $subdistrict_name[$name] = $id;
            }
        }

        foreach ($district_polygons as $district_polygon) {
            $code = $district_polygon[0];
            $name = $district_polygon[1];
            $lat = $district_polygon[2];
            $long = $district_polygon[3];
            $polygon = $district_polygon[4];
            $data = [
                'subdistrict_id' => isset($subdistrict_id[$code]) ? $subdistrict_id[$code] : $subdistrict_name[$name],
                'lat' => $lat,
                'long' => $long,
                'polygon' => $polygon,
            ];

            $subdistrict_id_after_save = MasterSubdistrictPolygon::create($data)->subdistrict_id;

            $subdistrict_id_by_code_polygons[$code] = $subdistrict_id_after_save;
        }

        $newData = [];
        $seen = [];

        foreach ($postal_codes as $postal_code) {
            $prefix = substr($postal_code[0], 0, 8); // Ambil 8 karakter pertama
            $value = $postal_code[1];
            $key = $prefix . '|' . $value; // Gabungkan jadi key unik

            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $newData[] = [$prefix, $value];
            }
        }

        foreach ($newData as $val) {
            $data = [
                'subdistrict_id' => isset($subdistrict_id[$val[0]]) ? $subdistrict_id[$val[0]] : $subdistrict_id_by_code_polygons[$val[0]],
                'code' => $val[1],
            ];

            MasterPostalCode::create($data);
        }
    }
}
