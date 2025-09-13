<?php

namespace App\Http\Controllers;

use App\Helpers\UrlRemove;
use App\Http\Requests\UserLoginRequest;
use App\Models\Customer;
use App\Models\Invitation;
use App\Models\Reseller;
use App\Models\MomentUserCategorie;
use App\Models\MomentUserTask;
use App\Models\Otp;
use App\Models\Token;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Users;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Tymon\JWTAuth\Facades\JWTAuth;
use PHPMailer\PHPMailer\PHPMailer;

class UsersController extends Controller
{
    public function registerNew(Request $request)
    {
        $user = User::where("email", $request->contact)
            ->orWhere("whatsapp", $request->contact)
            ->first();

        if ($user) {
            return $this->utilityService->is422Response("Email atau WhatsApp sudah digunakan");
        }

        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $randomString = '';

        for ($i = 0; $i < 6; $i++) {
            $randomString .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $isEmail = filter_var($request->contact, FILTER_VALIDATE_EMAIL);
        $data = [
            "name" => $request->fullName,
            "email" => $request->contact,
            "password" => app("hash")->make(env('PASSWORD_DEFAULT')),
            "role" => $request->role,
            "whatsapp" => $isEmail ? null : $request->contact,
            'username' => $randomString,
            'start' => Carbon::now()
        ];

        $dataWithoutPassword = [
            'username' => $randomString,
            "name" => $request->name,
            "email" => $request->contact,
            "role" => $request->role,
            "whatsapp" => $isEmail ? null : $request->contact,
        ];

        $insert = Users::create($data);

        if ($insert) {
            // $success_message = "success registration for your user";
            // return $this->utilityService->is200Response($success_message);
            $user = User::where("email", $request->contact)->first();
            $dataProfile = [
                'user_id' => $user->id,
                'name' => $request->fullName,
                'gender' => $request->gender,
                'date_birth' => $request->birthDate,
            ];
            UserProfile::create($dataProfile);
            // $user = DB::table('users')->orderBy('id', 'desc')->first();
            $dataWithoutPassword = [
                'username' => $randomString,
                "name" => $request->fullName,
                "email" => $request->contact,
                'id' => $user->id,
                "role" => $request->role,
                "whatsapp" => $isEmail ? null : $request->contact,
            ];
            $contact = $request->contact;
            $password = env('PASSWORD_DEFAULT');

            // Cek apakah contact adalah email atau nomor HP
            $fieldType = filter_var($contact, FILTER_VALIDATE_EMAIL) ? 'email' : 'whatsapp';

            // Set credentials dinamis sesuai jenis contact
            $credentials = [
                $fieldType => $contact,
                'password' => $password,
            ];
            $token = Auth::guard('users')->attempt($credentials);
            Token::create([
                'token' => $token,
                'email' => $request->contact,
                'is_active' => 0
            ]);

            return $this->respondWithToken($token, $dataWithoutPassword);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
    public function checkAccount(Request $request)
    {
        $contact = $request->contact;

        $user = User::where("email", $contact)
            ->orWhere("whatsapp", $contact)
            ->first();

        if ($user) {
            return $this->utilityService->is422Response("Email atau WhatsApp sudah digunakan");
        }

        $otp = rand(100000, 999999);
        $expiredAt = time() + 600;

        // Cek apakah OTP sudah ada, kalau ada update, kalau belum insert
        Otp::updateOrCreate(
            ['contact' => $contact], // cari berdasarkan contact
            [
                'otp' => $otp,
                'expired_at' => $expiredAt
            ]
        );

        $message = "Kode OTP kamu adalah: $otp\nGunakan dalam 2 menit untuk aktivasi akun.";

        // Kirim berdasarkan jenis kontak
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            // Kirim via email
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION');
            $mail->Port = env('MAIL_PORT');

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($contact); // ke email

            $mail->isHTML(true);
            $mail->Subject = 'OTP Aktivasi Akun Anda';

            $encryptedEmail = Crypt::encryptString($contact);
            $bodyContent = View::make('otp', [
                'email' => $encryptedEmail,
                'name' => 'Pengguna', // bisa ambil dari $request->fullName kalau ada
                'otp' => $otp
            ])->render();

            $mail->Body = $bodyContent;
            $mail->AltBody = strip_tags($bodyContent);

            $mail->send();
            return $this->utilityService->is200Response("OTP berhasil dikirim ke email.");
        } else {
            // Kirim via WhatsApp
            $data = [
                'target' => $contact,
                'message' => $message
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.fonnte.com/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_HTTPHEADER => [
                    "Authorization:" . env('TOKEN_WA')
                ],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            return $this->utilityService->is200Response("OTP berhasil dikirim ke WhatsApp.");
        }
    }
    public function checkAccountPassword(Request $request)
    {
        $contact = $request->contact;

        $user = User::where("email", $contact)
            ->orWhere("whatsapp", $contact)
            ->first();

        if (!$user) {
            return $this->utilityService->is422Response("Email atau WhatsApp tidak ditemukan");
        }

        $otp = rand(100000, 999999);
        $expiredAt = time() + 600;

        // Cek apakah OTP sudah ada, kalau ada update, kalau belum insert
        Otp::updateOrCreate(
            ['contact' => $contact], // cari berdasarkan contact
            [
                'otp' => $otp,
                'expired_at' => $expiredAt
            ]
        );

        $message = "Kode OTP kamu adalah: $otp\nGunakan dalam 2 menit untuk aktivasi akun.";

        // Kirim berdasarkan jenis kontak
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            // Kirim via email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = env('MAIL_HOST');
                $mail->SMTPAuth = true;
                $mail->Username = env('MAIL_USERNAME');
                $mail->Password = env('MAIL_PASSWORD');
                $mail->SMTPSecure = env('MAIL_ENCRYPTION');
                $mail->Port = env('MAIL_PORT');

                $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $mail->addAddress($contact); // ke email

                $mail->isHTML(true);
                $mail->Subject = 'OTP Aktivasi Akun Anda';

                $encryptedEmail = Crypt::encryptString($contact);
                $bodyContent = View::make('otp', [
                    'email' => $encryptedEmail,
                    'name' => 'Pengguna', // bisa ambil dari $request->fullName kalau ada
                    'otp' => $otp
                ])->render();

                $mail->Body = $bodyContent;
                $mail->AltBody = strip_tags($bodyContent);

                $mail->send();
                return $this->utilityService->is200Response("OTP berhasil dikirim ke email.");
            } catch (Exception $e) {
                return $this->utilityService->is500Response("Gagal mengirim email: " . $mail->ErrorInfo);
            }
        } else {
            // Kirim via WhatsApp
            $data = [
                'target' => $contact,
                'message' => $message
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.fonnte.com/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_HTTPHEADER => [
                    "Authorization:" . env('TOKEN_WA')
                ],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            return $this->utilityService->is200Response("OTP berhasil dikirim ke WhatsApp.");
        }
    }
    public function forgetPassword(Request $request)
    {
        $contact = $request->contact;
        $password = $request->password;

        $user = User::where("email", $contact)
            ->orWhere("whatsapp", $contact)
            ->first();

        if (!$user) {
            return $this->utilityService->is404Response("Akun tidak ditemukan.");
        }

        // Update password
        $user->password = app('hash')->make($password);
        $user->save();

        if ($user->save()) {
            $isEmail = filter_var($request->contact, FILTER_VALIDATE_EMAIL);
            $dataWithoutPassword = [
                'username' => $user->username,
                "name" => $user->name,
                "email" => $request->contact,
                'id' => $user->id,
                "role" => $user->role,
                "whatsapp" => $isEmail ? null : $request->contact,
            ];
            $contact = $request->contact;
            $password = $request->password;

            // Cek apakah contact adalah email atau nomor HP
            $fieldType = filter_var($contact, FILTER_VALIDATE_EMAIL) ? 'email' : 'whatsapp';

            // Set credentials dinamis sesuai jenis contact
            $credentials = [
                $fieldType => $contact,
                'password' => $password,
            ];
            $token = Auth::guard('users')->attempt($credentials);
            Token::create([
                'token' => $token,
                'email' => $request->contact,
                'is_active' => 0
            ]);

            return $this->respondWithToken($token, $dataWithoutPassword);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }


    public function register(Request $request)
    {
        $user = User::where("email", $request->email)
            ->orWhere("whatsapp", $request->whatsapp)
            ->first();

        if ($user) {
            return $this->utilityService->is422Response("Email atau WhatsApp sudah digunakan");
        }
        // $chars = 'abcdefghijklmnopqrstuvwxyz';
        // $randomString = '';

        // for ($i = 0; $i < 6; $i++) {
        //     $randomString .= $chars[random_int(0, strlen($chars) - 1)];
        // }
        // $isEmail = filter_var($request->contact, FILTER_VALIDATE_EMAIL);
        $data = [
            "name" => $request->name,
            "email" => $request->email,
            "password" => app("hash")->make($request->password),
            "role" => $request->role,
            'username' => $request->username,
            'whatsapp' => $request->whatsapp,
            'status' => 1,
            'start' => date('Y-m-d')
        ];

        $insert = Users::create($data);

        if ($insert) {
            $password = $request->password;
            $credentials = [
                'email' => $request->email,
                'password' => $password,
            ];
            $token = Auth::guard('users')->attempt($credentials);
            Token::create([
                'token' => $token,
                'email' => $request->email,
                'is_active' => 0
            ]);

            return $this->utilityService->is200Response("Berhasil tambah akun");
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
    public function edit(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->utilityService->is404Response("User tidak ditemukan");
        }

        // Cek apakah email diganti
        if ($request->email && $request->email !== $user->email) {
            $cekEmail = User::where("email", $request->email)
                ->where("id", "!=", $id)
                ->first();

            if ($cekEmail) {
                return $this->utilityService->is422Response("Email sudah digunakan oleh pengguna lain");
            }

            // Update email di tabel tokens juga
            DB::table('tokens')
                ->where('email', $user->email)
                ->update(['email' => $request->email]);
        }

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->password) {
            $user->password = app("hash")->make($request->password);
        }

        if ($user->save()) {
            return $this->utilityService->is200Response("Berhasil update akun & token");
        } else {
            return $this->utilityService->is500Response("Problem with server");
        }
    }
    public function updateStatus(Request $request, $id)
    {
        $user = User::find($id);

        $user->status = $request->status;
        if ($request->status == 0) {
            $user->expierd = date('Y-m-d');
        } else {
            $user->expierd = null;
        }
        if ($user->save()) {
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

        $query = DB::table('users')
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
        $items = collect($data->items())->map(function ($item) {
            if ($item->role === 'user') {
                unset($item->password);
            }
            return $item;
        });

        $meta = [
            'current_page' => $data->currentPage(),
            'per_page'     => $data->perPage(),
            'total'        => $data->total(),
            'last_page'    => $data->lastPage(),
            'from'         => $data->firstItem(),
            'to'           => $data->lastItem(),
        ];

        if ($items->isNotEmpty()) {
            return $this->utilityService->is200ResponseWithDataAndMeta($items, $meta);
        } else {
            return $this->utilityService->is404Response("Data tidak ditemukan");
        }
    }

    // public function register(Request $request)
    // {
    //     $user = User::where("email", $request->email)->first();

    //     if ($user) {
    //         return $this->utilityService->is404Response("Email sudah digunakan");
    //     }

    //     $chars = 'abcdefghijklmnopqrstuvwxyz';
    //     $randomString = '';

    //     for ($i = 0; $i < 6; $i++) {
    //         $randomString .= $chars[random_int(0, strlen($chars) - 1)];
    //     }
    //     $data = [
    //         "name" => $request->name,
    //         "email" => $request->email,
    //         "password" => app("hash")->make($request->password),
    //         "role" => $request->role,
    //         'whatsapp' => $request->whatsapp,
    //         'username' => $randomString
    //     ];

    //     $dataWithoutPassword = [
    //         'username' => $randomString,
    //         "name" => $request->name,
    //         "email" => $request->email,
    //         "role" => $request->role,
    //         'whatsapp' => $request->whatsapp,
    //     ];

    //     $insert = Users::create($data);

    //     if ($insert) {
    //         // $success_message = "success registration for your user";
    //         // return $this->utilityService->is200Response($success_message);
    //         $user = User::where("email", $request->email)->first();

    //         // $user = DB::table('users')->orderBy('id', 'desc')->first();
    //         $dataWithoutPassword = [
    //             'username' => $randomString,
    //             "name" => $request->name,
    //             "email" => $request->email,
    //             'id' => $user->id,
    //             "role" => $request->role,
    //             'whatsapp' => $request->whatsapp,
    //         ];
    //         $credentials = $request->only(["email", "password"]);

    //         $token = Auth::guard('users')->attempt($credentials);
    //         Token::create([
    //             'token' => $token,
    //             'email' => $request->email,
    //             'is_active' => 0
    //         ]);

    //         return $this->respondWithToken($token, $dataWithoutPassword);
    //     } else {
    //         return $this->utilityService->is500Response("problem with server");
    //     }
    // }
    public function registerWithOtp(Request $request)
    {
        $user = User::where("email", $request->email)->first();
        $whatsapp = User::where("whatsapp", $request->whatsapp)->first();

        if ($user) {
            return $this->utilityService->is404Response("Email sudah digunakan");
        }
        if ($whatsapp) {
            return $this->utilityService->is404Response("No Whatsapp sudah digunakan");
        }

        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $randomString = '';

        for ($i = 0; $i < 6; $i++) {
            $randomString .= $chars[random_int(0, strlen($chars) - 1)];
        }

        $data = [
            "name" => $request->name,
            "email" => $request->email,
            "password" => app("hash")->make($request->password),
            "role" => $request->role,
            'whatsapp' => $request->whatsapp,
            'username' => $randomString
        ];

        $dataWithoutPassword = [
            'username' => $randomString,
            "name" => $request->name,
            "email" => $request->email,
            "role" => $request->role,
            'whatsapp' => $request->whatsapp,
        ];

        $insert = Users::create($data);

        if ($insert) {
            // $success_message = "success registration for your user";
            // return $this->utilityService->is200Response($success_message);
            $user = User::where("email", $request->email)->first();

            // $user = DB::table('users')->orderBy('id', 'desc')->first();
            $dataWithoutPassword = [
                'username' => $randomString,
                "name" => $request->name,
                "email" => $request->email,
                'id' => $user->id,
                "role" => $request->role,
                'whatsapp' => $request->whatsapp,
            ];
            $credentials = $request->only(["email", "password"]);
            $otp = rand(100000, 999999);
            $expiredAt = time() + 600;

            $dataOtp = [
                'user_id' => $user->id,
                'otp' => $otp,
                'expired_at' => $expiredAt
            ];
            Otp::create($dataOtp);
            $message = "Kode OTP kamu adalah: $otp\nGunakan dalam 2 menit untuk aktivasi akun.";

            $data = [
                'target' => $request->whatsapp,
                'message' => $message
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.fonnte.com/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_HTTPHEADER => [
                    "Authorization:" . env('TOKEN_WA')
                ],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
            ]);

            $response = curl_exec($curl);
            curl_close($curl);
            $token = Auth::guard('users')->attempt($credentials);
            Token::create([
                'token' => $token,
                'email' => $request->email,
                'is_active' => 0
            ]);

            return $this->respondWithToken($token, $dataWithoutPassword);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
    public function registerWithWhatsapp(Request $request)
    {
        $whatsapp = User::where("whatsapp", $request->whatsapp)->first();

        if ($whatsapp) {
            $otps = DB::table('otps')->where('user_id', $whatsapp->id)->first();
            $updateOTP = Otp::find($otps->id);
            $otp = rand(100000, 999999);
            $expiredAt = time() + 600;
            $message = "Kode OTP kamu adalah: $otp\nGunakan dalam 2 menit untuk aktivasi akun.";

            $data = [
                'target' => $request->whatsapp,
                'message' => $message
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.fonnte.com/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_HTTPHEADER => [
                    "Authorization:" . env('TOKEN_WA')
                ],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
            ]);

            $response = curl_exec($curl);
            curl_close($curl);
            $updateOTP->otp = $otp;
            $updateOTP->expired_at = $expiredAt;
            $updateOTP->save();


            $credentials = $request->only(["email", "password"]);
            $tokens = DB::table('tokens')->where('email', $whatsapp->email)->first();
            $token = Auth::guard('users')->attempt($credentials);
            if ($tokens->is_active === 1) {
                if ($request->password === ENV("PASSWORD_DEFAULT")) {
                    if (!$token = Auth::guard('users')->attempt($credentials)) {
                        $dataWithoutPassword = [
                            'username' => $whatsapp->username,
                            "name" => $whatsapp->name,
                            "email" => $whatsapp->email,
                            "role" => "user",
                            "id" => $whatsapp->id,
                            'whatsapp' => $request->whatsapp,
                        ];
                        return $this->respondWithToken($tokens->token, $dataWithoutPassword);
                    } else {

                        $dataWithoutPassword = [
                            'username' =>  $whatsapp->username,
                            "name" => $whatsapp->name,
                            "email" => $whatsapp->email,
                            "id" => $whatsapp->id,
                            "role" => "user",
                            'whatsapp' => $request->whatsapp,
                            'is_active' => 0
                        ];
                        return $this->respondWithToken($tokens->token, $dataWithoutPassword);
                    }
                } else {
                    $dataWithoutPassword = [
                        'username' => $whatsapp->username,
                        "name" => $whatsapp->name,
                        "id" => $whatsapp->id,
                        "email" => $whatsapp->email,
                        "role" => "user",
                        'whatsapp' => $request->whatsapp,
                        'is_active' => 0
                    ];
                    return $this->respondWithToken($tokens->token, $dataWithoutPassword);
                }
            } else {
                $dataWithoutPassword = [
                    'username' => $whatsapp->username,
                    "name" => $whatsapp->name,
                    "email" => $whatsapp->email,
                    "id" => $whatsapp->id,
                    "role" => "user",
                    'whatsapp' => $request->whatsapp,
                    'is_active' => 0
                ];
                return $this->respondWithToken($token, $dataWithoutPassword);
            }
        }

        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $randomString = '';

        for ($i = 0; $i < 6; $i++) {
            $randomString .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $data = [
            "name" => $request->whatsapp,
            "email" => $request->email,
            "password" => app("hash")->make($request->password),
            "role" => "user",
            'whatsapp' => $request->whatsapp,
            'username' => $randomString
        ];

        $dataWithoutPassword = [
            'username' => $randomString,
            "name" => $request->whatsapp,
            "email" => $request->email,
            "role" => "user",
            'whatsapp' => $request->whatsapp,
            'is_active' => 0
        ];

        $insert = Users::create($data);

        if ($insert) {
            // $success_message = "success registration for your user";
            // return $this->utilityService->is200Response($success_message);
            $user = User::where("email", $request->email)->first();

            // $user = DB::table('users')->orderBy('id', 'desc')->first();
            $dataWithoutPassword = [
                'username' => $randomString,
                "name" => $request->whatsapp,
                "email" => $request->email,
                'id' => $user->id,
                "role" => "user",
                'whatsapp' => $request->whatsapp,
                'is_active' => 0
            ];
            $credentials = $request->only(["email", "password"]);
            $otp = rand(100000, 999999);
            $expiredAt = time() + 600;

            $dataOtp = [
                'user_id' => $user->id,
                'otp' => $otp,
                'expired_at' => $expiredAt
            ];
            Otp::create($dataOtp);
            $message = "Kode OTP kamu adalah: $otp\nGunakan dalam 2 menit untuk aktivasi akun.";

            $data = [
                'target' => $request->whatsapp,
                'message' => $message
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.fonnte.com/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_HTTPHEADER => [
                    "Authorization:" . env('TOKEN_WA')
                ],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
            ]);

            $response = curl_exec($curl);
            curl_close($curl);
            $token = Auth::guard('users')->attempt($credentials);
            Token::create([
                'token' => $token,
                'email' => $request->email,
                'is_active' => 0
            ]);

            return $this->respondWithToken($token, $dataWithoutPassword);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }



    public function create(Request $request)
    {
        $user = User::where("email", $request->email)->first();

        if ($user) {
            return $this->utilityService->is404Response("Email sudah digunakan");
        }

        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $randomString = '';

        for ($i = 0; $i < 6; $i++) {
            $randomString .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $data = [
            "name" => $request->name,
            "email" => $request->email,
            "whatsapp" => $request->whatsapp,
            "password" => app("hash")->make($request->password),
            "role" => $request->role,
            'username' => $randomString
        ];

        $dataWithoutPassword = [
            'username' => $randomString,
            "name" => $request->name,
            "email" => $request->email,
            "whatsapp" => $request->whatsapp,
            "role" => $request->role
        ];

        $insert = Users::create($data);

        if ($insert) {
            // $success_message = "success registration for your user";
            // return $this->utilityService->is200Response($success_message);
            $users = $insert->fresh();
            $credentials = $request->only(["email", "password"]);

            $token = Auth::guard('users')->attempt($credentials);
            Token::create([
                'token' => $token,
                'email' => $request->email,
                "whatsapp" => $request->whatsapp,
                'is_active' => 0
            ]);

            return $this->respondWithToken($token, $dataWithoutPassword);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function current(Request $request)
    {
        $credentials = $request->only(["email", "password"]);

        if (!$token = Auth::guard('users')->attempt($credentials)) {
            $responseMessage = "password salah";
            return $this->utilityService->is422Response($responseMessage);
        } else {
            $success_message = "Password Benar";
            return $this->utilityService->is200Response($success_message);
        }
    }
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->utilityService->is404Response("User tidak ditemukan");
        }

        // Cek apakah email sudah digunakan oleh user lain
        $existingUser = DB::table('users')
            ->where('email', $request->email)
            ->where('id', '!=', $id)
            ->first();

        if ($existingUser) {
            return $this->utilityService->is404Response("Email sudah digunakan oleh user lain");
        }

        // Update data user
        $user->name = $request->name;
        $user->whatsapp = $request->whatsapp;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = app('hash')->make($request->password);
        }

        if ($user->save()) {
            return $this->utilityService->is200ResponseWithData("Data user berhasil diupdate", $user);
        } else {
            return $this->utilityService->is500Response("Terjadi kesalahan pada server");
        }
    }


    public function forgotPassword(Request $request)
    {
        $email = $request->email;
        $data = DB::table('users')->where('email', $email)->first();

        $url = Crypt::encryptString($email);
        $name = $data->name;
        Mail::send('forgot-password', $data = ['name' => "Arunkumar", 'url' => $url], function ($message) use ($email, $name) {
            $message->to($email, $name)->subject('Invitation');
            $message->from('eksloba21@gmail.com', 'Invitation');
        });
        $success_message = "Email ganti password berhasil dikirim";
        return $this->utilityService->is200Response($success_message);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function login(Request $request)
    // {
    //     $credentials = $request->only(["email", "password"]);

    //     $tokens = DB::table('tokens')->where('email', $request->email)->first();
    //     $user = User::leftJoin('user_profiles', 'user_profiles.user_id', '=', 'users.id')
    //         ->where('email', $request->email)
    //         ->select(
    //             'users.*',
    //             'user_profiles.image',
    //             DB::raw('COALESCE(user_profiles.name, users.name) as name')
    //         )
    //         ->first();

    //     if (!$user) {
    //         $responseMessage = "Email tidak terdaftar";
    //         return $this->utilityService->is422Response($responseMessage);
    //     }
    //     if (!$token = Auth::guard('users')->attempt($credentials)) {
    //         if (!$user ||  $request->password !== ENV("PASSWORD_DEFAULT")) {
    //             $responseMessage = "Email atau password salah";
    //             return $this->utilityService->is422Response($responseMessage);
    //         }
    //     }
    //     $data = $token ? $token : $tokens->token;
    //     return $this->respondWithToken($data, $user);
    // }
    public function login(Request $request)
    {
        $contact = $request->input('contact'); // bisa email atau no HP
        $password = $request->input('password');

        $isEmail = filter_var($contact, FILTER_VALIDATE_EMAIL);

        // Ambil user berdasarkan email atau phone
        $user = User::leftJoin('user_profiles', 'user_profiles.user_id', '=', 'users.id')
            ->when($isEmail, function ($query) use ($contact) {
                return $query->where('users.email', $contact);
            }, function ($query) use ($contact) {
                return $query->where('users.whatsapp', $contact);
            })
            ->select(
                'users.*',
                'user_profiles.image',
                DB::raw('COALESCE(user_profiles.name, users.name) as name')
            )
            ->first();

        if (!$user) {
            $responseMessage = $isEmail ? "Email tidak terdaftar" : "Nomor HP tidak terdaftar";
            return $this->utilityService->is422Response($responseMessage);
        }

        // Buat field credentials sesuai input
        $credentials = $isEmail
            ? ['email' => $contact, 'password' => $password ?? env("PASSWORD_DEFAULT")]
            : ['whatsapp' => $contact, 'password' => $password ?? env("PASSWORD_DEFAULT")];

        $tokens = DB::table('tokens')->where('email', $user->email)->first();

        if (!$token = Auth::guard('users')->attempt($credentials)) {
            // Cek fallback default password (jika ada)
            if (!$user || $password !== env("PASSWORD_DEFAULT")) {
                return $this->utilityService->is422Response("Email/Nomor HP atau password salah");
            }
        }

        $data = $token ?: ($tokens->token ?? null);
        return $this->respondWithToken($data, $user);
    }


    public function loginWithGoogle(Request $request)
    {
        $tokens = DB::table('tokens')->where('email', $request->email)->first();

        $user = Users::where("email", $request->email)
            ->first();

        if (!$user) {
            return $this->utilityService->is404Response("Email dan token tidak sesuai");
        }

        $token = JWTAuth::fromUser($user);

        $user->token = null;
        $user->save();
        $data = [
            'token' => $token,
            'tokens' => $tokens
        ];
        return $this->respondWithToken($data, $user);
    }


    public function updateImage(Request $request, $id)
    {
        $user = Users::find($id);

        if ($user->image) {
            $delete_image = $user->image;
            $url = UrlRemove::remover($delete_image);
            Storage::disk('minio')->delete($url);
        }

        $fileName = 'ImageUser-' . time() . '.webp';
        $image = $this->utilityService->convertImageToWebp($request->file('image'));
        Storage::disk('minio')->put($fileName, $image);
        $urlImage = Storage::disk('minio')->url($fileName);
        $user->image = $urlImage;
        if ($user->save()) {
            return $this->utilityService->is200ResponseWithData("Data user berhasil diupdate", $user);
        } else {
            return $this->utilityService->is500Response("Terjadi kesalahan pada server");
        }
    }
    public function redirectToGoogle()
    {
        $query = http_build_query([
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
            'response_type' => 'code',
            'scope' => 'email profile',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    // public function handleGoogleCallback(Request $request)
    // {
    //     if ($request->get('error')) {
    //         $query = http_build_query([
    //             'client_id' => env('GOOGLE_CLIENT_ID'),
    //             'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
    //             'response_type' => 'code',
    //             'scope' => 'email profile',
    //         ]);

    //         return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    //     } else {
    //         $response = Http::asForm()->post('https://accounts.google.com/o/oauth2/token', [
    //             'client_id' => env('GOOGLE_CLIENT_ID'),
    //             'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    //             'code' => $request->input('code'),
    //             'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
    //             'grant_type' => 'authorization_code',
    //         ]);
    //         // dd($response);
    //         $access_token = $response['access_token'];

    //         $user_info = Http::get('https://www.googleapis.com/oauth2/v1/userinfo', [
    //             'access_token' => $access_token,
    //         ])->json();

    //         $user = User::where("email", $user_info['email'])->first();

    //         if ($user) {
    //             $userProfile = DB::table('user_profiles')->where('user_id', $user->id)->first();
    //             if (isset($userProfile)) {
    //                 $image = $userProfile->image;
    //             } else {
    //                 $image = '';
    //             }
    //             $credentials = [
    //                 'email' => $user_info['email'],
    //                 'password' => env('PASSWORD_DEFAULT'),
    //             ];

    //             $token = DB::table('tokens')->where('email', $user_info['email'])->first();

    //             header("Location: " . env('FRONT_END_URL') . "/auth/change-password?email=" . $user_info['email'] . "&name=" . $user_info['name'] . "&whatsapp=" . $user->whatsapp . "&role=" . $user->role . "&id=" . $user->id . "&token=" . $token->token . "&username=" . $user->username . "&image=" . $image);


    //             $user->token = $access_token;
    //             $user->save();

    //             exit;
    //         } else {
    //             $chars = 'abcdefghijklmnopqrstuvwxyz';
    //             $randomString = '';

    //             for ($i = 0; $i < 6; $i++) {
    //                 $randomString .= $chars[random_int(0, strlen($chars) - 1)];
    //             }
    //             $dataUser = [
    //                 'name' => $user_info['name'],
    //                 'email' => $user_info['email'],
    //                 'password' => app("hash")->make(env('PASSWORD_DEFAULT')),
    //                 'role' => 'user',
    //                 'username' => $randomString
    //             ];
    //             $insert =  Users::create($dataUser);
    //             $credentials = [
    //                 'email' => $user_info['email'],
    //                 'password' => env('PASSWORD_DEFAULT'),
    //             ];

    //             $token = Auth::guard('users')->attempt($credentials);

    //             Token::create([
    //                 'token' => $token,
    //                 'email' => $user_info['email'],
    //                 'is_active' => 1
    //             ]);
    //             $users = $insert->fresh();
    //             $userProfile = DB::table('user_profiles')->where('user_id', $users->id)->first();
    //             if (isset($userProfile)) {
    //                 $image = $userProfile->image;
    //             } else {
    //                 $image = '';
    //             }
    //             header("Location: " . env('FRONT_END_URL') . "/auth/change-password?email=" . $user_info['email'] . "&name=" . $user_info['name'] . "&whatsapp=" . $users->whatsapp . "&is_active=1&role=" . $users->role . "&id=" . $users->id . "&token=" . $token . "&username=" . $users->username . "&image=" . $image);
    //             exit;
    //         }
    //     }
    // }
    public function handleGoogleCallback(Request $request)
    {
        if ($request->get('error')) {
            $query = http_build_query([
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
                'response_type' => 'code',
                'scope' => 'email profile',
            ]);
            return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
        }

        // Step 1: Ambil Access Token
        $response = Http::asForm()->post('https://accounts.google.com/o/oauth2/token', [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'code' => $request->input('code'),
            'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
            'grant_type' => 'authorization_code',
        ]);

        $access_token = $response['access_token'];

        // Step 2: Ambil Data User Google
        $user_info = Http::get('https://www.googleapis.com/oauth2/v1/userinfo', [
            'access_token' => $access_token,
        ])->json();

        // Step 3: Cek apakah user sudah ada
        $user = User::where("email", $user_info['email'])->first();

        if (!$user) {
            // ⛔ Tidak ditemukan → redirect ke auth/register dengan email Google
            $query = http_build_query([
                'contact' => $user_info['email']
            ]);
            return redirect(env('FRONT_END_URL') . '/auth/complete-registration?' . $query);
        }

        // ✅ USER EXIST
        $image = DB::table('user_profiles')->where('user_id', $user->id)->value('image') ?? '';

        $contactKey = $user->whatsapp ?? $user->phone ?? $user->email;
        $tokenRecord = DB::table('tokens')->where('email', $contactKey)->first();

        $user->token = $access_token;
        $user->save();

        return $this->redirectToFrontend($user, $tokenRecord->token ?? '', $image);
    }

    private function redirectToFrontend($user, $token, $image)
    {
        $query = http_build_query([
            'email' => $user->email,
            'name' => $user->name,
            'whatsapp' => $user->whatsapp,
            'role' => $user->role,
            'id' => $user->id,
            'token' => $token,
            'username' => $user->username,
            'image' => $image,
            'is_active' => 1
        ]);

        header("Location: " . env('FRONT_END_URL') . "/?$query");
        exit;
    }



    // public function getMe()
    // {
    //     $token = JWTAuth::getToken();
    //     $payload = JWTAuth::getPayload($token)->toArray();
    //     $user = Users::find($payload['id']);
    //     if (!$user) {
    //         return $this->utilityService->is422Response("User tidak ditemukan");
    //     }

    //     return $this->utilityService->is200ResponseWithData("User ditemukan", $user);
    // }
    public function getMe(Request $request)
    {
        $email = $request->get('email');
        $user = DB::table('users')->where('email', $email)->orWhere('whatsapp', $email)->first();
        if (!$user) {
            return $this->utilityService->is422Response("User tidak ditemukan");
        }

        return $this->utilityService->is200ResponseWithData("User ditemukan", $user);
    }
    public function getProfil(Request $request)
    {
        $email = $request->get('email');
        $whatsapp = $request->get('whatsapp');
        if ($whatsapp) {
            $user = DB::table('users')->where('email', $email)->where('whatsapp', $whatsapp)->first();
        } else {
            $user = DB::table('users')->where('email', $email)->first();
            if ($user->whatsapp) {
                return $this->utilityService->is422Response("User tidak ditemukan");
            }
        }
        if (!$user) {
            return $this->utilityService->is422Response("User tidak ditemukan");
        }

        return $this->utilityService->is200ResponseWithData("User ditemukan", $user);
    }

    public function list(Request $request)
    {
        $search = $request->get('search') ? $request->get('search') : '';
        $sort_order = $request->get('sort_order') ? $request->get('sort_order') : 'desc';
        $sort_by = $request->get('sort_by') ? $request->get('sort_by') : 'created_at';
        $page_size = $request->get('page_size') ? $request->get('page_size') : '10';

        $dataWithPaginate = Users::where('name', 'like', '%' . $search . '%')->leftJoin('tokens', 'tokens.email', '=', 'users.email')
            ->select(
                'users.*',
                'tokens.is_active'
            )
            ->orderBy($sort_by, $sort_order)->paginate($page_size);
        $data = $dataWithPaginate->items();
        $total = $dataWithPaginate->total();
        $limit = $page_size;
        $page = $dataWithPaginate->currentPage();

        $meta = [
            'total' => $total,
            'limit' => $limit,
            'page' => $page,
        ];

        return $this->utilityService->is200ResponseWithDataAndMeta($data, $meta);
    }

    public function changepassword(Request $request)
    {
        if ($request->encryptEmail) {
            $email = Crypt::decryptString($request->encryptEmail);
        } else {
            $email = $request->email;
        }
        $users = DB::table('users')->where('email', $email)->first();
        $changePassword = User::find($users->id);
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
        $user = Users::find($id);
        if ($user == null) {
            return $this->utilityService->is500Response('Data not found');
        }
        $tokens = DB::table('tokens')->where('email', $user->email)->delete();
        $user->delete();
        $success_message = "Data User Berhasil Dihapus";
        return $this->utilityService->is200Response($success_message);
    }
}
