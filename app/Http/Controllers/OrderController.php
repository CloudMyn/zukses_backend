<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\IncomeDetail;
use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShopIncome;
use App\Models\UserProfile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class OrderController extends Controller
{
    public function __construct()
    {
        Config::$isProduction = config('midtrans.is_production');
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
    }

    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
            'totalAmount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->utilityService->is422Response($validator->errors()->first());
        }

        $orderId = 'ORDER-' . time();
        $itemDetails = [];
        $userId = Auth::id();
        $userProfile = UserProfile::where('user_id', $userId)->first();

        $order = \App\Models\Order::create([
            'order_id'       => $orderId,
            'user_profile_id' => $userProfile->id,
            'total_price'    => 0,
            'status'         => 'pending',
        ]);
        $totalProduk  = 0;
        $itemDetails  = [];
        // ===== Siapkan array untuk menyimpan total per seller =====
        $sellerIncomes = [];

        foreach ($request->items as $item) {
            $subtotal = $item['qty'] * $item['price'];
            $totalProduk += $subtotal;

            $shipping = null;
            if (!empty($item['selectedShipping'])) {
                $shipping = DB::table('couriers')->where('name', $item['selectedShipping'])->first();
            }

            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'address_id' => $request->address_id,
                'original_price' => $item['originalPrice'] ?? 0,
                'insurance' => $item['insurance'] ?? 0,
                'service_fee' => $item['service_fee'] ?? 0,
                'payment_fee' => $item['payment_fee'] ?? 0,
                'discount' => $item['discount'] ?? 0,
                'subsidy' => $item['subsidy'] ?? 0,
                'voucher' => $item['voucher'] ?? 0,
                'courier_id'     => $shipping->id ?? null,
                'shipping'       => $item['selectedShipping'] ?? null,
                'price_shipping' => $item['priceShipping'] ?? 0,
            ]);

            // ===== Kumpulkan income per seller =====
            $seller = DB::table('products')->where('id', $item['product_id'])->first();
            if ($seller) {
                $shopProfileId = $seller->seller_id;

                $perItemComponents = [
                    'product_sale' => ($item['originalPrice'] * $item['qty']) ?? 0,
                    'discount'     => ($item['discount'] ?? 0) * -1,
                    'voucher'      => ($item['voucher'] ?? 0) * -1,
                    'subsidy'      => ($item['subsidy'] ?? 0) * -1,
                ];

                $totalIncomeItem = array_sum($perItemComponents);

                if (!isset($sellerIncomes[$shopProfileId])) {
                    $sellerIncomes[$shopProfileId] = [
                        'shop_income' => ShopIncome::firstOrCreate(
                            ['shop_profile_id' => $shopProfileId, 'order_id' => $order->id],
                            ['total_income' => 0]
                        ),
                        'total_income' => 0,
                        'onceComponents' => [
                            'insurance'   => $item['insurance'] ?? 0,
                            'service_fee' => $item['service_fee'] ?? 0,
                            'payment_fee' => $item['payment_fee'] ?? 0,
                            'priceShipping' => $item['priceShipping'] ?? 0,
                        ]
                    ];
                }

                // Tambahkan income item
                $sellerIncomes[$shopProfileId]['total_income'] += $totalIncomeItem;
            }

            // ===== Hapus cart user sesuai item dibeli =====
            $auth = Auth::id();
            $profile = UserProfile::where('user_id', $auth)->first();

            $carts  = DB::table('carts')
                ->where('product_id', $item['product_id'])
                ->where('user_profile_id', $profile->id)->first();

            if ($carts) {
                $query = DB::table('carts')
                    ->where('product_id', $item['product_id'])
                    ->where('user_profile_id', $profile->id);

                if ($item['variant_id'] === 'null' || $item['variant_id'] === null) {
                    $query->whereNull('variant_price_id');
                } else {
                    $query->where('variant_price_id', $item['variant_id']);
                }

                $dataCart = $query->first();
                if ($dataCart) {
                    Cart::find($dataCart->id)?->delete();
                }
            }
        }

        // ===== Setelah loop semua item, update income per seller =====
        foreach ($sellerIncomes as $shopProfileId => $data) {
            $shopIncome = $data['shop_income'];

            // Tambahkan sekali komponen (insurance, fee, shipping)
            $totalOnceComponents = array_sum($data['onceComponents']);
            $finalIncome =  $data['total_income'];
            $detailSeller = 0;
            $detailAdministrasi = 0;
            $detailOrder = 0;
            // ===== Potongan fee per seller =====
            $sellerFee = DB::table('service_fees')->where('fee_type', 'seller_fee')->first();
            if ($sellerFee) {
                $sellerDetail = DB::table('income_details')->where('type', 'seller_fee')->where('shop_income_id', $shopIncome->id)->first();
                if (!$sellerDetail) {
                    IncomeDetail::create([
                        'shop_income_id' => $shopIncome->id,
                        'type' => 'seller_fee',
                        'amount' => $sellerFee->fee_amount
                    ]);
                    $detailSeller = $sellerFee->fee_amount;
                }
            }

            $otherFee = DB::table('service_fees')
                ->where('fee_type', 'other_fee')
                ->where('min_transaction', '<=', $finalIncome)
                ->where('max_transaction', '>=', $finalIncome)
                ->first();
            if ($otherFee) {
                $administrasiDetail = DB::table('income_details')->where('type', 'administrasi_fee')->where('shop_income_id', $shopIncome->id)->first();
                if (!$administrasiDetail) {
                    IncomeDetail::create([
                        'shop_income_id' => $shopIncome->id,
                        'type' => 'administrasi_fee',
                        'amount' => $otherFee->fee_amount
                    ]);
                    $detailAdministrasi = $otherFee->fee_amount;
                    // $finalIncome -= $otherFee->fee_amount;
                }
            }
            $orderFee = DB::table('service_fees')
                ->where('fee_type', 'order_processing')
                ->where('min_transaction', '<=', $finalIncome)
                ->where('max_transaction', '>=', $finalIncome)
                ->first();
            if ($orderFee) {
                $administrasiDetail = DB::table('income_details')->where('type', 'order_processing')->where('shop_income_id', $shopIncome->id)->first();
                if (!$administrasiDetail) {
                    IncomeDetail::create([
                        'shop_income_id' => $shopIncome->id,
                        'type' => 'order_processing',
                        'amount' => $orderFee->fee_amount
                    ]);
                    $detailOrder = $orderFee->fee_amount;
                    // $finalIncome -= $otherFee->fee_amount;
                }
            }

            // Update total income terakhir
            $shopIncome->total_income = $finalIncome - ($detailAdministrasi + $detailSeller + $detailOrder);
            $shopIncome->save();
        }

        // langsung pakai totalAmount dari request
        $order->total_price = $request->totalAmount;
        $order->save();
        $selisih = $request->totalAmount - $totalProduk;

        if ($selisih > 0) {
            $itemDetails[] = [
                'id'       => 'EXTRA',
                'price'    => $selisih,
                'quantity' => 1,
                'name'     => 'Ongkos Kirim',
            ];
        }

        $snapParams = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $request->totalAmount,
            ],
            'customer_details' => [
                'first_name' => $request->customer_name,
                'email'      => $request->customer_email,
                'phone'      => $request->customer_phone,
            ],
            'item_details' => $itemDetails,
        ];

        $snapToken = Snap::getSnapToken($snapParams);

        $data = [
            'snap_token' => $snapToken,
            'order_id'   => $orderId,
            'client_key' => config('midtrans.client_key'),
        ];

        return response()->json([
            'status'  => 'success',
            'message' => "Berhasil Checkout",
            'data'    => $data
        ], 200);
    }


    public function midtransCallback(Request $request)
    {
        try {
            // 1. Log payload asli
            Log::info('🔔 Midtrans Callback Received', $request->all());

            // 2. Ambil data penting
            $order_id     = $request->input('order_id');
            $status_code  = $request->input('status_code');
            $gross_amount = $request->input('gross_amount'); // jangan ubah dulu
            $signature    = $request->input('signature_key');
            $trx_status   = $request->input('transaction_status');

            // 3. Ambil server key
            $serverKey = config('midtrans.server_key');
            if (!$serverKey) {
                Log::error('❌ Server key not found in config.');
                return response()->json(['message' => 'Server key not configured'], 500);
            }

            // 4. Normalisasi gross_amount menjadi format string "10000.00"
            $gross_amount_normalized = number_format((float)$gross_amount, 2, '.', '');

            // 8. Cari order
            $order = \App\Models\Order::where('order_id', $order_id)->first();
            if (!$order) {
                Log::warning('❌ Order not found', ['order_id' => $order_id]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // 9. Update status order
            DB::transaction(function () use ($order, $trx_status) {
                switch ($trx_status) {
                    case 'settlement':
                        $order->status = 'paid';
                        foreach ($order->items as $item) {
                            $item->status = 2;
                            $item->save();
                        }
                        break;
                    case 'pending':
                        $order->status = 'pending';
                        break;
                    case 'cancel':
                    case 'expire':
                    case 'deny':
                        $order->status = 'failed';
                        break;
                    default:
                        Log::warning('⚠️ Unknown transaction_status', ['status' => $trx_status]);
                        throw new \Exception('Unknown status');
                }
                $order->save();
            });

            Log::info('✅ Order status updated', [
                'order_id' => $order_id,
                'status'   => $order->status,
                'trx_status' => $trx_status
            ]);

            return response()->json(['message' => 'Order updated'], 200);
        } catch (\Exception $e) {
            Log::error('❌ Midtrans callback error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Callback processing failed'], 500);
        }
    }


    public function payVa(Request $request)
    {
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$serverKey = config('midtrans.server_key');

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string',
            'customer_email' => 'required|string',
            'customer_phone' => 'required|string',
            'bank' => 'required|in:bca,bni,bri',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->utilityService->is422Response($validator->errors()->first());
        }

        $orderId = 'ORDER-' . time();
        $total = 0;
        $userId = Auth::id();
        $userProfile = UserProfile::where('user_id', $userId)->first();
        $order = \App\Models\Order::create([
            'order_id' => $orderId,
            'user_profile_id' => $userProfile->id,
            'total_price' => 0,
            'status' => 'pending',
        ]);
        $totalProduk  = 0;
        $itemDetails  = [];
        // ===== Siapkan array untuk menyimpan total per seller =====
        $sellerIncomes = [];

        foreach ($request->items as $item) {
            $subtotal = $item['qty'] * $item['price'];
            $totalProduk += $subtotal;

            $shipping = null;
            if (!empty($item['selectedShipping'])) {
                $shipping = DB::table('couriers')->where('name', $item['selectedShipping'])->first();
            }

            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'address_id' => $request->address_id,
                'original_price' => $item['originalPrice'] ?? 0,
                'insurance' => $item['insurance'] ?? 0,
                'service_fee' => $item['service_fee'] ?? 0,
                'payment_fee' => $item['payment_fee'] ?? 0,
                'discount' => $item['discount'] ?? 0,
                'subsidy' => $item['subsidy'] ?? 0,
                'voucher' => $item['voucher'] ?? 0,
                'courier_id'     => $shipping->id ?? null,
                'shipping'       => $item['selectedShipping'] ?? null,
                'price_shipping' => $item['priceShipping'] ?? 0,
            ]);

            // ===== Kumpulkan income per seller =====
            $seller = DB::table('products')->where('id', $item['product_id'])->first();
            if ($seller) {
                $shopProfileId = $seller->seller_id;

                $perItemComponents = [
                    'product_sale' => ($item['originalPrice'] * $item['qty']) ?? 0,
                    'discount'     => ($item['discount'] ?? 0) * -1,
                    'voucher'      => ($item['voucher'] ?? 0) * -1,
                    'subsidy'      => ($item['subsidy'] ?? 0) * -1,
                ];

                $totalIncomeItem = array_sum($perItemComponents);

                if (!isset($sellerIncomes[$shopProfileId])) {
                    $sellerIncomes[$shopProfileId] = [
                        'shop_income' => ShopIncome::firstOrCreate(
                            ['shop_profile_id' => $shopProfileId, 'order_id' => $order->id],
                            ['total_income' => 0]
                        ),
                        'total_income' => 0,
                        'onceComponents' => [
                            'insurance'   => $item['insurance'] ?? 0,
                            'service_fee' => $item['service_fee'] ?? 0,
                            'payment_fee' => $item['payment_fee'] ?? 0,
                            'priceShipping' => $item['priceShipping'] ?? 0,
                        ]
                    ];
                }

                // Tambahkan income item
                $sellerIncomes[$shopProfileId]['total_income'] += $totalIncomeItem;
            }

            // ===== Hapus cart user sesuai item dibeli =====
            $auth = Auth::id();
            $profile = UserProfile::where('user_id', $auth)->first();

            $carts  = DB::table('carts')
                ->where('product_id', $item['product_id'])
                ->where('user_profile_id', $profile->id)->first();

            if ($carts) {
                $query = DB::table('carts')
                    ->where('product_id', $item['product_id'])
                    ->where('user_profile_id', $profile->id);

                if ($item['variant_id'] === 'null' || $item['variant_id'] === null) {
                    $query->whereNull('variant_price_id');
                } else {
                    $query->where('variant_price_id', $item['variant_id']);
                }

                $dataCart = $query->first();
                if ($dataCart) {
                    Cart::find($dataCart->id)?->delete();
                }
            }
        }

        // ===== Setelah loop semua item, update income per seller =====
        foreach ($sellerIncomes as $shopProfileId => $data) {
            $shopIncome = $data['shop_income'];

            // Tambahkan sekali komponen (insurance, fee, shipping)
            $totalOnceComponents = array_sum($data['onceComponents']);
            $finalIncome =  $data['total_income'];
            $detailSeller = 0;
            $detailAdministrasi = 0;
            $detailOrder = 0;
            // ===== Potongan fee per seller =====
            $sellerFee = DB::table('service_fees')->where('fee_type', 'seller_fee')->first();
            if ($sellerFee) {
                $sellerDetail = DB::table('income_details')->where('type', 'seller_fee')->where('shop_income_id', $shopIncome->id)->first();
                if (!$sellerDetail) {
                    IncomeDetail::create([
                        'shop_income_id' => $shopIncome->id,
                        'type' => 'seller_fee',
                        'amount' => $sellerFee->fee_amount
                    ]);
                    $detailSeller = $sellerFee->fee_amount;
                }
            }

            $otherFee = DB::table('service_fees')
                ->where('fee_type', 'other_fee')
                ->where('min_transaction', '<=', $finalIncome)
                ->where('max_transaction', '>=', $finalIncome)
                ->first();
            if ($otherFee) {
                $administrasiDetail = DB::table('income_details')->where('type', 'administrasi_fee')->where('shop_income_id', $shopIncome->id)->first();
                if (!$administrasiDetail) {
                    IncomeDetail::create([
                        'shop_income_id' => $shopIncome->id,
                        'type' => 'administrasi_fee',
                        'amount' => $otherFee->fee_amount
                    ]);
                    $detailAdministrasi = $otherFee->fee_amount;
                    // $finalIncome -= $otherFee->fee_amount;
                }
            }
            $orderFee = DB::table('service_fees')
                ->where('fee_type', 'order_processing')
                ->where('min_transaction', '<=', $finalIncome)
                ->where('max_transaction', '>=', $finalIncome)
                ->first();
            if ($orderFee) {
                $administrasiDetail = DB::table('income_details')->where('type', 'order_processing')->where('shop_income_id', $shopIncome->id)->first();
                if (!$administrasiDetail) {
                    IncomeDetail::create([
                        'shop_income_id' => $shopIncome->id,
                        'type' => 'order_processing',
                        'amount' => $orderFee->fee_amount
                    ]);
                    $detailOrder = $orderFee->fee_amount;
                    // $finalIncome -= $otherFee->fee_amount;
                }
            }

            // Update total income terakhir
            $shopIncome->total_income = $finalIncome - ($detailAdministrasi + $detailSeller + $detailOrder);
            $shopIncome->save();
        }


        $order->total_price = $request->totalAmount;
        $order->save();
        $customerEmail = $request->customer_email;

        // Cek apakah is valid email
        $isValidEmail = filter_var($customerEmail, FILTER_VALIDATE_EMAIL) !== false;

        // Siapkan customer_details
        $customerDetails = [
            'first_name' => $request->customer_name,
            'phone' => $request->customer_phone,
        ];

        if ($isValidEmail) {
            $customerDetails['email'] = $customerEmail;
        }

        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $request->totalAmount,
            ],
            'bank_transfer' => [
                'bank' => $request->bank,
            ],
            'customer_details' => $customerDetails,
        ];


        $response = \Midtrans\CoreApi::charge($params);

        return response()->json([
            'order_id' => $orderId,
            'bank' => $response->va_numbers[0]->bank ?? $request->bank,
            'va_number' => $response->va_numbers[0]->va_number ?? null,
            'expiry' => $response->expiry_time ?? null,
            'gross_amount' => $total
        ]);
    }




    public function showGroupedBySeller(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $searchTerm = $request->get('search'); // ➡️ Ambil parameter pencarian
            $fromDate = $request->get('from_date'); // ➡️ Ambil tanggal awal
            $toDate = $request->get('to_date');   // ➡️ Ambil tanggal akhir

            $ordersQuery = Order::with([
                'userProfile',
                'orderItems.product.shopProfile'
            ]);

            // ➡️ Logika Pencarian: Berdasarkan nama toko, pembeli, atau invoice
            if ($searchTerm) {
                $ordersQuery->where(function ($query) use ($searchTerm) {
                    // Cari berdasarkan invoice
                    $query->where('order_id', 'like', '%' . $searchTerm . '%');

                    // Cari berdasarkan nama pembeli (userProfile)
                    $query->orWhereHas('userProfile', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%');
                    });

                    // Cari berdasarkan nama toko (shopProfile)
                    $query->orWhereHas('orderItems.product.shopProfile', function ($q) use ($searchTerm) {
                        $q->where('shop_name', 'like', '%' . $searchTerm . '%');
                    });
                });
            }

            // ➡️ Logika Filter Tanggal: created_at
            if ($fromDate && $toDate) {
                $ordersQuery->whereBetween('created_at', [$fromDate . " 00:00:00", $toDate . " 23:59:59"]);
            } elseif ($fromDate) {
                $ordersQuery->whereDate('created_at', '>=', $fromDate);
            } elseif ($toDate) {
                $ordersQuery->whereDate('created_at', '<=', $toDate);
            }
            $orders = $ordersQuery->get();

            // Ambil semua order, tapi tambahkan filter berdasarkan status_id jika ada
            $status_id = $request->get('status_id');
            $packagesBySeller = [];

            foreach ($orders as $order) {
                $itemsBySeller = $order->orderItems->groupBy('product.seller_id');
                foreach ($itemsBySeller as $sellerId => $items) {
                    // Filter items (paket) di sini, di dalam loop, seperti perbaikan sebelumnya
                    if ($status_id) {
                        $firstItemStatus = $items->first()->status;
                        if (is_array($status_id)) {
                            if (!in_array($firstItemStatus, $status_id)) {
                                continue;
                            }
                        } else {
                            if ($firstItemStatus != $status_id) {
                                continue;
                            }
                        }
                    }

                    // ... (Sisa kode untuk memproses setiap paket) ...

                    $firstItem   = $items->first();
                    $shopProfile = $firstItem->product->shopProfile;
                    $statusItem  = $firstItem->status;
                    $shippingCost  = $firstItem->price_shipping;

                    $priceItem     = $items->sum(fn($i) => $i->qty * $i->original_price);
                    $insuranceCost = $firstItem->insurance;
                    $serviceCost   = $firstItem->service_fee;
                    $paymentCost   = $firstItem->payment_fee;
                    $discountCost  = $items->sum(fn($i) => $i->discount * $i->qty);
                    $subsidyCost   = $items->sum(fn($i) => $i->subsidy);
                    $voucherCost   = $items->sum(fn($i) => $i->voucher);

                    $buyerUser  = DB::table('users')->where('id', $order->userProfile->user_id)->first();
                    $sellerUser = DB::table('users')->where('id', $shopProfile->user_id)->first();

                    $status = DB::table('master_statuses')->where('id', $statusItem)->value('name');

                    $userAddress = DB::table('user_addresses')
                        ->join('master_provinces', 'master_provinces.id', '=', 'user_addresses.province_id')
                        ->join('master_cities', 'master_cities.id', '=', 'user_addresses.citie_id')
                        ->join('master_subdistricts', 'master_subdistricts.id', '=', 'user_addresses.subdistrict_id')
                        ->join('master_postal_codes', 'master_postal_codes.id', '=', 'user_addresses.postal_code_id')
                        ->where('user_addresses.id', $firstItem->address_id)
                        ->select(
                            'user_addresses.*',
                            'master_provinces.name as province_name',
                            'master_cities.name as city_name',
                            'master_subdistricts.name as district_name',
                            'master_postal_codes.code'
                        )->first();

                    $productDelivery = DB::table('product_deliveries')
                        ->where('product_id', $firstItem->product->id)
                        ->first();

                    $sellerAddressQuery = DB::table('shop_addresses')
                        ->join('master_provinces', 'master_provinces.id', '=', 'shop_addresses.province_id')
                        ->join('master_cities', 'master_cities.id', '=', 'shop_addresses.citie_id')
                        ->join('master_subdistricts', 'master_subdistricts.id', '=', 'shop_addresses.subdistrict_id')
                        ->join('master_postal_codes', 'master_postal_codes.id', '=', 'shop_addresses.postal_code_id')
                        ->select(
                            'shop_addresses.*',
                            'master_provinces.name as province_name',
                            'master_cities.name as city_name',
                            'master_subdistricts.name as district_name',
                            'master_postal_codes.code'
                        );

                    $sellerAddress = $productDelivery
                        ? $sellerAddressQuery->where('shop_addresses.id', $productDelivery->address_shop_id)->first()
                        : $sellerAddressQuery->where('shop_addresses.seller_id', $shopProfile->id)->first();

                    $items = $items->map(function ($item) {
                        $data = [
                            'product_name'   => $item->product->name,
                            'image'          => $item->product->image,
                            'quantity'       => $item->qty,
                            'price'          => $item->price,
                            'original_price' => $item->original_price,
                            'insurance'      => $item->insurance,
                            'service_fee'    => $item->service_fee,
                            'payment_fee'    => $item->payment_fee,
                            'discount'       => $item->discount,
                            'subsidy'        => $item->subsidy,
                            'voucher'        => $item->voucher,
                            'shipping'       => $item->shipping,
                            'price_shipping' => $item->price_shipping,
                        ];

                        if ($item->variant_id) {
                            $combinations = Product::with([
                                'variants.values',
                                'variantPrices.compositions.value.variant',
                                'variantPrices.promotion',
                            ])
                                ->where('products.id', $item->product->id)
                                ->whereHas('variantPrices', function ($q) use ($item) {
                                    $q->where('id', $item->variant_id);
                                })
                                ->first();

                            if ($combinations) {
                                $selectedVariant = $combinations->variantPrices
                                    ->where('id', $item->variant_id)
                                    ->first();

                                $data['variant_prices'] = $selectedVariant;
                            }
                        }

                        return $data;
                    })->values();

                    $details = [];
                    $income = DB::table('shop_incomes')
                        ->where('order_id', $order->id)
                        ->where('shop_profile_id', $shopProfile->id)
                        ->first();

                    $details['total_income'] = $income ? $income->total_income : 0;
                    if ($income) {
                        $incomDetail = DB::table('income_details')
                            ->join('shop_incomes', 'shop_incomes.id', '=', 'income_details.shop_income_id')
                            ->where('income_details.shop_income_id', $income->id)->get();

                        foreach ($incomDetail as $detail) {
                            if (in_array($detail->type, ['order_processing', 'administrasi_fee', 'seller_fee'])) {
                                $details[$detail->type] = $detail->amount;
                            }
                        }
                    }

                    $amount = $priceItem + $shippingCost + $insuranceCost + $serviceCost + $paymentCost - $discountCost - $subsidyCost - $voucherCost;

                    $packagesBySeller[] = [
                        'id'            => $order->id,
                        'invoiceDate'   => $order->created_at,
                        'invoiceNumber' => $order->order_id,
                        'amount'        => $priceItem + $shippingCost + $insuranceCost + $serviceCost + $paymentCost - $discountCost - $subsidyCost - $voucherCost,
                        'priceItem'     => $priceItem,
                        'shippingCost'  => $shippingCost,
                        'insuranceCost' => $insuranceCost,
                        'serviceCost'   => $serviceCost,
                        'paymentCost'   => $paymentCost,
                        'discountCost'  => $discountCost,
                        'subsidyCost'   => $subsidyCost,
                        'voucherCost'   => $voucherCost,
                        'buyer'         => $order->userProfile->name ?? null,
                        'buyerPhone'    => $buyerUser->whatsapp ?? null,
                        'buyerCity'     => $userAddress->city_name ?? null,
                        'buyerAddress'  => $userAddress ?? null,
                        'store'         => $shopProfile->shop_name ?? null,
                        'storePhone'    => $sellerUser->whatsapp ?? null,
                        'storeCity'     => $sellerAddress->city_name ?? null,
                        'storeAddress'  => $sellerAddress ?? null,
                        'status'        => $status,
                        'status_id'     => $statusItem,
                        'items'         => $items,
                        'details'       => $details,
                    ];
                }
            }

            $collection = collect($packagesBySeller);
            // === SUMMARY ===
            $summary = [
                'total_order'  => $collection->count(),
                'total_amount' => $collection->sum('amount'),
            ];

            // Hitung berdasarkan status_id
            foreach ([1, 2, 3, 4, 5, 6] as $statusId) {
                $summary["status_$statusId"] = $collection->where('status_id', $statusId)->count();
            }
            // paginate manual
            $paginated = new LengthAwarePaginator(
                $collection->forPage($page, $perPage)->values(),
                $collection->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Data cart ditemukan',
                'data'    => $paginated->items(),
                'summary' => $summary,
                'meta'    => [
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'per_page'     => (string) $paginated->perPage(),
                    'total'        => (string) $paginated->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Problem with server',
                'error_details' => $e->getMessage()
            ], 500);
        }
    }
    public function showGroupedByCourier(Request $request, $courier_id)
    {
        try {
            $perPage = $request->get('per_page', 25);
            $statusFilter = $request->get('status_id'); // bisa single atau comma separated
            $startDate = $request->get('start_date');   // format: Y-m-d
            $endDate   = $request->get('end_date');     // format: Y-m-d
            $search = $request->get('search');

            $ordersQuery = Order::with([
                'userProfile',
                'orderItems' => function ($query) use ($courier_id, $statusFilter, $search) {
                    $query->where('courier_id', $courier_id);

                    if ($statusFilter) {
                        $statuses = explode(',', $statusFilter);
                        $query->whereIn('status', $statuses);
                    }

                    $query->with('product.shopProfile');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            // Search resi
                            $q->where('resi', 'like', "%{$search}%")
                                // Search nama toko
                                ->orWhereHas('product.shopProfile', function ($q2) use ($search) {
                                    $q2->where('shop_name', 'like', "%{$search}%");
                                });
                        });
                    }
                }
            ])
                // Pastikan order memiliki orderItems sesuai filter
                ->whereHas('orderItems', function ($query) use ($courier_id, $statusFilter, $search) {
                    $query->where('courier_id', $courier_id);

                    if ($statusFilter) {
                        $statuses = explode(',', $statusFilter);
                        $query->whereIn('status', $statuses);
                    }

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('resi', 'like', "%{$search}%")
                                ->orWhereHas('product.shopProfile', function ($q2) use ($search) {
                                    $q2->where('shop_name', 'like', "%{$search}%");
                                });
                        });
                    }
                });

            // Search nama buyer
            if ($search) {
                $ordersQuery->orWhereHas('userProfile', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            // Filter tanggal
            if ($startDate && $endDate) {
                $ordersQuery->whereBetween('created_at', [$startDate . " 00:00:00", $endDate . " 23:59:59"]);
            } elseif ($startDate) {
                $ordersQuery->whereDate('created_at', '>=', $startDate);
            } elseif ($endDate) {
                $ordersQuery->whereDate('created_at', '<=', $endDate);
            }

            $orders = $ordersQuery->paginate($perPage);

            $packages = [];

            foreach ($orders as $order) {
                $items = $order->orderItems;
                if ($items->isEmpty()) continue;

                $firstItem = $items->first();
                $shopProfile = $firstItem->product->shopProfile;
                $statusItem = $firstItem->status;

                $shippingCost = $items->sum(fn($i) => $i->price_shipping ?? 0);
                $priceShipping = $firstItem->price_shipping;
                $priceItem     = $items->sum(fn($i) => $i->qty * $i->original_price);
                $insuranceCost = $items->sum(fn($i) => $i->insurance ?? 0);
                $serviceCost   = $items->sum(fn($i) => $i->service_fee ?? 0);
                $paymentCost   = $items->sum(fn($i) => $i->payment_fee ?? 0);
                $discountCost  = $items->sum(fn($i) => $i->discount * $i->qty);
                $subsidyCost   = $items->sum(fn($i) => $i->subsidy ?? 0);
                $voucherCost   = $items->sum(fn($i) => $i->voucher ?? 0);

                // Buyer & seller
                $buyerUser  = DB::table('users')->where('id', $order->userProfile->user_id)->first();
                $sellerUser = DB::table('users')->where('id', $shopProfile->user_id)->first();

                // Status
                $status = DB::table('master_statuses')->where('id', $statusItem)->value('name');

                // Buyer address
                $userAddress = DB::table('user_addresses')
                    ->join('master_provinces', 'master_provinces.id', '=', 'user_addresses.province_id')
                    ->join('master_cities', 'master_cities.id', '=', 'user_addresses.citie_id')
                    ->join('master_subdistricts', 'master_subdistricts.id', '=', 'user_addresses.subdistrict_id')
                    ->join('master_postal_codes', 'master_postal_codes.id', '=', 'user_addresses.postal_code_id')
                    ->where('user_addresses.id', $firstItem->address_id)
                    ->select(
                        'user_addresses.*',
                        'master_provinces.name as province_name',
                        'master_cities.name as city_name',
                        'master_subdistricts.name as district_name',
                        'master_postal_codes.code'
                    )->first();

                // Seller address
                $productDelivery = DB::table('product_deliveries')
                    ->where('product_id', $firstItem->product->id)
                    ->first();

                $sellerAddressQuery = DB::table('shop_addresses')
                    ->join('master_provinces', 'master_provinces.id', '=', 'shop_addresses.province_id')
                    ->join('master_cities', 'master_cities.id', '=', 'shop_addresses.citie_id')
                    ->join('master_subdistricts', 'master_subdistricts.id', '=', 'shop_addresses.subdistrict_id')
                    ->join('master_postal_codes', 'master_postal_codes.id', '=', 'shop_addresses.postal_code_id')
                    ->select(
                        'shop_addresses.*',
                        'master_provinces.name as province_name',
                        'master_cities.name as city_name',
                        'master_subdistricts.name as district_name',
                        'master_postal_codes.code'
                    );

                $sellerAddress = $productDelivery
                    ? $sellerAddressQuery->where('shop_addresses.id', $productDelivery->address_shop_id)->first()
                    : $sellerAddressQuery->where('shop_addresses.seller_id', $shopProfile->id)->first();

                // Items detail
                $itemsDetail = $items->map(function ($item) {
                    $data = [
                        'product_name'   => $item->product->name,
                        'image'          => $item->product->image,
                        'quantity'       => $item->qty,
                        'price'          => $item->price,
                        'original_price' => $item->original_price,
                        'insurance'      => $item->insurance,
                        'service_fee'    => $item->service_fee,
                        'payment_fee'    => $item->payment_fee,
                        'discount'       => $item->discount,
                        'subsidy'        => $item->subsidy,
                        'voucher'        => $item->voucher,
                        'shipping'       => $item->shipping,
                        'price_shipping' => $item->price_shipping,
                    ];

                    if ($item->variant_id) {
                        $combinations = Product::with([
                            'variants.values',
                            'variantPrices.compositions.value.variant',
                            'variantPrices.promotion',
                        ])
                            ->where('products.id', $item->product->id)
                            ->whereHas('variantPrices', function ($q) use ($item) {
                                $q->where('id', $item->variant_id);
                            })
                            ->first();

                        if ($combinations) {
                            $selectedVariant = $combinations->variantPrices
                                ->where('id', $item->variant_id)
                                ->first();
                            $data['variant_prices'] = $selectedVariant;
                        }
                    }

                    return $data;
                })->values();

                // Income details
                $details = [];
                $income = DB::table('shop_incomes')
                    ->where('order_id', $order->id)
                    ->where('shop_profile_id', $shopProfile->id)
                    ->first();

                $details['total_income'] = $income ? $income->total_income : 0;

                if ($income) {
                    $incomeDetails = DB::table('income_details')
                        ->where('shop_income_id', $income->id)
                        ->get();

                    foreach ($incomeDetails as $detail) {
                        if (in_array($detail->type, ['order_processing', 'administrasi_fee', 'seller_fee'])) {
                            $details[$detail->type] = $detail->amount;
                        }
                    }
                }

                // Gabungkan semua
                $packages[] = [
                    'id'            => $order->id,
                    'invoiceDate'   => $order->created_at,
                    'invoiceNumber' => $order->order_id,
                    'amount'        => $priceItem + $shippingCost + $insuranceCost + $serviceCost + $paymentCost - $discountCost - $subsidyCost - $voucherCost,
                    'priceItem'     => $priceItem,
                    'shippingCost'  => $priceShipping,
                    'insuranceCost' => $insuranceCost,
                    'serviceCost'   => $serviceCost,
                    'paymentCost'   => $paymentCost,
                    'discountCost'  => $discountCost,
                    'subsidyCost'   => $subsidyCost,
                    'voucherCost'   => $voucherCost,
                    'buyer'         => $order->userProfile->name ?? null,
                    'buyerPhone'    => $buyerUser->whatsapp ?? null,
                    'buyerCity'     => $userAddress->city_name ?? null,
                    'buyerAddress'  => $userAddress ?? null,
                    'store'         => $shopProfile->shop_name ?? null,
                    'storePhone'    => $sellerUser->whatsapp ?? null,
                    'storeCity'     => $sellerAddress->city_name ?? null,
                    'storeAddress'  => $sellerAddress ?? null,
                    'status'        => $status,
                    'resi'          => $firstItem->resi,
                    'items'         => $itemsDetail,
                    'details'       => $details,
                ];
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Data cart ditemukan',
                'data'    => $packages,
                'meta'    => [
                    'current_page' => $orders->currentPage(),
                    'last_page'    => $orders->lastPage(),
                    'per_page'     => (string) $orders->perPage(),
                    'total'        => (string) $orders->total(),
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Keranjang tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Problem with server',
                'error_details' => $e->getMessage()
            ], 500);
        }
    }


    public function showProductBySeller($order_id, $seller_id)
    {
        $order = OrderItem::where('order_id', $order_id)
            ->whereHas('product', function ($query) use ($seller_id) {
                $query->where('seller_id', $seller_id);
            })
            ->with('product') // Memuat data produk terkait
            ->get();
        $productIds = $order->pluck('product_id')->toArray();
        $variantIds = $order->pluck('variant_id')->toArray();
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

            if ($variantId) {
                $combinations = Product::with([
                    'variants.values',
                    'variantPrices.compositions.value.variant',
                    'variantPrices.promotion',
                ])
                    ->where('products.id', $product->id)
                    ->whereHas('variantPrices', function ($q) use ($variantId) {
                        $q->where('id', $variantId);
                    })
                    ->first();

                if ($combinations) {
                    $variants = $combinations->variants->map(fn($v) => [
                        'id'      => $v->id,
                        'variant' => $v->variant,
                        'options' => $v->values->pluck('value'),
                    ]);

                    // hanya ambil 1 variant price yang sesuai
                    $selectedVariant = $combinations->variantPrices
                        ->where('id', $variantId)
                        ->first();

                    $product['variant_prices'] = $selectedVariant;
                }
            }

            $groupedStores[] = $product;
        }
        return response()->json([
            'status' => 'success',
            'data'   => $groupedStores
        ]);
    }
}
