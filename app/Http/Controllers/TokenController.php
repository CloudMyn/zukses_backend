<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Models\Token;
use App\Models\User;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;

class TokenController extends Controller
{
    public function list(Request $request)
    {
        $search = $request->get('search') ? $request->get('search') : '';
        $search_by = $request->get('search_by') ? $request->get('search_by') : 'all';
        $sort_by = $request->get('sort_by') ? $request->get('sort_by') : 'created_at';
        $sort_order = $request->get('sort_order') ? $request->get('sort_order') : 'desc';
        $page_size = $request->get('page_size') ? $request->get('page_size') : '10000';

        $users = [];

        if ($search_by === "all") {
            $token = Token::where('email', 'like', '%' . $search . '%');
        } else {
            $token = Token::where($search_by, 'like', '&' . $search . '%');
        }

        $token = $token->orderBy($sort_by, $sort_order)->paginate($page_size);

        if (count($token) < 1) {
            return $this->utilityService->is404Response('Token tidak ditemukan');
        }

        return $this->utilityService->is200ResponseWithData('token ditemukan', $token);
    }

    public function index(Request $request)
    {
        $token = DB::table('tokens')->where('email', $request->email)->first();
        return $this->utilityService->is200ResponseWithData('token ditemukan', $token);
    }
}
