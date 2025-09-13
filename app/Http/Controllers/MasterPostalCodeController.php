<?php

namespace App\Http\Controllers;

use App\Models\MasterCity;
use App\Models\MasterPostalCode;
use App\Models\MasterProvince;
use App\Models\MasterSubdistrict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterPostalCodeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ?: null;
        $subdistrict = $request->get('subdistrict') ?: null;
        $city = $request->get('city') ?: null; // tambahan filter city
        $province = $request->get('province') ?: null; // tambahan filter province
        $sort_order = $request->get('sort_order') ?: 'asc';
        $sort_by = $request->get('sort_by') ?: 'code';
        $page_size = $request->get('page_size') ?: 10;

        // Base query
        $baseQuery = MasterPostalCode::query()
            ->select(
                'master_postal_codes.id',
                'master_postal_codes.code',
                'master_postal_codes.created_at',
                'master_postal_codes.updated_at',
                'master_subdistricts.name as subdistrict_name',
                'master_subdistricts.id as subdistrict_id',
                'master_cities.name as city_name',
                'master_cities.id as city_id',
                DB::raw('COALESCE(master_cities.name) as city_name_en'),
                'master_provinces.name as province_name',
                'master_provinces.id as province_id'
            )
            ->join('master_subdistricts', 'master_subdistricts.id', '=', 'master_postal_codes.subdistrict_id')
            ->join('master_cities', 'master_cities.id', '=', 'master_subdistricts.city_id')
            ->join('master_provinces', 'master_provinces.id', '=', 'master_cities.province_id')
            ->selectRaw('
            (SELECT COUNT(DISTINCT u.id)
             FROM user_addresses ua
             JOIN users u ON u.id = ua.user_id
             WHERE ua.postal_code_id = master_postal_codes.id) as total_users
        ')
            ->selectRaw('
            (SELECT COUNT(DISTINCT sa.id)
             FROM shop_addresses sa
             WHERE sa.postal_code_id = master_postal_codes.id) as total_shops
        ')
            ->when($search, fn($q) => $q->where('master_postal_codes.code', 'like', '%' . $search . '%'))
            ->when($subdistrict, fn($q) => $q->where('master_subdistricts.id', $subdistrict))
            ->when($city, fn($q) => $q->where('master_cities.id', $city))
            ->when($province, fn($q) => $q->where('master_provinces.id', $province));

        // ðŸ”¹ Summary
        if ($search || $subdistrict || $city || $province) {
            $summaryData = (clone $baseQuery)->get();
            $summary = [
                'total_provinces'    => $summaryData->pluck('province_name')->unique()->count(),
                'total_cities'       => $summaryData->pluck('city_name')->unique()->count(),
                'total_subdistricts' => $summaryData->pluck('subdistrict_name')->unique()->count(),
                'total_postal_codes' => $summaryData->count(),
            ];
        } else {
            $summary = [
                'total_provinces'    => MasterProvince::count(),
                'total_cities'       => MasterCity::join('master_provinces', 'master_provinces.id', '=', 'master_cities.province_id')->count(),
                'total_subdistricts' => MasterSubdistrict::join('master_cities', 'master_cities.id', '=', 'master_subdistricts.city_id')
                    ->join('master_provinces', 'master_provinces.id', '=', 'master_cities.province_id')->count(),
                'total_postal_codes' => MasterPostalCode::join('master_subdistricts', 'master_subdistricts.id', '=', 'master_postal_codes.subdistrict_id')
                    ->join('master_cities', 'master_cities.id', '=', 'master_subdistricts.city_id')
                    ->join('master_provinces', 'master_provinces.id', '=', 'master_cities.province_id')->count(),
            ];
        }

        // ðŸ”¹ Pagination
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
            'code' => 'required|numeric',
            'subdistrict_id' => 'required|exists:master_subdistricts,id',
        ]);

        $data = [
            "code" => $request->code,
            "subdistrict_id" => $request->subdistrict_id,
        ];

        $insert = MasterPostalCode::create($data);

        if ($insert) {
            $success_message = "Postal Code data successfully added";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'code' => 'required|numeric',
            'subdistrict_id' => 'required|exists:master_subdistricts,id',
        ]);

        $postalCode = MasterPostalCode::find($id);

        if (!$postalCode) {
            return $this->utilityService->is404Response("Postal Code not found!");
        }

        $postalCode->code = $request->code;
        $postalCode->subdistrict_id = $request->subdistrict_id;

        if ($postalCode->save()) {
            $success_message = "Postal Code data successfully updated";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function delete($id)
    {
        $postalCode = MasterPostalCode::find($id);

        if (!$postalCode) {
            return $this->utilityService->is404Response("Postal Code not found!");
        }

        if ($postalCode->delete()) {
            $success_message = "Postal Code data successfully deleted";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
}
