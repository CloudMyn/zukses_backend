<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BankController extends Controller
{
    // Get All Banks
    public function index(Request $request)
    {
        $data = Bank::all();
        $message = "Data banks ditemukan";
        return $this->utilityService->is200ResponseWithData($message, $data);
    }

    // Store New Bank
    public function store(Request $request)
    {
        $this->validate($request, [
            'name_bank' => 'required|string',
            'icon' => 'nullable|file|image|max:2048',
        ]);

        $iconUrl = null;
        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('bank-icons', 'minio');
            $iconUrl = Storage::disk('minio')->url($path);
        }

        $bank = Bank::create([
            'name_bank' => $request->name_bank,
            'icon' => $iconUrl,
        ]);
        if ($bank) {
            $success_message = "Bank data successfully added";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    // Get Single Bank
    public function show($id)
    {
        $bank = Bank::find($id);
        if (!$bank) {
            return response()->json(['message' => 'Bank not found'], 404);
        }
        return response()->json($bank);
    }

    // Update Bank
    public function update(Request $request, $id)
    {
        $bank = Bank::find($id);
        if (!$bank) {
            return response()->json(['message' => 'Bank not found'], 404);
        }

        $this->validate($request, [
            'name_bank' => 'sometimes|string',
            'icon' => 'nullable|file|image|max:2048',
        ]);

        if ($request->hasFile('icon')) {
            // Hapus icon lama jika ada
            if ($bank->icon) {
                $oldPath = parse_url($bank->icon, PHP_URL_PATH);
                $oldPath = ltrim($oldPath, '/');
                Storage::disk('minio')->delete($oldPath);
            }

            $path = $request->file('icon')->store('bank-icons', 'minio');
            $bank->icon = Storage::disk('minio')->url($path);
        }

        if ($request->has('name_bank')) {
            $bank->name_bank = $request->name_bank;
        }
        if ($bank->save()) {
            $success_message = "Bank data successfully added";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    // Delete Bank
    public function destroy($id)
    {
        $bank = Bank::find($id);
        if (!$bank) {
            return response()->json(['message' => 'Bank not found'], 404);
        }

        // Hapus icon dari MinIO jika ada
        if ($bank->icon) {
            $path = parse_url($bank->icon, PHP_URL_PATH);
            $path = ltrim($path, '/');
            Storage::disk('minio')->delete($path);
        }

        if ($bank->delete()) {
            $success_message = "Province data successfully deleted";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
}
