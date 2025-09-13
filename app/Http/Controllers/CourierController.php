<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\ShopProfile;
use App\Models\StoreShippingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourierController extends Controller
{
    /**
     * Mengambil daftar semua kurir aktif beserta layanannya.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Mengambil data kurir beserta relasi layanannya (Eager Loading)
        $couriers = Courier::with(['services' => function ($query) {
            $query->where('is_active', true);
        }])
            ->where('is_active', true)
            ->get();

        // Mengubah format data agar sesuai dengan yang dibutuhkan frontend
        $formattedCouriers = $couriers->map(function ($courier) {
            return [
                'id' => $courier->code, // Menggunakan 'code' sebagai 'id' di frontend
                'name' => $courier->name,
                'logo' => $courier->logo_url,
                'services' => $courier->services, // Mengambil array berisi nama layanan
            ];
        });
        if ($couriers) {
            $message = "Data Kurir ditemukan";
            return $this->utilityService->is200ResponseWithData($message, $formattedCouriers);
        }
    }
    public function list()
    {
        $userId = Auth::id();
        $seller = ShopProfile::where('user_id', $userId)->first();
        $shippingSetting = StoreShippingSetting::where('seller_id', $seller->id)->first();


        // Cek jika tidak ada setting
        if (!$shippingSetting || empty($shippingSetting->enabled_service_ids)) {
            return $this->utilityService->is404Response("Pengaturan layanan kurir tidak ditemukan.");
        }

        // Decode string array seperti: "[1,2,6,7]" → [1,2,6,7]
        $enabledServiceIds = is_array($shippingSetting->enabled_service_ids)
            ? $shippingSetting->enabled_service_ids
            : json_decode($shippingSetting->enabled_service_ids, true);


        // Ambil kurir aktif yang punya layanan aktif dan sesuai ID
        $couriers = Courier::where('is_active', true)
            ->whereHas('services', function ($query) use ($enabledServiceIds) {
                $query->where('is_active', true)
                    ->whereIn('id', $enabledServiceIds);
            })
            ->with(['services' => function ($query) use ($enabledServiceIds) {
                $query->where('is_active', true)
                    ->whereIn('id', $enabledServiceIds);
            }])
            ->get();

        // Format hasil
        $formattedCouriers = $couriers->map(function ($courier) {
            return [
                'id' => $courier->code,
                'name' => $courier->name,
                'logo' => $courier->logo_url,
                'services' => $courier->services,
            ];
        });

        // Return response
        if ($couriers->isNotEmpty()) {
            return $this->utilityService->is200ResponseWithData("Data Kurir ditemukan", $formattedCouriers);
        } else {
            return $this->utilityService->is404Response("Tidak ada kurir dengan layanan yang tersedia.");
        }
    }
    public function listSeller($seller_id)
    {
        $shippingSetting = StoreShippingSetting::where('seller_id', $seller_id)->first();


        // Cek jika tidak ada setting
        if (!$shippingSetting || empty($shippingSetting->enabled_service_ids)) {
            return $this->utilityService->is404Response("Pengaturan layanan kurir tidak ditemukan.");
        }

        // Decode string array seperti: "[1,2,6,7]" → [1,2,6,7]
        $enabledServiceIds = is_array($shippingSetting->enabled_service_ids)
            ? $shippingSetting->enabled_service_ids
            : json_decode($shippingSetting->enabled_service_ids, true);


        // Ambil kurir aktif yang punya layanan aktif dan sesuai ID
        $couriers = Courier::where('is_active', true)
            ->whereHas('services', function ($query) use ($enabledServiceIds) {
                $query->where('is_active', true)
                    ->whereIn('id', $enabledServiceIds);
            })
            ->with(['services' => function ($query) use ($enabledServiceIds) {
                $query->where('is_active', true)
                    ->whereIn('id', $enabledServiceIds);
            }])
            ->get();

        // Format hasil
        $formattedCouriers = $couriers->map(function ($courier) {
            return [
                'id' => $courier->code,
                'name' => $courier->name,
                'logo' => $courier->logo_url,
                'services' => $courier->services,
            ];
        });

        // Return response
        if ($couriers->isNotEmpty()) {
            return $this->utilityService->is200ResponseWithData("Data Kurir ditemukan", $formattedCouriers);
        } else {
            return $this->utilityService->is404Response("Tidak ada kurir dengan layanan yang tersedia.");
        }
    }
}
