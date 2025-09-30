<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends Controller
{
    /**
     * READ: Menampilkan semua data bank account.
     * GET /bank-accounts
     */
    public function index($user_id)
    {
        $data = DB::table('bank_accounts')
            ->join('banks', 'banks.id', '=', 'bank_accounts.bank_id')
            ->where('user_id', $user_id)
            ->select(
                'bank_accounts.*',
                'banks.name_bank',
                'banks.icon',
            )
            ->get()
            ->map(function ($item) {
                return [
                    'id' => (int) $item->id,
                    'user_id' => (int) $item->user_id,
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
            return $this->utilityService->is404Response('Data Rekening tidak ditemukan');
        }

        return $this->utilityService->is200ResponseWithData('Data Rekening ditemukan', $data);
    }


    /**
     * CREATE: Menyimpan data bank account baru.
     * POST /bank-accounts
     */
    public function store(Request $request, $user_id)
    {
        if ($request->is_primary == 1) {
            $userBanks = DB::table('bank_accounts')->where('is_primary', 1)->where('user_id', $user_id)->first();
            if ($userBanks) {
                $ud = BankAccount::find($userBanks->id);
                $ud->is_primary = 0;
                $ud->save();
            }
        }
        $data = [
            'user_id' => $user_id,
            'bank_id' => $request->bank_id,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'is_primary' => $request->is_primary,
        ];

        $bankAccount = BankAccount::create($data);

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
        $bankAccount = BankAccount::findOrFail($id);
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
        $bankAccount = BankAccount::findOrFail($id);

        $bankAccount->bank_id = $request->bank_id ?? $bankAccount->bank_id;
        $bankAccount->account_number = $request->account_number ?? $bankAccount->account_number;
        $bankAccount->account_name = $request->account_name ?? $bankAccount->account_name;

        if ((int)$request->is_primary === 1) {
            // Reset semua Account Bank primary milik user ini
            BankAccount::where('user_id', $bankAccount->user_id)
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
        $address = BankAccount::find($id);

        if (!$address) {
            return $this->utilityService->is404Response("Account Bank tidak ditemukan");
        }

        $BankAccount = DB::table('bank_accounts')->where('is_primary', 1)->first();
        if ($BankAccount) {
            $ud = BankAccount::find($BankAccount->id);
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
        $menu = BankAccount::find($id);
        $menu->delete();
        $success_message = "Data Berhasil Dihapus";
        return $this->utilityService->is200Response($success_message);
    }
}
