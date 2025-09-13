<?php

namespace App\Http\Controllers;

use App\Models\CourierService;
use App\Models\ShopProfile;
use App\Models\StoreShippingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StoreShippingSettingController extends Controller
{
    /**
     * Get the shop profile for the authenticated user.
     */

    public function getSettings(Request $request)
    {
        $userId = Auth::id();
        $seller = ShopProfile::where('user_id', $userId)->first();

        if (!$seller) {
            return response()->json(['message' => 'Seller profile not found.'], 404);
        }
        $settings = StoreShippingSetting::where('seller_id', $seller->id)->first();

        if ($settings) {
            $message = "Data Pengaturan jasa ditemukan";
            return $this->utilityService->is200ResponseWithData($message, $settings);
        } else {
            return response()->json(['message' => 'Settings not found.'], 404);
        }
    }
    /**
     * Create or update the shop profile.
     */

   public function updateSettings(Request $request)
{
    $validator = Validator::make($request->all(), [
        'isStoreCourierActive' => 'required|boolean',
        'storeCourierSettings' => 'required|array',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $userId = Auth::id();
    $seller = ShopProfile::where('user_id', $userId)->first();

    if (!$seller) {
        return response()->json(['message' => 'Seller profile not found.'], 404);
    }

    $sellerId = $seller->id;

    // Siapkan data yang akan diupdate
    $updateData = [
        'is_store_courier_active' => $request->isStoreCourierActive,
        'distance_tiers' => $request->input('storeCourierSettings.distance.tiers'),
        'max_distance' => $request->input('storeCourierSettings.distance.max'),
        'weight_tiers' => $request->input('storeCourierSettings.weight.tiers'),
        'max_weight' => $request->input('storeCourierSettings.weight.max'),
    ];

    // Tambahkan enabled_service_ids jika tersedia
    if ($request->has('enabledCourierServices')) {
        $enabledServicesPayload = $request->enabledCourierServices;
        $serviceIds = [];

        foreach ($enabledServicesPayload as $courierCode => $serviceIdList) {
            foreach ($serviceIdList as $idStr) {
                if (is_numeric($idStr)) {
                    $serviceIds[] = (int) $idStr;
                }
            }
        }

        $updateData['enabled_service_ids'] = array_values(array_unique($serviceIds));
    }

    // Simpan setting ke database
    $setting = StoreShippingSetting::updateOrCreate(
        ['seller_id' => $sellerId],
        $updateData
    );

    $message = "Data Pengaturan jasa berhasil disimpan";
    return $this->utilityService->is200Response($message);
}

}
