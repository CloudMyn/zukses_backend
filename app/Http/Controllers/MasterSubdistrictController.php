<?php

namespace App\Http\Controllers;

use App\Models\MasterCity;
use App\Models\MasterProvince;
use App\Models\MasterSubdistrict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterSubdistrictController extends Controller
{
    public function index(Request $request)
    {
        $search    = $request->get('search') ?: null;
        $city      = $request->get('city') ?: null;
        $province  = $request->get('province') ?: null;
        $sort_order = $request->get('sort_order') ?: 'asc';
        $sort_by    = $request->get('sort_by') ?: 'name';
        $page_size  = $request->get('page_size') ?: 10;

        // ✅ Base query pakai JOIN biar data yatim otomatis hilang
        $baseQuery = MasterSubdistrict::query()
            ->join('master_cities', 'master_cities.id', '=', 'master_subdistricts.city_id')
            ->join('master_provinces', 'master_provinces.id', '=', 'master_cities.province_id')
            ->select(
                'master_subdistricts.id',
                'master_subdistricts.name',
                'master_subdistricts.city_id',
                'master_subdistricts.created_at',
                'master_subdistricts.updated_at',
                'master_cities.name as city_name',
                'master_provinces.id as province_id',
                'master_provinces.name as province_name'
            )
            ->selectRaw('
            (SELECT COUNT(DISTINCT u.id) 
             FROM user_addresses ua 
             JOIN users u ON u.id = ua.user_id 
             WHERE ua.subdistrict_id = master_subdistricts.id) as total_users
        ')
            ->selectRaw('
            (SELECT COUNT(DISTINCT sa.id) 
             FROM shop_addresses sa 
             WHERE sa.subdistrict_id = master_subdistricts.id) as total_shops
        ')
            ->selectRaw('
            (SELECT COUNT(*) 
             FROM master_postal_codes mp 
             WHERE mp.subdistrict_id = master_subdistricts.id) as total_postal_codes
        ')
            // 🔎 Filter search
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('master_subdistricts.name', 'like', '%' . $search . '%')
                        ->orWhere('master_subdistricts.id', 'like', '%' . $search . '%');
                });
            })
            // 🔎 Filter city
            ->when($city, fn($q) => $q->where('master_subdistricts.city_id', $city))
            // 🔎 Filter province
            ->when($province, fn($q) => $q->where('master_cities.province_id', $province));

        // 🔹 Summary
        if ($search || $city || $province) {
            $summaryData = (clone $baseQuery)->get();
            $summary = [
                'total_provinces'    => $summaryData->pluck('province_name')->unique()->count(),
                'total_cities'       => $summaryData->pluck('city_name')->unique()->count(),
                'total_subdistricts' => $summaryData->count(),
                'total_postal_codes' => $summaryData->sum('total_postal_codes'),
            ];
        } else {
            $summaryData = (clone $baseQuery)->get();

            $summary = [
                'total_provinces'    => $summaryData->pluck('province_name')->unique()->count(),
                'total_cities'       => $summaryData->pluck('city_name')->unique()->count(),
                'total_subdistricts' => $summaryData->count(),
                'total_postal_codes' => $summaryData->sum('total_postal_codes'),
            ];
        }

        // 🔹 Pagination
        $dataWithPaginate = $baseQuery
            ->orderBy($sort_by, $sort_order)
            ->paginate($page_size);

        $data = $dataWithPaginate->items();

        $meta = [
            'current_page'  => $dataWithPaginate->currentPage(),
            'per_page'      => $dataWithPaginate->perPage(),
            'total'         => $dataWithPaginate->total(),
            'last_page'     => $dataWithPaginate->lastPage(),
            'summary'       => $summary
        ];

        return $this->utilityService->is200ResponseWithDataAndMeta($data, $meta);
    }




    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'city_id' => 'required|exists:master_cities,id',
        ]);

        $data = [
            "name" => $request->name,
            "city_id" => $request->city_id,
        ];

        $insert = MasterSubdistrict::create($data);

        if ($insert) {
            $success_message = "Subdistrict data successfully added";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'city_id' => 'required|exists:master_cities,id',
        ]);

        $subdistrict = MasterSubdistrict::find($id);

        if (!$subdistrict) {
            return $this->utilityService->is404Response("Subdistrict not found!");
        }

        $subdistrict->name = $request->name;
        $subdistrict->city_id = $request->city_id;

        if ($subdistrict->save()) {
            $success_message = "Subdistrict data successfully updated";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function delete($id)
    {
        $subdistrict = MasterSubdistrict::find($id);

        if (!$subdistrict) {
            return $this->utilityService->is404Response("Subdistrict not found!");
        }

        if ($subdistrict->delete()) {
            $success_message = "Subdistrict data successfully deleted";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
}
