<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\ShopProfile;
use App\Models\StoreShippingSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
    public function listCourier(Request $request)
    {
        $startDate = $request->get('start_date'); // contoh: 2025-09-01
        $endDate   = $request->get('end_date');   // contoh: 2025-09-10

        $couriers = Courier::with(['services' => function ($query) {
            $query->where('is_active', true);
        }])
            ->where('is_active', true)
            ->get();

        $formattedCouriers = $couriers->map(function ($courier) use ($startDate, $endDate) {
            $statuses = [3, 4, 5]; // 3=Proses, 4=Selesai, 5=Dibatalkan
            $summary = [];

            foreach ($statuses as $status) {
                // Ambil semua order_id unik dan price_shipping per courier + status
                $ordersQuery = DB::table('order_items')
                    ->select('order_id', DB::raw('MAX(price_shipping) as price_shipping'))
                    ->where('courier_id', $courier->id)
                    ->where('status', $status);

                if ($startDate && $endDate) {
                    $ordersQuery->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }

                $orders = $ordersQuery->groupBy('order_id')->get();

                // Hitung total_orders dan total_amount
                $totalOrders = $orders->count();
                $totalAmount = $orders->sum('price_shipping');

                // Hitung seller unik per courier + status
                $totalSellers = DB::table('order_items')
                    ->join('products', 'products.id', '=', 'order_items.product_id')
                    ->where('order_items.courier_id', $courier->id)
                    ->where('order_items.status', $status);

                if ($startDate && $endDate) {
                    $totalSellers->whereBetween('order_items.created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }

                $totalSellers = $totalSellers->distinct('products.seller_id')->count('products.seller_id');

                $label = $status === 3 ? 'Proses' : ($status === 4 ? 'Selesai' : 'Dibatalkan');

                $summary["status_$label"] = $totalOrders;
                $summary["amount_status_$label"] = $totalAmount;
                $summary["seller_status_$label"] = $totalSellers;
            }

            $countShipping = ($summary["status_Proses"] ?? 0)
                + ($summary["status_Selesai"] ?? 0)
                + ($summary["status_Dibatalkan"] ?? 0);

            $amountShipping = ($summary["amount_status_Proses"] ?? 0)
                + ($summary["amount_status_Selesai"] ?? 0)
                + ($summary["amount_status_Dibatalkan"] ?? 0);

            $countSellers = ($summary["seller_status_Proses"] ?? 0)
                + ($summary["seller_status_Selesai"] ?? 0)
                + ($summary["seller_status_Dibatalkan"] ?? 0);

            return array_merge([
                'id' => $courier->id,
                'name' => $courier->name,
                'logo' => $courier->logo_url,
                'services' => $courier->services,
                'countShipping' => $countShipping,
                'amountShipping' => $amountShipping,
                'countSellers' => $countSellers,
            ], $summary);
        });

        // Hitung seller unik global (seluruh courier)
        $sellerQuery = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereIn('order_items.status', [3, 4, 5]);

        if ($startDate && $endDate) {
            $sellerQuery->whereBetween('order_items.created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        $uniqueSellers = $sellerQuery->distinct('products.seller_id')->count('products.seller_id');

        $globalSummary = [
            'totalCouriers' => $formattedCouriers->count(),
            'totalCountShipping' => $formattedCouriers->sum('countShipping'),
            'totalPriceShipping' => $formattedCouriers->sum('amountShipping'),
            'total_amount_status_Selesai' => $formattedCouriers->sum('amount_status_Selesai'),
            'total_amount_status_Proses' => $formattedCouriers->sum('amount_status_Proses'),
            'total_amount_status_Dibatalkan' => $formattedCouriers->sum('amount_status_Dibatalkan'),
            'totalSellers' => $uniqueSellers,
        ];

        if ($couriers->isNotEmpty()) {
            return $this->utilityService->is200ResponseWithDataAndSummary("Data Kurir ditemukan", $formattedCouriers, $globalSummary);
        }

        return $this->utilityService->is404Response("Data Kurir tidak ditemukan");
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
