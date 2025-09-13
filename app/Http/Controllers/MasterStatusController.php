<?php

namespace App\Http\Controllers;

use App\Models\MasterStatus;
use Illuminate\Http\Request;

class MasterStatusController extends Controller
{
    protected $utilityService;

    public function index(Request $request)
    {
        $search = $request->get('search') ? $request->get('search') : null;
        $sort_order = $request->get('sort_order') ? $request->get('sort_order') : 'asc';
        $sort_by = $request->get('sort_by') ? $request->get('sort_by') : 'id';
        $page_size = $request->get('page_size') ? $request->get('page_size') : '10';

        $dataWithPaginate = MasterStatus::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
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
            'desc' => 'required',
        ]);

        $data = [
            'name' => $request->name,
            'desc' => $request->desc,
        ];

        $insert = MasterStatus::create($data);

        if ($insert) {
            $success_message = 'Status data successfully added';
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response('problem with server');
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $status = MasterStatus::find($id);

        if (!$status) {
            return $this->utilityService->is404Response("Status not found!");
        }

        $status->name = $request->name;

        if ($status->save()) {
            $success_message = "Status data successfully updated";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function delete($id)
    {
        $status = MasterStatus::find($id);

        if (!$status) {
            return $this->utilityService->is404Response("Status not found!");
        }

        if ($status->delete()) {
            $success_message = "Status data successfully deleted";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
}

