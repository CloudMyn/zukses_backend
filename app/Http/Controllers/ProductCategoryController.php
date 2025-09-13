<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// Asumsikan Anda memiliki UtilityService yang di-inject
// use App\Services\UtilityService;

class ProductCategoryController extends Controller
{
    // protected $utilityService;

    // public function __construct(UtilityService $utilityService)
    // {
    //     $this->utilityService = $utilityService;
    // }

    public function index(Request $request)
    {
        $search = $request->get('search') ? $request->get('search') : null;
        $sort_order = $request->get('sort_order') ? $request->get('sort_order') : 'asc';
        $sort_by = $request->get('sort_by') ? $request->get('sort_by') : 'name';
        $page_size = $request->get('page_size') ? $request->get('page_size') : '10';
        $parent_id = $request->get('parent_id') ? $request->get('parent_id') : null;

        $dataWithPaginate = ProductCategory::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->when($parent_id !== null, function ($query) use ($parent_id) {
                $query->where('parent_id', $parent_id);
            }, function ($query) {
                $query->whereNull('parent_id');
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

        // Ganti dengan response service Anda
        // return response()->json(['data' => $data, 'meta' => $meta]);
        return $this->utilityService->is200ResponseWithDataAndMeta($data, $meta);
    }

    public function show()
    {
        $categories = ProductCategory::whereNull('parent_id')
            ->with('children') // recursive
            ->get();
        if ($categories) {
            return $this->utilityService->is200ResponseWithData("Kategori ditemukan", $categories);
        }
    }
    public function list(Request $request)
    {
        $parentId = $request->get('parent_id');
        $level    = (int) $request->get('level', 0);
        $perPage  = $request->get('per_page', 10);
        $search   = $request->get('search'); // ambil keyword search

        $query = ProductCategory::withCount('products')->with('parent', 'allChildren');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // === FILTER KHUSUS: parent_id null + level tertentu ===
        if (($parentId === 'null' || is_null($parentId)) && $level > 0) {
            if ($level == 1) {
                $query->whereHas('parent', fn($q) => $q->whereNull('parent_id'));
            } elseif ($level == 2) {
                $query->whereHas('parent.parent', fn($q) => $q->whereNull('parent_id'));
            } elseif ($level == 3) {
                $query->whereHas('parent.parent.parent', fn($q) => $q->whereNull('parent_id'));
            }
        } elseif ($parentId && $level == 3) {
            $node = ProductCategory::with('parent.parent')->find($parentId);

            if ($node) {
                if ($node->parent_id === null) {
                    $query->whereHas('parent.parent', function ($q) use ($parentId) {
                        $q->where('parent_id', $parentId);
                    });
                } elseif ($node->parent && $node->parent->parent_id === null) {
                    $query->whereHas('parent', function ($q) use ($parentId) {
                        $q->where('parent_id', $parentId);
                    });
                } else {
                    $query->where('parent_id', $parentId);
                }
            }
        } elseif ($parentId && $level == 2) {
            $node = ProductCategory::with('parent')->find($parentId);

            if ($node) {
                if ($node->parent_id === null) {
                    $query->whereHas('parent', function ($q) use ($parentId) {
                        $q->where('parent_id', $parentId);
                    });
                } elseif ($node->parent && $node->parent->parent_id === null) {
                    $query->where('parent_id', $parentId);
                } else {
                    $query->where('parent_id', $node->parent_id);
                }
            }
        } else {
            if ($parentId === 'null' || is_null($parentId)) {
                $query->whereNull('parent_id'); // ROOT
            } else {
                $query->where('parent_id', $parentId);
            }
        }

        // ambil semua kategori dulu
        $categories = $query->get();

        // SORT by total_products_count (karena accessor, harus di-collection)
        $categories = $categories->sortByDesc('total_products_count')->values();

        // paginate manual
        $page = $request->get('page', 1);
        $paged = new \Illuminate\Pagination\LengthAwarePaginator(
            $categories->forPage($page, $perPage),
            $categories->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $data = $paged->getCollection()
            ->map(function ($category) use ($parentId, $level) {
                $item = [
                    'id'                   => $category->id,
                    'name'                 => $category->name,
                    'price_admin'                 => $category->price_admin,
                    'parent_id'            => ($level == 3 && $parentId) ? (int) $parentId : $category->parent_id,
                    'products_count'       => $category->products_count,
                    'total_products_count' => $category->total_products_count,
                    'size_guide'           => $category->size_guide,
                    'shipping_information' => $category->shipping_information,
                    'dimensions'           => $category->dimensions,
                    'has_children'         => $category->children()->exists(),
                ];

                if ($category->parent_id === null) {
                    $item['total_children_level_1'] = $category->getChildrenByLevel(1);
                    $item['total_children_level_2'] = $category->getChildrenByLevel(2);
                    $item['total_children_level_3'] = $category->getChildrenByLevel(3);
                } elseif ($category->parent?->parent_id === null) {
                    $item['total_children_level_2'] = $category->getChildrenByLevel(1);
                    $item['total_children_level_3'] = $category->getChildrenByLevel(2);
                } elseif ($category->parent?->parent?->parent_id === null) {
                    $item['total_children_level_3'] = $category->getChildrenByLevel(1);
                }

                // build chain parent
                $chain = [];
                $trace = $category;
                while ($trace) {
                    $chain[] = ['id' => $trace->id, 'name' => $trace->name];
                    $trace   = $trace->parent;
                }
                $chain = array_reverse($chain);

                $parents = [];
                $i = 1;
                foreach ($chain as $p) {
                    $parents["parent_" . $i] = $p;
                    $i++;
                }

                return array_merge($item, $parents);
            })
            ->values() // reset jadi array index numerik
            ->all();   // convert ke array biasa


        // =======================
        // HITUNG TOTAL PER LEVEL
        // =======================
        $totalRoot = ProductCategory::whereNull('parent_id')->count();
        $node = null;
        if ($parentId && $parentId !== 'null') {
            $node = ProductCategory::with('parent.parent.parent')->find($parentId);
        }

        // Cari root ancestor dari node sekarang
        $rootId = null;
        if ($node) {
            $trace = $node;
            while ($trace && $trace->parent_id !== null) {
                $trace = $trace->parent;
            }
            $rootId = $trace?->id; // ID root dari parentId
        }

        $totalLevel1 = 0;
        $totalLevel2 = 0;
        $totalLevel3 = 0;

        if (($parentId === 'null' || is_null($parentId)) && $level > 0) {
            // GLOBAL TANPA PARENT → hitung semua level
            $totalLevel1 = ProductCategory::whereHas('parent', fn($q) => $q->whereNull('parent_id'))->count();
            $totalLevel2 = ProductCategory::whereHas('parent.parent', fn($q) => $q->whereNull('parent_id'))->count();
            $totalLevel3 = ProductCategory::whereHas('parent.parent.parent', fn($q) => $q->whereNull('parent_id'))->count();
        } elseif ($parentId === 'null' || is_null($parentId)) {
            // ROOT (GLOBAL)
            $totalLevel1 = ProductCategory::whereHas('parent', fn($q) => $q->whereNull('parent_id'))->count();
            $totalLevel2 = ProductCategory::whereHas('parent.parent', fn($q) => $q->whereNull('parent_id'))->count();
            $totalLevel3 = ProductCategory::whereHas('parent.parent.parent', fn($q) => $q->whereNull('parent_id'))->count();
        } elseif ($level == 1 && $rootId) {
            $totalLevel1 = ProductCategory::where('parent_id', $rootId)->count();
            $totalLevel2 = ProductCategory::whereHas('parent', fn($q) => $q->where('parent_id', $rootId))->count();
            $totalLevel3 = ProductCategory::whereHas('parent.parent', fn($q) => $q->where('parent_id', $rootId))->count();
        } elseif ($level == 2) {
            if ($node && $node->parent_id === null) {
                // parent_id adalah ROOT → hitung cucu dan cicit
                $totalLevel1 = ProductCategory::where('parent_id', $parentId)->count();
                $totalLevel2 = ProductCategory::whereHas('parent', fn($q) => $q->where('parent_id', $parentId))->count();
                $totalLevel3 = ProductCategory::whereHas('parent.parent', fn($q) => $q->where('parent_id', $parentId))->count();
            } else {
                // parent_id level 1 → level2 = anak dari parent_id, level3 = cicit
                $totalLevel2 = ProductCategory::where('parent_id', $parentId)->count();
                $totalLevel3 = ProductCategory::whereHas('parent', fn($q) => $q->where('parent_id', $parentId))->count();

                // level1 tetap ambil dari root ancestor
                if ($rootId) {
                    $totalLevel1 = ProductCategory::where('parent_id', $rootId)->count();
                }
            }
        } elseif ($level == 3) {
            if ($node && $node->parent_id === null) {
                // parent_id adalah ROOT
                $totalLevel1 = ProductCategory::where('parent_id', $parentId)->count();
                $totalLevel2 = ProductCategory::whereHas('parent', fn($q) => $q->where('parent_id', $parentId))->count();
                $totalLevel3 = ProductCategory::whereHas('parent.parent', fn($q) => $q->where('parent_id', $parentId))->count();
            } else {
                // parent_id level 1 atau 2
                $totalLevel3 = ProductCategory::where('parent_id', $parentId)->count();
                if ($rootId) {
                    $totalLevel1 = ProductCategory::where('parent_id', $rootId)->count();
                    $totalLevel2 = ProductCategory::whereHas('parent', fn($q) => $q->where('parent_id', $rootId))->count();
                }
            }
        }

        $meta = [
            'current_page'  => $paged->currentPage(),
            'per_page'      => $paged->perPage(),
            'total'         => $paged->total(),
            'last_page'     => $paged->lastPage(),
            'total_root'    => $totalRoot,
            'total_level1'  => $totalLevel1,
            'total_level2'  => $totalLevel2,
            'total_level3'  => $totalLevel3,
        ];

        return response()->json([
            'status'  => 'success',
            'message' => 'Kategori ditemukan',
            'data'    => $data,
            'meta'    => $meta,
        ]);
    }




    // Fungsi bantu untuk hitung turunan berdasarkan parent_id & depth
    private function countChildrenByLevel($parentId, $depth = 1)
    {
        if ($depth <= 0) {
            return 0;
        }

        $children = ProductCategory::where('parent_id', $parentId)->pluck('id');

        $count = $children->count();

        if ($depth > 1 && $children->isNotEmpty()) {
            foreach ($children as $childId) {
                $count += $this->countChildrenByLevel($childId, $depth - 1);
            }
        }

        return $count;
    }





    /**
     * Ambil semua id keturunan tepat di level $level di bawah $parentId.
     * Level=1 -> anak langsung, Level=2 -> cucu, Level=3 -> cicit, dst.
     * Implementasi iteratif supaya tidak salah offset walau parentId adalah turunan.
     */
    private function descendantIdsAtLevel(int $parentId, int $level): array
    {
        // frontier = id parent awal
        $currentIds = [$parentId];

        for ($i = 1; $i <= $level; $i++) {
            if (empty($currentIds)) {
                return []; // tidak ada lagi
            }
            // Ambil semua ID anak dari semua ID pada frontier saat ini
            $currentIds = ProductCategory::whereIn('parent_id', $currentIds)->pluck('id')->all();
        }

        // Setelah loop, $currentIds = id pada level target
        return $currentIds;
    }

    /**
     * Helper buat response 200 + meta dari paginator.
     */
    private function okWithMeta(string $message, $data, $paginator)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }



    public function showList()
    {
        $categories = ProductCategory::whereNull('parent_id')
            ->get();
        if ($categories) {
            return $this->utilityService->is200ResponseWithData("Kategori ditemukan", $categories);
        }
    }
    public function showListArray()
    {
        $categories = ProductCategory::whereNull('parent_id')->pluck('name')->toArray();
        $column1 = array_slice($categories, 0, 9);
        $column2 = array_slice($categories, 9, 8);
        $column3 = array_slice($categories, 17); // sisanya
        $categoryColumns = [
            [
                'title' => 'Kategori',
                'items' => $column1,
            ],
            [
                'items' => $column2,
            ],
            [
                'items' => $column3,
            ],
        ];

        if ($categories) {
            return $this->utilityService->is200ResponseWithData("Kategori ditemukan", $categoryColumns);
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:product_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            "name" => $request->name,
            "parent_id" => $request->parent_id,
            "price_admin" => $request->price_admin,
            "size_guide" => $request->size_guide,
            "shipping_information" => $request->shipping_information,
            "dimensions" => $request->dimensions,
        ];

        if ($request->hasFile('image')) {
            $fileName = 'category-' . time() . '.webp';
            // Asumsikan utilityService->convertImageToWebp ada dan berfungsi
            // $image = $this->utilityService->convertImageToWebp($request->file('image'));
            $imageContents = file_get_contents($request->file('image')->getRealPath()); // Placeholder logic

            Storage::disk('minio')->put($fileName, $imageContents);
            $urlImage = Storage::disk('minio')->url($fileName);

            $data['url_image'] = $urlImage;
        }

        $insert = ProductCategory::create($data);

        if ($insert) {
            $success_message = "Product category data successfully added";
            // Ganti dengan response service Anda
            // return response()->json(['message' => $success_message, 'data' => $insert], 201);
            return $this->utilityService->is200Response($success_message);
        } else {
            // Ganti dengan response service Anda
            // return response()->json(['message' => "Problem with server"], 500);
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'parent_id' => 'nullable|exists:product_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = ProductCategory::find($id);

        if (!$category) {
            // Ganti dengan response service Anda
            // return response()->json(['message' => "Category not found!"], 404);
            return $this->utilityService->is404Response("Category not found!");
        }

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            // Hapus gambar lama dari Minio jika ada
            if ($category->url_image) {
                Storage::disk('minio')->delete(basename($category->url_image));
            }

            $fileName = 'category-' . time() . '.webp';
            // $image = $this->utilityService->convertImageToWebp($request->file('image'));
            $imageContents = file_get_contents($request->file('image')->getRealPath()); // Placeholder logic

            Storage::disk('minio')->put($fileName, $imageContents);
            $urlImage = Storage::disk('minio')->url($fileName);

            $data['url_image'] = $urlImage;
        }

        if ($request->has('parent_id') && $id == $request->parent_id) {
            // Ganti dengan response service Anda
            // return response()->json(['message' => "The parent ID must not be the same as the current category ID!"], 422);
            return $this->utilityService->is404Response("The parent ID must not be the same as the current category ID!");
        }

        $category->update($data);

        $success_message = "Product category data successfully updated";
        // Ganti dengan response service Anda
        // return response()->json(['message' => $success_message, 'data' => $category]);
        return $this->utilityService->is200Response($success_message);
    }

    public function delete($id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            // Ganti dengan response service Anda
            // return response()->json(['message' => "Category not found!"], 404);
            return $this->utilityService->is404Response("Category not found!");
        }

        // Hapus gambar dari Minio sebelum menghapus record
        if ($category->url_image) {
            Storage::disk('minio')->delete(basename($category->url_image));
        }

        $delete = $category->delete();

        if ($delete) {
            $success_message = "Product category data successfully deleted";
            // Ganti dengan response service Anda
            // return response()->json(['message' => $success_message]);
            return $this->utilityService->is200Response($success_message);
        } else {
            // Ganti dengan response service Anda
            // return response()->json(['message' => "Problem with server"], 500);
            return $this->utilityService->is500Response("problem with server");
        }
    }
}
