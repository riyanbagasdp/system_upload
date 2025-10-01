<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // ✅ tambahin ini
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        Log::info('LOGIN TRY', $credentials);

        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            Log::error("EMAIL NOT FOUND");
        } else {
            Log::info("USER FOUND", [
                'email' => $user->email,
                'password_match' => Hash::check($credentials['password'], $user->password),
            ]);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            Log::error("JWTAuth::attempt FAILED");
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info("LOGIN SUCCESS", ['email' => $credentials['email']]);

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60,
        ]);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to logout, token invalid'], 500);
        }
    }

    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token is invalid or expired'], 401);
        }
    }
}
