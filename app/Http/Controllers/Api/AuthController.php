<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error(__('messages.auth.invalid_credentials'), 401);
        }

        $deviceName = $request->device_name ?? $request->userAgent() ?? 'web';
        $token = $user->createToken($deviceName)->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];

        $message = 'Login berhasil';

        return ApiResponse::success($data, __('messages.auth.login_success'), 200);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return ApiResponse::success($user, __('messages.auth.me_success'), 200);
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'visitor',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];

        return ApiResponse::success($data, __('messages.auth.register_success'), 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, __('messages.auth.logout_success'), 200);
    }

    public function logutAllDevices(Request $request)
    {
        $request->user()->tokens()->delete();

        return ApiResponse::success(null, __('messages.auth.logout_all_devices_success'), 200);
    }
}
