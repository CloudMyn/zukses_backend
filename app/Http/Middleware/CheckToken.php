<?php

namespace App\Http\Middleware;

use App\Http\Services\UtilityService;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected $utilityService;
     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        $this->utilityService = new UtilityService;

    }

    public function handle($request, Closure $next)
    {
        try {
            $token = JWTAuth::getToken();
            // attempt to verify the credentials and create a token for the user
            $payload = JWTAuth::getPayload($token)->toArray();
            //check role token yang dikirimkan
            // return $payload;
            // $role = $payload['role'];
            // $clientId = $payload['client_id'];
            //1 = super admin
            //2 = brand owner
            //3 = reseller
            //4 = mempelai
            
            // if($role === '1' || $role === '2' || $role === '3' || $role === '4'){
                // $request->merge(['clientId' => $clientId]);
                return $next($request);
  
            // }else {
                // return $this->utilityService->is401Response();
                
            // }
            
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
    
            return $this->utilityService->is422Response("token expired");

    
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
    
            return $this->utilityService->is422Response("invalid token");
    
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return $this->utilityService->is422Response("a token is required");
    
        }
    }
}
