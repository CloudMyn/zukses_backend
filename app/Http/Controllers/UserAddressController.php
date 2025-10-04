<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// Asumsikan Anda memiliki UtilityService yang di-inject
// use App\Services\UtilityService; 

class UserAddressController extends Controller
{
    public function index(Request $request, $user_id)
    {
        $data = UserAddress::join('master_provinces', 'master_provinces.id', '=', 'user_addresses.province_id')
            ->join('master_cities', 'master_cities.id', '=', 'user_addresses.citie_id')
            ->join('master_subdistricts', 'master_subdistricts.id', '=', 'user_addresses.subdistrict_id')
            ->join('master_postal_codes', 'master_postal_codes.id', '=', 'user_addresses.postal_code_id')
            ->where('user_id', $user_id)
            ->select(
                'master_provinces.name as provinces',
                'master_cities.name as cities',
                'master_subdistricts.name as subdistricts',
                'master_postal_codes.code as postal_codes',
                'user_addresses.*'
            )->orderBy('is_primary', 'DESC')->get();
        if (!$data) {
            return $this->utilityService->is404Response('Data Users tidak ditemukan');
        }
        return $this->utilityService->is200ResponseWithData('Data Users ditemukan', $data);
    }
    public function create(Request $request, $user_id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name_receiver' => 'required|string|max:255',
            'number_receiver' => 'required|string|max:20',
            'province_name' => 'required|string|max:255',
            'city_name' => 'required|string|max:255',
            'subdistrict_name' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'full_address' => 'required|string',
            'detail_address' => 'nullable|string',
            'lat' => 'required|numeric|between:-90,90',
            'long' => 'required|numeric|between:-180,180',
            'is_primary' => 'required|boolean',
            'is_store' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->utilityService->is422Response($validator->errors()->first());
        }

        // Set alamat primary jika diinginkan
        if ($request->is_primary == 1) {
            $userAddress = DB::table('user_addresses')->where('is_primary', 1)->where('user_id', $user_id)->first();
            if ($userAddress) {
                $ud = UserAddress::find($userAddress->id);
                $ud->is_primary = 0;
                $ud->save();
            }
        }

        // Cari provinsi berdasarkan nama
        $province = DB::table('master_provinces')
            ->where('name', 'LIKE', '%' . trim($request->province_name) . '%')
            ->first();
        if (!$province) {
            return $this->utilityService->is422Response('Provinsi tidak ditemukan: ' . $request->province_name);
        }

        // Cari kota berdasarkan nama dan provinsi
        $city = DB::table('master_cities')
            ->where('name', 'LIKE', '%' . trim($request->city_name) . '%')
            ->where('province_id', $province->id)
            ->first();
        if (!$city) {
            return $this->utilityService->is422Response('Kota tidak ditemukan: ' . $request->city_name);
        }

        // Cari subdistrict berdasarkan nama dan kota
        $subdistrict = DB::table('master_subdistricts')
            ->where('name', 'LIKE', '%' . trim($request->subdistrict_name) . '%')
            ->where('city_id', $city->id)
            ->first();
        if (!$subdistrict) {
            return $this->utilityService->is422Response('Kecamatan tidak ditemukan: ' . $request->subdistrict_name);
        }

        // Cari kode pos berdasarkan kode dan subdistrict
        $postalCode = DB::table('master_postal_codes')
            ->where('code', trim($request->postal_code))
            ->where('subdistrict_id', $subdistrict->id)
            ->first();
        if (!$postalCode) {
            return $this->utilityService->is422Response('Kode pos tidak ditemukan: ' . $request->postal_code);
        }

        $data = [
            'user_id' => $user_id,
            'name_receiver' => $request->name_receiver,
            'number_receiver' => $request->number_receiver,
            'province_id' => $province->id,
            'citie_id' => $city->id,
            'subdistrict_id' => $subdistrict->id,
            'postal_code_id' => $postalCode->id,
            'full_address' => $request->full_address,
            'detail_address' => $request->detail_address,
            'lat' => $request->lat,
            'long' => $request->long,
            'is_primary' => $request->is_primary,
            'is_store' => $request->is_store,
        ];

        $insert = UserAddress::create($data);
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
        $address = UserAddress::find($id);

        if (!$address) {
            return $this->utilityService->is404Response("Alamat tidak ditemukan");
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'name_receiver' => 'required|string|max:255',
            'number_receiver' => 'required|string|max:20',
            'province_name' => 'required|string|max:255',
            'city_name' => 'required|string|max:255',
            'subdistrict_name' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'full_address' => 'required|string',
            'detail_address' => 'nullable|string',
            'lat' => 'required|numeric|between:-90,90',
            'long' => 'required|numeric|between:-180,180',
            'is_primary' => 'required|boolean',
            'is_store' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->utilityService->is422Response($validator->errors()->first());
        }

        // Set alamat primary jika diinginkan
        if ($request->is_primary == 1) {
            // Reset semua alamat primary milik user ini
            if ($address->is_primary != 1) {
                UserAddress::where('user_id', $address->user_id)
                    ->where('is_primary', 1)
                    ->update(['is_primary' => 0]);
            }
            $address->is_primary = 1;
        } else {
            $address->is_primary = 0;
        }

        // Cari provinsi berdasarkan nama
        $province = DB::table('master_provinces')
            ->where('name', 'LIKE', '%' . trim($request->province_name) . '%')
            ->first();
        if (!$province) {
            return $this->utilityService->is422Response('Provinsi tidak ditemukan: ' . $request->province_name);
        }

        // Cari kota berdasarkan nama dan provinsi
        $city = DB::table('master_cities')
            ->where('name', 'LIKE', '%' . trim($request->city_name) . '%')
            ->where('province_id', $province->id)
            ->first();
        if (!$city) {
            return $this->utilityService->is422Response('Kota tidak ditemukan: ' . $request->city_name);
        }

        // Cari subdistrict berdasarkan nama dan kota
        $subdistrict = DB::table('master_subdistricts')
            ->where('name', 'LIKE', '%' . trim($request->subdistrict_name) . '%')
            ->where('city_id', $city->id)
            ->first();
        if (!$subdistrict) {
            return $this->utilityService->is422Response('Kecamatan tidak ditemukan: ' . $request->subdistrict_name);
        }

        // Cari kode pos berdasarkan kode dan subdistrict
        $postalCode = DB::table('master_postal_codes')
            ->where('code', trim($request->postal_code))
            ->where('subdistrict_id', $subdistrict->id)
            ->first();
        if (!$postalCode) {
            return $this->utilityService->is422Response('Kode pos tidak ditemukan: ' . $request->postal_code);
        }

        // Update data alamat
        $address->name_receiver = $request->name_receiver;
        $address->number_receiver = $request->number_receiver;
        $address->province_id = $province->id;
        $address->citie_id = $city->id;
        $address->subdistrict_id = $subdistrict->id;
        $address->postal_code_id = $postalCode->id;
        $address->full_address = $request->full_address;
        $address->detail_address = $request->detail_address;
        $address->lat = $request->lat;
        $address->long = $request->long;
        $address->is_store = $request->is_store;

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
        $address = UserAddress::find($id);

        if (!$address) {
            return $this->utilityService->is404Response("Alamat tidak ditemukan");
        }

        $userAddress = DB::table('user_addresses')->where('is_primary', 1)->first();
        if ($userAddress) {
            $ud = UserAddress::find($userAddress->id);
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
        $menu = UserAddress::find($id);
        $menu->delete();
        $success_message = "Data Berhasil Dihapus";
        return $this->utilityService->is200Response($success_message);
    }
}
