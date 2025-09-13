<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


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
        $this->validate($request, [
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
        foreach ($request->items as $item) {
            $subtotal     = $item['price'] * $item['qty'];
            $totalProduk += $subtotal;
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'qty'        => $item['qty'],
                'price'      => $item['price'],
                'address_id' => $request->address_id
            ]);

            $itemDetails[] = [
                'id'       => $item['product_id'],
                'price'    => $item['price'],
                'quantity' => $item['qty'],
                'name'     => 'Produk #' . $item['product_id'],
            ];
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

        $this->validate($request, [
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

        foreach ($request->items as $item) {
            $subtotal = $item['qty'] * $item['price'];
            $total += $subtotal;

            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'address_id' => $request->address_id
            ]);

            $auth = Auth::id();
            $profile = UserProfile::where('user_id', $auth)->first();

            // Query utama sesuai variant yang diberikan
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

                if (!$dataCart) {
                    // Jika tidak ditemukan, coba cari data keranjang lain dengan product_id sama tapi tanpa variant_price_id (null)
                    $fallbackQuery = DB::table('carts')
                        ->where('product_id', $item['product_id'])
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
                }

                $cart = Cart::find($dataCart->id);
                $cart->delete();
            }
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
}
