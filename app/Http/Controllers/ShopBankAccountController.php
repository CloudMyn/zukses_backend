<?php

namespace App\Http\Controllers;

use App\Models\ShopBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShopBankAccountController extends Controller
{
    /**
     * READ: Menampilkan semua data bank account.
     * GET /bank-accounts
     */
    public function index($seller_id)
    {
        $data = DB::table('shop_bank_accounts')
            ->join('banks', 'banks.id', '=', 'shop_bank_accounts.bank_id')
            ->where('seller_id', $seller_id)
            ->select(
                'shop_bank_accounts.*',
                'banks.name_bank',
                'banks.icon',
            )
            ->orderByDesc('shop_bank_accounts.is_primary')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => (int) $item->id,
                    'seller_id' => (int) $item->seller_id,
                    'bank_id' => (int) $item->bank_id,
                    'account_number' => $item->account_number,
                    'account_name' => $item->account_name,
                    'is_primary' => (bool) $item->is_primary,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'name_bank' => $item->name_bank,
                    'icon' => $item->icon,
                ];
            });

        if ($data->isEmpty()) {
            return $this->utilityService->is404Response('Data Users tidak ditemukan');
        }

        return $this->utilityService->is200ResponseWithData('Data Users ditemukan', $data);
    }


    /**
     * CREATE: Menyimpan data bank account baru.
     * POST /bank-accounts
     */
    public function store(Request $request, $seller_id)
    {
        $validator = Validator::make($request->all(), [
            'bank_id' => 'required|integer|exists:banks,id',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
            'is_primary' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->utilityService->is422Response($validator->errors()->first());
        }

        if ($request->is_primary == 1) {
            $userBanks = DB::table('shop_bank_accounts')->where('is_primary', 1)->where('seller_id', $seller_id)->first();
            if ($userBanks) {
                $ud = ShopBankAccount::find($userBanks->id);
                $ud->is_primary = 0;
                $ud->save();
            }
        }
        $data = [
            'seller_id' => $seller_id,
            'bank_id' => $request->bank_id,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'is_primary' => $request->is_primary,
        ];

        $bankAccount = ShopBankAccount::create($data);

        if ($bankAccount) {
            $success_message = "Account bank data successfully added";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    /**
     * READ: Menampilkan satu data bank account berdasarkan ID.
     * GET /bank-accounts/{id}
     */
    public function show($id)
    {
        // findOrFail akan otomatis return 404 jika data tidak ditemukan
        $bankAccount = ShopBankAccount::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Detail akun bank',
            'data'    => $bankAccount
        ], 200);
    }

    /**
     * UPDATE: Memperbarui data bank account berdasarkan ID.
     * PUT /bank-accounts/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'bank_id' => 'required|integer|exists:banks,id',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
            'is_primary' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->utilityService->is422Response($validator->errors()->first());
        }

        $bankAccount = ShopBankAccount::findOrFail($id);

        $bankAccount->bank_id = $request->bank_id;
        $bankAccount->account_number = $request->account_number;
        $bankAccount->account_name = $request->account_name;

        if ((int)$request->is_primary === 1) {
            // Reset semua Account Bank primary milik user ini
            ShopBankAccount::where('seller_id', $bankAccount->seller_id)
                ->where('is_primary', 1)
                ->update(['is_primary' => 0]);

            $bankAccount->is_primary = 1;
        } else {
            $bankAccount->is_primary = 0;
        }


        if ($bankAccount->save()) {
            $success_message = "Account bank data successfully updated";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function isPrimary(Request $request, $id)
    {
        // Pastikan ada id Account Bank yang akan diupdate
        $address = ShopBankAccount::find($id);

        if (!$address) {
            return $this->utilityService->is404Response("Account Bank tidak ditemukan");
        }

        $BankAccount = DB::table('shop_bank_accounts')->where('is_primary', 1)->first();
        if ($BankAccount) {
            $ud = ShopBankAccount::find($BankAccount->id);
            $ud->is_primary = 0;
            $ud->save();
        }


        // Data yang akan diupdate
        $data = [
            'is_primary' => 1,
        ];

        // Lakukan update
        $updated = $address->update($data);

        // Ambil data terbaru jika update berhasil
        $data = $address->fresh();

        if ($updated) {
            $success_message = "Data Berhasil Diupdate";
            return $this->utilityService->is200ResponseWithData($success_message, $data);
        } else {
            return $this->utilityService->is500Response("Terjadi kesalahan saat memperbarui data");
        }
    }
    /**
     * DELETE: Menghapus data bank account berdasarkan ID.
     * DELETE /bank-accounts/{id}
     */
    public function destroy($id)
    {
        $menu = ShopBankAccount::find($id);
        $menu->delete();
        $success_message = "Data Berhasil Dihapus";
        return $this->utilityService->is200Response($success_message);
    }
}
