<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use App\Models\RequermentProduct;
use App\Models\RequermentShops;
use App\Models\ShopProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// Asumsikan Anda memiliki UtilityService yang di-inject
// use App\Services\UtilityService; 

class RequermentController extends Controller
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

    /**
     * Create a new banner with image upload.
     * POST /banners
     */
    public function store(Request $request, $seller_id, $user_id)
    {
        $dataSellerRequirment = RequermentShops::where('shop_profil_id', $seller_id)->first();
        $shopProfil = ShopProfile::find($seller_id);
        if ($request->status) {
            $shopProfil->status = $request->status;
            $shopProfil->verificator = $user_id;
            $shopProfil->save();
        }
        if ($dataSellerRequirment) {
            // Ambil data lama
            $requerment = RequermentShops::find($dataSellerRequirment->id);
            // Update hanya jika request punya field (tidak null)
            if ($request->has('logo')) $requerment->logo = $request->logo;
            if ($request->has('namaToko')) $requerment->name_shop = $request->namaToko;
            if ($request->has('alamatPickup')) $requerment->address_pickup = $request->alamatPickup;
            if ($request->has('rekening')) $requerment->rekening = $request->rekening;
            if ($request->has('selfie')) $requerment->selfie = $request->selfie;
            if ($request->has('nomorNik')) $requerment->nik = $request->nomorNik;
            if ($request->has('fotoKtp')) $requerment->ktp = $request->fotoKtp;
            if ($request->has('namaLengkap')) $requerment->full_name = $request->namaLengkap;
            if ($request->has('jenisUsaha')) $requerment->type_shop = $request->jenisUsaha;
            if ($request->has('deskripsiToko')) $requerment->desc_shop = $request->deskripsiToko;
            if ($request->has('nomorHandphone')) $requerment->number_phone = $request->nomorHandphone;
            if ($request->has('email')) $requerment->email = $request->email;
            if ($request->has('noted')) $requerment->noted = $request->noted;

            $requerment->verificator = $user_id;

            if ($requerment->save()) {
                return $this->utilityService->is200Response("Requirement berhasil diupdate");
            }
            return $this->utilityService->is500Response("Problem with server");
        } else {
            // Kalau belum ada → buat baru
            $data = [
                'shop_profil_id' => $seller_id,
                'verificator' => $user_id,
                'logo' => $request->logo,
                'name_shop' => $request->namaToko,
                'address_pickup' => $request->alamatPickup,
                'rekening' => $request->rekening,
                'selfie' => $request->selfie,
                'nik' => $request->nomorNik,
                'ktp' => $request->fotoKtp,
                'full_name' => $request->namaLengkap,
                'type_shop' => $request->jenisUsaha,
                'desc_shop' => $request->deskripsiToko,
                'number_phone' => $request->phone_number,
                'email' => $request->email,
                'noted' => $request->noted,
            ];

            $requirment = RequermentShops::create($data);
            if ($requirment) {
                return $this->utilityService->is200Response("Requirement berhasil ditambahkan");
            }
            return $this->utilityService->is500Response("Problem with server");
        }
    }
    public function updateProduct(Request $request, $product_id, $user_id)
    {
        $product = Product::find($product_id);
        $dataProductRequirement = RequermentProduct::where('product_id', $product_id)->first();

        // === Update status product (jika ada) ===
        if ($request->has('status')) {
            $product->status = $request->status;
            $product->verificator = $user_id;
            $product->save();
        }

        // Helper untuk ubah "ya/tidak" jadi 1/0/null
        $parseYesNo = function ($value) {
            return $value === 'ya' ? 1 : ($value === 'tidak' ? 0 : null);
        };

        $dataUpdate = [];
        if ($request->has('media')) $dataUpdate['image_product'] = $request->media;
        if ($request->has('promoImage')) $dataUpdate['promo_image'] = $parseYesNo($request->promoImage);
        if ($request->has('imageVariant')) {
            $dataUpdate['image_variant'] = is_string($request->imageVariant)
                ? json_decode($request->imageVariant, true) // kalau string JSON
                : $request->imageVariant;                   // kalau array
        }

        if ($request->has('price')) $dataUpdate['price'] = $parseYesNo($request->price);
        if ($request->has('delivery')) $dataUpdate['delivery'] = $parseYesNo($request->delivery);
        if ($request->has('shipping')) $dataUpdate['shipping'] = $parseYesNo($request->shipping);
        if ($request->has('productName')) $dataUpdate['name_product'] = $parseYesNo($request->productName);
        if ($request->has('productDesc')) $dataUpdate['desc_product'] = $parseYesNo($request->productDesc);
        if ($request->has('productVariant')) $dataUpdate['variant_product'] = $parseYesNo($request->productVariant);
        if ($request->has('pickupAddress')) $dataUpdate['address_pickup_product'] = $parseYesNo($request->pickupAddress);
        if ($request->has('guideImage')) $dataUpdate['image_guide'] = $parseYesNo($request->guideImage);
        if ($request->has('video')) $dataUpdate['video'] = $parseYesNo($request->video);
        if ($request->has('noted')) $dataUpdate['noted'] = $request->noted;

        $dataUpdate['verificator'] = $user_id;

        // === Jika sudah ada requirement → update ===
        if ($dataProductRequirement) {
            $updated = $dataProductRequirement->update($dataUpdate);

            if ($updated) {
                return $this->utilityService->is200Response("Requirement berhasil diupdate");
            }
            return $this->utilityService->is500Response("Problem with server");
        }

        // === Jika belum ada → create baru ===
        $dataUpdate['product_id'] = $product_id;
        $created = RequermentProduct::create($dataUpdate);

        if ($created) {
            return $this->utilityService->is200Response("Requirement berhasil ditambahkan");
        }
        return $this->utilityService->is500Response("Problem with server");
    }



    /**
     * Get a single banner by its ID.
     * GET /banners/{id}
     */

    /**
     * Update a banner by its ID with image upload.
     * PUT /banners/{id}
     */


    /**
     * Delete a banner by its ID.
     * DELETE /banners/{id}
     */
}
