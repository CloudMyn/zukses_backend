<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\UrlRemove;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ? $request->get('search') : '';
        $search_by = $request->get('search_by') ? $request->get('search_by') : '';
        $sort_order = $request->get('sort_order') ? $request->get('sort_order') : 'desc';
        $sort_by = $request->get('sort_by') ? $request->get('sort_by') : 'created_at';
        $page_size = $request->get('page_size') ? $request->get('page_size') : '10';

        $dataWithPaginate = Menu::where('name', 'like', '%' . $search . '%')
            ->orderBy($sort_by, $sort_order)->paginate($page_size);
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

    public function parent(Request $request)
    {
        $search = $request->get('search') ? $request->get('search') : '';
        $sort_order = $request->get('sort_order') ? $request->get('sort_order') : 'desc';
        $sort_by = $request->get('sort_by') ? $request->get('sort_by') : 'created_at';
        $page_size = $request->get('page_size') ? $request->get('page_size') : '10';

        $dataWithPaginate = Menu::whereNull('parent_name')
            ->orderBy($sort_by, $sort_order)->paginate($page_size);
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

    public function tree(Request $request, $parent_name)
    {
        $parentName = preg_replace('/%20/', ' ', $parent_name);
        $parent = DB::table('menus')->where('name',  $parentName)->whereNull('parent_name')->first();
        $child = DB::table('menus')->where('parent_name',  $parentName)->orderByRaw('CAST(order_number AS UNSIGNED)')->get();
        $data = [
            'parent' => $parent,
            'menu' => $child
        ];
        $massege = "Tree ditemukan";

        return $this->utilityService->is200ResponseWithData($massege, $data);
    }


    public function treeRole(Request $request, $id_role)
    {
        $data = [];

        // Query untuk mendapatkan parent menus
        $parent = DB::table('menus')
            ->leftJoin('users_access_menu', function ($join) use ($id_role) {
                $join->on('users_access_menu.menu_id', '=', 'menus.id')
                    ->where('users_access_menu.role', '=', $id_role);
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
                ->leftJoin('users_access_menu', function ($join) use ($id_role) {
                    $join->on('users_access_menu.menu_id', '=', 'menus.id')
                        ->where('users_access_menu.role', '=', $id_role);
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



    // public function list(Request $request)
    // {
    //     $search = $request->get('search') ? $request->get('search') : '';
    //     $search_by = $request->get('search_by') ? $request->get('search_by') : 'all';
    //     $sort_by = $request->get('sort_by') ? $request->get('sort_by') : 'created_at';
    //     $sort_order = $request->get('sort_order') ? $request->get('sort_order') : 'desc';
    //     $page_size = $request->get('page_size') ? $request->get('page_size') : '10000';

    //     $bank = [];

    //     if ($search_by === "all") {
    //         $bank = Bank::where(function ($query) use ($search) {
    //             $query->where('bank_name', 'like', '%' . $search . '%')
    //                 ->orWhere('bank_code', 'like', '%' . $search . '%');
    //         });
    //     } else {
    //         $bank = Bank::where($search_by, 'like', '&' . $search . '%');
    //     }

    //     $bank = $bank->orderBy($sort_by, $sort_order)->paginate($page_size);

    //     if (count($bank) < 1) {
    //         return $this->utilityService->is404Response('Bank tidak ditemukan');
    //     }

    //     return $this->utilityService->is200ResponseWithData('Bank ditemukan', $bank);
    // }



    public function create(Request $request)
    {
        $this->validate($request, [
            'level' => 'required',
            'url' => 'required',
            'is_active_flag' => 'required',
            'name' => 'required',
        ]);

        $lastMenu = Menu::latest('id')->first();
        $nextCode = $lastMenu ? $lastMenu->id + 1 : 1;
        if ($nextCode < 10) {
            $code = "MENU-000" . $nextCode;
        } elseif ($nextCode < 100) {
            $code = "MENU-00" . $nextCode;
        } elseif ($nextCode < 1000) {
            $code = "MENU-0" . $nextCode;
        } elseif ($nextCode < 10000) {
            $code = "MENU-" . $nextCode;
        }
        $data = [
            "code" => $code,
            "name" => $request->name,
            "level" => $request->level,
            "url" => $request->url,
            "is_active_flag" => $request->is_active_flag,
            "icon" => $request->icon,
            "parent_name" => $request->parent_name
        ];

        $insert = Menu::create($data);

        if ($insert) {
            $success_message = "Data Menu Berhasil Ditambahkan";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
    public function update(Request $request, $id)
    {
        $menu = Menu::find($id);

        $menu->icon = $request->icon;
        $menu->name = $request->name;
        $menu->level = $request->level;
        $menu->url = $request->url;
        $menu->is_active_flag = $request->is_active_flag;
        $menu->parent_name = $request->parent_name;
        if ($menu->save()) {
            $success_message = "Data Menu Berhasil Diubah";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }


    public function ordinal(Request $request)
    {
        $id = $request->id;
        $arrayOrdinal = $request->ordinal;

        for ($i = 0; $i < count($id); $i++) {
            $invitationGallery = Menu::find($id[$i]);
            if ($invitationGallery) {
                $invitationGallery->order_number = $arrayOrdinal[$i];
                $invitationGallery->save();
            }
        }

        $success_message = "Order Number Menu Berhasil Diubah";
        return $this->utilityService->is200Response($success_message);
    }

    public function destroy($id)
    {
        $menu = Menu::find($id);
        $menu->delete();
        $success_message = "Data Menu Berhasil Dihapus";
        return $this->utilityService->is200Response($success_message);
    }
}
