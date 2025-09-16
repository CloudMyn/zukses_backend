<?php

namespace App\Http\Controllers;

use App\Models\ServiceFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceFeeController extends Controller
{
    /**
     * Mengembalikan semua data pengaturan dalam format JSON.
     * Endpoint ini akan diakses oleh frontend.
     */
    public function getSettings()
    {
        // Logika pengambilan dan pengelompokan data tetap sama
        $fees = ServiceFee::all()->groupBy('fee_type');

        // Kembalikan sebagai response JSON, bukan view
        return $this->utilityService->is200ResponseWithData("Biaya layanan ditemukan", $fees);
    }

    /**
     * Menyimpan atau memperbarui data dari request.
     */
    /**
     * Menyimpan atau memperbarui data dari request tanpa blok transaksi.
     * Metode ini menghapus data lama per jenis biaya, lalu memasukkan data baru.
     */

    public function index(Request $request)
    {
        $feeType = $request->query('fee_type');
        $amount  = $request->query('amount');

        if ($feeType === 'buyer_fee' && $amount) {
            // khusus buyer_fee -> ambil 1 sesuai range
            $serviceFees = ServiceFee::where('fee_type', 'buyer_fee')
                ->where('min_transaction', '<=', $amount)
                ->where('max_transaction', '>=', $amount)
                ->first(); // hanya 1 record

            $serviceFees = $serviceFees ? [$serviceFees] : [];
        } elseif ($feeType) {
            // kalau fee_type lain, ambil semua data dengan fee_type tsb
            $serviceFees = ServiceFee::where('fee_type', $feeType)->get();
        } else {
            // kalau fee_type tidak ada, ambil semua
            $serviceFees = ServiceFee::all();
        }

        return response()->json([
            'message' => 'Pengaturan biaya berhasil diambil!',
            'status'  => 'success',
            'data'    => $serviceFees
        ], 200);
    }



    public function updateSettings(Request $request)
    {
        try {
            // 1. Proses Biaya Layanan Pembeli (buyerServiceFees)
            if ($request->has('buyerServiceFees')) {
                // Hapus data lama untuk tipe ini
                ServiceFee::where('fee_type', 'buyer_fee')->delete();
                // Masukkan data baru dari payload
                foreach ($request->input('buyerServiceFees') as $fee_item) {
                    if (isset($fee_item['fee'])) { // Pengecekan sederhana agar lebih aman
                        ServiceFee::create([
                            'fee_type'        => 'buyer_fee',
                            'name'            => 'Biaya Layanan Pembeli',
                            'fee_amount'      => $fee_item['fee'],
                            'min_transaction' => $fee_item['minTransaction'],
                            'max_transaction' => $fee_item['maxTransaction'],
                            'admin_id' =>  Auth::id(),
                        ]);
                    }
                }
            }

            // 2. Proses Biaya Payment Gateway (paymentGatewayFee)
            if ($request->has('paymentGatewayFee')) {
                ServiceFee::where('fee_type', 'payment_gateway')->delete();
                ServiceFee::create([
                    'fee_type'   => 'payment_gateway',
                    'name'       => 'Biaya Payment Gateway',
                    'fee_amount' => $request->input('paymentGatewayFee'),
                    'admin_id' =>  Auth::id(),
                ]);
            }

            // 3. Proses Biaya Layanan Penjual (sellerServiceFee)
            if ($request->has('sellerServiceFee')) {
                ServiceFee::where('fee_type', 'seller_fee')->delete();
                ServiceFee::create([
                    'fee_type'   => 'seller_fee',
                    'name'       => 'Biaya Layanan Penjual',
                    'fee_amount' => $request->input('sellerServiceFee'),
                    'admin_id' =>  Auth::id(),
                ]);
            }

            // 4. Proses Biaya Proses Pesanan (orderProcessingFees)
            if ($request->has('orderProcessingFees')) {
                ServiceFee::where('fee_type', 'order_processing')->delete();
                foreach ($request->input('orderProcessingFees') as $fee_item) {
                    if (isset($fee_item['fee'])) {
                        ServiceFee::create([
                            'fee_type'        => 'order_processing',
                            'name'            => 'Biaya Proses Pesanan',
                            'fee_amount'      => $fee_item['fee'],
                            'min_transaction' => $fee_item['minTransaction'],
                            'max_transaction' => $fee_item['maxTransaction'],
                            'admin_id' =>  Auth::id(),
                        ]);
                    }
                }
            }

            // 5. Proses Biaya Lainnya (otherFees)
            if ($request->has('otherFees')) {
                ServiceFee::where('fee_type', 'other_fee')->delete();
                foreach ($request->input('otherFees') as $fee_item) {
                    if (isset($fee_item['fee'])) {
                        ServiceFee::create([
                            'fee_type'        => 'other_fee',
                            'name'            => 'Biaya Lainnya',
                            'fee_amount'      => $fee_item['fee'],
                            'min_transaction' => $fee_item['minTransaction'],
                            'max_transaction' => $fee_item['maxTransaction'],
                            'admin_id' =>  Auth::id(),
                        ]);
                    }
                }
            }

            return response()->json(['message' => 'Pengaturan biaya berhasil disimpan!'], 200);
        } catch (\Exception $e) {
            // Karena tidak ada transaksi, tidak perlu rollback. Cukup laporkan error.
            return response()->json([
                'message' => 'Terjadi kesalahan fatal saat menyimpan.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
