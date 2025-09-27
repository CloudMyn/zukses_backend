<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Admins;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class AdminsController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,user,seller',
            'username' => 'required|string|unique:admins,username',
            'whatsapp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->utilityService->is422Response($validator->errors()->first());
        }

        $email = Admin::where("email", $request->email)
            ->first();
        $whatsapp = Admin::where("whatsapp", $request->whatsapp)
            ->first();

        if ($email) {
            return $this->utilityService->is422Response("Email sudah digunakan");
        }
        if ($whatsapp) {
            return $this->utilityService->is422Response("WhatsApp sudah digunakan");
        }

        $data = [
            "name" => $request->name,
            "email" => $request->email,
            "password" => app("hash")->make($request->password),
            "role" => $request->role,
            'username' => $request->username,
            'whatsapp' => $request->whatsapp,
            'status' => 1,
            'start_date' => date('Y-m-d')
        ];

        $insert = Admins::create($data);

        if ($insert) {
            $password = $request->password;
            $credentials = [
                'email' => $request->email,
                'password' => $password,
            ];
            $token = Auth::guard('admins')->attempt($credentials);
            Token::create([
                'token' => $token,
                'email' => $request->email,
                'is_active' => 0,
                'type' => 'admin'
            ]);

            return $this->utilityService->is200Response("Berhasil tambah akun");
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
    public function edit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:admins,username,'.$id,
            'email' => 'required|email|unique:admins,email,'.$id,
            'role' => 'required|string|in:admin,user,seller',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->utilityService->is422Response($validator->errors()->first());
        }

        $admin = Admin::find($id);
        if (!$admin) {
            return $this->utilityService->is404Response("Admin tidak ditemukan");
        }

        // Cek apakah email diganti
        if ($request->email && $request->email !== $admin->email) {
            $cekEmail = Admin::where("email", $request->email)
                ->where("id", "!=", $id)
                ->first();

            if ($cekEmail) {
                return $this->utilityService->is422Response("Email sudah digunakan oleh pengguna lain");
            }

            // Update email di tabel tokens juga
            DB::table('tokens')
                ->where('email', $admin->email)
                ->update(['email' => $request->email]);
        }

        $admin->name = $request->name;
        $admin->username = $request->username;
        $admin->email = $request->email;
        $admin->role = $request->role;

        if ($request->password) {
            $admin->password = app("hash")->make($request->password);
        }

        if ($admin->save()) {
            return $this->utilityService->is200Response("Berhasil update akun");
        } else {
            return $this->utilityService->is500Response("Problem with server");
        }
    }
    public function updateStatus(Request $request, $id)
    {
        $admin = Admins::find($id);

        $admin->is_active = $request->status;
        if ($request->status == 0) {
            $admin->end_date = date('Y-m-d');
        } else {
            $admin->end_date = null;
        }
        if ($admin->save()) {
            return $this->utilityService->is200Response("Berhasil update akun & token");
        } else {
            return $this->utilityService->is500Response("Problem with server");
        }
    }

    public function index(Request $request)
    {
        $role       = $request->get('role');
        $isActive   = $request->get('is_active');  // 0 atau 1
        $search     = $request->get('search');
        $perPage    = $request->get('per_page', 10);

        $query = DB::table('admins')
            ->when(!empty($role), function ($query) use ($role) {
                return $query->where('role', $role);
            })
            ->when(isset($isActive) && $isActive !== '', function ($query) use ($isActive) {
                return $query->where('status', $isActive);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc');

        $data = $query->paginate($perPage);

        // ambil data dan hilangkan password kalau role user
        $items = $data->items();

        $meta = [
            'current_page' => $data->currentPage(),
            'per_page'     => $data->perPage(),
            'total'        => $data->total(),
            'last_page'    => $data->lastPage(),
            'from'         => $data->firstItem(),
            'to'           => $data->lastItem(),
        ];

        if ($items) {
            return $this->utilityService->is200ResponseWithDataAndMeta($items, $meta);
        } else {
            return $this->utilityService->is404Response("Data tidak ditemukan");
        }
    }


    public function login(Request $request)
    {
        $contact = $request->input('contact'); // bisa email atau no HP
        $password = $request->input('password');

        $isEmail = filter_var($contact, FILTER_VALIDATE_EMAIL);

        // Ambil user berdasarkan email atau phone
        $admin = Admin::when($isEmail, function ($query) use ($contact) {
            return $query->where('email', $contact);
        }, function ($query) use ($contact) {
            return $query->where('whatsapp', $contact);
        })->first();

        if (!$admin) {
            $responseMessage = $isEmail ? "Email tidak terdaftar" : "Nomor HP tidak terdaftar";
            return $this->utilityService->is422Response($responseMessage);
        }

        if ($admin->is_active == 0) {
            return $this->utilityService->is422Response("Your account is inactive, please contact support");
        }

        // Buat field credentials sesuai input
        $credentials = $isEmail
            ? ['email' => $contact, 'password' => $password ?? env("PASSWORD_DEFAULT")]
            : ['whatsapp' => $contact, 'password' => $password ?? env("PASSWORD_DEFAULT")];

        $tokens = DB::table('tokens')->where('email', $admin->email)->first();

        if (!$token = Auth::guard('admins')->attempt($credentials)) {
            // Cek fallback default password (jika ada)
            if (!$admin || $password !== env("PASSWORD_DEFAULT")) {
                return $this->utilityService->is422Response("Email/Nomor HP atau password salah");
            }
        }

        $data = $token ?: ($tokens->token ?? null);
        return $this->respondWithToken($data, $admin);
    }


    public function changepassword(Request $request)
    {
        if ($request->encryptEmail) {
            $email = Crypt::decryptString($request->encryptEmail);
        } else {
            $email = $request->email;
        }
        $admins = DB::table('users')->where('email', $email)->first();
        $changePassword = Admin::find($admins->id);
        $changePassword->password = app("hash")->make($request->password);

        if ($changePassword->save()) {
            $success_message = "Data Users Berhasil Diubah";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function destroy($id)
    {
        $admin = Admins::find($id);
        if ($admin == null) {
            return $this->utilityService->is500Response('Data not found');
        }
        $tokens = DB::table('tokens')->where('email', $admin->email)->delete();
        $admin->delete();
        $success_message = "Data User Berhasil Dihapus";
        return $this->utilityService->is200Response($success_message);
    }
}
