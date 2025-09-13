<?php

namespace App\Http\Controllers;

use App\Models\MasterCity;
use App\Models\MasterPostalCode;
use App\Models\MasterProvince;
use App\Models\MasterSubdistrict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterProvinceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ?? null;
        $sort_order = $request->get('sort_order') ?? 'asc';
        $sort_by = $request->get('sort_by') ?? 'name';
        $page_size = $request->get('page_size') ?? 10;

        $baseQuery = MasterProvince::query()
            ->select('master_provinces.*')
            ->selectRaw('
            (SELECT COUNT(DISTINCT u.id) 
             FROM user_addresses ua 
             JOIN users u ON u.id = ua.user_id 
             WHERE ua.province_id = master_provinces.id) as total_users
        ')
            ->selectRaw('
            (SELECT COUNT(DISTINCT sa.id) 
             FROM shop_addresses sa 
             WHERE sa.province_id = master_provinces.id) as total_shops
        ')
            ->selectRaw('
            (SELECT COUNT(*) 
             FROM master_cities mc 
             WHERE mc.province_id = master_provinces.id) as total_cities
        ')
            ->selectRaw('
            (SELECT COUNT(*) 
             FROM master_subdistricts ms 
             JOIN master_cities mc ON mc.id = ms.city_id 
             WHERE mc.province_id = master_provinces.id) as total_subdistricts
        ')
            ->selectRaw('
            (SELECT COUNT(*) 
             FROM master_postal_codes mp 
             JOIN master_subdistricts ms ON ms.id = mp.subdistrict_id
             JOIN master_cities mc ON mc.id = ms.city_id 
             WHERE mc.province_id = master_provinces.id) as total_postal_codes
        ');

        // ðŸ”Ž Filter
        if ($search) {
            $baseQuery = DB::table(DB::raw("({$baseQuery->toSql()}) as t"))
                ->mergeBindings($baseQuery->getQuery()) // penting agar binding ikut
                ->where('t.name', 'like', "%$search%")
                ->orWhere('t.id', 'like', "%$search%")
                ->orWhere('t.total_users', 'like', "%$search%")
                ->orWhere('t.total_shops', 'like', "%$search%")
                ->orWhere('t.total_cities', 'like', "%$search%")
                ->orWhere('t.total_subdistricts', 'like', "%$search%")
                ->orWhere('t.total_postal_codes', 'like', "%$search%");
        }

        // ðŸ‘‰ ambil dulu semua hasil (SEBELUM paginate)
        $summaryData = (clone $baseQuery)->get();

        $summary = [
            'total_provinces'    => $summaryData->count(),
            'total_cities'       => $summaryData->sum('total_cities'),
            'total_subdistricts' => $summaryData->sum('total_subdistricts'),
            'total_postal_codes' => $summaryData->sum('total_postal_codes'),
        ];

        // baru paginate
        $baseQuery->orderBy($sort_by, $sort_order);
        $dataWithPaginate = $baseQuery->paginate($page_size);

        $data = $dataWithPaginate->items();


        $meta = [
            'current_page'  => $dataWithPaginate->currentPage(),
            'per_page'      => $dataWithPaginate->perPage(),
            'total'         => $dataWithPaginate->total(),
            'last_page'     => $dataWithPaginate->lastPage(),
            'summary' => $summary
        ];

        return $this->utilityService->is200ResponseWithDataAndMeta($data, $meta);
    }




    public function list(Request $request)
    {
        $search = $request->get('search') ? $request->get('search') : null;
        $sort_order = $request->get('sort_order') ? $request->get('sort_order') : 'asc';
        $sort_by = $request->get('sort_by') ? $request->get('sort_by') : 'name';
        $page_size = $request->get('page_size') ? $request->get('page_size') : '99';

        $dataWithPaginate = MasterProvince::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
                $query->orWhere('id', 'like', '%' . $search . '%');
            })
            ->orderBy($sort_by, $sort_order)
            ->paginate($page_size);

        $data = $dataWithPaginate->items();
        $total = $dataWithPaginate->total();
        $limit = $page_size;
        $page = $dataWithPaginate->currentPage();

        $meta = [
            'total' => $total,
            'limit' => $limit,
            'page' => $page,
        ];

        return $this->utilityService->is200ResponseWithDataAndMeta($data, $meta);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $data = [
            "name" => $request->name,
        ];

        $insert = MasterProvince::create($data);

        if ($insert) {
            $success_message = "Province data successfully added";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $province = MasterProvince::find($id);

        if (!$province) {
            return $this->utilityService->is404Response("Province not found!");
        }

        $province->name = $request->name;

        if ($province->save()) {
            $success_message = "Province data successfully updated";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function delete($id)
    {
        $province = MasterProvince::find($id);

        if (!$province) {
            return $this->utilityService->is404Response("Province not found!");
        }

        if ($province->delete()) {
            $success_message = "Province data successfully deleted";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
}
