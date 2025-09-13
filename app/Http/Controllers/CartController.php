<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    // protected $utilityService;

    // public function __construct(UtilityService $utilityService)
    // {
    //     $this->utilityService = $utilityService;
    // }
    public function headerCart(Request $request)
    {
        $auth = Auth::id();
        $profile = UserProfile::where('user_id', $auth)->first();

        $cart = DB::table('carts')
            ->leftJoin('products', 'products.id', '=', 'carts.product_id')
            ->leftJoin('product_variant_prices', 'product_variant_prices.id', '=', 'carts.variant_price_id')
            ->leftJoin('product_variant_price_compositions', 'product_variant_price_compositions.product_variant_price_id', '=', 'product_variant_prices.id')
            ->leftJoin('product_variant_values', 'product_variant_values.id', '=', 'product_variant_price_compositions.product_variant_value_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'product_variant_values.variant_id')
            ->leftJoin('product_promotions', function ($join) {
                $join->on(function ($q) {
                    $q->on('product_promotions.product_variant_price_id', '=', 'product_variant_prices.id')
                        ->orOn(function ($q2) {
                            $q2->on('product_promotions.product_id', '=', 'products.id')
                                ->whereNull('product_promotions.product_variant_price_id');
                        });
                })
                    ->where('product_promotions.status', '=', 'active');
            })
            ->where('carts.user_profile_id', $profile->id)
            ->select(
                'carts.id as cart_id',
                'carts.qty',
                'carts.variant_price_id',
                'products.id as product_id',
                'products.name as product_name',
                'products.image as product_image',
                DB::raw('COALESCE(product_variant_prices.price, products.price) as original_price'),
                'product_promotions.discount_price',
                'product_promotions.discount_percent',
                'product_variants.variant',
                'product_variant_values.value'
            )
            ->orderBy('carts.id', 'desc')
            ->get();

        // Filter item cacat
        $validCart = $cart->groupBy('cart_id')->filter(function ($items) {
            $first = $items->first();

            // Skip jika produk tidak ada
            if (!$first->product_id) {
                return false;
            }

            // Skip jika produk punya varian tapi variant_price_id kosong/null
            $hasVariant = DB::table('product_variants')->where('product_id', $first->product_id)->exists();
            if ($hasVariant && empty($first->variant_price_id)) {
                return false;
            }

            // Skip jika variant_price_id ada tapi tidak ditemukan di DB
            if (!empty($first->variant_price_id)) {
                $variantExists = DB::table('product_variant_prices')->where('id', $first->variant_price_id)->exists();
                if (!$variantExists) {
                    return false;
                }
            }

            return true;
        });

        // Hitung ulang jumlah qty hanya dari item valid
        $totalQty = $validCart->sum(function ($items) {
            return $items->first()->qty;
        });

        // Format output
        $groupedCart = $validCart->map(function ($items) {
            $first = $items->first();

            $variantString = $items
                ->filter(fn($i) => $i->variant !== null)
                ->map(fn($i) => "{$i->variant} {$i->value}")
                ->unique()
                ->implode(' ');

            return [
                'cart_id'          => $first->cart_id,
                'product_name'     => $first->product_name,
                'product_image'    => $first->product_image,
                'qty'              => (int) $first->qty,
                'original_price'   => $first->original_price,
                'discount_price'   => $first->discount_price,
                'discount_percent' => $first->discount_percent,
                'final_price'      => $first->discount_price ?? $first->original_price,
                'variants'         => $variantString
            ];
        })->values();

        $data = [
            'count' => (int) $totalQty,
            'cart'  => $groupedCart
        ];

        $message = "Data cart ditemukan";
        return $this->utilityService->is200ResponseWithData($message, $data);
    }


    public function index(Request $request)
    {
        $auth = Auth::id();
        $profile = UserProfile::where('user_id', $auth)->first();
        $cart = DB::table('carts')->where('user_profile_id', $profile->id)->get();

        $productIds = $cart->pluck('product_id')->toArray();
        $variantIds = $cart->pluck('variant_price_id')->toArray();
        $qtys       = $cart->pluck('qty')->toArray();
        $id_cart    = $cart->pluck('id')->toArray();

        // Pastikan semua array panjangnya sama
        $variantIds = array_pad($variantIds, count($productIds), null);
        $qtys       = array_pad($qtys, count($productIds), 1);
        $id_cart    = array_pad($id_cart, count($productIds), null);

        $groupedStores = [];

        foreach ($productIds as $i => $productId) {
            $variantId = $variantIds[$i];

            $product = Product::join('product_deliveries', 'product_deliveries.product_id', '=', 'products.id')
                ->join('shop_profiles', 'shop_profiles.id', '=', 'products.seller_id')
                ->leftJoin('product_promotions', function ($join) {
                    $join->on('products.id', '=', 'product_promotions.product_id')
                        ->whereNull('product_promotions.product_variant_price_id')
                        ->where('product_promotions.status', 'active');
                })
                ->where('products.id', $productId)
                ->select(
                    'products.*',
                    'product_promotions.discount_price',
                    'product_promotions.discount_percent',
                    'product_deliveries.weight',
                    'product_deliveries.length',
                    'product_deliveries.width',
                    'product_deliveries.height',
                    'product_deliveries.is_dangerous_product',
                    'product_deliveries.is_pre_order',
                    'product_deliveries.is_cost_by_seller',
                    'product_deliveries.address_shop_id',
                    'product_deliveries.insurance',
                    'product_deliveries.service_ids',
                    'product_deliveries.subsidy',
                    'product_deliveries.preorder_duration',
                    'shop_profiles.shop_name',
                    'shop_profiles.id as shop_id'
                )
                ->first();

            if (!$product) continue;

            // ⛔ Skip kalau variant kosong tapi produk punya varian
            if (
                ($variantId === null || $variantId === '' || $variantId === 'null') &&
                DB::table('product_variants')->where('product_id', $productId)->exists()
            ) {
                continue;
            }

            // Kalau ada variantId, pastikan datanya ada
            if ($variantId !== null && $variantId !== '' && $variantId !== 'null') {
                $variant = DB::table('product_variant_prices')
                    ->leftJoin('product_promotions', function ($join) {
                        $join->on('product_variant_prices.id', '=', 'product_promotions.product_variant_price_id')
                            ->where('product_promotions.status', 'active');
                    })
                    ->where('product_variant_prices.id', $variantId)
                    ->select(
                        'product_variant_prices.*',
                        'product_promotions.discount_price',
                        'product_promotions.discount_percent'
                    )
                    ->first();

                // Kalau variant tidak ditemukan → skip
                if (!$variant) {
                    continue;
                }

                $product->discount_percent = $variant->discount_percent;
                $product->discount_price   = $variant->discount_price;
                $product->price            = $variant->price;
                $product->variant          = $variant;
                $product['id_variant']     = $variant->id;
            }

            $dataProduct = $product;
            $dataProduct['quantity'] = (int) ($qtys[$i] ?? 1);

            $combinations = Product::with([
                'variants.values',
                'variantPrices.compositions.value.variant',
                'variantPrices.promotion',
            ])->where('products.id', $product->id)->first();

            $variants = $combinations->variants->map(fn($v) => [
                'id'      => $v->id,
                'variant' => $v->variant,
                'options' => $v->values->pluck('value'),
            ]);

            $dataProduct['combinations']    = $combinations;
            $dataProduct['variant_prices']  = $variants;

            $shopId = $product->shop_id;

            if (!isset($groupedStores[$shopId])) {
                $citiesSeller = DB::table('shop_addresses')
                    ->join('master_cities', 'master_cities.id', '=', 'shop_addresses.citie_id')
                    ->where('seller_id', $shopId)
                    ->where('is_primary', 1)
                    ->select('master_cities.name')
                    ->first();

                if (!$citiesSeller) {
                    $citiesSeller = DB::table('shop_addresses')
                        ->join('master_cities', 'master_cities.id', '=', 'shop_addresses.citie_id')
                        ->where('seller_id', $shopId)
                        ->orderBy('shop_addresses.id', 'desc')
                        ->select('master_cities.name')
                        ->first();
                }

                $groupedStores[$shopId] = [
                    'id'      => (string) $shopId,
                    'name'    => $product->shop_name,
                    'cities'  => $citiesSeller->name ?? null,
                    'products' => [],
                ];
            }

            $groupedStores[$shopId]['products'][] = $dataProduct;
        }

        $message = "Data cart ditemukan";
        return $this->utilityService->is200ResponseWithData($message, array_values($groupedStores));
    }


    /**
     * Create a new banner with image upload.
     * POST /banners
     */
    public function store(Request $request)
    {
        $auth = Auth::id();
        $profile = UserProfile::where('user_id', $auth)->first();

        // Cek apakah sudah ada data cart untuk kombinasi ini
        $existingCart = Cart::where('user_profile_id', $profile->id)
            ->where('product_id', $request->product_id)
            ->where('variant_price_id', $request->variant_price_id)
            ->first();

        if ($existingCart) {
            // Tambahkan qty
            $existingCart->qty += $request->qty;
            $existingCart->save();
            $message = "Jumlah produk di keranjang berhasil diperbarui!";
        } else {
            // Buat data baru
            Cart::create([
                'user_profile_id' => $profile->id,
                'product_id' => $request->product_id,
                'variant_price_id' => $request->variant_price_id,
                'qty' => $request->qty,
            ]);
            $message = "Tambah keranjang berhasil!";
        }

        return $this->utilityService->is200Response($message);
    }

    public function updateVariant(Request $request)
    {
        $auth = Auth::id();
        $profile = UserProfile::where('user_id', $auth)->first();
        $dataCart = DB::table('carts')->where('product_id', $request->product_id)->where('variant_price_id', $request->varian_id_old)->where('user_profile_id', $profile->id)->first();
        if (!$dataCart) {
            return $this->utilityService->is404Response('Keranjang tidak ditemukan');
        }
        $Cart = Cart::find($dataCart->id);
        $Cart->variant_price_id = $request->varian_id_new;
        if ($Cart->save()) {
            $success_message = "Varian berhasil diganti";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
    public function updateQty(Request $request)
    {
        $auth = Auth::id();
        $profile = UserProfile::where('user_id', $auth)->first();
        $dataCart = DB::table('carts')->where('product_id', $request->product_id)->where('variant_price_id', $request->varian_id_old)->where('user_profile_id', $profile->id)->first();
        if (!$dataCart) {
            return $this->utilityService->is404Response('Keranjang tidak ditemukan');
        }
        $Cart = Cart::find($dataCart->id);
        $Cart->qty = $request->qty;
        if ($Cart->save()) {
            $success_message = "Varian berhasil diganti";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
    public function delete($id_product, $id_variant)
    {
        $auth = Auth::id();
        $profile = UserProfile::where('user_id', $auth)->first();

        // Query utama sesuai variant yang diberikan
        $query = DB::table('carts')
            ->where('product_id', $id_product)
            ->where('user_profile_id', $profile->id);

        if ($id_variant === 'null' || $id_variant === null) {
            $query->whereNull('variant_price_id');
        } else {
            $query->where('variant_price_id', $id_variant);
        }

        $dataCart = $query->first();

        if (!$dataCart) {
            // Jika tidak ditemukan, coba cari data keranjang lain dengan product_id sama tapi tanpa variant_price_id (null)
            $fallbackQuery = DB::table('carts')
                ->where('product_id', $id_product)
                ->where('user_profile_id', $profile->id)
                ->whereNull('variant_price_id');

            $fallbackCart = $fallbackQuery->first();

            if ($fallbackCart) {
                // Hapus data fallback yang ditemukan
                $cart = Cart::find($fallbackCart->id);
                if ($cart && $cart->delete()) {
                    return $this->utilityService->is200Response("Keranjang fallback berhasil dihapus");
                } else {
                    return $this->utilityService->is500Response("Problem dengan server saat hapus fallback");
                }
            }

            // Jika tidak ada fallback juga, baru return 404
            return $this->utilityService->is404Response('Keranjang tidak ditemukan');
        }

        $cart = Cart::find($dataCart->id);
        if ($cart->delete()) {
            return $this->utilityService->is200Response("Keranjang berhasil dihapus");
        } else {
            return $this->utilityService->is500Response("Problem dengan server");
        }
    }


    public function deleteMultiple(Request $request)
    {
        $auth = Auth::id();
        $profile = UserProfile::where('user_id', $auth)->first();

        $productIds = $request->input('product_id', []);
        $variantIds = $request->input('variant_id', []);

        // Validasi jumlah data sama
        if (count($productIds) !== count($variantIds)) {
            return $this->utilityService->is404Response('Jumlah product_id dan variant_id tidak sesuai');
        }

        $deletedCount = 0;

        foreach ($productIds as $index => $productId) {
            $variantId = $variantIds[$index] ?? null;

            $query = DB::table('carts')
                ->where('product_id', $productId)
                ->where('user_profile_id', $profile->id);

            if ($variantId === 'null' || $variantId === null || $variantId === '') {
                $query->whereNull('variant_price_id');
            } else {
                $query->where('variant_price_id', $variantId);
            }

            $dataCart = $query->first();

            if (!$dataCart) {
                // Fallback: coba cari keranjang dengan variant_price_id null untuk produk yang sama
                $fallbackCart = DB::table('carts')
                    ->where('product_id', $productId)
                    ->where('user_profile_id', $profile->id)
                    ->whereNull('variant_price_id')
                    ->first();

                if ($fallbackCart) {
                    $cart = Cart::find($fallbackCart->id);
                    if ($cart && $cart->delete()) {
                        $deletedCount++;
                    }
                    continue; // lanjut ke iterasi berikutnya
                }
            } else {
                $cart = Cart::find($dataCart->id);
                if ($cart && $cart->delete()) {
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0) {
            return $this->utilityService->is200Response("$deletedCount keranjang berhasil dihapus");
        } else {
            return $this->utilityService->is404Response("Tidak ada keranjang yang dihapus");
        }
    }
}
