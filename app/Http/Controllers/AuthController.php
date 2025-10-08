<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Services\LogActivityService;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        
        $loginAttemptKey = 'login.fails.' . $request->email;

        if (! $token = auth('api')->attempt($validator->validated())) {
            
        
            $attempts = Cache::increment($loginAttemptKey);

            if ($attempts >= 5) {
        
                LogActivityService::addToLog(
                    'Warning: 5 or more failed login attempts for email: ' . $request->email,
                    $request,
                    'warning'
                );
            } else {
        
                LogActivityService::addToLog(
                    'Login attempt failed for email: ' . $request->email . ' (Attempt ' . $attempts . ')',
                    $request,
                    'failed'
                );
            }

            return response()->json(['error' => 'Email atau password salah'], 401);
        }

        
        Cache::forget($loginAttemptKey);
        
        LogActivityService::addToLog('User logged in', $request, 'success');

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout(Request $request)
    {
        
        LogActivityService::addToLog('User logged out', $request);

        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);
    }
}