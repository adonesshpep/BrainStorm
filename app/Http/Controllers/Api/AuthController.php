<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'name'=> 'required|string|max:255|unique:users,name',
            'isadmin'=>'required|boolean'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password'=> Hash::make($request->password),
            'isadmin'=>$request->isadmin
        ]);

        return response()->json([
            'message' => 'user instance has been created successfully',
            'user'=>$user,
            'token' => $user->createToken('mobile-app')->plainTextToken
        ]);
    }
    public function login(Request $request){
        // $request->validate([
        //     'email' => 'required|string|email|max:255',
        //     'password'=> 'required|string|min:8'
        // ]);
        // $user = User::Where('email', $request->email)->first();

        // if (!$user || !Hash::check($request->password, $user->password)) {
        //     throw ValidationException::withMessages([
        //         'email' => ['The provided credentials are incorrect.'],
        //     ]);
        // }

        // return response()->json([
        //     'token' => $user->createToken('mobile-app')->plainTextToken
        // ]);
        $request->validate([
            'email'=>'email|exists:users,email|required',
            'password'=>'required'
        ]);
        $user=User::where('email',$request->email)->first();
        if(!$user || !Hash::check($request->password,$user->password)){
            return [
                'message'=>'wrong stuff'
            ];
        }
        $token=$user->createToken($user->name);
        return response()->json([
            'user'=>$user,
            'token'=>$token->plainTextToken
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
