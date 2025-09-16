<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// Asumsikan Anda memiliki UtilityService yang di-inject
// use App\Services\UtilityService; 

class BannerController extends Controller
{
    // protected $utilityService;

    // public function __construct(UtilityService $utilityService)
    // {
    //     $this->utilityService = $utilityService;
    // }

    /**
     * Get all banners, ordered by 'order' field.
     * GET /banners
     */
    public function index(Request $request)
    {

        $query = Banner::join('admins', 'admins.id', '=', 'banners.admin_id')
            ->select(
                'banners.*',
                'admins.name'
            )->where('banners.is_active', 1)
            ->orderBy('order', 'asc')->get();


        return $query
            ? response()->json([
                'status'  => 'success',
                'message' => 'Data Banner ditemukan',
                'data'    => $query,           // pagination info
            ])
            : $this->utilityService->is404Response("Data Banner tidak ditemukan");
    }

    public function List(Request $request)
    {
        $perPage   = $request->get('per_page', 10); // default 10 item per halaman
        $search    = $request->get('search');       // kata kunci pencarian
        $is_active = $request->get('is_active');    // filter status aktif

        $query = Banner::join('admins', 'admins.id', '=', 'banners.admin_id')
            ->select(
                'banners.*',
                'admins.name'
            )
            ->orderBy('order', 'asc');

        // filter pencarian (misalnya berdasarkan title/description)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('banners.title', 'like', "%{$search}%")
                    ->orWhere('banners.description', 'like', "%{$search}%")
                    ->orWhere('admins.name', 'like', "%{$search}%");
            });
        }

        // filter berdasarkan is_active
        if ($is_active !== null && $is_active !== '') {
            $query->where('banners.is_active', $is_active);
        }

        // lakukan paginate sekali saja
        $banners = $query->paginate($perPage);

        // meta dari paginator
        $meta = [
            'current_page' => $banners->currentPage(),
            'per_page'     => $banners->perPage(),
            'total'        => $banners->total(),
            'last_page'    => $banners->lastPage(),
        ];

        return $banners->total() > 0
            ? response()->json([
                'status'  => 'success',
                'message' => 'Data Banner ditemukan',
                'data'    => $banners->items(),
                'meta'    => $meta,
            ])
            : $this->utilityService->is404Response("Data Banner tidak ditemukan");
    }



    /**
     * Create a new banner with image upload.
     * POST /banners
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp', // Validasi untuk file gambar
            'target_url' => 'nullable|url',
            'is_active' => 'sometimes|boolean',
            'order' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except('image');
        $data['admin_id'] = Auth::id();
        if ($request->hasFile('image')) {
            $fileName = 'banner-' . time() . '.webp';
            // Asumsikan utilityService->convertImageToWebp ada dan berfungsi
            // $image = $this->utilityService->convertImageToWebp($request->file('image'));
            $image = $request->file('image')->get(); // Placeholder logic

            Storage::disk('minio')->put($fileName, $image);
            $urlImage = Storage::disk('minio')->url($fileName);

            $data['image_url'] = $urlImage;
        }

        $banner = Banner::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Banner created successfully',
            'data' => $banner,
        ], 201);
    }

    /**
     * Get a single banner by its ID.
     * GET /banners/{id}
     */
    public function show($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json(['status' => 'error', 'message' => 'Banner not found'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $banner]);
    }

    /**
     * Update a banner by its ID with image upload.
     * PUT /banners/{id}
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json(['status' => 'error', 'message' => 'Banner not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp', // Validasi gambar opsional
            'target_url' => 'nullable|url',
            'is_active' => 'sometimes|boolean',
            'order' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except('image');
        $data['admin_id'] = Auth::id();
        if ($request->hasFile('image')) {
            // Hapus gambar lama dari Minio jika ada
            if ($banner->image_url) {
                Storage::disk('minio')->delete(basename($banner->image_url));
            }

            // Unggah gambar baru
            $fileName = 'banner-' . time() . '.webp';
            // $image = $this->utilityService->convertImageToWebp($request->file('image'));
            $image = $request->file('image')->get(); // Placeholder logic

            Storage::disk('minio')->put($fileName, $image);
            $urlImage = Storage::disk('minio')->url($fileName);

            $data['image_url'] = $urlImage;
        }

        $banner->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Banner updated successfully',
            'data' => $banner,
        ]);
    }
    public function isActive(Request $request, $id)
    {
        $banner = Banner::find($id);

        $banner->is_active = $request->is_active;
        $banner->admin_id = Auth::id();

        $banner->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Banner updated successfully',
            'data' => $banner,
        ]);
    }

    /**
     * Delete a banner by its ID.
     * DELETE /banners/{id}
     */
    public function destroy($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json(['status' => 'error', 'message' => 'Banner not found'], 404);
        }

        // Hapus gambar dari Minio sebelum menghapus record
        if ($banner->image_url) {
            Storage::disk('minio')->delete(basename($banner->image_url));
        }

        $banner->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Banner deleted successfully',
        ]);
    }
}
