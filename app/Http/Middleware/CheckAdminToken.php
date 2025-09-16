<?php

namespace App\Http\Middleware;

use App\Http\Services\UtilityService;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class CheckAdminToken
{
    protected $utilityService;

    public function __construct()
    {
        $this->utilityService = new UtilityService;
    }

    public function handle($request, Closure $next)
    {
        try {
            $token = JWTAuth::getToken();
            Auth::shouldUse('admins');

            $admin = auth('admins')->setToken($token)->authenticate();

            if (!$admin) {
                return $this->utilityService->is401Response("Unauthorized: Admin only");
            }

            // 🔹 Tambahkan cek status aktif
            if ($admin->is_active == 0) {
                return $this->utilityService->is422Response("Your account is inactive, please contact support");
            }

            $request->merge(['admin_id' => $admin->id]);
            $request->setUserResolver(fn() => $admin);

            return $next($request);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return $this->utilityService->is422Response("token expired");
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return $this->utilityService->is422Response("invalid token");
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return $this->utilityService->is422Response("a token is required");
        }
    }
}
