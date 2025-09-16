<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\Token;
use App\Models\User;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\View;

class AuthController extends Controller
{
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
        } else {
            $response = Http::asForm()->post('https://accounts.google.com/o/oauth2/token', [
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'code' => $request->input('code'),
                'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
                'grant_type' => 'authorization_code',
            ]);
            // dd($response);
            $access_token = $response['access_token'];

            $user_info = Http::get('https://www.googleapis.com/oauth2/v1/userinfo', [
                'access_token' => $access_token,
            ])->json();

            $user = User::where("email", $user_info['email'])->first();

            if ($user) {
                $userProfile = DB::table('user_profiles')->where('user_id', $user->id)->first();
                if (isset($userProfile)) {
                    $image = $userProfile->image;
                } else {
                    $image = '';
                }
                $credentials = [
                    'email' => $user_info['email'],
                    'password' => env('PASSWORD_DEFAULT'),
                ];

                $token = DB::table('tokens')->where('email', $user_info['email'])->first();
                $this->sendOTP($request, $user->email, $user->name, $user->id);
                header("Location: " . env('FRONT_END_URL') . "/auth/verification-account?email=" . $user_info['email'] . "&type=email&user_id=" . $user->id);


                $user->token = $access_token;
                $user->save();

                exit;
            } else {
                $chars = 'abcdefghijklmnopqrstuvwxyz';
                $randomString = '';

                for ($i = 0; $i < 6; $i++) {
                    $randomString .= $chars[random_int(0, strlen($chars) - 1)];
                }
                $dataUser = [
                    'name' => $user_info['name'],
                    'email' => $user_info['email'],
                    'password' => app("hash")->make(env('PASSWORD_DEFAULT')),
                    'role' => 'user',
                    'username' => $randomString
                ];
                $insert =  Users::create($dataUser);
                $credentials = [
                    'email' => $user_info['email'],
                    'password' => env('PASSWORD_DEFAULT'),
                ];

                $token = Auth::guard('users')->attempt($credentials);

                Token::create([
                    'token' => $token,
                    'email' => $user_info['email'],
                    'is_active' => 1
                ]);
                $userNew = $insert->fresh();
                $this->sendOTP($request, $userNew->email, $userNew->name, $userNew->id);
                header("Location: " . env('FRONT_END_URL') . "/auth/verification-account?email=" . $user_info['email'] . "&type=email&user_id=" . $userNew->id);
                exit;
            }
        }
    }


    public function login(Request $request)
    {
        $user = DB::table('users')
            ->where(function ($query) use ($request) {
                $query->where('email', $request->email_whatsapp)
                    ->orWhere('whatsapp', $request->email_whatsapp);
            })
            ->first();

        if (!$user) {
            return $this->utilityService->is422Response('Akun belum terdaftar');
        }
        // Jika ditemukan, kirim OTP ke WhatsApp

        if (filter_var($request->email_whatsapp, FILTER_VALIDATE_EMAIL)) {
            $this->sendOTP($request, $request->email_whatsapp, $user->name, $user->id);
        } else {
            $this->sendOTPWhatsapp($request, $user->whatsapp, $user->id);
        }

        return $this->utilityService->is200ResponseWithData("OTP berhasil dikirim", $user->id);
    }
    public function loginAdmin(Request $request)
    {
        $user = DB::table('admins')
            ->where(function ($query) use ($request) {
                $query->where('email', $request->email_whatsapp)
                    ->orWhere('whatsapp', $request->email_whatsapp);
            })
            ->first();

        if (!$user) {
            return $this->utilityService->is422Response('Akun belum terdaftar');
        }
        // Jika ditemukan, kirim OTP ke WhatsApp

        if (filter_var($request->email_whatsapp, FILTER_VALIDATE_EMAIL)) {
            $this->sendOTP($request, $request->email_whatsapp, $user->name, $user->id);
        } else {
            $this->sendOTPWhatsapp($request, $user->whatsapp, $user->id);
        }

        return $this->utilityService->is200ResponseWithData("OTP berhasil dikirim", $user->id);
    }


    public function register(Request $request)
    {
        $userExists = DB::table('users')
            ->where('email', $request->email_whatsapp)
            ->orWhere('whatsapp', $request->email_whatsapp)
            ->exists();

        if ($userExists) {
            return $this->utilityService->is422Response('Akun sudah terdaftar');
        }
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $randomString = '';
        for ($i = 0; $i < 6; $i++) {
            $randomString .= $chars[random_int(0, strlen($chars) - 1)];
        }

        // Data user default
        $dataUser = [
            'name' => $request->name,
            'email' => $request->email_whatsapp,
            'password' => app("hash")->make(env('PASSWORD_DEFAULT')),
            'role' => 'user',
            'username' => $randomString,
        ];

        // Jika register via whatsapp, sesuaikan field
        if ($request->type === 'whatsapp') {
            $dataUser['whatsapp'] = $request->email_whatsapp;
        }

        // Simpan user baru
        $newUser = Users::create($dataUser);
        $credentials = [
            'email' => $request->email_whatsapp,
            'password' => env('PASSWORD_DEFAULT'),
        ];

        $token = Auth::guard('users')->attempt($credentials);
        Token::create([
            'token' => $token,
            'email' => $request->email_whatsapp,
            'is_active' => 1
        ]);
        // Kirim OTP
        $this->sendOTPWhatsapp($request, $newUser->whatsapp, $newUser->id);

        if (filter_var($request->email_whatsapp, FILTER_VALIDATE_EMAIL)) {
            $this->sendOTP($request, $request->email_whatsapp, $request->name, $newUser->id);
        }

        return $this->utilityService->is200ResponseWithData('Registrasi berhasil, OTP telah dikirim', $newUser->id);
    }

    private  function sendOTP(Request $request, $email, $name, $user_id)
    {
        $otpCode = rand(100000, 999999);
        $expiredAt = time() + 600;

        // Cek apakah OTP sudah ada
        $existingOtp = DB::table('otps')->where('user_id', $user_id)->first();

        if ($existingOtp) {
            $otp = Otp::find($existingOtp->id);
            $otp->otp = $otpCode;
            $otp->expired_at = $expiredAt;
            $otp->save();
        } else {
            Otp::create([
                'user_id' => $user_id,
                'otp' => $otpCode,
                'expired_at' => $expiredAt
            ]);
        }

        // Konfigurasi PHPMailer dengan data dari .env
        $mail = new PHPMailer(true);

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
            $mail->Subject = 'Kode Verifikasi Anda';

            $encryptedEmail = Crypt::encryptString($email);
            $bodyContent = View::make('otp', [
                'email' => $encryptedEmail,
                'name' => $name,
                'otp' => $otpCode
            ])->render();

            $mail->Body = $bodyContent;
            $mail->AltBody = strip_tags($bodyContent);

            $mail->send();
        } catch (Exception $e) {
            return $this->utilityService->is500Response("Gagal mengirim email: " . $mail->ErrorInfo);
        }
    }
    private  function sendOTPWhatsapp(Request $request, $whatsapp, $user_id)
    {
        $otp = rand(100000, 999999);
        $expiredAt = time() + 600;

        $otpUser = DB::table('otps')->where('user_id', $user_id)->first();
        if ($otpUser) {
            $otps = Otp::find($otpUser->id);
            $otps->otp = $otp;
            $otps->expired_at = $expiredAt;
            $otps->save();
        } else {
            $dataOtp = [
                'user_id' => $user_id,
                'otp' => $otp,
                'expired_at' => $expiredAt
            ];
            Otp::create($dataOtp);
        }
        $message = "Kode OTP kamu adalah: $otp\nGunakan dalam 2 menit untuk aktivasi akun.";

        $data = [
            'target' => $whatsapp,
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
}
