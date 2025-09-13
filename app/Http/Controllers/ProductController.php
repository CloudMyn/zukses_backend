<?php

namespace App\Http\Controllers;

use App\Helpers\UrlRemove;
use App\Models\MasterCity;
use App\Models\MasterPostalCode;
use App\Models\MasterProvince;
use App\Models\MasterSubdistrict;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDelivery;
use App\Models\ProductMedia;
use App\Models\ProductPromotion;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;
use App\Models\ProductVariantDelivery;
use App\Models\ProductVariantPrice;
use App\Models\ProductVariantPriceComposition;
use App\Models\ProductVariantValue;
use App\Models\ProductWholesale;
use App\Models\RequermentProduct;
use App\Models\ShopAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Exception;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $categori_id = $request->get('categorie_id');
            $seller_id = $request->get('seller_id');
            $search = $request->get('search');
            $province_id = $request->get('province_id');
            $condition = $request->get('condition', []);
            $payment = $request->get('payment', []);
            $perPage = (int) $request->get('per_page', 10);
            $perPageShop = (int) $request->get('per_page_shop', 10);

            $productsQuery = Product::with([
                'category.parent.parent.parent',
                'specifications',
                'variants.values',
                'media',
                'variantPrices.compositions.value.variant',
                'delivery',
                'shopProfile.shopAddresses.province',
                'shopProfile.shopAddresses.cities',
                'variantPrices.promotion',
                'promotion',
            ])
                ->when($categori_id, fn($q) => $q->where('category_id', $categori_id))
                ->when($seller_id, fn($q) => $q->where('seller_id', $seller_id))
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhereHas('category', function ($q1) use ($search) {
                                $q1->where('name', 'like', '%' . $search . '%');
                                $parentLevel = 'parent';
                                for ($i = 0; $i < 10; $i++) {
                                    $q1->orWhereHas($parentLevel, function ($subQ) use ($search) {
                                        $subQ->where('name', 'like', '%' . $search . '%');
                                    });
                                    $parentLevel .= '.parent';
                                }
                            })
                            ->orWhereHas('shopProfile', function ($q5) use ($search) {
                                $q5->where('shop_name', 'like', '%' . $search . '%');
                            });
                    });
                })
                ->when(!empty($province_id), function ($query) use ($province_id) {
                    $query->whereHas('shopProfile.shopAddresses', function ($q) use ($province_id) {
                        if (is_array($province_id)) {
                            $q->whereIn('province_id', $province_id);
                        } else {
                            $q->where('province_id', $province_id);
                        }
                    });
                })
                ->when(!empty($condition), function ($q) use ($condition) {
                    if (is_array($condition)) {
                        $q->whereIn('is_used', $condition);
                    } else {
                        $q->where('is_used', $condition);
                    }
                })
                ->when(!empty($payment), function ($q) use ($payment) {
                    if (is_array($payment)) {
                        $q->whereIn('is_cod_enabled', $payment);
                    } else {
                        $q->where('is_cod_enabled', $payment);
                    }
                })
                ->orderByDesc('id');

            $products = $productsQuery->paginate($perPage);

            $buildCategoryPath = function ($category) {
                if (!$category) return null;
                $path = [$category->name];
                while ($category->parent) {
                    $category = $category->parent;
                    array_unshift($path, $category->name);
                }
                return implode(' > ', $path);
            };

            // Mapping produk (tetap sama)
            $result = $products->getCollection()->map(function ($product) use ($buildCategoryPath) {
                $variants = $product->variants->map(fn($v) => [
                    'id' => $v->id,
                    'variant' => $v->variant,
                    'options' => $v->values->pluck('value'),
                ]);

                $combinations = $product->variantPrices->map(function ($vp) {
                    $comb = [];
                    foreach ($vp->compositions as $comp) {
                        $variantName = $comp->value->variant->variant ?? null;
                        $optionValue = $comp->value->value ?? null;
                        $comb[$variantName] = $optionValue;
                    }
                    return [
                        'sku' => $vp->variant_code,
                        'id' => $vp->id,
                        'price' => $vp->price,
                        'stock' => $vp->stock,
                        'image' => $vp->image,
                        'combination' => $comb,
                        'combination_label' => implode(' - ', array_values($comb)),
                        'discount_price' => $vp->promotion?->discount_price,
                        'discount_percent' => $vp->promotion?->discount_percent,
                    ];
                });

                $specs = $product->specifications->pluck('value', 'label')->toArray();
                $categoryPath = $buildCategoryPath($product->category);
                $specifications = array_merge(['Kategori' => $categoryPath], $specs);

                $shopProfile = $product->shopProfile;
                $addresses = $shopProfile?->shopAddresses ?? collect();
                $address = $addresses->firstWhere('is_primary', 1) ?? $addresses->first();
                $locationName = $address?->cities?->name ?? $address?->province?->name ?? '-';

                $seller = [
                    'name' => $shopProfile->shop_name ?? '-',
                    'avatarUrl' => $shopProfile->logo_url ?? '',
                    'lastActive' => '28 Menit Lalu',
                    'location' => $locationName,
                    'stats' => [
                        'reviews' => '1,1RB',
                        'rating' => 5,
                        'reviewsCount' => 300,
                        'products' => 93,
                        'chatResponseRate' => '98%',
                        'chatResponseTime' => 'hitungan jam',
                        'joined' => '8 tahun lalu',
                        'followers' => '5,4RB'
                    ]
                ];

                return [
                    'category_id' => $product->category_id,
                    'desc' => $product->desc,
                    'id' => $product->id,
                    'image' => $product->image,
                    'is_used' => $product->is_used,
                    'max_purchase' => $product->max_purchase,
                    'media' => $product->media,
                    'min_purchase' => $product->min_purchase,
                    'name' => $product->name,
                    'is_cod_enabled' => $product->is_cod_enabled,
                    'price' => $product->price,
                    'seller_id' => $product->seller_id,
                    'scheduled_date' => $product->scheduled_date,
                    'sku' => $product->sku,
                    'specifications' => $specifications,
                    'stock' => $product->stock,
                    'seller' => $seller,
                    'category' => $categoryPath,
                    'variants' => $combinations,
                    'delivery' => $product->delivery,
                    'voucher' => $product->voucher,
                    'variant_prices' => $variants,
                    'combinations' => $combinations,
                    'rate'      => rand(1, 5),
                    'reviewers' => rand(150, 6000),
                    'bought'    => rand(30, 1000),
                    'viewed'    => rand(4000, 10000),
                ];
            });

            // Buat paginate untuk shop
            $shops = $products->getCollection()->map(function ($product) {
                $shopProfile = $product->shopProfile;
                $addresses = $shopProfile?->shopAddresses ?? collect();
                $address = $addresses->firstWhere('is_primary', 1) ?? $addresses->first();
                $locationName = $address?->cities?->name ?? $address?->province?->name ?? '-';

                return [
                    'id' => $shopProfile->id ?? null,
                    'name' => $shopProfile->shop_name ?? '-',
                    'image' => $shopProfile->logo_url ?? '-',
                    'kota' => $locationName,
                ];
            })->unique('id')->values();

            // Manual paginate untuk shop
            $page = (int) $request->get('page_shop', 1);
            $offset = ($page - 1) * $perPageShop;
            $shopsPaginated = $shops->slice($offset, $perPageShop)->values();

            return response()->json([
                'status' => 'success',
                'message' => 'Daftar produk berhasil diambil.',
                'products' => [
                    'paginate' => [
                        'total' => $products->total(),
                        'per_page' => $products->perPage(),
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                    ],
                    'data' => $result,
                ],
                'shops' => [
                    'paginate' => [
                        'total' => $shops->count(),
                        'per_page' => $perPageShop,
                        'current_page' => $page,
                        'last_page' => ceil($shops->count() / $perPageShop),
                    ],
                    'data' => $shopsPaginated,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal mengambil daftar produk: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data produk.'
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $product = Product::with([
                'category.parent.parent',
                'specifications',
                'variants.values',
                'media',
                'variantPrices.compositions.value.variant',
                'delivery',
                'shopProfile.shopAddresses.province',
                'shopProfile.shopAddresses.cities',
                'variantPrices.promotion',
                'promotion',
            ])->where('products.id', $id)->first();

            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Produk tidak ditemukan.'
                ], 404);
            }

            // Build full category path
            $buildCategoryPath = function ($category) {
                if (!$category) return null;
                $path = [$category->name];
                while ($category->parent) {
                    $category = $category->parent;
                    array_unshift($path, $category->name);
                }
                return implode(' > ', $path);
            };

            $variants = $product->variants->map(fn($v) => [
                'id' => $v->id,
                'variant' => $v->variant,
                'options' => $v->values->pluck('value'),
            ]);

            $combinations = $product->variantPrices->map(function ($vp) {
                $comb = [];
                foreach ($vp->compositions as $comp) {
                    $variantName = $comp->value->variant->variant ?? null;
                    $optionValue = $comp->value->value ?? null;
                    $comb[$variantName] = $optionValue;
                }
                return [
                    'sku' => $vp->variant_code,
                    'id' => $vp->id,
                    'price' => $vp->price,
                    'stock' => $vp->stock,
                    'image' => $vp->image,
                    'combination' => $comb,
                    'combination_label' => implode(' - ', array_values($comb)),
                    'discount_price' => $vp->promotion?->discount_price,
                    'discount_percent' => $vp->promotion?->discount_percent,
                ];
            });

            $specs = $product->specifications->pluck('value', 'label')->toArray();
            $categoryPath = $buildCategoryPath($product->category);
            $specifications = array_merge(['Kategori' => $categoryPath], $specs);

            $shopProfile = $product->shopProfile;
            $addresses = $shopProfile?->shopAddresses ?? collect();
            $address = $addresses->firstWhere('is_primary', 1) ?? $addresses->first();

            $locationName = $address?->cities?->name ?? $address?->province?->name ?? '-';

            $seller = [
                'name' => $shopProfile->shop_name ?? '-',
                'avatarUrl' => $shopProfile->logo_url ?? '',
                'lastActive' => '28 Menit Lalu',
                'location' => $locationName,
                'stats' => [
                    'reviews' => '1,1RB',
                    'rating' => 5,
                    'reviewsCount' => 300,
                    'products' => 93,
                    'chatResponseRate' => '98%',
                    'chatResponseTime' => 'hitungan jam',
                    'joined' => '8 tahun lalu',
                    'followers' => '5,4RB'
                ]
            ];

            $reviews = [/* ... dummy reviews ... */];
            $vouchers = ['Diskon Rp10RB', 'Diskon Rp20RB', 'Cashback 5%'];

            $data = [
                'category_id' => $product->category_id,
                'desc' => $product->desc,
                'id' => $product->id,
                'image' => $product->image,
                'is_used' => $product->is_used,
                'max_purchase' => $product->max_purchase,
                'media' => $product->media,
                'min_purchase' => $product->min_purchase,
                'name' => $product->name,
                'is_cod_enabled' => $product->is_cod_enabled,
                'price' => $product->price,
                'seller_id' => $product->seller_id,
                'scheduled_date' => $product->scheduled_date,
                'sku' => $product->sku,
                'specifications' => $specifications,
                'stock' => $product->stock,
                'seller' => $seller,
                'category' => $categoryPath,
                'variants' => $combinations,
                'delivery' => $product->delivery,
                'variant_prices' => $variants,
                'combinations' => $combinations,
                'reviews' => $reviews,
                'vouchers' => $vouchers,
                'voucher' => $product->voucher,
            ];

            if ($product->promotion?->discount_price) {
                $data['discount_price'] = $product->promotion->discount_price;
            }
            if ($product->promotion?->discount_percent) {
                $data['discount_percent'] = $product->promotion->discount_percent;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Detail produk berhasil diambil.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal mengambil detail produk: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data produk.'
            ], 500);
        }
    }



    public function list()
    {
        try {
            $shopeProfile = DB::table('shop_profiles')->where('user_id', auth()->user()->id)->first();
            $products = Product::with([
                'category.parent.parent', // Load kategori hingga grandparent
                'specifications',
                'variants.values',
                'media',
                'variantPrices.compositions.value.variant',
                'variantPrices.delivery',
                'variantPrices.promotion',
                'delivery',
                'promotion',
            ])->where('seller_id', $shopeProfile->id)->orderBy('id', 'desc')->get();

            // Fungsi untuk membangun path kategori lengkap
            $buildCategoryPath = function ($category) {
                if (!$category) return null;

                $path = [$category->name];
                while ($category->parent) {
                    $category = $category->parent;
                    array_unshift($path, $category->name);
                }
                return implode(' > ', $path);
            };

            $result = $products->map(function ($product) use ($buildCategoryPath) {
                // Variants dan Options
                $variants = $product->variants->map(function ($v) {
                    return [
                        'variant' => $v->variant,
                        'options' => $v->values->pluck('value'),
                    ];
                });


                // Kombinasi Harga dan Opsi
                $combinations = $product->variantPrices->map(function ($vp) {
                    $comb = [];
                    foreach ($vp->compositions as $comp) {
                        $variantName = $comp->value->variant->variant;
                        $optionValue = $comp->value->value;
                        $comb[$variantName] = $optionValue;
                    }
                    $delivery = $vp->delivery;
                    $promotion = $vp->promotion;

                    return [
                        'sku' => $vp->variant_code,
                        'id' => $vp->id,
                        'price' => $vp->price,
                        'stock' => $vp->stock,
                        'image' => $vp->image,
                        'combination' => $comb,
                        'weight' => $delivery?->weight  ?? 0,
                        'length' => $delivery?->length  ?? 0,
                        'width'  => $delivery?->width   ?? 0,
                        'height' => $delivery?->height  ?? 0,
                        'discount_price' => $promotion?->discount_price,
                        'discount_percent' => $promotion?->discount_percent,

                    ];
                });

                // Spesifikasi Produk
                $specs = $product->specifications->map(function ($s) {
                    return [
                        'name' => $s->label,
                        'value' => $s->value
                    ];
                });

                $datas = [
                    'category_id' => $product->category_id,
                    'desc' => $product->desc,
                    'id' => $product->id,
                    'image' => $product->image,
                    'is_used' => $product->is_used,
                    'max_purchase' => $product->max_purchase,
                    'media' => $product->media,
                    'min_purchase' => $product->min_purchase,
                    'name' => $product->name,
                    'price' => $product->price,
                    'seller_id' => $product->seller_id,
                    'is_cod_enabled' => $product->is_cod_enabled,
                    'scheduled_date' => $product->scheduled_date,
                    'sku' => $product->sku,
                    'specifications' => $specs,
                    'stock' => $product->stock,
                    'category' => $buildCategoryPath($product->category),
                    'variants' => $variants,
                    'delivery' => $product->delivery,
                    'variant_prices' => $variants,
                    'combinations' => $combinations,
                    'voucher' => $product->voucher
                ];

                if (isset($product->promotion['discount_price'])) {
                    $datas['discount_price'] = $product->promotion['discount_price'];
                }
                if (isset($product->promotion['discount_percent'])) {
                    $datas['discount_percent'] = $product->promotion['discount_percent'];
                }
                // Hasil akhir per produk
                return $datas;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Daftar produk berhasil diambil.',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Gagal mengambil daftar produk: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data produk.'
            ], 500);
        }
    }

    public function listDetailSeller(Request $request)
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $status  = $request->get('status');   // nilai: 1,2,3
            $keyword = $request->get('keyword'); // pencarian nama produk
            $sort    = $request->get('sort');
            $categoryId = $request->get('category_id'); // bisa parent/child/subchild
            $repair  = $request->get('repair'); // yes => tampilkan data yaCount < tidakCount
            $stockLimit = $request->get('stock_limit');
            $seller = $request->get('seller');
            // Query dasar produk (tanpa orderBy dulu)
            if ($seller) {
                $query = Product::with([
                    'category.parent.parent',
                    'specifications',
                    'variants.values',
                    'media',
                    'variantPrices.compositions.value.variant',
                    'variantPrices.delivery',
                    'variantPrices.promotion',
                    'delivery',
                    'promotion',
                ])->join('shop_profiles', 'shop_profiles.id', '=', 'products.seller_id')
                    ->select('products.*', 'shop_profiles.shop_name')->where('seller_id', $seller);
            } else {
                $query = Product::with([
                    'category.parent.parent',
                    'specifications',
                    'variants.values',
                    'media',
                    'variantPrices.compositions.value.variant',
                    'variantPrices.delivery',
                    'variantPrices.promotion',
                    'delivery',
                    'promotion',
                ])->join('shop_profiles', 'shop_profiles.id', '=', 'products.seller_id') // pastikan prefix 'products.'
                    ->select('products.*', 'shop_profiles.shop_name'); // ambil shop_name
            }
            // === Filter status jika dikirim ===
            if (in_array($status, [0, 1, 2, 3])) {
                $query->where('products.status', $status);
            }

            // === Filter keyword (nama produk) jika dikirim ===
            if (!empty($keyword)) {
                $query->where('name', 'like', '%' . $keyword . '%');
            }
            if ($stockLimit === 'yes') {
                $query->where('stock', '<', 100);
            }

            // === Sorting ===
            switch ($sort) {
                case 'oldest':
                    $query->orderBy('id', 'asc');
                    break;
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('id', 'desc');
                    break;
            }

            // === Filter kategori (parent/child/subchild) ===
            if (!empty($categoryId)) {
                $category = ProductCategory::with('childrenRecursive')->find($categoryId);
                if ($category) {
                    $ids = collect([$category->id]);
                    $addChildren = function ($cat) use (&$ids, &$addChildren) {
                        foreach ($cat->childrenRecursive as $child) {
                            $ids->push($child->id);
                            $addChildren($child);
                        }
                    };
                    $addChildren($category);
                    $query->whereIn('category_id', $ids);
                }
            }

            // Paginate hasil query
            $products = $query->paginate($perPage);

            // Fungsi untuk membangun path kategori
            $buildCategoryPath = function ($category) {
                if (!$category) return null;
                $path = [$category->name];
                while ($category->parent) {
                    $category = $category->parent;
                    array_unshift($path, $category->name);
                }
                return implode(' > ', $path);
            };

            // Mapping hasil paginate
            $result = $products->getCollection()->map(function ($product) use ($buildCategoryPath) {
                $productRequirment = RequermentProduct::where('product_id', $product->id)->first();

                $yaCount = 0;
                $tidakCount = 0;
                $totalYa = 0;
                $totalTidak = 0;

                if ($productRequirment) {
                    if ($productRequirment->image_product) {
                        $data = json_decode($productRequirment->image_product, true);
                        if (is_array($data)) {
                            $yaCount = collect($data)->where('status', 'ya')->count();
                            $tidakCount = collect($data)->where('status', 'tidak')->count();
                        }
                    }

                    $fields = ['name_product', 'desc_product', 'variant_product', 'address_pickup_product', 'promo_image'];
                    foreach ($fields as $field) {
                        if ($productRequirment->$field == 1) $totalYa++;
                        else $totalTidak++;
                    }

                    $hasVideo = $product->media->where('type', 'video')->isNotEmpty();
                    $hasImageGuide = $product->media->where('type', 'image_guide')->isNotEmpty();

                    if ($hasVideo)      $productRequirment->video == 1 ? $totalYa++ : $totalTidak++;
                    if ($hasImageGuide) $productRequirment->image_guide == 1 ? $totalYa++ : $totalTidak++;

                    $totalYa += $yaCount;
                    $totalTidak += $tidakCount;
                }


                $verificator = User::find($product->verificator);

                $variants = $product->variants->map(function ($v) {
                    return [
                        'variant' => $v->variant,
                        'options' => $v->values->pluck('value'),
                    ];
                });

                $delivery = $product->delivery;
                $shopAddress = $delivery && $delivery->address_shop_id
                    ? ShopAddress::find($delivery->address_shop_id)
                    : null;

                $province    = $shopAddress ? MasterProvince::find($shopAddress->province_id) : null;
                $city        = $shopAddress ? MasterCity::find($shopAddress->citie_id) : null;
                $subdistrict = $shopAddress ? MasterSubdistrict::find($shopAddress->subdistrict_id) : null;
                $postalCode  = $shopAddress ? MasterPostalCode::where('subdistrict_id', $shopAddress->subdistrict_id)->first() : null;

                $combinations = $product->variantPrices->map(function ($vp) use ($delivery) {
                    $comb = [];
                    foreach ($vp->compositions as $comp) {
                        $variantName = $comp->value->variant->variant;
                        $optionValue = $comp->value->value;
                        $comb[$variantName] = $optionValue;
                    }
                    return [
                        'sku' => $vp->variant_code,
                        'id' => $vp->id,
                        'price' => $vp->price,
                        'stock' => $vp->stock,
                        'image' => $vp->image,
                        'combination' => $comb,
                        'weight' => $delivery?->weight  ?? 0,
                        'length' => $delivery?->length  ?? 0,
                        'width'  => $delivery?->width   ?? 0,
                        'height' => $delivery?->height  ?? 0,
                        'discount_price'   => $vp->promotion?->discount_price,
                        'discount_percent' => $vp->promotion?->discount_percent,
                    ];
                });

                $specs = $product->specifications->map(fn($s) => [
                    'name' => $s->label,
                    'value' => $s->value
                ]);

                $datas = [
                    'category_id' => $product->category_id,
                    'desc' => $product->desc,
                    'id' => $product->id,
                    'image' => $product->image,
                    'is_used' => $product->is_used,
                    'max_purchase' => $product->max_purchase,
                    'media' => $product->media,
                    'min_purchase' => $product->min_purchase,
                    'name' => $product->name,
                    'price' => $product->price,
                    'seller_id' => $product->seller_id,
                    'name_seller' => $product->shop_name,
                    'is_cod_enabled' => $product->is_cod_enabled,
                    'scheduled_date' => $product->scheduled_date,
                    'sku' => $product->sku,
                    'specifications' => $specs,
                    'stock' => $product->stock,
                    'category' => $buildCategoryPath($product->category),
                    'variants' => $variants,
                    'delivery' => $product->delivery,
                    'variant_prices' => $product->variantPrices,
                    'combinations' => $combinations,
                    'voucher' => $product->voucher,
                    'status' => $product->status,
                    'created_at' => $product->created_at,
                    'verificator' =>  $verificator->name ?? null,
                    'requirment' => $productRequirment ?? null,
                    'yaCount' => $totalYa,
                    'tidakCount' => $totalTidak,
                    'address' => $shopAddress ? [
                        'full_address' => $shopAddress->full_address,
                        'name_shop' => $shopAddress->name_shop,
                        'number_shop' => $shopAddress->number_shop,
                        'label' => $shopAddress->label,
                        'province' => $province?->name,
                        'city' => $city?->name,
                        'subdistrict' => $subdistrict?->name,
                        'postal_code' => $postalCode?->code,
                    ] : null,
                ];

                if (isset($product->promotion['discount_price'])) {
                    $datas['discount_price'] = $product->promotion['discount_price'];
                }
                if (isset($product->promotion['discount_percent'])) {
                    $datas['discount_percent'] = $product->promotion['discount_percent'];
                }

                return $datas;
            });
            // Jika filter repair diminta, baru lakukan filter untuk hasil pagination
            if ($repair === 'yes') {
                $result = $result->filter(fn($item) => $item['yaCount'] < $item['tidakCount'])->values();
            }

            // Replace collection di paginator
            $products->setCollection($result);


            // ==== Hitung statistik ====
            if ($seller) {
                $baseQuery = Product::where('seller_id', $seller);
            } else {
                $baseQuery = new Product();
            }
            $semua           = (clone $baseQuery)->count();
            $disetujui       = (clone $baseQuery)->where('products.status', 3)->count();
            $pending = (clone $baseQuery)->where('products.status', 1)->count();
            $diblokir        = (clone $baseQuery)->where('products.status', 2)->count();
            $belum_disetujui        = (clone $baseQuery)->where('products.status', 0)->count();
            $lowStock        = (clone $baseQuery)->where('stock', '<', 100)->count();
            $allProductsForRepair = (clone $baseQuery)->with('media')->get();

            $repairTotal = $allProductsForRepair->filter(function ($product) {
                $productRequirment = RequermentProduct::where('product_id', $product->id)->first();

                $yaCount = 0;
                $tidakCount = 0;

                if ($productRequirment) {
                    if ($productRequirment->image_product) {
                        $data = json_decode($productRequirment->image_product, true);
                        if (is_array($data)) {
                            $yaCount = collect($data)->where('status', 'ya')->count();
                            $tidakCount = collect($data)->where('status', 'tidak')->count();
                        }
                    }

                    $fields = ['name_product', 'desc_product', 'variant_product', 'address_pickup_product', 'promo_image'];
                    foreach ($fields as $field) {
                        if ($productRequirment->$field == 1) $yaCount++;
                        else $tidakCount++;
                    }

                    $hasVideo = $product->media->where('type', 'video')->isNotEmpty();
                    $hasImageGuide = $product->media->where('type', 'image_guide')->isNotEmpty();

                    if ($hasVideo)      $productRequirment->video == 1 ? $yaCount++ : $tidakCount++;
                    if ($hasImageGuide) $productRequirment->image_guide == 1 ? $yaCount++ : $tidakCount++;
                }

                return $yaCount < $tidakCount;
            })->count();
            return response()->json([
                'status' => 'success',
                'message' => 'Daftar produk berhasil diambil.',
                'data' => $products->items(),
                'meta' => [
                    'current_page'     => $products->currentPage(),
                    'last_page'        => $products->lastPage(),
                    'per_page'         => (string) $products->perPage(),
                    'total'            => (string) $products->total(),
                    'semua'            => $semua,
                    'disetujui'        => $disetujui,
                    'belum_disetujui'  => $belum_disetujui,
                    'pending'  => $pending,
                    'diblokir'         => $diblokir,
                    'repair'           => $repairTotal,
                    'low_stock'        => $lowStock,
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Gagal mengambil daftar produk: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function performaProduct(Request $request)
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $status  = $request->get('status');   // nilai: 1,2,3
            $keyword = $request->get('keyword'); // pencarian nama produk
            $sort    = $request->get('sort');
            $categoryId = $request->get('category_id'); // bisa parent/child/subchild
            $repair  = $request->get('repair'); // yes => tampilkan data yaCount < tidakCount
            $stockLimit = $request->get('stock_limit');
            $seller = $request->get('seller');

            // Query dasar produk
            if ($seller) {
                $query = Product::with([
                    'category.parent.parent',
                    'specifications',
                    'variants.values',
                    'media',
                    'variantPrices.compositions.value.variant',
                    'variantPrices.delivery',
                    'variantPrices.promotion',
                    'delivery',
                    'promotion',
                ])->join('shop_profiles', 'shop_profiles.id', '=', 'products.seller_id')
                    ->select('products.*', 'shop_profiles.shop_name')
                    ->where('seller_id', $seller);
            } else {
                $query = Product::with([
                    'category.parent.parent',
                    'specifications',
                    'variants.values',
                    'media',
                    'variantPrices.compositions.value.variant',
                    'variantPrices.delivery',
                    'variantPrices.promotion',
                    'delivery',
                    'promotion',
                ])->join('shop_profiles', 'shop_profiles.id', '=', 'products.seller_id')
                    ->select('products.*', 'shop_profiles.shop_name');
            }

            // === Filter status
            if (in_array($status, [0, 1, 2, 3])) {
                $query->where('products.status', $status);
            }

            // === Filter keyword
            if (!empty($keyword)) {
                $query->where('name', 'like', '%' . $keyword . '%');
            }

            if ($stockLimit === 'yes') {
                $query->where('stock', '<', 100);
            }

            // === Sorting normal (skip kalau custom sort dipakai)
            if (!in_array($sort, ['most_viewed', 'most_liked', 'most_bought', 'most_cheapest'])) {
                switch ($sort) {
                    case 'oldest':
                        $query->orderBy('id', 'asc');
                        break;
                    case 'price_low':
                        $query->orderBy('price', 'asc');
                        break;
                    case 'price_high':
                        $query->orderBy('price', 'desc');
                        break;
                    case 'newest':
                    default:
                        $query->orderBy('id', 'desc');
                        break;
                }
            }

            // === Filter kategori (parent/child/subchild)
            if (!empty($categoryId)) {
                $category = ProductCategory::with('childrenRecursive')->find($categoryId);
                if ($category) {
                    $ids = collect([$category->id]);
                    $addChildren = function ($cat) use (&$ids, &$addChildren) {
                        foreach ($cat->childrenRecursive as $child) {
                            $ids->push($child->id);
                            $addChildren($child);
                        }
                    };
                    $addChildren($category);
                    $query->whereIn('category_id', $ids);
                }
            }

            // Paginate hasil query
            $products = $query->paginate($perPage);

            // Fungsi untuk path kategori
            $buildCategoryPath = function ($category) {
                if (!$category) return null;
                $path = [$category->name];
                while ($category->parent) {
                    $category = $category->parent;
                    array_unshift($path, $category->name);
                }
                return implode(' > ', $path);
            };

            // Mapping hasil paginate
            $result = $products->getCollection()->map(function ($product) use ($buildCategoryPath) {
                $productRequirment = RequermentProduct::where('product_id', $product->id)->first();

                $yaCount = 0;
                $tidakCount = 0;
                $totalYa = 0;
                $totalTidak = 0;

                if ($productRequirment) {
                    if ($productRequirment->image_product) {
                        $data = json_decode($productRequirment->image_product, true);
                        if (is_array($data)) {
                            $yaCount = collect($data)->where('status', 'ya')->count();
                            $tidakCount = collect($data)->where('status', 'tidak')->count();
                        }
                    }

                    $fields = ['name_product', 'desc_product', 'variant_product', 'address_pickup_product', 'promo_image'];
                    foreach ($fields as $field) {
                        if ($productRequirment->$field == 1) $totalYa++;
                        else $totalTidak++;
                    }

                    $hasVideo = $product->media->where('type', 'video')->isNotEmpty();
                    $hasImageGuide = $product->media->where('type', 'image_guide')->isNotEmpty();

                    if ($hasVideo)      $productRequirment->video == 1 ? $totalYa++ : $totalTidak++;
                    if ($hasImageGuide) $productRequirment->image_guide == 1 ? $totalYa++ : $totalTidak++;

                    $totalYa += $yaCount;
                    $totalTidak += $tidakCount;
                }

                $verificator = User::find($product->verificator);

                $variants = $product->variants->map(function ($v) {
                    return [
                        'variant' => $v->variant,
                        'options' => $v->values->pluck('value'),
                    ];
                });

                $delivery = $product->delivery;
                $shopAddress = $delivery && $delivery->address_shop_id
                    ? ShopAddress::find($delivery->address_shop_id)
                    : null;

                $province    = $shopAddress ? MasterProvince::find($shopAddress->province_id) : null;
                $city        = $shopAddress ? MasterCity::find($shopAddress->citie_id) : null;
                $subdistrict = $shopAddress ? MasterSubdistrict::find($shopAddress->subdistrict_id) : null;
                $postalCode  = $shopAddress ? MasterPostalCode::where('subdistrict_id', $shopAddress->subdistrict_id)->first() : null;

                $combinations = $product->variantPrices->map(function ($vp) use ($delivery) {
                    $comb = [];
                    foreach ($vp->compositions as $comp) {
                        $variantName = $comp->value->variant->variant;
                        $optionValue = $comp->value->value;
                        $comb[$variantName] = $optionValue;
                    }
                    return [
                        'sku' => $vp->variant_code,
                        'id' => $vp->id,
                        'price' => $vp->price,
                        'stock' => $vp->stock,
                        'image' => $vp->image,
                        'combination' => $comb,
                        'weight' => $delivery?->weight  ?? 0,
                        'length' => $delivery?->length  ?? 0,
                        'width'  => $delivery?->width   ?? 0,
                        'height' => $delivery?->height  ?? 0,
                        'discount_price'   => $vp->promotion?->discount_price,
                        'discount_percent' => $vp->promotion?->discount_percent,
                    ];
                });

                $specs = $product->specifications->map(fn($s) => [
                    'name' => $s->label,
                    'value' => $s->value
                ]);


                $shopProfile = $product->shopProfile;
                $addresses = $shopProfile?->shopAddresses ?? collect();
                $address = $addresses->firstWhere('is_primary', 1) ?? $addresses->first();
                $locationName = $address?->cities?->name ?? $address?->province?->name ?? '-';



                $datas = [
                    'category_id' => $product->category_id,
                    'desc' => $product->desc,
                    'id' => $product->id,
                    'image' => $product->image,
                    'is_used' => $product->is_used,
                    'max_purchase' => $product->max_purchase,
                    'media' => $product->media,
                    'min_purchase' => $product->min_purchase,
                    'name' => $product->name,
                    'price' => $product->price,
                    'seller_id' => $product->seller_id,
                    'name_seller' => $product->shop_name,
                    'is_cod_enabled' => $product->is_cod_enabled,
                    'scheduled_date' => $product->scheduled_date,
                    'sku' => $product->sku,
                    'specifications' => $specs,
                    'stock' => $product->stock,
                    'category' => $buildCategoryPath($product->category),
                    'variants' => $variants,
                    'delivery' => $product->delivery,
                    'variant_prices' => $product->variantPrices,
                    'combinations' => $combinations,
                    'voucher' => $product->voucher,
                    'status' => $product->status,
                    'created_at' => $product->created_at,
                    'verificator' =>  $verificator->name ?? null,
                    'requirment' => $productRequirment ?? null,
                    'yaCount' => $totalYa,
                    'tidakCount' => $totalTidak,
                    // random performance
                    'rate'      => rand(1, 5),
                    'reviewers' => rand(150, 6000),
                    'bought'    => rand(30, 1000),
                    'viewed'    => rand(4000, 10000),
                    'id_store' => $shopProfile->id ?? null,
                    'name_store' => $shopProfile->shop_name ?? '-',
                    'image_store' => $shopProfile->logo_url ?? '-',
                    'city_store' => $locationName,
                    'address' => $shopAddress ? [
                        'full_address' => $shopAddress->full_address,
                        'name_shop' => $shopAddress->name_shop,
                        'number_shop' => $shopAddress->number_shop,
                        'label' => $shopAddress->label,
                        'province' => $province?->name,
                        'city' => $city?->name,
                        'subdistrict' => $subdistrict?->name,
                        'postal_code' => $postalCode?->code,
                    ] : null,
                ];

                if (isset($product->promotion['discount_price'])) {
                    $datas['discount_price'] = $product->promotion['discount_price'];
                }
                if (isset($product->promotion['discount_percent'])) {
                    $datas['discount_percent'] = $product->promotion['discount_percent'];
                }

                return $datas;
            });

            // === Filter repair ===
            if ($repair === 'yes') {
                $result = $result->filter(fn($item) => $item['yaCount'] < $item['tidakCount'])->values();
            }

            // === Sorting custom ===
            if (in_array($sort, ['most_viewed', 'most_liked', 'most_bought', 'most_cheapest'])) {
                if ($sort === 'most_viewed') {
                    $result = $result->sortByDesc('viewed')->values();
                } elseif ($sort === 'most_liked') {
                    $result = $result->sortByDesc('rate')->values();
                } elseif ($sort === 'most_bought') {
                    $result = $result->sortByDesc('bought')->values();
                } elseif ($sort === 'most_cheapest') {
                    $result = $result->sortBy('price')->values();
                }

                // ambil acak 4-7 produk biar natural
                $randomCount = rand(4, 7);
                $result = $result->take($randomCount);
            }

            // Replace collection di paginator
            $products->setCollection($result);

            // ==== Hitung statistik ====
            if ($seller) {
                $baseQuery = Product::where('seller_id', $seller);
            } else {
                $baseQuery = new Product();
            }

            $semua           = (clone $baseQuery)->count();
            $disetujui       = (clone $baseQuery)->where('products.status', 3)->count();
            $pending         = (clone $baseQuery)->where('products.status', 1)->count();
            $diblokir        = (clone $baseQuery)->where('products.status', 2)->count();
            $belum_disetujui = (clone $baseQuery)->where('products.status', 0)->count();
            $lowStock        = (clone $baseQuery)->where('stock', '<', 100)->count();
            $allProductsForRepair = (clone $baseQuery)->with('media')->get();

            $repairTotal = $allProductsForRepair->filter(function ($product) {
                $productRequirment = RequermentProduct::where('product_id', $product->id)->first();
                $yaCount = 0;
                $tidakCount = 0;

                if ($productRequirment) {
                    if ($productRequirment->image_product) {
                        $data = json_decode($productRequirment->image_product, true);
                        if (is_array($data)) {
                            $yaCount = collect($data)->where('status', 'ya')->count();
                            $tidakCount = collect($data)->where('status', 'tidak')->count();
                        }
                    }

                    $fields = ['name_product', 'desc_product', 'variant_product', 'address_pickup_product', 'promo_image'];
                    foreach ($fields as $field) {
                        if ($productRequirment->$field == 1) $yaCount++;
                        else $tidakCount++;
                    }

                    $hasVideo = $product->media->where('type', 'video')->isNotEmpty();
                    $hasImageGuide = $product->media->where('type', 'image_guide')->isNotEmpty();

                    if ($hasVideo)      $productRequirment->video == 1 ? $yaCount++ : $tidakCount++;
                    if ($hasImageGuide) $productRequirment->image_guide == 1 ? $yaCount++ : $tidakCount++;
                }

                return $yaCount < $tidakCount;
            })->count();

            return response()->json([
                'status' => 'success',
                'message' => 'Daftar produk berhasil diambil.',
                'data' => $products->items(),
                'meta' => [
                    'current_page'     => $products->currentPage(),
                    'last_page'        => $products->lastPage(),
                    'per_page'         => (string) $products->perPage(),
                    'total'            => (string) $products->total(),
                    'semua'            => $semua,
                    'disetujui'        => $disetujui,
                    'belum_disetujui'  => $belum_disetujui,
                    'pending'          => $pending,
                    'diblokir'         => $diblokir,
                    'repair'           => $repairTotal,
                    'low_stock'        => $lowStock,
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Gagal mengambil daftar produk: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }





    public function store(Request $request)
    {
        $uploaded = [];
        DB::beginTransaction();
        try {
            $shopeProfile = DB::table('shop_profiles')->where('user_id', auth()->user()->id)->first();
            $imageUrl = null;

            if ($request->hasFile('productPromo')) {
                $firstFile = $request->file('productPromo');
                $path = 'product_media/ProductPromo-'  . time() . '-' . uniqid() . '.webp';
                Storage::disk('minio')->put($path, file_get_contents($firstFile->getRealPath()), 'public');
                $imageUrl = Storage::disk('minio')->url($path);
            }
            // 1. Simpan Produk Utama
            $dataProduct = [
                'seller_id'    =>  $shopeProfile->id,
                'category_id'  => $request->idCategorie,
                'name'         => $request->productName,
                'price'         => $request->price,
                'stock'         => $request->stock,
                'desc'         => $request->description,
                'sku'          => $request->parentSku,
                'scheduled_date'          => $request->scheduledDate,
                'scheduled_date'          => $request->scheduledDate,
                'is_cod_enabled'          => $request->isCodEnabled,
                'is_used'      => $request->condition,
                'min_purchase' => 1,
                'max_purchase' => 999,
                'image' => $imageUrl,
            ];
            if ($request->voucher) {
                $dataProduct['voucher'] = $request->voucher;
            }
            $product = Product::create($dataProduct);

            // 2. Media utama
            foreach ($request->file() as $key => $file) {
                if (Str::startsWith($key, 'productPhotos')) {
                    foreach ((array)$file as $idx => $f) {
                        $path = 'product_media/Product-' . time() . '-' . uniqid() . '.webp';
                        Storage::disk('minio')->put($path, file_get_contents($f->getRealPath()), 'public');
                        $uploaded[] = $path;
                        ProductMedia::create([
                            'product_id' => $product->id,
                            'url' => Storage::disk('minio')->url($path),
                            'ordinal' => $idx,
                            'type' => 'image'
                        ]);
                    }
                }
                if ($key == 'productVideo') {
                    $f = is_array($file) ? $file[0] : $file;
                    $path = 'product_media/VideoProduct-'  . time() . '-' . uniqid() .  '.' . $f->getClientOriginalExtension();
                    Storage::disk('minio')->put($path, file_get_contents($f->getRealPath()), 'public');
                    $uploaded[] = $path;
                    ProductMedia::create([
                        'product_id' => $product->id,
                        'url' => Storage::disk('minio')->url($path),
                        'ordinal' => 999,
                        'type' => 'video'
                    ]);
                }
            }

            if ($request->hasFile('image_guide')) {
                $fileName = 'product_media/ImageGuide-' . time() . '.webp';
                $image = $this->utilityService->convertImageToWebp($request->file('image_guide'));
                Storage::disk('minio')->put($fileName, $image);
                $imageUrlGuide = Storage::disk('minio')->url($fileName);
                ProductMedia::create([
                    'product_id' => $product->id,
                    'url' => $imageUrlGuide,
                    'ordinal' => $idx,
                    'type' => 'image_guide'
                ]);
            }
            // 3. Spesifikasi dinamis
            $specs = json_decode($request->specifications, true);
            foreach ($specs as $label => $value) {
                ProductSpecification::create([
                    'product_id' => $product->id,
                    'label' => $label,
                    'value' => $value
                ]);
            }

            // 4. Pengiriman
            $delivery = [
                'product_id' => $product->id,
                'weight' => $request->shippingWeight,
                'length' => $request->length ?? 0,
                'width' => $request->width ?? 0,
                'height' => $request->height ?? 0,
                'is_dangerous_product' => $request->isHazardous,
                'is_pre_order' => $request->isProductPreOrder,
                'is_cost_by_seller' => $request->shippingInsurance,
                'service_ids' => $request->courierServicesIds,
                'address_shop_id' => $request->id_address,
            ];

            if ($request->subsidy) {
                $delivery['subsidy'] = $request->subsidy;
            }
            if ($request->preorder_duration) {
                $delivery['preorder_duration'] = $request->preorder_duration;
            }

            ProductDelivery::create($delivery);

            if ($request->variations && $request->productVariants) {
                $variations = json_decode($request->variations, true);
                $variantPrices = json_decode($request->productVariants, true);
                $valueIds = [];

                for ($i = 0; $i < count($variations); $i++) {
                    $v = $variations[$i];
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'variant' => $v['name'],
                        'ordinal' => $i
                    ]);

                    $ids = [];

                    for ($j = 0; $j < count($v['options']); $j++) {
                        $opt = $v['options'][$j];

                        if ($opt) {
                            $val = ProductVariantValue::create([
                                'variant_id' => $variant->id,
                                'value' => $opt,
                                'ordinal' => $j
                            ]);

                            $ids[$opt] = $val->id;
                        }
                    }

                    $valueIds[] = $ids;
                }

                $minPrice = null;
                $sumStock = 0;
                $uploaded = [];

                for ($k = 0; $k < count($variantPrices); $k++) {
                    $vp = $variantPrices[$k];
                    $imgURL = null;
                    $fkey = "variant_images." . $k;
                    if ($request->hasFile($fkey)) {
                        $f = $request->file($fkey);
                        $f = is_array($f) ? $f[0] : $f;

                        $path = 'product_media/variant/ProductVariant-' . time() . '-' . uniqid() .  '.webp';
                        Storage::disk('minio')->put($path, file_get_contents($f->getRealPath()), 'public');
                        $uploaded[] = $path;
                        $imgURL = Storage::disk('minio')->url($path);
                    }

                    $price = (int)$vp['price'];
                    $stock = (int)$vp['stock'];
                    $sumStock += $stock;

                    if ($minPrice === null || $price < $minPrice) {
                        $minPrice = $price;
                    }

                    $pv = ProductVariantPrice::create([
                        'product_id' => $product->id,
                        'price' => $price,
                        'stock' => $stock,
                        'image' => $imgURL,
                        'variant_code' => $vp['sku']
                    ]);
                    ProductVariantDelivery::create([
                        'product_variant_price_id' => $pv->id,
                        'weight' => (int)$vp['weight'],
                        'length' => (int)$vp['length'],
                        'width' => (int)$vp['width'],
                        'height' => (int)$vp['height'],
                    ]);
                    if ($vp['discountPercent'] > 0) {
                        ProductPromotion::create([
                            'product_id' => $product->id,
                            'product_variant_price_id' => $pv->id,
                            'discount_price' => $vp['discount'],
                            'discount_percent' => $vp['discountPercent'],
                        ]);
                    }

                    foreach ($vp['combination'] as $varName => $opt) {
                        $variantIndex = array_search($varName, array_column($variations, 'name'));
                        $vid = $valueIds[$variantIndex][$opt];

                        ProductVariantPriceComposition::create([
                            'product_variant_price_id' => $pv->id,
                            'product_variant_value_id' => $vid
                        ]);
                    }
                }


                $product->update(['price' => $minPrice, 'stock' => $sumStock]);
            } else {
                if ($request->discount > 0) {
                    ProductPromotion::create([
                        'product_id' => $product->id,
                        'discount_percent' => $request->discount,
                        'discount_price' => $request->price_discount,
                    ]);
                }
            }
            // 5. Variasi & Varian
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Produk tersimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($uploaded as $p) Storage::disk('minio')->delete($p);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // 1. Profil toko & produk
            // $shopProfile = DB::table('shop_profiles')
            //     ->where('user_id', auth()->id())
            //     ->first();

            $product = Product::findOrFail($id);

            // 2. Data utama
            $product->name          = $request->productName;
            $product->category_id   = $request->idCategorie;
            $product->desc          = $request->description;
            $product->sku           = $request->parentSku;
            $product->scheduled_date = $request->scheduledDate;
            $product->is_cod_enabled  = $request->isCodEnabled;
            $product->is_used       = $request->condition;
            if ($request->voucher) {
                $product->voucher = $request->voucher;
            } else {
                $product->voucher = null;
            }
            $this->syncProductMedia($request, $product);
            if ($request->showSizeGuide == '1') {
                $productMedia = DB::table('product_media')->where('product_id', $id)->where('type', 'image_guide')->first();
                if ($productMedia) {
                    if ($request->file('image_guide')) {
                        $delete_image = $productMedia->url;
                        $url = UrlRemove::remover($delete_image);
                        Storage::disk('minio')->delete($url);

                        $editGuide = ProductMedia::find($productMedia->id);

                        $fileName = 'product_media/ImageGuide-' . time() . '.webp';
                        $image = $this->utilityService->convertImageToWebp($request->file('image_guide'));
                        Storage::disk('minio')->put($fileName, $image);
                        $imageUrlGuide = Storage::disk('minio')->url($fileName);

                        $editGuide->url = $imageUrlGuide;
                        $editGuide->save();
                    }
                } else {
                    $fileName = 'product_media/ImageGuide-' . time() . '.webp';
                    $image = $this->utilityService->convertImageToWebp($request->file('image_guide'));
                    Storage::disk('minio')->put($fileName, $image);
                    $imageUrlGuide = Storage::disk('minio')->url($fileName);
                    ProductMedia::create([
                        'product_id' => $product->id,
                        'url' => $imageUrlGuide,
                        'ordinal' => 100,
                        'type' => 'image_guide'
                    ]);
                }
            } else {
                $productMedia = DB::table('product_media')->where('product_id', $id)->where('type', 'image_guide')->first();
                if ($productMedia) {
                    $delete_image = $productMedia->url;
                    $url = UrlRemove::remover($delete_image);
                    Storage::disk('minio')->delete($url);

                    $deleteGuide = ProductMedia::find($productMedia->id);
                    $deleteGuide->delete();
                }
            }
            // 4. Varian ‑ stok ‑ harga
            if ($request->isVariationActive == '1') {
                $this->syncProductVariants($request, $product);
            } else {
                $this->deleteAllProductVariants($product->id);
                $product->price = (int) str_replace('.', '', $request->price);
                $product->stock = (int) $request->stock;
                if ($request->discount > 0) {
                    // Cek apakah sudah ada promo tanpa varian
                    $promotion = ProductPromotion::where('product_id', $product->id)
                        ->whereNull('product_variant_price_id')
                        ->first();

                    if ($promotion) {
                        // Update jika ada
                        $promotion->update([
                            'discount_percent' => $request->discount,
                            'discount_price' => $request->price_discount,
                        ]);
                    } else {
                        // Buat baru jika tidak ada
                        ProductPromotion::create([
                            'product_id' => $product->id,
                            'product_variant_price_id' => null,
                            'discount_percent' => $request->discount,
                            'discount_price' => $request->price_discount,
                        ]);
                    }
                } else {
                    // Jika discount <= 0, hapus promo jika ada
                    $promotion = ProductPromotion::where('product_id', $product->id)
                        ->whereNull('product_variant_price_id')
                        ->first();

                    if ($promotion) {
                        $promotion->delete();
                    }
                }
            }

            $product->save();

            // 5. Data pengiriman

            if ($request->subsidy) {
                $subsidy = $request->subsidy;
            } else {
                $subsidy = null;
            }
            $preorder_duration = null;
            if ($request->preorder_duration > 0) {
                $preorder_duration = $request->preorder_duration;
            }
            $delivery = ProductDelivery::where('product_id', $product->id)->first();
            if ($delivery) {
                $delivery->update([
                    'weight'               => (float) ($request->shippingWeight ?? 0),
                    'length'               => (float) ($request->length ?? 0),
                    'width'                => (float) ($request->width ?? 0),
                    'height'               => (float) ($request->height ?? 0),
                    'is_dangerous_product' => $request->isHazardous,
                    'is_pre_order'         => $request->isProductPreOrder,
                    'is_cost_by_seller'    => $request->shippingInsurance,
                    'service_ids' => $request->courierServicesIds,
                    'subsidy' => $subsidy,
                    'preorder_duration' => $preorder_duration,
                ]);
            }

            DB::commit();
            return response()->json([
                'status'  => 'success',
                'message' => 'Produk berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], 500);
        }
    }

    private function deleteAllProductVariants($productId): void
    {
        // Kumpulkan url gambar lama
        $variantPrices = ProductVariantPrice::where('product_id', $productId)->get();

        // Hapus komposisi
        ProductVariantPriceComposition::whereIn(
            'product_variant_price_id',
            $variantPrices->pluck('id')
        )->delete();

        // Hapus file di MinIO bila tidak dipertahankan
        foreach ($variantPrices as $vp) {
            if (
                $vp->image &&
                !in_array(
                    $vp->image,
                    request()->input('preserveVariantImages', [])
                )
            ) {
                $path   = ltrim(parse_url($vp->image, PHP_URL_PATH), '/');
                $bucket = config('filesystems.disks.minio.bucket');
                if (Str::startsWith($path, $bucket . '/')) {
                    $path = Str::after($path, $bucket . '/');
                }
                if ($path) {
                    Storage::disk('minio')->delete($path);
                }
            }
        }

        // Hapus record varian
        ProductVariantPrice::where('product_id', $productId)->delete();
        $variantIds = ProductVariant::where('product_id', $productId)->pluck('id');
        ProductVariantValue::whereIn('variant_id', $variantIds)->delete();
        ProductVariant::where('product_id', $productId)->delete();
    }

    private function deleteSinglePriceVariant(ProductVariantPrice $variantPrice): void
    {
        // Hapus file di MinIO
        if ($variantPrice->image) {
            $path = ltrim(parse_url($variantPrice->image, PHP_URL_PATH), '/');
            $bucket = config('filesystems.disks.minio.bucket');
            if (Str::startsWith($path, $bucket . '/')) {
                $path = Str::after($path, $bucket . '/');
            }
            if ($path) {
                Storage::disk('minio')->delete($path);
            }
        }

        // Hapus relasi (cascade delete biasanya di-handle oleh DB, tapi eksplisit lebih aman)
        $variantPrice->composition()->delete();
        $variantPrice->delivery()->delete();
        $variantPrice->promotion()->delete();

        // Hapus record itu sendiri
        $variantPrice->delete();
    }


    /**
     * Sinkronisasi varian produk dengan metode UPDATE, CREATE, dan DELETE yang efisien.
     * Menjaga ID database tetap stabil.
     */
    // ... use statements ...

    private function syncProductVariants(Request $request, Product $product): void
    {
        DB::transaction(function () use ($request, $product) {
            $incomingVariations = json_decode($request->variations, true) ?? [];
            $incomingVariantPrices = json_decode($request->productVariants, true) ?? [];

            $product->load('variants.values', 'variantPrices.composition', 'variantPrices.delivery', 'variantPrices.promotion');

            /*
        |--------------------------------------------------------------------------
        | Langkah 1: Sinkronisasi Master Variant & Values
        |--------------------------------------------------------------------------
        */
            $allValueIds = collect();
            $valueIdMap = [];
            $incomingVariantNames = collect($incomingVariations)->pluck('name');

            foreach ($product->variants as $existingVariant) {
                if (!$incomingVariantNames->contains($existingVariant->variant)) {
                    $existingVariant->delete();
                }
            }

            foreach ($incomingVariations as $i => $vData) {
                $variant = ProductVariant::updateOrCreate(
                    ['product_id' => $product->id, 'variant' => $vData['name']],
                    ['ordinal' => $i]
                );

                $valueIdMap[$vData['name']] = [];
                $incomingOptions = collect($vData['options']);

                foreach ($variant->values as $existingValue) {
                    if (!$incomingOptions->contains($existingValue->value)) {
                        $existingValue->delete();
                    }
                }

                foreach ($vData['options'] as $j => $opt) {
                    $val = ProductVariantValue::updateOrCreate(
                        ['variant_id' => $variant->id, 'value' => $opt],
                        ['ordinal' => $j]
                    );
                    $allValueIds->push($val->id);
                    $valueIdMap[$vData['name']][$opt] = $val->id;
                }
            }

            /*
        |--------------------------------------------------------------------------
        | Langkah 2: Sinkronisasi Kombinasi Harga/SKU
        |--------------------------------------------------------------------------
        */
            $existingPriceMap = $product->variantPrices->keyBy(function ($vp) {
                return $vp->composition->pluck('product_variant_value_id')->sort()->implode('-');
            });

            foreach ($incomingVariantPrices as $idx => $vpData) {
                $currentKeyParts = [];
                foreach ($vpData['combination'] as $variantName => $option) {
                    if (isset($valueIdMap[$variantName][$option])) {
                        $currentKeyParts[] = $valueIdMap[$variantName][$option];
                    }
                }
                $currentKey = collect($currentKeyParts)->sort()->implode('-');

                if (empty($currentKey)) continue;

                $variantPrice = $existingPriceMap->get($currentKey);
                $imageUrl = $vpData['image']['preview'] ?? null;

                // ========================= BAGIAN YANG DIPERBAIKI =========================
                if ($request->hasFile("variant_images.$idx")) {
                    // Jika ada gambar baru, hapus HANYA FILE gambar lama dari storage.
                    if ($variantPrice && $variantPrice->image) {
                        $oldPath = ltrim(parse_url($variantPrice->image, PHP_URL_PATH), '/');
                        $bucket = config('filesystems.disks.minio.bucket');
                        if (Str::startsWith($oldPath, $bucket . '/')) {
                            $oldPath = Str::after($oldPath, $bucket . '/');
                        }
                        if ($oldPath) {
                            Storage::disk('minio')->delete($oldPath);
                        }
                    }

                    // Upload file gambar baru
                    $file = $request->file("variant_images.$idx");
                    $filename = 'Variasi-' . time() . '-' . uniqid() . '.webp';
                    $path = 'product_media/variant/' . $filename;
                    $webp = Image::make($file)->encode('webp', 90);
                    Storage::disk('minio')->put($path, $webp, 'public');
                    $imageUrl = Storage::disk('minio')->url($path);
                }
                // =========================================================================

                $priceData = [
                    'price' => (int) str_replace('.', '', $vpData['price']),
                    'stock' => (int) $vpData['stock'],
                    'image' => $imageUrl,
                    'variant_code' => $vpData['sku'],
                ];

                if ($variantPrice) {
                    $variantPrice->update($priceData);
                    $existingPriceMap->forget($currentKey);
                } else {
                    $priceData['product_id'] = $product->id;
                    $variantPrice = ProductVariantPrice::create($priceData);
                    foreach ($currentKeyParts as $valueId) {
                        ProductVariantPriceComposition::create([
                            'product_variant_price_id' => $variantPrice->id,
                            'product_variant_value_id' => $valueId,
                        ]);
                    }
                }

                ProductVariantDelivery::updateOrCreate(
                    ['product_variant_price_id' => $variantPrice->id],
                    [
                        'weight' => (float) ($vpData['weight'] ?? 0),
                        'length' => (float) ($vpData['length'] ?? 0),
                        'width'  => (float) ($vpData['width']  ?? 0),
                        'height' => (float) ($vpData['height'] ?? 0),
                    ]
                );

                if (isset($vpData['discountPercent']) && $vpData['discountPercent'] > 0) {
                    ProductPromotion::updateOrCreate(
                        ['product_variant_price_id' => $variantPrice->id],
                        [
                            'product_id'       => $product->id,
                            'discount_percent' => $vpData['discountPercent'],
                            'discount_price'   => $vpData['discount'],
                        ]
                    );
                } else {
                    ProductPromotion::where('product_variant_price_id', $variantPrice->id)->delete();
                }
            }

            /*
        |--------------------------------------------------------------------------
        | Langkah 3: Hapus Kombinasi Harga/SKU yang Usang
        |--------------------------------------------------------------------------
        */
            foreach ($existingPriceMap as $variantPriceToDelete) {
                $this->deleteSinglePriceVariant($variantPriceToDelete);
            }

            /*
        |--------------------------------------------------------------------------
        | Langkah 4: Update Harga Minimum & Stok Total Produk Utama
        |--------------------------------------------------------------------------
        */
            $product->load('variantPrices');
            $product->price = $product->variantPrices->min('price') ?? 0;
            $product->stock = $product->variantPrices->sum('stock') ?? 0;
            $product->save();
        });
    }
    private function syncProductMedia(Request $request, Product $product): void
    {
        $existingMediaUrls = $request->input('existing_media', []);

        /* hapus foto lama yang tidak dipertahankan */
        $mediaToDelete = ProductMedia::where('product_id', $product->id)
            ->where('type', 'image')
            ->whereNotIn('url', $existingMediaUrls)
            ->get();

        foreach ($mediaToDelete as $media) {
            $path   = ltrim(parse_url($media->url, PHP_URL_PATH), '/');
            $bucket = config('filesystems.disks.minio.bucket');
            if (Str::startsWith($path, $bucket . '/')) {
                $path = Str::after($path, $bucket . '/');
            }
            if ($path) {
                Storage::disk('minio')->delete($path);
            }
            $media->delete();
        }

        /* upload file baru (jika ada) */
        if ($request->hasFile('productPhotos')) {
            foreach ($request->file('productPhotos') as $idx => $file) {

                $filename = 'ProductMedia-' . 'Product-'  . time() . '-' . uniqid() .  '.webp';
                $path     = 'product_media/' . $filename;

                $webp = Image::make($file)->encode('webp', 90);
                Storage::disk('minio')->put($path, $webp, 'public');

                ProductMedia::create([
                    'product_id' => $product->id,
                    'url'        => Storage::disk('minio')->url($path),
                    'ordinal'    => $idx,
                    'type'       => 'image',
                ]);
            }
        }
        if ($request->hasFile('productVideo')) {
            $existingMedia = ProductMedia::where('product_id', $product->id)
                ->where('type', 'video')
                ->first();

            if ($existingMedia && $existingMedia->url) {
                $pathToDelete = UrlRemove::remover($existingMedia->url);
                if (Storage::disk('minio')->exists($pathToDelete)) {
                    Storage::disk('minio')->delete($pathToDelete);
                }
            }

            $file = $request->file('productVideo');
            $fileName = 'VideoProduct-'  . time() . '-' . uniqid() .  '.' . $file->getClientOriginalExtension();
            $path = 'product_media/' . $fileName;

            Storage::disk('minio')->put($path, file_get_contents($file->getRealPath()), 'public');
            $urlVideo = Storage::disk('minio')->url($path);

            // Simpan atau update data media di database
            ProductMedia::updateOrCreate(
                ['product_id' => $product->id, 'type' => 'video'],
                ['url' => $urlVideo, 'ordinal' => 100]
            );
        }

        if ($request->hasFile('productPromo')) {
            $delete_image =  $request->promoImage;
            $url = UrlRemove::remover($delete_image);

            Storage::disk('minio')->delete($url);

            $firstFile = $request->file('productPromo');
            $path = 'product_media/ProductPromo-'  . time() . '-' . uniqid() .  '.webp';
            Storage::disk('minio')->put($path, file_get_contents($firstFile->getRealPath()), 'public');
            $imageUrl = Storage::disk('minio')->url($path);
            $product->image = $imageUrl;
        }
    }

    public function deleteVariantPrice($id)
    {
        $productVariant = ProductVariantPrice::find($id);

        if (!$productVariant) {
            return $this->utilityService->is404Response("Product not found!");
        }
        if ($productVariant->image) {
            $delete_image = $productVariant->image;
            $url = UrlRemove::remover($delete_image);
            Storage::disk('minio')->delete($url);
        }
        if ($productVariant->delete()) {
            $success_message = "Product variant successfully deleted";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
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
