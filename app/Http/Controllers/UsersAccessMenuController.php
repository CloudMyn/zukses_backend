<?php

namespace App\Http\Controllers;

use App\Models\UsersAccessMenu;
use App\Models\UsersMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UsersAccessMenuController extends Controller
{
    public function index(Request $request, $id_role)
    {
        $search = $request->get('search') ? $request->get('search') : '';
        $search_by = $request->get('search_by') ? $request->get('search_by') : '';
        $sort_order = $request->get('sort_order') ? $request->get('sort_order') : 'desc';
        $sort_by = $request->get('sort_by') ? $request->get('sort_by') : 'created_at';
        $page_size = $request->get('page_size') ? $request->get('page_size') : '10000';

        $dataWithPaginate = DB::table('users_access_menu')->where('role', 6)->orderBy($sort_by, $sort_order)->orderBy($sort_by, $sort_order)->paginate($page_size);
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
        $rol = preg_replace('/%20/', ' ', $role);

        $roleUser = DB::table('users_role')->where('role', $rol)->first();
        $data = [];
        $id = $roleUser->id;
        // Query untuk mendapatkan parent menus
        $parent = DB::table('menus')
            ->join('users_access_menu', function ($join) use ($id) {
                $join->on('users_access_menu.menu_id', '=', 'menus.id')
                    ->where('users_access_menu.role', '=', $id);
            })
            ->select(
                'menus.id',
                'menus.name',
                'menus.url',
                'menus.icon',
                'menus.order_number',
                'menus.code', // add other required columns explicitly
                DB::raw('MAX(users_access_menu.menu_id) as menu_id') // Aggregate non-grouped field
            )
            ->groupBy('menus.id', 'menus.name', 'menus.url', 'menus.icon', 'menus.order_number', 'menus.code')
            ->whereNull('parent_name')
            ->orderByRaw('CAST(order_number AS UNSIGNED)')
            ->get();

        foreach ($parent as $p) {
            // Query untuk mendapatkan children dari parent yang sesuai
            $children = DB::table('menus')
                ->join('users_access_menu', function ($join) use ($id) {
                    $join->on('users_access_menu.menu_id', '=', 'menus.id')
                        ->where('users_access_menu.role', '=', $id);
                })
                ->select(
                    'menus.id',
                    'menus.name',
                    'menus.url',
                    'menus.icon',
                    'menus.order_number',
                    'menus.code', // add other required columns explicitly
                    DB::raw('MAX(users_access_menu.menu_id) as menu_id') // Aggregate non-grouped field
                )
                ->groupBy('menus.id', 'menus.name', 'menus.url', 'menus.icon', 'menus.order_number', 'menus.code')
                ->where('parent_name', $p->name)
                ->orderByRaw('CAST(order_number AS UNSIGNED)')
                ->get();

            // Membuat array parentData dengan children kosong
            $parentData = [
                'parent_name' => $p->name,
                'id_parent' => $p->id,
                'url' => $p->url,
                'menu_id' => $p->menu_id,
                'icon' => $p->icon,
                'children' => [],
            ];

            // Menambahkan children ke parentData
            foreach ($children as $child) {
                $parentData['children'][] = [
                    'name' => $child->name,
                    'url' => $child->url,
                    'id_child' => $child->id,
                    'menu_id' => $child->menu_id,
                ];
            }

            // Menambahkan parentData ke data
            $data[] = $parentData;
        }

        $message = "Tree ditemukan";

        return $this->utilityService->is200ResponseWithData($message, $data);
    }



    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required',
            'menu_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->utilityService->is422Response($validator->errors()->first());
        }

        $data = [
            "role" => $request->role,
            "menu_id" => $request->menu_id,

        ];

        $insert = UsersAccessMenu::create($data);

        if ($insert) {
            $success_message = "Data Users Access Menu Berhasil Ditambahkan";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
    public function update(Request $request, $id)
    {
        $productVariant = UsersAccessMenu::find($id);

        $productVariant->role = $request->role;
        $productVariant->menu_id = $request->menu_id;

        if ($productVariant->save()) {
            $success_message = "Data Users Access Menu Berhasil Diubah";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function destroy($role, $menu)
    {
        $rol = preg_replace('/%20/', ' ', $role);
        UsersAccessMenu::where([
            'role' => '' . $rol . '',
            'menu_id' => $menu
        ])->delete();
        $success_message = "Data Users Access Menu Berhasil Dihapus";
        return $this->utilityService->is200Response($success_message);
    }
}
