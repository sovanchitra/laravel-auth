<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|confirmed',
            ]);

            $user = $this->authService->register($data);

            return ApiResponse::success(
                ['name' => $user->name, 'email' => $user->email],
                'User created successfully',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        }
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        $result = $this->authService->login($data);

        if (!$result) {
            return ApiResponse::error('Invalid login credentials', [], 401);
        }

        $tokenName = $request->boolean('remember') ? 'long_lived_token' : 'auth_token';
        $token = $result['user']->createToken($tokenName)->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'name' => $result['user']->name,
            'email' => $result['user']->email,
        ], 'User logged in successfully');
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return ApiResponse::success([], 'Logged out successfully');
    }

    public function profile()
    {
        return ApiResponse::success([
            'id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'created_at' => auth()->user()->created_at->format('Y-m-d H:i:s'),
        ], 'User details');
    }
}
