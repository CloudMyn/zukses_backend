<?php

namespace App\Http\Controllers;

use App\Models\MasterSubdistrictPolygon;
use Illuminate\Http\Request;

class MasterSubdistrictPolygonController extends Controller
{
    public function check_coordinate(Request $request)
    {
        $lat = $request->lat;
        $long = $request->long;

        $polygons = MasterSubdistrictPolygon::select([
            'provinces.id as province_id',
            'provinces.name as province_name',
            'cities.id as city_id',
            'cities.name as city_name',
            'subdistricts.id as subdistrict_id',
            'subdistricts.name as subdistrict_name',
            'polygons.polygon'
        ])
        ->from('master_subdistrict_polygons as polygons')
        ->join('master_subdistricts as subdistricts', 'polygons.subdistrict_id', '=', 'subdistricts.id')
        ->join('master_cities as cities', 'subdistricts.city_id', '=', 'cities.id')
        ->join('master_provinces as provinces', 'cities.province_id', '=', 'provinces.id')
        ->whereRaw(
            'ST_Distance_Sphere(point(polygons.long, polygons.lat), point(?, ?)) < ?',
            [$long, $lat, 10000]
        )
        ->get();

        $detail_polygon = null;
        
        foreach ($polygons as $polygon) {
            $isInside = $this->utilityService->checkPolygon($polygon, $lat, $long);

            if ($isInside) {
                $detail_polygon = $polygon;
                break;
            }
        }

        if (!$detail_polygon) {
            return $this->utilityService->is404Response("Titik kordinat yang dimasukan salah atau diluar jangkauan indonesia");
        }

        return $this->utilityService->is200ResponseWithData("Detail lokasi ditemukan", $detail_polygon);
        
        // dd($detail_polygon);
        // return $this->utilityService->is404Response($this->utilityService->checkPolygon($lat, $long, $kode_kecamatan));
    }

    public function validate_coordinate(Request $request)
    {
        $lat = $request->lat;
        $long = $request->long;
        $subdistrict_id = $request->subdistrict_id;

        $dataPolygon = MasterSubdistrictPolygon::where('subdistrict_id', $subdistrict_id)->select('polygon')->first();

        $isInside = $this->utilityService->checkPolygon($dataPolygon, $lat, $long);

        if ($isInside) {
            $message = "Titik berada di dalam polygon";
            return $this->utilityService->is200Response($message);
        } else {
            $message = "Titik berada di luar polygon";
            return $this->utilityService->is404Response($message);
        }

    }
}
