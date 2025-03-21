<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'username'=> 'required|string|max:255|unique:users,username'
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password'=> Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'user instance has been created successfully',
            'token' => $user->createToken('mobile-app')->plainTextToken
        ]);
    }
    public function login(Request $request){
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password'=> 'required|string|min:8'
        ]);
        $user = User::Where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'token' => $user->createToken('mobile-app')->plainTextToken
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
