<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\ShopAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// Asumsikan Anda memiliki UtilityService yang di-inject
// use App\Services\UtilityService; 

class ShopAddressController extends Controller
{
    public function index(Request $request, $seller_id)
    {
        $data = ShopAddress::join('master_provinces', 'master_provinces.id', '=', 'shop_addresses.province_id')
            ->join('master_cities', 'master_cities.id', '=', 'shop_addresses.citie_id')
            ->join('master_subdistricts', 'master_subdistricts.id', '=', 'shop_addresses.subdistrict_id')
            ->join('master_postal_codes', 'master_postal_codes.id', '=', 'shop_addresses.postal_code_id')
            ->where('seller_id', $seller_id)
            ->select(
                'master_provinces.name as provinces',
                'master_cities.name as cities',
                'master_subdistricts.name as subdistricts',
                'master_postal_codes.code as postal_codes',
                'shop_addresses.*'
            )->orderBy('is_primary', 'DESC')->get();
        if (!$data) {
            return $this->utilityService->is404Response('Data Users tidak ditemukan');
        }
        return $this->utilityService->is200ResponseWithData('Data Users ditemukan', $data);
    }
    public function create(Request $request, $seller_id)
    {
        if ($request->is_primary == 1) {
            $shopAddress = ShopAddress::where('is_primary', 1)
                ->where('seller_id', $seller_id)
                ->first();

            if ($shopAddress) {
                $shopAddress->is_primary = 0;
                $shopAddress->save();
            }
        }

        $input = $request->full_location;

        // Pisahkan berdasarkan koma
        $parts = explode(',', $input);

        // Ambil bagian kecamatan (asumsi urutan ke-3) dan kode pos (terakhir)
        $kecamatanRaw = isset($parts[2]) ? trim($parts[2]) : '';
        $kodePosInput = isset($parts[3]) ? trim($parts[3]) : '';

        // Hilangkan kata "KECAMATAN" jika ada, dan kecilkan hurufnya
        $kecamatan = strtolower(str_replace('KECAMATAN ', '', $kecamatanRaw));

        $dataKecamatan = DB::table('master_subdistricts')
            ->where('name', 'LIKE', '' . $kecamatan . '')
            ->first();


        if (!$dataKecamatan) {
            return $this->utilityService->is422Response('Kecamatan tidak ditemukan');
        }

        // Cari kode pos berdasarkan subdistrict_id
        $kodePos = DB::table('master_postal_codes')
            ->where('subdistrict_id', $dataKecamatan->id)
            ->where('code', $kodePosInput) // pastikan cocok juga dengan input kode pos
            ->first();

        if (!$kodePos) {
            return $this->utilityService->is422Response('Kode pos tidak ditemukan');
        }

        // Cari kota
        $kota = DB::table('master_cities')
            ->where('id', $dataKecamatan->city_id)
            ->first();

        if (!$kota) {
            return response()->json(['error' => 'Kota tidak ditemukan'], 404);
        }

        if (!$kota) {
            return $this->utilityService->is422Response('Kota tidak ditemukan');
        }

        // Cari provinsi
        $prov = DB::table('master_provinces')
            ->where('id', $kota->province_id)
            ->first();

        if (!$prov) {
            return $this->utilityService->is422Response('Provinsi tidak ditemukan');
        }
        $data = [
            'seller_id' => $seller_id,
            'name_shop' => $request->name_shop,
            'number_shop' => $request->number_shop,
            'province_id' => $prov->id,
            'citie_id' => $kota->id,
            'subdistrict_id' => $dataKecamatan->id,
            'postal_code_id' => $kodePos->id,
            'full_address' => $request->full_address,
            'detail_address' => $request->detail_address,
            'lat' => $request->lat,
            'long' => $request->long,
            'is_primary' => $request->is_primary,
        ];
        $insert = ShopAddress::create($data);
        $data = $insert->fresh();
        if ($insert) {
            $success_message = "Data Berhasil Ditambahkan";
            return $this->utilityService->is200ResponseWithData($success_message, $data);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
    public function update(Request $request, $id)
    {
        // Pastikan ada id alamat yang akan diupdate
        $address = ShopAddress::find($id);

        if (!$address) {
            return $this->utilityService->is404Response("Alamat tidak ditemukan");
        }

        if ($request->is_primary == 1) {
            // Reset semua alamat primary milik user ini
            if ($address->is_primary != 1) {
                ShopAddress::where('seller_id', $address->seller_id)
                    ->where('is_primary', 1)
                    ->update(['is_primary' => 0]);
            }

            $address->is_primary = 1;
        } else {
            $address->is_primary = 0;
        }
        $input = $request->full_location;

        // Pisahkan berdasarkan koma
        $parts = explode(',', $input);

        // Ambil bagian kecamatan (asumsi urutan ke-3) dan kode pos (terakhir)
        $kecamatanRaw = isset($parts[2]) ? trim($parts[2]) : '';
        $kodePosInput = isset($parts[3]) ? trim($parts[3]) : '';

        // Hilangkan kata "KECAMATAN" jika ada, dan kecilkan hurufnya
        $kecamatan = strtolower(str_replace('KECAMATAN ', '', $kecamatanRaw));

        $dataKecamatan = DB::table('master_subdistricts')
            ->where('name', 'LIKE', '' . $kecamatan . '')
            ->first();


        if (!$dataKecamatan) {
            return response()->json(['error' => 'Kecamatan tidak ditemukan'], 404);
        }

        // Cari kode pos berdasarkan subdistrict_id
        $kodePos = DB::table('master_postal_codes')
            ->where('subdistrict_id', $dataKecamatan->id)
            ->where('code', $kodePosInput) // pastikan cocok juga dengan input kode pos
            ->first();

        if (!$kodePos) {
            return response()->json(['error' => 'Kode pos tidak ditemukan'], 404);
        }

        // Cari kota
        $kota = DB::table('master_cities')
            ->where('id', $dataKecamatan->city_id)
            ->first();

        if (!$kota) {
            return response()->json(['error' => 'Kota tidak ditemukan'], 404);
        }

        // Cari provinsi
        $prov = DB::table('master_provinces')
            ->where('id', $kota->province_id)
            ->first();

        if (!$prov) {
            return response()->json(['error' => 'Provinsi tidak ditemukan'], 404);
        }

        $address->name_shop = $request->name_shop;
        $address->number_shop = $request->number_shop;
        $address->province_id = $prov->id;
        $address->citie_id = $kota->id;
        $address->subdistrict_id = $dataKecamatan->id;
        $address->postal_code_id = $kodePos->id;
        $address->full_address = $request->full_address;
        $address->lat = $request->lat;
        $address->long = $request->long;


        if ($address->save()) {
            $success_message = "Data Berhasil Diupdate";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("Terjadi kesalahan saat memperbarui data");
        }
    }
    public function isPrimary(Request $request, $id)
    {
        // Pastikan ada id alamat yang akan diupdate
        $address = ShopAddress::find($id);

        if (!$address) {
            return $this->utilityService->is404Response("Alamat tidak ditemukan");
        }

        $ShopAddress = DB::table('shop_addresses')->where('is_primary', 1)->first();
        if ($ShopAddress) {
            $ud = ShopAddress::find($ShopAddress->id);
            $ud->is_primary = 0;
            $ud->save();
        }


        // Data yang akan diupdate
        $data = [
            'is_primary' => 1,
        ];

        // Lakukan update
        $updated = $address->update($data);

        // Ambil data terbaru jika update berhasil
        $data = $address->fresh();

        if ($updated) {
            $success_message = "Data Berhasil Diupdate";
            return $this->utilityService->is200ResponseWithData($success_message, $data);
        } else {
            return $this->utilityService->is500Response("Terjadi kesalahan saat memperbarui data");
        }
    }
    public function destroy($id)
    {
        $menu = ShopAddress::find($id);
        $menu->delete();
        $success_message = "Data Berhasil Dihapus";
        return $this->utilityService->is200Response($success_message);
    }
}
