<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Otp;
use App\Models\Token;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FullAddressController extends Controller
{
    public function listAddress(Request $request)
    {
        // Ambil query pencarian dan bersihkan
        $query = strtolower($request->get('search', ''));
        $limit = 5; // Batas data yang ditampilkan

        // 1. Mulai query dari tabel utama (postal_codes) dan gunakan Query Builder
        $addressQuery = DB::table('master_postal_codes as pc')
            // 2. Lakukan join ke tabel lain untuk mendapatkan nama
            ->join('master_subdistricts as sub', 'pc.subdistrict_id', '=', 'sub.id')
            ->join('master_cities as city', 'sub.city_id', '=', 'city.id')
            ->join('master_provinces as prov', 'city.province_id', '=', 'prov.id')
            // 3. Pilih kolom yang dibutuhkan dan berikan alias agar mudah dibaca
            ->select(
                'pc.id as postcode_id',
                'pc.code',
                'sub.id as district_id',
                'sub.name as district_name',
                'city.id as city_id',
                'city.name as city_name',
                'prov.id as province_id',
                'prov.name as province_name'
            );

        // 4. Terapkan filter pencarian dinamis JIKA query tidak kosong
        if (!empty($query)) {
            $addressQuery->where(function ($q) use ($query) {
                $q->where('pc.code', 'LIKE', "%{$query}%")
                    ->orWhere('sub.name', 'LIKE', "%{$query}%")
                    ->orWhere('city.name', 'LIKE', "%{$query}%")
                    ->orWhere('prov.name', 'LIKE', "%{$query}%");
            });
        }

        // 5. Ambil data dengan limitasi menggunakan paginate untuk skalabilitas
        $results = $addressQuery->paginate($limit);

        // 6. Jika tidak ada hasil, kembalikan response 404
        if ($results->isEmpty()) {
            return $this->utilityService->is404Response("Alamat tidak ditemukan");
        }

        // 7. Transformasi data sesuai format yang diinginkan
        $data = $results->map(function ($item) {
            return [
                'label' => sprintf(
                    '%s, %s, %s, %s',
                    strtoupper($item->province_name),
                    strtoupper($item->city_name),
                    strtoupper($item->district_name),
                    $item->code
                ),
                'code' => $item->code,
                'compilationID' => [
                    'province_id' => $item->province_id,
                    'city_id'     => $item->city_id,
                    'district_id' => $item->district_id,
                    'postcode_id' => $item->postcode_id,
                ]
            ];
        });

        // 8. Kembalikan response 200 dengan data yang sudah dipaginasi
        // Strukturnya akan mengikuti standar paginasi Laravel
        // return $this->utilityService->is200ResponseWithData("Alamat ditemukan", $results->setCollection($data));
        return $this->utilityService->is200ResponseWithData("Alamat ditemukan", $results->setCollection($data));
    }

    // public function getNearbyPlaces(Request $request)
    // {
    //     // Polygon dalam format GeoJSON [ [ [lng, lat], ... ] ]
    //     $polygon = [[
    //         [-3.331388014445281, 114.59626083337082],
    //         [-3.339816034382409, 114.60303031369813],
    //         [-3.3372287172314827, 114.61719890320683],
    //         [-3.3501991138979292, 114.62677107662398],
    //         [-3.368429495924431, 114.61594985370289],
    //         [-3.3667899760764612, 114.59572996266775],
    //         [-3.3821400040578737, 114.58615002090676],
    //         [-3.3735803431582667, 114.56662026781112],
    //         [-3.3667800961245007, 114.56987064521422],
    //         [-3.374026486033472, 114.54902077368979],
    //         [-3.363419451046809, 114.52228128615252],
    //         [-3.336407810671858, 114.54796933311832],
    //         [-3.337401119965648, 114.56033116215349],
    //         [-3.347437749152732, 114.56300503185685],
    //         [-3.3391823333979005, 114.57573682219663],
    //         [-3.3431030483669133, 114.58450366531201],
    //         [-3.3331696613507233, 114.58224924031026],
    //         [-3.331388014445281, 114.59626083337082],
    //     ]];

    //     foreach ($polygon as $ply) {
    //         dd($ply);
    //     }
    //     // $coordinates = $polygon[0];

    //     // // Hitung centroid
    //     // $latSum = 0;
    //     // $lngSum = 0;
    //     // $totalPoints = count($coordinates);

    //     // foreach ($coordinates as $point) {
    //     //     $lngSum += $point[1]; // lng
    //     //     $latSum += $point[0]; // lat
    //     // }

    //     // $lat = $latSum / $totalPoints;
    //     // $lng = $lngSum / $totalPoints;

    //     // $apiKey = env('GOOGLE_MAPS_API_KEY'); // simpan di .env
    //     // $radius = 1000; // dalam meter

    //     // $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location={$lat},{$lng}&radius={$radius}&key={$apiKey}";

    //     // $response = file_get_contents($url);
    //     // $data = json_decode($response, true);

    //     // if (!isset($data['results'])) {
    //     //     return response()->json(['error' => 'Gagal mengambil data dari Google'], 500);
    //     // }

    //     // $places = collect($data['results'])->map(function ($item) {
    //     //     return [
    //     //         'nama' => $item['name'] ?? '',
    //     //         'alamat' => $item['vicinity'] ?? '',
    //     //         'lokasi' => $item['geometry']['location'] ?? [],
    //     //     ];
    //     // });

    //     // return response()->json([
    //     //     'lokasi_centroid' => ['lat' => $lat, 'lng' => $lng],
    //     //     'jumlah' => count($places),
    //     //     'tempat_terdekat' => $places
    //     // ]);
    // }

    public function getNearbyPlaces(Request $request)
    {
        $subdistrictPolygons = DB::table('master_subdistrict_polygons')->where('subdistrict_id', $request->input('id_subdistrict'))->first();

        $polygonRaw = json_decode($subdistrictPolygons->polygon, true); // jadi array nested
        $polygon = $polygonRaw[0]; // ambil array dalamnya

        // $apiKey = env('GOOGLE_MAPS_API_KEY');
        // $radius = 1000; // meter
        // $allPlaces = collect();

        // foreach ($polygon as $point) {
        //     $lat = $point[0];
        //     $lng = $point[1];

        //     $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location={$lat},{$lng}&radius={$radius}&key={$apiKey}";

        //     $response = file_get_contents($url);
        //     $data = json_decode($response, true);

        //     if (!isset($data['results'])) {
        //         continue; // skip kalau gagal
        //     }

        //     $places = collect($data['results'])->map(function ($item) {
        //         return [
        //             'nama' => $item['name'] ?? '',
        //             'alamat' => $item['vicinity'] ?? '',
        //             'lokasi' => $item['geometry']['location'] ?? [],
        //         ];
        //     });

        //     $allPlaces = $allPlaces->merge($places);
        // }

        // // Hilangkan duplikat berdasarkan nama dan lokasi
        // $uniquePlaces = $allPlaces->unique(function ($item) {
        //     return $item['nama'] . '|' . ($item['lokasi']['lat'] ?? '') . '|' . ($item['lokasi']['lng'] ?? '');
        // })->values();

        // return response()->json([
        //     'jumlah_total_tempat' => $uniquePlaces->count(),
        //     'tempat_terdekat' => $uniquePlaces
        // ]);
    }
}
