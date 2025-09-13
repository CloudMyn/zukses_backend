<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use App\Models\Product;
use App\Models\ProductVariantPrice;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ? $request->get('search') : null;
        $status = $request->get('status') ? $request->get('status') : null;
        $sort_order = $request->get('sort_order') ? $request->get('sort_order') : 'asc';
        $sort_by = $request->get('sort_by') ? $request->get('sort_by') : 'name';
        $page_size = $request->get('page_size') ? $request->get('page_size') : '10';

        $transactions = Transaction::with([
            'seller',
            'buyer',
            'details.product',
            'details.variant.variantValues.variant',
        ])->select([
            'transactions.*',
            'status.name as status_name',
        ])
        ->join('master_statuses as status', 'transactions.status_id', '=', 'status.id')
        // ->where('status', function ($q) use ($search) {
        //     $q->where('shop_name', 'like', "%$search%");
        // })
        ->paginate(10);

        $data = $transactions->getCollection()->map(function ($trx) {
            return [
                'transaction_id' => $trx->id,
                'status_name' => $trx->status_name,
                'total_amount' => $trx->total_amount,
                'payment_method' => $trx->payment_method,
                'seller' => [
                    'id' => $trx->seller->id,
                    'shop_name' => $trx->seller->shop_name,
                    'email' => $trx->seller->email,
                ],
                'buyer' => [
                    'id' => $trx->buyer->id,
                    'email' => $trx->buyer->email,
                ],
                'details' => $trx->details->map(function ($detail) {
                    return [
                        'product_name' => $detail->product->name ?? null,
                        'qty' => $detail->qty,
                        'price' => $detail->price,
                        'total_price' => $detail->total_price,
                        'variant' => $detail->variant ? [
                            'sku' => $detail->variant->sku,
                            'image' => $detail->variant->image,
                            'price' => $detail->variant->price,
                            'stock' => $detail->variant->stock,
                            'variant_values' => $detail->variant->variantValues->map(function ($vv) {
                                return [
                                    'variant_name' => $vv->variant->variant,
                                    'value' => $vv->value,
                                ];
                            }),
                        ] : null,
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $data,
            'current_page' => $transactions->currentPage(),
            'per_page' => $transactions->perPage(),
            'total' => $transactions->total(),
            'last_page' => $transactions->lastPage(),
        ]);

        // $data = $dataWithPaginate->items();
        // $total = $dataWithPaginate->total();
        // $limit = $page_size;
        // $page = $dataWithPaginate->currentPage();

        // $meta = [
        //     'total' => $total,
        //     'limit' => $limit,
        //     'page' => $page,
        // ];

        // return $this->utilityService->is200ResponseWithDataAndMeta($data, $meta);
    }

    public function create(Request $request)
    {
        DB::transaction(function () use ($request) {
            $transaction = Transaction::create([
                'seller_id' => $request->seller_id,
                'buyer_id' => $request->buyer_id,
                'status_id' => 1,
                // 'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                // 'reference_tripay' => $request->reference_tripay,
            ]);

            $total_amount = 0;

            // $buyer = Buyer::find($request->buyer_id);
            // $user = User::where('email', $buyer->email)->first();

            $items = [];

            
            foreach ($request->detail as $detail) {
                // $product = Product::find($detail)

                $product = Product::find($detail['product_id']);
                $product->stock = $product->stock - (int)$detail['qty'];

                if ($product->stock < 0) {
                    return $this->utilityService->is404Response("Kuantitas melebihi jumlah stok!");
                }

                $product->save();

                if ($detail['product_variant_id']) {
                    $product_variant_price = ProductVariantPrice::find($detail['product_variant_id']);
                    $price = $product_variant_price->price;
                    $total_price = $price * (int)$detail['qty'];

                    $product_variant_price->stock = $product_variant_price->stock - (int)$detail['qty'];

                    if ($product_variant_price->stock < 0) {
                        return $this->utilityService->is404Response("Kuantitas melebihi jumlah stok!");
                    }

                    $product_variant_price->save();
                } else {
                    $price = $product->price;
                    $total_price = $price * (int)$detail['qty'];
                }

                $total_amount += $total_price;

               TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $detail['product_id'],
                    'product_variant_id' => $detail['product_variant_id'],
                    'price' => $price,
                    'qty' => $detail['qty'],
                    'total_price' => $total_price,
                ]);

                $items[] = [
                    'name'     => $product->name,
                    'price'    => $price,
                    'quantity' => $detail['qty'],
                ];
            }

            $callback_url = env('APP_URL') . 'callback/transaction';

            $tripay = $this->utilityService->requestTransaction($transaction->id, $total_amount, "Agi Sahriza", "agisahriza@gmail.com", $request->payment_method, $items, $callback_url);
            // dd($tripay);

            if (isset($tripay->success) && $tripay->success == false) {
                return $this->utilityService->is404Response("Gagal membuat pembayaran");
            }

            $transaction->total_amount = $total_amount;
            $transaction->reference_tripay = $tripay->data->reference;

            $transaction->save();
        });

        return $this->utilityService->is200Response("Berhasil melakukan transaksi");
    }

    public function callback(Request $request)
    {
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();
        $signature = hash_hmac('sha256', $json, env('TRIPAY_PRIVATE_KEY'));

        if ($signature !== (string) $callbackSignature) {
            return $this->utilityService->is404Response('Invalid signature');
        }

        if ('payment_status' !== (string) $request->server('HTTP_X_CALLBACK_EVENT')) {
            return $this->utilityService->is404Response('Unrecognized callback event, no action was taken');
        }

        $data = json_decode($json);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return $this->utilityService->is404Response('Invalid data sent by tripay');
        }

        $id_transaction = $data->merchant_ref;
        $status = strtoupper((string) $data->status);

        if ($data->is_closed_payment === 1) {
            $transaction = Transaction::find($id_transaction);

            if (!$transaction) {
                return $this->utilityService->is404Response('Transaction not found or already paid');
            }

            switch ($status) {
                case 'PAID':
                    $transaction->update(['status_id' => 2]);
                    break;

                case 'EXPIRED':
                    $transaction->update(['status_id' => 5]);
                    break;

                case 'FAILED':
                    $transaction->update(['status_id' => 5]);
                    break;

                default:
                    return $this->utilityService->is404Response('Unrecognized payment status');
            }
            return $this->utilityService->is200Response('Success');
        }
    }

}
