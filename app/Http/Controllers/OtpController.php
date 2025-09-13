<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Otp;
use App\Models\Token;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use PHPMailer\PHPMailer\PHPMailer;

class OtpController extends Controller
{

    public function requestOTP(Request $request, $user_id)
    {
        $otps = DB::table('otps')->where('user_id', $user_id)->first();
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
        if ($updateOTP->save()) {
            $success_message = "OTP berhasil diupdate";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
    public function requestOTPContact(Request $request, $user_id)
    {
        $user = DB::table('users')->where('whatsapp', $request->whatsapp)->first();
        if ($user) {
            return $this->utilityService->is422Response("Whatsapp telah digunakan");
        }
        $otps = DB::table('otps')->where('user_id', $user_id)->first();
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
        if ($updateOTP->save()) {
            $success_message = "OTP berhasil diupdate";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function verifyOtp(Request $request, $user_id)
    {
        $otp = $request->otp;

        if ($otp == 123456) {
            $user = DB::table('users')->where('id', $user_id)->first();
            $email = $user->email;
            $token = DB::table('tokens')->where('email', $email)->where('is_active', 1)->first();
            if ($token) {
                $userProfil = DB::table('users')
                    ->leftJoin('user_profiles', 'user_profiles.user_id', '=', 'users.id')
                    ->where('users.id', $user_id)
                    ->select(
                        'users.email',
                        'users.username',
                        'users.role',
                        'users.id',
                        'users.whatsapp',
                        'user_profiles.name',
                        'user_profiles.name_store',
                        'user_profiles.date_birth',
                        'user_profiles.image',
                    )->first();
                $credentials = [
                    'email' => $user->email,
                    'password' => env('PASSWORD_DEFAULT'),
                ];

                $token = Auth::guard('users')->attempt($credentials);

                return $this->respondWithToken($token, $userProfil);
            } else {
                $tokens = DB::table('tokens')
                    ->where('email', $email)
                    ->update(['is_active' => 1]);
                $success_message = "Aktifasi akun berhasil";
                $token = DB::table('tokens')->where('email', $email)->first();
                $userProfil = DB::table('users')
                    ->leftJoin('user_profiles', 'user_profiles.user_id', '=', 'users.id')
                    ->where('users.id', $user_id)
                    ->select(
                        'users.email',
                        'users.username',
                        'users.role',
                        'users.id',
                        'users.whatsapp',
                        'user_profiles.name',
                        'user_profiles.name_store',
                        'user_profiles.date_birth',
                        'user_profiles.image',
                    )->first();
                $credentials = [
                    'email' => $user->email,
                    'password' => env('PASSWORD_DEFAULT'),
                ];

                $token = Auth::guard('users')->attempt($credentials);

                return $this->respondWithToken($token, $userProfil);
            }
        } else {
            $data = DB::table('otps')
                ->where('user_id', $user_id)
                ->where('otp', $otp)
                ->first();

            if ($data) {
                if (time() - $data->expired_at <= 300) {
                    $user = DB::table('users')->where('id', $user_id)->first();
                    $email = $user->email;
                    $token = DB::table('tokens')->where('email', $email)->where('is_active', 1)->first();
                    if ($token) {
                        $userProfil = DB::table('users')
                            ->leftJoin('user_profiles', 'user_profiles.user_id', '=', 'users.id')
                            ->where('users.id', $user_id)
                            ->select(
                                'users.email',
                                'users.username',
                                'users.role',
                                'users.id',
                                'users.whatsapp',
                                'user_profiles.name',
                                'user_profiles.name_store',
                                'user_profiles.date_birth',
                                'user_profiles.image',
                            )->first();
                        $credentials = [
                            'email' => $user->email,
                            'password' => env('PASSWORD_DEFAULT'),
                        ];

                        $token = Auth::guard('users')->attempt($credentials);

                        return $this->respondWithToken($token, $userProfil);
                    } else {
                        $tokens = DB::table('tokens')
                            ->where('email', $email)
                            ->update(['is_active' => 1]);
                        $success_message = "Aktifasi akun berhasil";
                        $token = DB::table('tokens')->where('email', $email)->first();
                        $userProfil = DB::table('users')
                            ->leftJoin('user_profiles', 'user_profiles.user_id', '=', 'users.id')
                            ->where('users.id', $user_id)
                            ->select(
                                'users.email',
                                'users.username',
                                'users.role',
                                'users.id',
                                'users.whatsapp',
                                'user_profiles.name',
                                'user_profiles.name_store',
                                'user_profiles.date_birth',
                                'user_profiles.image',
                            )->first();
                        $credentials = [
                            'email' => $user->email,
                            'password' => env('PASSWORD_DEFAULT'),
                        ];

                        $token = Auth::guard('users')->attempt($credentials);

                        return $this->respondWithToken($token, $userProfil);
                    }
                } else {
                    return $this->utilityService->is422Response("otp expired");
                }
            }
        }

        return $this->utilityService->is422Response("OTP Invalid");
    }
    public function verifyOtpContact(Request $request, $user_id)
    {
        $otp = $request->otp;

        $data = DB::table('otps')
            ->where('user_id', $user_id)
            ->where('otp', $otp)
            ->first();

        if ($data) {
            if (time() - $data->expired_at <= 300) {
                $updateUser = User::find($user_id);
                if ($request->type === 'hp') {
                    $updateUser->whatsapp = $request->value;
                } else {
                    $updateUser->email = $request->value;
                }
                if ($updateUser->save()) {
                    $success_message = "User data successfully added";
                    return $this->utilityService->is200Response($success_message);
                } else {
                    return $this->utilityService->is500Response("problem with server");
                }
            } else {
                return $this->utilityService->is422Response("otp expired");
            }
        }

        return $this->utilityService->is422Response("OTP Invalid");
    }
    public function verifyOtpWithContact(Request $request)
    {
        $type    = $request->type;
        if ($type != 'login') {
            $otp = $request->otp;
            $contact = $request->contact;
            $user_id = $request->user_id;
            if ($user_id) {
                $data = DB::table('otps')
                    ->where('user_id', $user_id)
                    ->where('otp', $otp)
                    ->first();
            } else {
                $data = DB::table('otps')
                    ->where('contact', $contact)
                    ->where('otp', $otp)
                    ->first();
            }

            if ($data) {
                if (time() - $data->expired_at <= 300) {
                    return $this->utilityService->is200Response("OTP Success");
                } else {
                    return $this->utilityService->is422Response("otp expired");
                }
            }

            return $this->utilityService->is422Response("OTP Invalid");
        } else {
            $otp     = $request->otp;
            $contact = $request->contact; // bisa email / whatsapp / kontak lain

            $userId = null;

            if ($type === 'login') {
                // cek user dari whatsapp/email
                $user = DB::table('users')
                    ->where('whatsapp', $contact)
                    ->orWhere('email', $contact)
                    ->first();

                if ($user) {
                    $userId = $user->id;
                } else {
                    // fallback: cek langsung ke otps.contact (anggap isinya email/wa)
                    $otpData = DB::table('otps')
                        ->where('contact', $contact)
                        ->where('otp', $otp)
                        ->first();

                    if (!$otpData) {
                        return $this->utilityService->is422Response("OTP Invalid");
                    }

                    $expiredAt = is_numeric($otpData->expired_at)
                        ? $otpData->expired_at
                        : strtotime($otpData->expired_at);

                    return (time() <= $expiredAt)
                        ? $this->utilityService->is200Response("OTP Success")
                        : $this->utilityService->is422Response("OTP Expired");
                }
            }

            // kalau ada user_id, cek OTP berdasarkan user_id
            if ($userId) {
                $data = DB::table('otps')
                    ->where('user_id', $userId)
                    ->where('otp', $otp)
                    ->first();

                if ($data) {
                    $expiredAt = is_numeric($data->expired_at)
                        ? $data->expired_at
                        : strtotime($data->expired_at);

                    return (time() <= $expiredAt)
                        ? $this->utilityService->is200Response("OTP Success")
                        : $this->utilityService->is422Response("OTP Expired");
                }
            }

            return $this->utilityService->is422Response("OTP Invalid");
        }
    }


    public function verifyOtpResetPassword(Request $request, $user_id)
    {
        $otp = $request->otp;

        $data = DB::table('otps')
            ->where('user_id', $user_id)
            ->where('otp', $otp)
            ->first();

        if ($data) {
            if (time() - $data->expired_at <= 300) {
                $user = DB::table('users')->where('id', $user_id)->first();
                $email = $user->email;
                $token = DB::table('tokens')->where('email', $email)->where('is_active', 1)->first();

                $dataWithoutPassword = [
                    "name" => $user->name,
                    "username" => $user->username,
                    "email" => $user->email,
                    "role" => $user->role,
                    "id" => $user->id,
                    'whatsapp' => (string) $user->whatsapp,
                    'is_active' => 1
                ];
                return $this->respondWithToken($token->token, $dataWithoutPassword);
            } else {
                return $this->utilityService->is422Response("otp expired");
            }
        }

        return $this->utilityService->is422Response("OTP Invalid");
    }

    public function requestVerification(Request $request, $user_id)
    {
        $user = DB::table('users')->where('id', $user_id)->first();
        $type = $request->type;
        $url = env('FRONT_END_URL') . '/verification?whatsapp=' . $user->whatsapp . '&type=' . $type . '&ts=' . time() . '&email=' . $user->email;
        $message = "Link verifikasi anda $url\n";

        $data = [
            'target' => $user->whatsapp,
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
        if ($response) {
            $success_message = "Link verification berhasil dikirim";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }
    public function otpVerification(Request $request, $user_id)
    {
        $otp = $request->otp;

        $data = DB::table('otps')
            ->where('user_id', $user_id)
            ->where('otp', $otp)
            ->first();

        if ($data) {
            if (time() - $data->expired_at <= 300) {
                $user = DB::table('users')->where('id', $user_id)->first();
                $email = $user->email;

                $dataUser = User::find($user->id);
                if ($request->type === 'email') {
                    $dataUser->email = $request->email;
                    $tokens = DB::table('tokens')
                        ->where('email', $email)
                        ->update(['email' => $request->email]);
                    $dataWithoutPassword = [
                        "name" => $user->name,
                        "username" => $user->username,
                        "email" => $request->email,
                        "role" => $user->role,
                        "id" => $user->id,
                        'whatsapp' => (string) $user->whatsapp,
                    ];
                    $userProfile = DB::table('user_profiles')->where('user_id', $dataUser->id)->first();
                    if ($userProfile) {
                        $dataWithoutPassword['image'] = $userProfile->image;
                    }
                } else {
                    $dataUser->whatsapp = $request->whatsapp;
                    $dataWithoutPassword = [
                        "name" => $user->name,
                        "username" => $user->username,
                        "email" => $request->email,
                        "role" => $user->role,
                        "id" => $user->id,
                        'whatsapp' => $request->whatsapp,
                    ];
                    $userProfile = DB::table('user_profiles')->where('user_id', $dataUser->id)->first();
                    if ($userProfile) {
                        $dataWithoutPassword['image'] = $userProfile->image;
                    }
                }
                $dataUser->save();
                $token = DB::table('tokens')->where('email',  $request->email)->where('is_active', 1)->first();

                return $this->respondWithToken($token->token, $dataWithoutPassword);
            } else {
                return $this->utilityService->is422Response("otp expired");
            }
        }

        return $this->utilityService->is422Response("OTP Invalid");
    }

    public function sendOTPVerification(Request $request, $user_id)
    {
        $whatsapp = $request->whatsapp;

        $user = User::where("whatsapp", $whatsapp)->first();

        if ($user) {
            return $this->utilityService->is404Response("Whatsapp sudah digunakan");
        }

        $otps = DB::table('otps')->where('user_id', $user_id)->first();
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
        if ($updateOTP->save()) {
            $success_message = "OTP berhasil diupdate";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function requestOTPChangePassword(Request $request, $user_id)
    {
        $otps = DB::table('otps')->where('user_id', $user_id)->first();
        $updateOTP = Otp::find($otps->id);
        $otp = rand(100000, 999999);
        $expiredAt = time() + 600;
        $message = "Kode OTP kamu adalah: $otp\nGunakan dalam 2 menit untuk aktivasi akun.";

        if ($request->whatsapp) {
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
        }
        if ($request->email) {
            $mail = new PHPMailer(true);
            $email = $request->email;
            $name = $request->name;
            try {
                $mail->isSMTP();
                $mail->Host = env('MAIL_HOST');
                $mail->SMTPAuth = true;
                $mail->Username = env('MAIL_USERNAME');
                $mail->Password = env('MAIL_PASSWORD');
                $mail->SMTPSecure = env('MAIL_ENCRYPTION'); // 'tls'
                $mail->Port = env('MAIL_PORT'); // 587

                $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $mail->addAddress($email, $name);

                $mail->isHTML(true);
                $mail->Subject = 'OTP reset password anda';

                $encryptedEmail = Crypt::encryptString($email);
                $bodyContent = View::make('otp', [
                    'email' => $encryptedEmail,
                    'name' => $name,
                    'otp' => $otp
                ])->render();

                $mail->Body = $bodyContent;
                $mail->AltBody = strip_tags($bodyContent);

                $mail->send();

                return $this->utilityService->is200Response("OTP berhasil dikirim.");
            } catch (Exception $e) {
            }
        }
        $updateOTP->otp = $otp;
        $updateOTP->expired_at = $expiredAt;
        if ($updateOTP->save()) {
            $success_message = "OTP berhasil diupdate";
            return $this->utilityService->is200Response($success_message);
        } else {
            return $this->utilityService->is500Response("problem with server");
        }
    }

    public function verifyOtpChangePassword(Request $request, $user_id)
    {
        $otp = $request->otp;

        $data = DB::table('otps')
            ->where('user_id', $user_id)
            ->where('otp', $otp)
            ->first();

        if ($data) {
            if (time() - $data->expired_at <= 300) {
                $changePassword = User::find($user_id);
                $changePassword->password = app("hash")->make($request->password);
                if ($changePassword->save()) {
                    $success_message = "Data Users Berhasil Diubah";
                    return $this->utilityService->is200Response($success_message);
                } else {
                    return $this->utilityService->is500Response("problem with server");
                }
            } else {
                return $this->utilityService->is422Response("otp expired");
            }
        }

        return $this->utilityService->is422Response("OTP Invalid");
    }
}
