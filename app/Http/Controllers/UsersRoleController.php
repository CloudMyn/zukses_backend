<?php

namespace App\Http\Controllers;

use App\Models\UsersRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersRoleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ? $request->get('search') : '';
        $search_by = $request->get('search_by') ? $request->get('search_by') : '';
        $sort_order = $request->get('sort_order') ? $request->get('sort_order') : 'desc';
        $sort_by = $request->get('sort_by') ? $request->get('sort_by') : 'created_at';
        $page_size = $request->get('page_size') ? $request->get('page_size') : '10000';

        $dataWithPaginate = UsersRole::where('role', 'like', '%' . $search . '%')->orderBy($sort_by, $sort_order)->paginate($page_size);
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

    public function list(Request $request, $role)
    {
        $rol =  preg_replace('/%20/', ' ', $role);
        $sort_by = $request->get('sort_by') ? $request->get('sort_by') : 'created_at';
        $sort_order = $request->get('sort_order') ? $request->get('sort_order') : 'desc';

        // $invitationComment = UsersAccessMenu::with('menu')->orderBy($sort_by, $sort_order)->get();
        $invitationComment = DB::table('users_role')->where('role', '=', '' . $rol . '')->orderBy($sort_by, $sort_order)->get();
        if (count($invitationComment) < 1) {
            return $this->utilityService->is404Response('Data Users Menu tidak ditemukan', $rol);
        }
        return $this->utilityService->is200ResponseWithData('Data Users Menu ditemukan', $invitationComment);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'role' => 'required',
        ]);

        $data = [
            "role" => $request->role,

        ];

        $insert = UsersRole::create($data);

        if ($insert) {
            $success_message = "Data Users Role Berhasil Ditambahkan";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
    public function update(Request $request, $id)
    {
        $productVariant = UsersRole::find($id);

        $productVariant->role = $request->role;

        if ($productVariant->save()) {
            $success_message = "Data Users Role Berhasil Diubah";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function destroy($id)
    {
        UsersRole::find($id)->delete();
        $success_message = "Data Users Role Berhasil Dihapus";
        return $this->utilityService->is200Response($success_message);
    }
}
