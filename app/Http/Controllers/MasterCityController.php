<?php

namespace App\Http\Controllers;

use App\Models\MasterCity;
use App\Models\MasterProvince;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterCityController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ?: null;
        $province = $request->get('province') ?: null;
        $sort_order = $request->get('sort_order') ?: 'asc';
        $sort_by = $request->get('sort_by') ?: 'name';
        $page_size = $request->get('page_size') ?: 10;

        // Base query cities
        $baseQuery = MasterCity::query()
            ->join('master_provinces', 'master_provinces.id', '=', 'master_cities.province_id') // ✅ pastikan hanya yang punya province
            ->select(
                'master_cities.id',
                'master_cities.name',
                'master_cities.province_id',
                'master_cities.created_at',
                'master_cities.updated_at',
                'master_provinces.name as province_name' // ambil langsung dari join
            )
            ->selectRaw('
        (SELECT COUNT(DISTINCT u.id)
         FROM user_addresses ua
         JOIN users u ON u.id = ua.user_id
         JOIN master_subdistricts ms ON ms.id = ua.subdistrict_id
         WHERE ms.city_id = master_cities.id) as total_users
    ')
            ->selectRaw('
        (SELECT COUNT(DISTINCT sa.id)
         FROM shop_addresses sa
         JOIN master_subdistricts ms2 ON ms2.id = sa.subdistrict_id
         WHERE ms2.city_id = master_cities.id) as total_shops
    ')
            ->selectRaw('
        (SELECT COUNT(*)
         FROM master_subdistricts ms3
         WHERE ms3.city_id = master_cities.id) as total_subdistricts
    ')
            ->selectRaw('
        (SELECT COUNT(*)
         FROM master_postal_codes mp
         JOIN master_subdistricts ms4 ON ms4.id = mp.subdistrict_id
         WHERE ms4.city_id = master_cities.id) as total_postal_codes
    ')
            // Filter search
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('master_cities.name', 'like', '%' . $search . '%')
                        ->orWhere('master_cities.id', 'like', '%' . $search . '%');
                });
            })
            // Filter province
            ->when($province, fn($q) => $q->where('master_cities.province_id', $province));


        // ðŸ”¹ Summary
        if ($search || $province) {
            // Jika ada filter, summary ikut data terfilter
            $summaryData = (clone $baseQuery)->get();
            $summary = [
                'total_provinces'    => $summaryData->pluck('province_name')->unique()->count(),
                'total_cities'       => $summaryData->count(),
                'total_subdistricts' => $summaryData->sum('total_subdistricts'),
                'total_postal_codes' => $summaryData->sum('total_postal_codes'),
            ];
        } else {
            // 🔹 Summary selalu ikut hasil baseQuery (sudah exclude data orphan)
            $summaryData = (clone $baseQuery)->get();

            $summary = [
                'total_provinces'    => $summaryData->pluck('province_name')->unique()->count(),
                'total_cities'       => $summaryData->count(),
                'total_subdistricts' => $summaryData->sum('total_subdistricts'),
                'total_postal_codes' => $summaryData->sum('total_postal_codes'),
            ];
        }

        // ðŸ”¹ Pagination
        $dataWithPaginate = $baseQuery
            ->orderBy($sort_by, $sort_order)
            ->paginate($page_size);

        $data = $dataWithPaginate->items();
        $total = $dataWithPaginate->total();
        $limit = $page_size;
        $page = $dataWithPaginate->currentPage();

        $meta = [
            'current_page'  => $dataWithPaginate->currentPage(),
            'per_page'      => $dataWithPaginate->perPage(),
            'total'         => $dataWithPaginate->total(),
            'last_page'     => $dataWithPaginate->lastPage(),
            'summary' => $summary
        ];

        return $this->utilityService->is200ResponseWithDataAndMeta($data, $meta);
    }




    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'province_id' => 'required|exists:master_provinces,id',
        ]);

        $data = [
            "name" => $request->name,
            "province_id" => $request->province_id,
        ];

        $insert = MasterCity::create($data);

        if ($insert) {
            $success_message = "City data successfully added";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'province_id' => 'required|exists:master_provinces,id',
        ]);

        $city = MasterCity::find($id);

        if (!$city) {
            return $this->utilityService->is404Response("City not found!");
        }

        $city->name = $request->name;
        $city->province_id = $request->province_id;

        if ($city->save()) {
            $success_message = "City data successfully updated";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function delete($id)
    {
        $city = MasterCity::find($id);

        if (!$city) {
            return $this->utilityService->is404Response("City not found!");
        }

        if ($city->delete()) {
            $success_message = "City data successfully deleted";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
}
