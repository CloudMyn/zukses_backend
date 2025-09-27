<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message; // Pastikan ini diimpor
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Mengambil riwayat pesan dari database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Ambil 100 pesan terakhir, diurutkan berdasarkan created_at secara ascending
        $messages = Message::orderBy('created_at', 'asc')->limit(100)->get();

        return response()->json($messages);
    }

    /**
     * Menyimpan pesan baru ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi data yang masuk dari request
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'user_profile_id' => 'nullable|string',   // Validasi untuk kolom baru
            'product_id' => 'nullable|integer',        // Validasi untuk kolom baru
            'variant_price_id' => 'nullable|integer',  // Validasi untuk kolom baru
        ]);

        if ($validator->fails()) {
            return $this->utilityService->is422Response($validator->errors()->first());
        }

        // Buat pesan baru menggunakan model Message
        $message = Message::create([
            'text' => $request->input('text'),
            'user_profile_id' => $request->input('user_profile_id'),
            'product_id' => $request->input('product_id'),
            'variant_price_id' => $request->input('variant_price_id'),
        ]);

        // Kembalikan pesan yang baru disimpan dengan status 201 (Created)
        return response()->json($message, 201);
    }
}
