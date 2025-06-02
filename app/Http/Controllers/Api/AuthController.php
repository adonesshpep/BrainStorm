<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerifyMail;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VerifyE;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
        //i think it should an event
        $token=rand(1000,9999);
        $verify=new VerifyE();
        $verify->token=$token;
        $verify->email=$request->email;
        $verify->save();
        $user_name=$request->name;
        $user_email=$request->email;
        Mail::to($request->email)->send(new VerifyMail($token,$user_name,$user_email));
        return response()->json([
            'message' => 'user instance has been created successfully',
            'user'=>$user,
            'token' => $user->createToken($user->name)->plainTextToken
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
    public function userActivation(Request $request){
        $verify_token=VerifyE::where('token',$request->token)->first();
        if($verify_token){
        $email = $verify_token->email;
        $user = $request->user();
            //instade i think that should be a policy
            if($email === $user->email){
            $user->is_activated=1;
            $user->email_verified_at=now();
            $user->save();
            $verify_token->delete();
            return [
                'message'=>'user has been activated sucessfuly'
            ];}
            else{
                return [
                    "message" => 'faild to activate the user'
                ];
            }
        }else{
            return [
                "message"=>'faild to activate the user'
            ];
        }
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
