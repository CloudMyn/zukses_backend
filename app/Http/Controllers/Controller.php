<?php

namespace App\Http\Controllers;
use App\Http\Services\UtilityService;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $utilityService;

    public function __construct()
    {
        $this->utilityService = new UtilityService;
    }

    public function respondWithToken($token, $data)
    {
        return response()->json([
            'status' => 'success',
            'token' => $token,
            'data' => $data,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60 * 60 * 24 * 7 // 1 minggu
        ], 200);
    }
    
}
