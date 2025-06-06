<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Mail\ResetPassword;
use App\Mail\VerifyMail;
use App\Mail\WelcomMail;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VerifyE;
use Hidehalo\Nanoid\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $atts=$request->validate([
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'name'=> 'required|string|max:255|unique:users,name',
            'isadmin'=>'required|boolean',
            'avatar_id'=>'nullable|integer|min:1|max:2'
        ]);
        $client = new Client();
        $nanoid = $client->generateId(12);
        Log::info('User Data Before Insert:', ['userData' => [
            'nanoid' => $nanoid,
            'name' => $atts['name'],
            'email' => $atts['email'],
            'avatar_id'=>$atts['avatar_id']
        ]]);

        //is admin thing need to be deleted after finish testing
        $user = User::create([
            'nanoid'=>$nanoid,
            'name' => $atts['name'],
            'email' => $atts['email'],
            'password'=> Hash::make($atts['password']),
            'isadmin'=>$request->isadmin,
            'avatar_id'=>$atts['avatar_id']??null
        ]);
        $user_name= $atts['name'];
        $user_email= $atts['email'];
        Mail::to($atts['email'])->send(new WelcomMail($user_name,$user_email));
        return response()->json([
            'message' => 'user instance has been created successfully',
            'user'=>UserResource::make($user),
            'token' => $user->createToken($user->name)->plainTextToken
        ]);
    }
    public function sendEmailVerification(Request $request){
        $user_name = $request->user()->name;
        $user_email =$request->user()->email;
        $token = rand(1000, 9999);
        VerifyE::updateOrCreate(
            ['email' => $user_email],
            ['token' => $token,'expires_at'=>now()->addHours(5)]
        );
        Mail::to($user_email)->send(new VerifyMail($token,$user_name, $user_email));
        return response()->json(['message' => 'Verification email sent successfully']);
    }
    public function login(Request $request){
        $atts=$request->validate([
            'email'=>'email|exists:users,email|required',
            'password'=>'required'
        ]);
        $user=User::where('email', $atts['email'])->first();
        if(!$user || !Hash::check($atts['password'],$user->password)){
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
        $atts=$request->validate([
            'token'=>'required'
        ]);
        $verify_token = VerifyE::where('token', $atts['token'])
            ->where('expires_at', '>=', now())
            ->first();
        if($verify_token){
        $email = $verify_token->email;
        $user = User::where('email',$email)->first();
            if($user->email==$request->user()->email){
                $user->is_activated=true;
                $user->email_verified_at=now();
                $user->save();
            $verify_token->delete();
            return response()->json(['message' => 'user has been activated sucessfuly']);
                
            }
            else{
            return response()->json(['message' => 'faild to activate the user try again']);
            }
        }else{
            return response()->json(['message' => 'faild to activate the user try again']);
        }
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
    public function sendResetEmail(Request $request){
        $atts=$request->validate(['email'=>'required|exists:users,email']);
        $atts['email'] = strtolower($atts['email']);
        $user=User::where('email',$atts['email'])->firstOrFail();
        if($request->user()){
            if($request->user()->email !== $user->email){
                return response()->json([
                    'message'=>'unauthorized'
                ],403);
            }
        }
        $token = rand(1000, 9999);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $atts['email']], // If an entry exists, update it
            ['token' => $token, 'created_at' => now()]
        );
        Mail::to($user->email)->send(new ResetPassword($token,$user->name,$user->email));
        return response()->json(['message' => 'Reset link sent successfully!']);
    }
    public function resetPassword(Request $request,$token=null){
        //note: in the endpint it shall be optional /{token?}
        $request->merge(['token' => $token]);
        $atts=$request->validate([
            'email'=>'nullable|required_without:current|email|exists:users,email',
            'current'=> 'nullable|required_without:token',
            'token'=> 'nullable|required_without:current',
            'password'=> 'required|string|min:8|confirmed'
        ]);
        if(isset($atts['current'])){
            if($request->user()&& Hash::check($atts['current'], $request->user()->password)){
            $newPassword=Hash::make($atts['password']);
            $request->user()->update(['password'=>$newPassword]);
            //maybe the user tried to send email first then remembered his current password so there would be a tokne for him in the DB that should be deleted
            DB::table('password_reset_tokens')->where('email',$request->user()->email)->delete();
            $request->user()->tokens()->delete();
            //return to who since they will be signed out
            return response()->json(['message'=>'your password has been reset'],200);
            }else{
                return response()->json(['message'=>'current password is incorrecr'],400);
            }
        }else{
        $atts['email'] = strtolower($atts['email']);
        DB::table('password_reset_tokens')->where('email',$atts['email'])->where('created_at','<',now()->subMinutes(5))->delete();
        $relatedToken=DB::table('password_reset_tokens')->where('email',$atts['email'])->first();
        if($relatedToken&&$atts['token']&&$atts['token']===$relatedToken->token){
            $newPassword=Hash::make($atts['password']);
            $user= User::where('email', $atts['email'])->firstOrFail();
            if($user){
                $user->update(['password' => $newPassword]);
            }else{
                return response()->json(['message'=>'user not found'],404);
            }
            DB::table('password_reset_tokens')->where('email', $atts['email'])->delete();
            $user->tokens()->delete();
            return response()->json(['message'=>'password has been updated']);
        }else{
            return response()->json(['message'=>'token is expired or does not exists try to send email again'],400);
        }
    }
    }
}
