<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDelivery;
use App\Models\ProductMedia;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\ProductVariantPriceComposition;
use App\Models\ProductVariantValue;
use App\Models\ProductWholesale;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Stmt\TryCatch;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ? $request->get('search') : null;
        $category_id = $request->get('category_id') ? $request->get('category_id') : null;
        $sort_order = $request->get('sort_order') ? $request->get('sort_order') : 'desc';
        $sort_by = $request->get('sort_by') ? $request->get('sort_by') : 'created_at';
        $page_size = $request->get('page_size') ? $request->get('page_size') : '10';

        $products = Product::with(['variantPrices.variantValues.variant', 'media'])
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhereHas('variantPrices', function ($q) use ($search) {
                        $q->where('sku', 'like', "%$search%");
                    });
            })
            ->when($category_id, function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            })
            ->orderBy($sort_by, $sort_order)
            ->paginate($page_size);

        $data = $products->getCollection()->map(function ($product) {
            return [
                'product_name' => $product->name,
                'product_price' => $product->price,
                'product_stock' => $product->stock,
                'brand' => $product->specification->brand,
                'country_origin' => $product->specification->country_origin,
                'price' => $product->variantPrices->map(function ($vp) {
                    return [
                        'sku' => $vp->sku,
                        'price' => $vp->price,
                        'stock' => $vp->stock,
                        'image' => $vp->image,
                        'variant_values' => $vp->variantValues->map(function ($vv) {
                            return [
                                'variant_name' => $vv->variant->variant,
                                'value' => $vv->value,
                            ];
                        }),
                    ];
                }),
                'media' => $product->media->map(function ($m) {
                    return [
                        'url' => $m->url,
                        'type' => $m->type,
                        'ordinal' => $m->ordinal,
                    ];
                }),
            ];
        });

        $total = $products->total();
        $limit = $page_size;
        $page = $products->currentPage();

        $meta = [
            'total' => $total,
            'limit' => $limit,
            'page' => $page,
        ];

        return $this->utilityService->is200ResponseWithDataAndMeta($data, $meta);
    }

    public function detail($id)
    {
        $product = Product::with(['specification', 'media', 'variants', 'variantPrices', 'wholesales'])->find($id);

        if (!$product) {
            return $this->utilityService->is404Response('Product not found');
        }

        return $this->utilityService->is200ResponseWithData('Product found', $product);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'desc' => 'required',
            'category_id' => 'required|exists:product_categories,id',
            'seller_id' => 'required',
            'media' => 'required|array',
            'media.*.file' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,avi',
            'media.*.ordinal' => 'required',
            'media.*.type' => 'required',
            'brand' => 'required',
            'country_origin' => 'required',
            'is_customizable' => 'required',
        ]);

        // Validasi ukuran file
        foreach ($request->media as $item) {
            $file = $item['file'];
            $mime = $file->getMimeType();
            $sizeMB = $file->getSize() / 1024 / 1024;

            if (str_starts_with($mime, 'image/') && $sizeMB > 1) {
                return $this->utilityService->is422Response('Image must not exceed 1MB.');
            }

            if (str_starts_with($mime, 'video/') && $sizeMB > 30) {
                return $this->utilityService->is422Response('Video must not exceed 30MB.');
            }
        }

        // Simpan path file yang diunggah untuk rollback jika terjadi error
        $uploadedFilePaths = [];

        try {
            DB::transaction(function () use ($request, &$uploadedFilePaths) {
                $product = Product::create([
                    'name' => $request->name,
                    'desc' => $request->desc,
                    'category_id' => $request->category_id,
                    'seller_id' => $request->seller_id,
                    'min_purchase' => $request->min_purchase,
                    'max_purchase' => $request->max_purchase,
                    'price' => $request->product_price,
                    'stock' => $request->stock,
                    'is_used' => false,
                    'location' => $request->location, // Menambahkan field baru
                ]);

                ProductDelivery::create([
                    'product_id' => $product->id,
                    'weight' => $request->weight,
                    'length' => $request->length,
                    'width' => $request->width,
                    'height' => $request->height,
                    'is_dangerous_product' => $request->is_dangerous_product,
                    'is_pre_order' => $request->is_pre_order,
                    'is_cost_by_seller' => $request->is_cost_by_seller,
                ]);

                ProductSpecification::create([
                    'product_id' => $product->id,
                    'brand' => $request->brand,
                    'country_origin' => $request->country_origin,
                    'is_customizable' => $request->is_customizable,
                ]);

                // Unggah media utama ke Minio
                foreach ($request->media as $key => $item) {
                    $file = $item['file'];

                    // PERBAIKAN: Menggunakan file_get_contents dan getRealPath()
                    $fileContents = file_get_contents($file->getRealPath());
                    $fileName = 'product_media/media-' . time() . '-' . $key . '.webp';

                    Storage::disk('minio')->put($fileName, $fileContents, 'public');
                    $url = Storage::disk('minio')->url($fileName);

                    $uploadedFilePaths[] = $fileName; // Simpan path untuk rollback

                    ProductMedia::create([
                        'product_id' => $product->id,
                        'url' => $url,
                        'ordinal' => $item['ordinal'],
                        'type' => $item['type'],
                    ]);
                }

                // Logika varian (jika ada)
                if ($request->has('variant')) {
                    $variantValueID = [];
                    $hargaTermurah = 0;
                    $totalStock = 0;

                    foreach ($request->variant as $indexVariant => $item) {
                        $variantValueID[$indexVariant] = [];
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'variant' => $item['variant'],
                            'ordinal' => $indexVariant,
                        ]);

                        foreach ($item['value'] as $indexValue => $itemValue) {
                            $variantValue = ProductVariantValue::create([
                                'variant_id' => $variant->id,
                                'value' => $itemValue,
                                'ordinal' => $indexValue,
                            ]);
                            $variantValueID[$indexVariant][] = $variantValue->id;
                        }
                    }

                    // Logika harga varian
                    if ($request->has('price')) {
                        foreach ($request->price as $i => $priceRow) {
                            foreach ($priceRow as $j => $priceData) {
                                $urlPrice = null;
                                // Unggah gambar varian ke Minio
                                if (isset($priceData['image'])) {
                                    $file = $priceData['image'];
                                    $fileName = 'product_media/variant/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                                    $fileContents = file_get_contents($file->getRealPath());

                                    Storage::disk('minio')->put($fileName, $fileContents, 'public');
                                    $urlPrice = Storage::disk('minio')->url($fileName);

                                    $uploadedFilePaths[] = $fileName;
                                }

                                $price = ProductVariantPrice::create([
                                    'product_id' => $product->id,
                                    'price' => $priceData['price'],
                                    'stock' => $priceData['stock'],
                                    'image' => $urlPrice,
                                    'variant_code' => $priceData['variant_code'] ?? null,
                                ]);

                                if (($i == 0 && $j == 0) || $hargaTermurah > $priceData['price']) {
                                    $hargaTermurah = $priceData['price'];
                                }
                                $totalStock += $priceData['stock'];

                                // Komposisi varian
                                ProductVariantPriceComposition::create([
                                    'product_variant_price_id' => $price->id,
                                    'product_variant_value_id' => $variantValueID[0][$i],
                                ]);
                                if (isset($variantValueID[1])) {
                                    ProductVariantPriceComposition::create([
                                        'product_variant_price_id' => $price->id,
                                        'product_variant_value_id' => $variantValueID[1][$j],
                                    ]);
                                }
                            }
                        }
                    }

                    // Update harga dan stok produk utama
                    $product->stock = $totalStock;
                    $product->price = $hargaTermurah;
                    $product->save();
                }

                // Logika Grosir
                if ($request->has('wholesale')) {
                    foreach ($request->wholesale as $item) {
                        ProductWholesale::create([
                            'product_id' => $product->id,
                            'wholesale_min_quantity' => $item['min_quantity'],
                            'wholesale_max_quantity' => $item['max_quantity'],
                            'wholesale_price' => $item['price'],
                        ]);
                    }
                }
            });

            return $this->utilityService->is200Response('Product successfully added');
        } catch (\Exception $e) {
            // Rollback: Hapus semua file yang sudah terunggah jika transaksi gagal
            foreach ($uploadedFilePaths as $path) {
                Storage::disk('minio')->delete($path);
            }

            // Menggunakan utilityService untuk response error
            return $this->utilityService->is500Response('An error occurred: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'desc' => 'required',
            'category_id' => 'required|exists:product_categories,id',
            'seller_id' => 'required',

            // media
            'media' => 'required|array',
            'media.*.ordinal' => 'required',
            'media.*.type' => 'required',

            // specification
            'brand' => 'required',
            'country_origin' => 'required',
            'is_customizable' => 'required',


        ]);


        foreach ($request->media as $item) {
            if (isset($item['file'])) {
                $file = $item['file'];
                $mime = $file->getMimeType();
                $sizeMB = $file->getSize() / 1024 / 1024; // convert to MB

                if (str_starts_with($mime, 'image/') && $sizeMB > 1) {
                    return $this->utilityService->is422Response('Image must not exceed 1MB.');
                }

                if (str_starts_with($mime, 'video/') && $sizeMB > 30) {
                    return $this->utilityService->is422Response('Video must not exceed 30MB.');
                }
            }
        }

        $filePathSaved = [];

        $countVariantArr = [];
        if ($request->variant) {
            $countVariantArr[0] = count($request->variant[0]['value']);
            $countVariantArr[1] = count($request->variant) === 1 ? 0 : count($request->variant[1]['value']);
        }

        // dd($countVariantArr);

        // try {
        DB::transaction(function () use ($request, &$filePathSaved, $countVariantArr, $id) {
            $product = Product::find($id);

            $product['name'] = $request->name;
            $product['desc'] = $request->desc;
            $product['category_id'] = $request->category_id;
            $product['min_purchase'] = $request->min_purchase;
            $product['max_purchase'] = $request->max_purchase;
            $product['price'] = $request->product_price;
            $product['stock'] = $request->stock;

            ProductSpecification::updateOrCreate(
                ['product_id' => $product->id,],
                [
                    'brand' => $request->brand,
                    'country_origin' => $request->country_origin,
                    'is_customizable' => $request->is_customizable,
                ]
            );

            // Step 1: Ambil media ID dari request (hanya media lama)
            $mediaIdFromRequest = [];
            foreach ($request->media ?? [] as $item) {
                if (isset($item['id'])) {
                    $mediaIdFromRequest[] = $item['id'];
                }
            }

            // Step 2: Ambil semua media lama dari database
            $existingMedia = ProductMedia::where('product_id', $id)->get();

            // Step 3: DELETE - Media yang dihapus (tidak dikirim lagi)
            $mediaToDelete = $existingMedia->filter(function ($item) use ($mediaIdFromRequest) {
                return !in_array($item->id, $mediaIdFromRequest);
            });

            foreach ($mediaToDelete as $media) {
                // Storage::delete($media->file_path); // Hapus file
                $media->delete();                   // Hapus record dari DB
            }

            // Step 4: LOOP semua media dari request
            foreach ($request->media ?? [] as $item) {
                // CASE A: Update media lama
                if (isset($item['id'])) {
                    $media = ProductMedia::find($item['id']);
                    if ($media) {
                        if ($item['file']) {
                            $path = $item['file']->store('product_media', 'my_public_uploads');
                            $media->url = $path;
                        }
                        $media->ordinal = $item['ordinal'];
                        $media->save();
                    }
                }
                // CASE B: Create media baru
                else if (isset($item['file']) && $item['file']->isValid()) {
                    $path = $item['file']->store('product_media', 'my_public_uploads');

                    ProductMedia::create([
                        'product_id' => $id,
                        'url' => $path,
                        'ordinal' => $item['ordinal'],
                    ]);
                }
            }

            $variantIdFromRequest = [];
            if (!empty($request->variant) && is_array($request->variant)) {
                foreach ($request->variant as $item) {
                    if (isset($item['id'])) {
                        $variantIdFromRequest[] = $item['id'];
                    }
                }
            }

            $hargaTermurah = 0;
            $totalStock = 0;

            $variantValueID = [];

            $existingVariant = ProductVariant::where('product_id', $id)->get();

            $variantToDelete = $existingVariant->filter(function ($item) use ($variantIdFromRequest) {
                return !in_array($item->id, $variantIdFromRequest);
            });

            foreach ($variantToDelete as $variant) {
                $variant->delete();
            }

            if (!empty($request->variant) && is_array($request->variant)) {
                foreach ($request->variant as $indexVariant => $item) {
                    $variantValueID[$indexVariant] = [];
                    // update
                    if (isset($item['id'])) {
                        $variant = ProductVariant::find($item['id']);
                        if ($variant) {
                            $variant->variant = $item['variant'];
                            $variant->ordinal = $indexVariant;
                            $variant->save();
                        }

                        $variantValueIdFromRequest = [];
                        foreach ($item->value as $itemValue) {
                            if (isset($itemValue['id'])) {
                                $variantValueIdFromRequest[] = $itemValue['id'];
                            }
                        }

                        $existingVariantValue = ProductVariantValue::where('variant_id', $item['id'])->get();

                        $variantValueToDelete = $existingVariantValue->filter(function ($item) use ($variantValueIdFromRequest) {
                            return !in_array($item->id, $variantValueIdFromRequest);
                        });

                        foreach ($variantValueToDelete as $value) {
                            $value->delete();
                        }

                        foreach ($item->value as $indexValue => $itemValue) {
                            // update
                            if (isset($item['id'])) {
                                $value = ProductVariantValue::find($itemValue['id']);
                                if ($value) {
                                    $value->value = $itemValue['value'];
                                    $value->ordinal = $indexValue;
                                    $value->save();
                                }
                            } else {
                                $value = ProductVariantValue::create([
                                    'variant_id' => $item['id'],
                                    'value' => $itemValue['value'],
                                    'ordinal' => $indexValue,
                                ]);
                            }
                            $variantValueID[$indexVariant][] = $value->id;
                        }
                    }
                    // create
                    else {
                        $variantValueID[$indexVariant] = [];

                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'variant' => $item['variant'],
                            'ordinal' => $indexVariant,
                        ]);
                        // dd('asd');

                        foreach ($item['value'] as $indexValue => $itemValue) {
                            $variantValue = ProductVariantValue::create([
                                'variant_id' => $variant->id,
                                'value' => $itemValue,
                                'ordinal' => $indexValue,
                            ]);
                            $variantValueID[$indexVariant][] = $variantValue->id;
                            // dd($variantValue->id);
                        }
                    }
                }
            }

            // Proses update atau create variant price
            foreach ($request->price ?? [] as $item) {
                if (isset($item['id'])) {
                    // update
                    $price = ProductVariantPrice::find($item['id']);
                    if ($price) {
                        $price->price = $item['price'];
                        $price->stock = $item['stock'] ?? null;
                        $price->save();

                        // Update compositions
                        $price->compositions()->delete(); // hapus semua dulu
                        foreach ($item['variant_value_ids'] as $variantValueId) {
                            ProductVariantPriceComposition::create([
                                'product_variant_price_id' => $price->id,
                                'variant_value_id' => $variantValueId,
                            ]);
                        }
                    }
                } else {
                    // create
                    $price = ProductVariantPrice::create([
                        'product_id' => $product->id,
                        'price' => $item['price'],
                        'stock' => $item['stock'] ?? null,
                    ]);

                    foreach ($item['variant_value_ids'] as $variantValueId) {
                        ProductVariantPriceComposition::create([
                            'product_variant_price_id' => $price->id,
                            'variant_value_id' => $variantValueId,
                        ]);
                    }
                }
            }

            if (isset($request->price)) {
                for ($i = 0; $i < $countVariantArr[0]; $i++) {
                    $j = 0;
                    do {
                        $url = null;
                        if (isset($request->price[$i][$j]['image'])) {
                            $path = $request->price[$i][$j]['image']->store('product_media', 'my_public_uploads');
                            $url = env('APP_URL') . 'upload/' . $path;
                        }

                        if (isset($request->price[$i][$j]['id'])) {
                            // update
                            $price = ProductVariantPrice::find($request->price[$i][$j]['id']);
                            $price->price = $item['price'];
                            $price->stock = $item['stock'];
                            $price->image = $url;
                            $price->variant_code = isset($request->price[$i][$j]['variant_code']) ? $request->price[$i][$j]['variant_code'] : null;
                            $price->save();
                        } else {
                            // create
                            $price = ProductVariantPrice::create([
                                'product_id' => $product->id,
                                'price' => $request->price[$i][$j]['price'],
                                'stock' => $request->price[$i][$j]['stock'],
                                'image' => $url,
                                'varaint_code' => isset($request->price[$i][$j]['variant_code']) ? $request->price[$i][$j]['variant_code'] : null,
                            ]);

                            // create composition
                            ProductVariantPriceComposition::create([
                                'product_variant_price_id' => $price->id,
                                'product_variant_value_id' => $variantValueID[0][$i],
                            ]);
                            if ($countVariantArr[1] > 0) {
                                ProductVariantPriceComposition::create([
                                    'product_variant_price_id' => $price->id,
                                    'product_variant_value_id' => $variantValueID[1][$j],
                                ]);
                            }
                        }



                        if (($i == 0 && $j == 0) || $hargaTermurah > $request->price[$i][$j]['price']) {
                            $hargaTermurah = $request->price[$i][$j]['price'];
                        }

                        $totalStock += $request->price[$i][$j]['stock'];

                        $j++;
                    } while ($j < $countVariantArr[1]);
                }
            }


            if ($hargaTermurah !== 0 && $totalStock !== 0) {
                $product->stock = $totalStock;
                $product->price = $hargaTermurah;
                $product->save();
            }

            // Step 1: Ambil wholesale ID dari request (hanya wholesale lama)
            $wholesaleIdFromRequest = [];
            foreach ($request->wholesale ?? [] as $item) {
                if (isset($item['id'])) {
                    $wholesaleIdFromRequest[] = $item['id'];
                }
            }

            // Step 2: Ambil semua wholesale lama dari database
            $existingWholesale = ProductWholesale::where('product_id', $id)->get();

            // Step 3: DELETE - wholesale yang dihapus (tidak dikirim lagi)
            $wholesaleToDelete = $existingWholesale->filter(function ($item) use ($wholesaleIdFromRequest) {
                return !in_array($item->id, $wholesaleIdFromRequest);
            });

            foreach ($wholesaleToDelete as $wholesale) {
                $wholesale->delete();
            }

            // Step 4: LOOP semua wholesale dari request
            foreach ($request->wholesale ?? [] as $item) {
                // CASE A: Update wholesale lama
                if (isset($item['id'])) {
                    $wholesale = ProductWholesale::find($item['id']);
                    if ($wholesale) {
                        $wholesale->wholesale_min_quantity = $item['wholesale_min_quantity'];
                        $wholesale->wholesale_max_quantity = $item['wholesale_max_quantity'];
                        $wholesale->wholesale_price = $item['wholesale_price'];
                        $wholesale->save();
                    }
                }
                // CASE B: Create wholesale baru
                else if (isset($item['file']) && $item['file']->isValid()) {
                    ProductWholesale::create([
                        'product_id' => $product->id,
                        'wholesale_min_quantity' => $item['min_quantity'],
                        'wholesale_max_quantity' => $item['max_quantity'],
                        'wholesale_price' => $item['price'],
                    ]);
                }
            }

            $variantPrices = ProductVariantPrice::where('product_id', $product->id)->get();

            foreach ($variantPrices as $price) {
                $compositionCount = ProductVariantPriceComposition::where('product_variant_price_id', $price->id)->count();
                if ($compositionCount === 0) {
                    $price->delete();
                }
            }
        });

        return $this->utilityService->is200Response('Product successfully updated');
        // } catch (\Exception $e) {
        //     // foreach ($filePathSaved as $path) {
        //     //     Storage::disk('public')->delete($path);
        //     // }

        //     return response()->json([
        //         'status' => 'error',
        //         'reason' => $e->getMessage(),
        //         'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        //     ], 500);
        // }
    }

    public function delete($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->utilityService->is404Response("Product not found!");
        }

        if ($product->delete()) {
            $success_message = "Product successfully deleted";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
}
