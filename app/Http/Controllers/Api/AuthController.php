<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $user->sendEmailVerificationNotification();

        // Assign user to role
        $user->roles()->attach(Role::where('name', 'user')->first()->id);

        $token = $user->createToken('passportToken')->accessToken;

        return ApiResponse::success(['user' => $user->load('roles'), 'token' => $token], 'Registered successfully', 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('passportToken')->accessToken;

        return ApiResponse::success(['user' => $user->load('roles'), 'token' => $token], 'Logged in successfully', 201);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([], 'Logged out successfully');
    }
}
