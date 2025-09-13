<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\ShopProfile;
use App\Models\StoreShippingSetting;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Mengambil daftar semua kurir aktif beserta layanannya.
     *
     * @return \Illuminate\Http\JsonResponse
     */


    public function getCustomers(Request $request)
    {
        $search = $request->get('search'); // kata kunci pencarian
        $status = $request->get('status'); // filter status
        $perPage = $request->get('per_page', 10); // default 10 per page

        $query = UserProfile::query()
            ->join('users', 'users.id', '=', 'user_profiles.user_id')
            ->leftJoin(DB::raw("
            (
                SELECT ua.user_id,
                       COALESCE(
                           MAX(CASE WHEN ua.is_primary = 1 THEN ua.id END),
                           MIN(ua.id)
                       ) as chosen_address_id
                FROM user_addresses ua
                GROUP BY ua.user_id
            ) addr_choice
        "), 'addr_choice.user_id', '=', 'users.id')
            ->leftJoin('user_addresses as ua', 'ua.id', '=', 'addr_choice.chosen_address_id')
            ->leftJoin('master_provinces as mp', 'mp.id', '=', 'ua.province_id')
            ->leftJoin('master_cities as mc', 'mc.id', '=', 'ua.citie_id')
            ->leftJoin('orders as o', 'o.user_profile_id', '=', 'user_profiles.id')
            ->select(
                'user_profiles.id',
                'user_profiles.user_id',
                'user_profiles.name',
                'user_profiles.name_store',
                'user_profiles.status',
                'user_profiles.created_at',
                'user_profiles.updated_at',
                'users.email',
                'users.whatsapp',
                'mp.name as province',
                'mc.name as city',
                DB::raw('COUNT(CASE WHEN o.status = "paid" THEN o.id END) as total_orders'),
                DB::raw('COALESCE(SUM(CASE WHEN o.status = "paid" THEN o.total_price END), 0) as total_amount')
            )
            ->groupBy(
                'user_profiles.id',
                'user_profiles.user_id',
                'user_profiles.name',
                'user_profiles.name_store',
                'user_profiles.status',
                'user_profiles.created_at',
                'user_profiles.updated_at',
                'users.email',
                'users.whatsapp',
                'mp.name',
                'mc.name'
            );


        // filter pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('user_profiles.name', 'LIKE', "%{$search}%")
                    ->orWhere('users.email', 'LIKE', "%{$search}%")
                    ->orWhere('users.whatsapp', 'LIKE', "%{$search}%")
                    ->orWhere('mc.name', 'LIKE', "%{$search}%")
                    ->orWhere('mp.name', 'LIKE', "%{$search}%");
            });
        }

        // filter status
        if ($status) {
            $query->where('user_profiles.status', $status);
        }

        $userProfiles = $query->paginate($perPage);

        if ($userProfiles->isNotEmpty()) {
            $message = "Data Customer ditemukan";

            $meta = [
                'current_page' => $userProfiles->currentPage(),
                'per_page' => $userProfiles->perPage(),
                'total' => $userProfiles->total(),
                'last_page' => $userProfiles->lastPage(),
            ];

            return $this->utilityService->is200ResponseWithDataAndMeta($userProfiles->items(), $meta);
        } else {
            return $this->utilityService->is404Response("tidak ditemukan");
        }
    }
}
