<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class EmailController extends Controller
{
    public function sendOTP(Request $request, $user_id)
    {
        $user = DB::table('users')->where('id', $user_id)->first();

        $email = $user->email;
        $name = $user->name;

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
            $mail->Subject = 'OTP reset password anda';

            $encryptedEmail = Crypt::encryptString($email);
            $bodyContent = View::make('otp', [
                'email' => $encryptedEmail,
                'name' => $name,
                'otp' => $otpCode
            ])->render();

            $mail->Body = $bodyContent;
            $mail->AltBody = strip_tags($bodyContent);

            $mail->send();

            return $this->utilityService->is200Response("OTP berhasil dikirim.");
        } catch (Exception $e) {
            return $this->utilityService->is500Response("Gagal mengirim email: " . $mail->ErrorInfo);
        }
    }
    public function sendOTPEmail(Request $request, $user_id)
    {
        $user = DB::table('users')->where('email', $request->email)->first();
        if ($user) {
            return $this->utilityService->is422Response("Email telah digunakan");
        }
        $user = DB::table('users')->where('id', $user_id)->first();

        $email = $request->email;
        $name = $user->name;

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
            $mail->Subject = 'OTP reset password anda';

            $encryptedEmail = Crypt::encryptString($email);
            $bodyContent = View::make('otp', [
                'email' => $encryptedEmail,
                'name' => $name,
                'otp' => $otpCode
            ])->render();

            $mail->Body = $bodyContent;
            $mail->AltBody = strip_tags($bodyContent);

            $mail->send();

            return $this->utilityService->is200Response("OTP berhasil dikirim.");
        } catch (Exception $e) {
            return $this->utilityService->is500Response("Gagal mengirim email: " . $mail->ErrorInfo);
        }
    }
    public function sendVerification(Request $request, $user_id)
    {
        $user = DB::table('users')->where('id', $user_id)->first();
        $email =  $user->email;
        $name =  $user->name;
        $type = $request->type;
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
            $mail->Subject = 'OTP reset password anda';
            $ts = time();
            $encryptedEmail = Crypt::encryptString($email);
            $bodyContent = View::make('verification', [
                'email' => $encryptedEmail,
                'name' => $name,
                'email' => $email,
                'type' => $type,
                'ts' => $ts,
                'whatsapp' => $user->whatsapp
            ])->render();

            $mail->Body = $bodyContent;
            $mail->AltBody = strip_tags($bodyContent);

            $mail->send();

            return $this->utilityService->is200Response("OTP berhasil dikirim.");
        } catch (Exception $e) {
            return $this->utilityService->is500Response("Gagal mengirim email: " . $mail->ErrorInfo);
        }
    }

    public function sendOTPVerification(Request $request, $user_id)
    {
        $email = $request->email;
        $name = $request->name;

        $user = User::where("email", $request->email)->first();

        if ($user) {
            return $this->utilityService->is404Response("Email sudah digunakan");
        }
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
            $mail->Subject = 'OTP reset password anda';

            $encryptedEmail = Crypt::encryptString($email);
            $bodyContent = View::make('otp', [
                'email' => $encryptedEmail,
                'name' => $name,
                'otp' => $otpCode
            ])->render();

            $mail->Body = $bodyContent;
            $mail->AltBody = strip_tags($bodyContent);

            $mail->send();

            return $this->utilityService->is200Response("OTP berhasil dikirim.");
        } catch (Exception $e) {
            return $this->utilityService->is500Response("Gagal mengirim email: " . $mail->ErrorInfo);
        }
    }
}
