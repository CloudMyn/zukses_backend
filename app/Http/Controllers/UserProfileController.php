<?php

namespace App\Http\Controllers;

use App\Helpers\UrlRemove;
use App\Models\Invitation;
use App\Models\Otp;
use App\Models\Token;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{

    public function create(Request $request, $user_id)
    {

        $useProfil = DB::table('user_profiles')->where('user_id', $user_id)->first();
        if ($useProfil) {
            $userProfile = UserProfile::find($useProfil->id);
            if ($request->image) {
                if ($userProfile->image) {
                    $delete_image =  $userProfile->image;
                    $url = UrlRemove::remover($delete_image);
                    Storage::disk('minio')->delete($url);
                }
                $fileName = 'ImageProfile-' . time() . '.webp';
                $image = $this->utilityService->convertImageToWebp($request->file('image'));
                Storage::disk('minio')->put($fileName, $image);
                $urlImage = Storage::disk('minio')->url($fileName);
                $userProfile->image = $urlImage;
            }
            $userProfile->name = $request->name;
            $userProfile->gender = $request->gender;
            $userProfile->date_birth = $request->date_birth;
            if ($userProfile->save()) {
                $success_message = "Data Berhasil diupdate";
                return $this->utilityService->is200ResponseWithData($success_message, $userProfile);
            } else {
                return $this->utilityService->is500Response("problem with server");
            }
        } else {

            $fileName = 'ImageProfile-' . time() . '.webp';
            $image = $this->utilityService->convertImageToWebp($request->file('image'));
            Storage::disk('minio')->put($fileName, $image);
            $urlImage = Storage::disk('minio')->url($fileName);

            $data = [
                'user_id' => $user_id,
                'name' => $request->name,
                'gender' => $request->gender,
                'date_birth' => $request->date_birth,
                'image' => $urlImage
            ];
            $insert = UserProfile::create($data);
            $data = $insert->fresh();
            if ($insert) {
                $success_message = "Data Berhasil Ditambahkan";
                return $this->utilityService->is200ResponseWithData($success_message, $data);
            } else {
                return $this->utilityService->is500Response("problem with server");
            }
        }
    }
    public function update(Request $request, $user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return $this->utilityService->is404Response("User tidak ditemukan.");
        }

        $useProfil = DB::table('user_profiles')->where('user_id', $user_id)->first();
        if (!$useProfil) {
            return $this->utilityService->is404Response("Profil pengguna tidak ditemukan.");
        }

        $userProfile = UserProfile::find($useProfil->id);

        // === VALIDASI EMAIL SUDAH DIPAKAI USER LAIN ===
        if ($request->email && filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $existingEmail = User::where('email', $request->email)
                ->where('id', '!=', $user_id)
                ->exists();
            if ($existingEmail) {
                return $this->utilityService->is404Response("Email sudah digunakan oleh pengguna lain.");
            }
        }

        // === VALIDASI WHATSAPP SUDAH DIPAKAI USER LAIN ===
        if ($request->whatsapp) {
            $existingWhatsapp = User::where('whatsapp', $request->whatsapp)
                ->where('id', '!=', $user_id)
                ->exists();
            if ($existingWhatsapp) {
                return $this->utilityService->is404Response("Nomor WhatsApp sudah digunakan oleh pengguna lain.");
            }
        }

        // === UPDATE GAMBAR ===
        if ($request->hasFile('image')) {
            if ($useProfil->image) {
                $delete_image = $userProfile->image;
                $url = UrlRemove::remover($delete_image);
                Storage::disk('minio')->delete($url);
            }

            $fileName = 'ImageProfile-' . time() . '.webp';
            $image = $this->utilityService->convertImageToWebp($request->file('image'));
            Storage::disk('minio')->put($fileName, $image);
            $urlImage = Storage::disk('minio')->url($fileName);
            $userProfile->image = $urlImage;
        }

        // === UPDATE DATA PROFIL ===
        $userProfile->name = $request->name;
        $userProfile->gender = $request->gender;
        $userProfile->date_birth = $request->date_birth;

        $oldEmail = $user->email;
        $shouldUpdateTokenEmail = false;
        $newTokenEmail = null;

        // === CEK EMAIL VALID ===
        if ($request->email && filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $user->email = $request->email;
            $newTokenEmail = $request->email;
            $shouldUpdateTokenEmail = true;

            if (!empty($request->whatsapp)) {
                $user->whatsapp = $request->whatsapp;
            }
        } elseif (!empty($request->whatsapp)) {
            // Email tidak valid, pakai whatsapp
            $user->email = $request->whatsapp;
            $user->whatsapp = $request->whatsapp;
            $newTokenEmail = $request->whatsapp;
            $shouldUpdateTokenEmail = true;
        }

        $user->name = $request->name;
        if ($request->whatsapp) {
            $user->whatsapp = $request->whatsapp;
        }

        $userSaved = $user->save();
        $profileSaved = $userProfile->save();

        // === UPDATE EMAIL DI TOKENS ===
        if ($shouldUpdateTokenEmail && $newTokenEmail) {
            DB::table('tokens')
                ->where('email', $oldEmail)
                ->update(['email' => $newTokenEmail]);
        }

        if ($userSaved && $profileSaved) {
            return $this->utilityService->is200ResponseWithData("Data berhasil diupdate.", $userProfile);
        } else {
            return $this->utilityService->is500Response("Terjadi masalah saat menyimpan data.");
        }
    }


    public function show(Request $request, $user_id)
    {
        $data = UserProfile::where('user_id', $user_id)->first();
        if (!$data) {
            return $this->utilityService->is404Response('Data Users tidak ditemukan');
        }
        return $this->utilityService->is200ResponseWithData('Data Users ditemukan', $data);
    }

    public function destroy($id)
    {
        $banner = UserProfile::find($id);

        if (!$banner) {
            return response()->json(['status' => 'error', 'message' => 'User profil not found'], 404);
        }

        // Hapus gambar dari Minio sebelum menghapus record
        if ($banner->image) {
            Storage::disk('minio')->delete(basename($banner->image));
        }

        $banner->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User Profil deleted successfully',
        ]);
    }
}
