<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariantPrice;
use App\Models\ShopProfile;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    // public function index(Request $request)
    // {
    //     $variantIds = $request->input('variant_id', []);
    //     $qtys = $request->input('qty', []);
    //     $productIds = $request->input('product_id', []);

    //     // Pastikan semua array panjangnya sama
    //     $variantIds = array_pad($variantIds, count($productIds), null);
    //     $qtys = array_pad($qtys, count($productIds), 1);

    //     $groupedStores = [];

    //     foreach ($productIds as $i => $productId) {
    //         $product = Product::join('product_deliveries', 'product_deliveries.product_id', '=', 'products.id')
    //             ->join('shop_profiles', 'shop_profiles.id', '=', 'products.seller_id')
    //             ->leftJoin('product_promotions', function ($join) {
    //                 $join->on('products.id', '=', 'product_promotions.product_id')
    //                     ->whereNull('product_promotions.product_variant_price_id')
    //                     ->where('product_promotions.status', 'active');
    //             })
    //             ->where('products.id', $productId)
    //             ->select(
    //                 'products.*',
    //                 'product_promotions.discount_price',
    //                 'product_promotions.discount_percent',
    //                 'product_deliveries.weight',
    //                 'product_deliveries.length',
    //                 'product_deliveries.width',
    //                 'product_deliveries.height',
    //                 'product_deliveries.is_dangerous_product',
    //                 'product_deliveries.is_pre_order',
    //                 'product_deliveries.is_cost_by_seller',
    //                 'product_deliveries.address_shop_id',
    //                 'product_deliveries.insurance',
    //                 'product_deliveries.service_ids',
    //                 'product_deliveries.subsidy',
    //                 'product_deliveries.preorder_duration',
    //                 'shop_profiles.shop_name',
    //                 'shop_profiles.id as shop_id'
    //             )
    //             ->first();

    //         if (!$product) continue;

    //         $variantId = $variantIds[$i];
    //         if ($variantId) {
    //             $variant = DB::table('product_variant_prices')
    //                 ->leftJoin('product_promotions', function ($join) {
    //                     $join->on('product_variant_prices.id', '=', 'product_promotions.product_variant_price_id')
    //                         ->where('product_promotions.status', 'active');
    //                 })
    //                 ->where('product_variant_prices.id', $variantId)
    //                 ->select(
    //                     'product_variant_prices.*',
    //                     'product_promotions.discount_price',
    //                     'product_promotions.discount_percent'
    //                 )
    //                 ->first();

    //             $product->variant = $variant;
    //         }

    //         $product->quantity = (int) ($qtys[$i] ?? 1);
    //         $combinations = Product::with([
    //             'variants.values',
    //             'variantPrices.compositions.value.variant',
    //         ])->where('products.id', $product->id)->first();
    //         $variants = $combinations->variants->map(fn($v) => [
    //             'id' => $v->id,
    //             'variant' => $v->variant,
    //             'options' => $v->values->pluck('value'),
    //         ]);
    //         $product->combinations = $combinations;
    //         $product->variant_prices = $variants;
    //         // Kelompokkan berdasarkan toko
    //         $shopId = $product->shop_id;
    //         if (!isset($groupedStores[$shopId])) {
    //             $citiesSeller = DB::table('shop_addresses')->join('master_cities', 'master_cities.id', '=', 'shop_addresses.citie_id')->where('is_primary', 1)->select('master_cities.name')->first();
    //             $groupedStores[$shopId] = [
    //                 'id' => (string) $shopId,
    //                 'name' => $product->shop_name,
    //                 'cities' => $citiesSeller->name,
    //                 'products' => [],
    //                 'shippingOptions' => [],
    //                 'selectedShipping' => null,
    //             ];
    //         }

    //         $groupedStores[$shopId]['products'][] = $product;

    //         // Proses shippingOptions (sekali per toko)
    //         if (empty($groupedStores[$shopId]['shippingOptions'])) {
    //             $serviceIds = json_decode($product->service_ids ?? '[]', true);
    //             $shippingOptions = [];

    //             if (!empty($serviceIds)) {
    //                 // Handle Instant (service ID = 0)
    //                 if (in_array(0, $serviceIds)) {
    //                     $shippingOptions[] = [
    //                         'courier' => 'Instant',
    //                         'logo_url' => 'https://example.com/logo/instant.png',
    //                         'service' => 'Instant Delivery',
    //                         'id' => 0,
    //                         'cost' => 0
    //                     ];
    //                 }

    //                 // Kurir dari DB (selain ID 0)
    //                 $filteredIds = array_filter($serviceIds, fn($id) => $id !== 0);

    //                 if (!empty($filteredIds)) {
    //                     $services = DB::table('courier_services')
    //                         ->join('couriers', 'courier_services.courier_id', '=', 'couriers.id')
    //                         ->whereIn('courier_services.id', $filteredIds)
    //                         ->select(
    //                             'courier_services.id as service_id',
    //                             'courier_services.name as service_name',
    //                             'couriers.name as courier_name',
    //                             'couriers.logo_url'
    //                         )
    //                         ->get();

    //                     $grouped = $services->groupBy('courier_name');

    //                     foreach ($grouped as $courierName => $courierServices) {
    //                         $logoUrl = $courierServices->first()->logo_url;

    //                         $servicesArr = $courierServices->map(function ($item) {
    //                             return [
    //                                 'id' => $item->service_id,
    //                                 'name' => $item->service_name
    //                             ];
    //                         })->values()->toArray();

    //                         $shippingOptions[] = [
    //                             'courier' => $courierName,
    //                             'logo_url' => $logoUrl,
    //                             'services' => $servicesArr,
    //                             'cost' => 0
    //                         ];
    //                     }
    //                 }
    //             }

    //             $groupedStores[$shopId]['shippingOptions'] = $shippingOptions;
    //             $groupedStores[$shopId]['selectedShipping'] = $shippingOptions[0] ?? null;
    //         }
    //     }

    //     $message = "Data checkout ditemukan";
    //     return $this->utilityService->is200ResponseWithData($message, array_values($groupedStores));
    // }
    public function index(Request $request)
    {
        $variantIds = $request->input('variant_id', []);
        $qtys = $request->input('qty', []);
        $productIds = $request->input('product_id', []);
        // Pastikan semua array panjangnya sama
        $variantIds = array_pad($variantIds, count($productIds), null);
        $qtys = array_pad($qtys, count($productIds), 1);

        $groupedStores = [];

        foreach ($productIds as $i => $productId) {
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
            $variantId = $variantIds[$i];
            if ($variantId) {
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

                if ($variant) {
                    // kalau ada variant, jangan fallback ke diskon produk
                    $product->variant = $variant;
                    $product['id_variant'] = $variant->id;

                    // kosongkan diskon di product supaya tidak dipakai
                    $product->discount_price = null;
                    $product->discount_percent = null;
                }
            }


            // $dataProduct = [
            //     'id' => (int) ($id_cart[$i] ?? 1),
            //     'name' => $product->name,
            //     'originalPrice'   => (string) ($discountedPrice ?? $product->price),
            //     'discountedPrice' => (string) ($originalPrice ?? $product->discount_price),
            //     'discount'        => (string) ($discount ?? $product->discount_percent),
            //     'voucher' => $product->voucher,
            //     'subsidy' => $product->subsidy,
            //     'variant' => $product->variant
            // ];

            $dataProduct = $product;
            $dataProduct['quantity'] = (int) ($qtys[$i] ?? 1);
            $combinations = Product::with([
                'variants.values',
                'variantPrices.compositions.value.variant',
                'variantPrices.promotion',
            ])->where('products.id', $product->id)->first();
            $variants = $combinations->variants->map(fn($v) => [
                'id' => $v->id,
                'variant' => $v->variant,
                'options' => $v->values->pluck('value'),
            ]);
            $dataProduct['combinations'] = $combinations;
            $dataProduct['variant_prices'] = $variants;
            // Kelompokkan berdasarkan toko
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
                        ->orderBy('shop_addresses.id', 'desc') // ambil data terakhir
                        ->select('master_cities.name')
                        ->first();
                }
                $groupedStores[$shopId] = [
                    'id' => (string) $shopId,
                    'name' => $product->shop_name,
                    'cities' => $citiesSeller->name,
                    'products' => [],
                ];
            }
            if (empty($groupedStores[$shopId]['shippingOptions'])) {
                $serviceIds = json_decode($product->service_ids ?? '[]', true);
                $shippingOptions = [];

                if (!empty($serviceIds)) {
                    // Handle Instant (service ID = 0)
                    if (in_array(0, $serviceIds)) {
                        $shippingOptions[] = [
                            'courier' => 'Instant',
                            'logo_url' => 'https://example.com/logo/instant.png',
                            'service' => 'Instant Delivery',
                            'id' => 0,
                            'cost' => 0
                        ];
                    }

                    // Kurir dari DB (selain ID 0)
                    $filteredIds = array_filter($serviceIds, fn($id) => $id !== 0);

                    if (!empty($filteredIds)) {
                        $services = DB::table('courier_services')
                            ->join('couriers', 'courier_services.courier_id', '=', 'couriers.id')
                            ->whereIn('courier_services.id', $filteredIds)
                            ->select(
                                'courier_services.id as service_id',
                                'courier_services.name as service_name',
                                'couriers.name as courier_name',
                                'couriers.logo_url'
                            )
                            ->get();

                        $grouped = $services->groupBy('courier_name');

                        foreach ($grouped as $courierName => $courierServices) {
                            $logoUrl = $courierServices->first()->logo_url;

                            $servicesArr = $courierServices->map(function ($item) {
                                return [
                                    'id' => $item->service_id,
                                    'name' => $item->service_name
                                ];
                            })->values()->toArray();

                            $shippingOptions[] = [
                                'courier' => $courierName,
                                'logo_url' => $logoUrl,
                                'services' => $servicesArr,
                                'cost' => 0
                            ];
                        }
                    }
                }

                $groupedStores[$shopId]['shippingOptions'] = $shippingOptions;
                $groupedStores[$shopId]['selectedShipping'] = $shippingOptions[0] ?? null;
            }

            $groupedStores[$shopId]['products'][] = $dataProduct;
        }

        $message = "Data cart ditemukan";
        return $this->utilityService->is200ResponseWithData($message, array_values($groupedStores));
    }
}
