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
            'avatar_id'=>'nullable|integer|min:1|max:2'
        ]);
        $client = new Client();
        $nanoid = $client->generateId(12);
        Log::info('User Data Before Insert:', ['userData' => [
            'nanoid' => $nanoid,
            'name' => $atts['name'],
            'email' => $atts['email'],
            'avatar_id'=>$atts['avatar_id']??null
        ]]);

        //is admin thing need to be deleted after finish testing
        $user = User::create([
            'nanoid'=>$nanoid,
            'name' => $atts['name'],
            'email' => $atts['email'],
            'password'=> Hash::make($atts['password']),
            'avatar_id'=>$atts['avatar_id']??1
        ]);
        $user_name= $atts['name'];
        $user_email= $atts['email'];
        Mail::to($atts['email'])->send(new WelcomMail($user_name,$user_email));
        return response()->json([
            'message' => 'user instance has been created successfully',
            'user'=>UserResource::make($user),
        ]);
    }
    public function sendEmailVerification(Request $request){
        $atts=$request->validate([
            'name'=>'required|string',
            'email'=>'required|email|exists:users,email'
        ]);
        $user_name = $atts['name'];
        $user_email =$atts['email'];
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
            return response()->json(['message'=>'wrong cardinatials'],400);
        }
        $token=$user->createToken($user->name);
        return response()->json([
            'user'=>$user,
            'token'=>$token->plainTextToken
        ]);
    }
    public function userActivation(Request $request){
        $atts=$request->validate([
            'token'=>'required|string',
            'email'=>'required|email|exists:users,email'
        ]);
        $verify_token = VerifyE::where('token', $atts['token'])
            ->where('expires_at', '>=', now())
            ->first();
        if($verify_token){
        $email = $verify_token->email;
        $user = User::where('email',$email)->first();
            if($user->email==$atts['email']){
                $user->is_activated=true;
                $user->email_verified_at=now();
                $user->save();
            $verify_token->delete();
            return response()->json(['message' => 'user has been activated sucessfuly']);
                
            }
            else{
            return response()->json(['message' => 'faild to activate the user try again'],403);
            }
        }else{
            return response()->json(['message' => 'faild to activate the user try again'],403);
        }
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
    public function sendResetEmail(Request $request){
        $atts=$request->validate(['email'=>'required|email|exists:users,email']);
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
            ['email' => $atts['email']],
            ['token' => $token, 'created_at' => now()]
        );
        Mail::to($user->email)->send(new ResetPassword($token,$user->name,$user->email));
        return response()->json(['message' => 'Reset link sent successfully!']);
    }
    public function resetPasswordViaCurrnet(Request $request){
        $atts=$request->validate([
            'current'=> 'required|string',
            'password'=> 'required|string|min:8|confirmed'
        ]);
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
    }
    public function resetPasswordViaEmail(Request $request){
        $atts=$request->validate([
            'email'=>'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed'

        ]);
        $atts['email'] = strtolower($atts['email']);
        DB::table('password_reset_tokens')->where('email', $atts['email'])->where('created_at', '<', now()->subMinutes(5))->delete();
        $relatedToken = DB::table('password_reset_tokens')->where('email', $atts['email'])->first();
        if ($relatedToken && $atts['token'] && $atts['token'] === $relatedToken->token) {
            $newPassword = Hash::make($atts['password']);
            $user = User::where('email', $atts['email'])->firstOrFail();
            if ($user) {
                $user->update(['password' => $newPassword]);
            } else {
                return response()->json(['message' => 'user not found'], 404);
            }
            DB::table('password_reset_tokens')->where('email', $atts['email'])->delete();
            $user->tokens()->delete();
            return response()->json(['message' => 'password has been updated']);
        } else {
            return response()->json(['message' => 'token is expired or does not exists try to send email again'], 400);
        }
    }
    public function update(Request $request){
        $atts = $request->validate([
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'name' => 'nullable|string|max:255|unique:users,name',
            'avatar_id' => 'nullable|integer|min:1|max:2'
        ]);
        $user=$request->user();
        $user->update([
            'name'=>$atts['name']??$user->name,
            'avatar_id'=>$atts['avatar_id']??$user->avatar_id
        ]);
        if(isset($atts['email'])){
            $user->update(['email'=>$atts['email'],'is_activated'=>false]);
            $user->tokens()->delete();
            return response()->json(['message'=>'user info has updated , you need to reactivate your account']);
        }else{
            return response()->json(['message'=>'user info has updated']);
        }
    }
    public function destroy(Request $request){
        $atts=$request->validate(['password'=>'required|string|']);
        if(Hash::check($atts['password'], $request->user()->password)){
            $request->user()->tokens()->delete();
            $request->user()->delete();
            return response()->json(['message'=>'user and all its related puzzles and communities has deleted']);
        }
    }
    public function getAnotherUser(Request $request,$id){
        $user=User::findOrFail($id);
        if($request->user()->id===$user->id){
            return response()->json(['message'=>'Wrong function to see your personal info'],400);
        }
        return response()->json(['user'=>UserResource::make($user)]);
    }
    public function getMe(Request $request){
        $answers=$request->user()->answers()->get();
        $played=$answers->count();
        $solved=$answers->where('iscorrect',true)->count();
        $failed=$answers->where('iscorrect',false)->count();
        return response()->json([
            'number of puzzles played'=>$played,
            'number of puzzles solved'=>$solved,
            'number of puzzles failed to solve'=>$failed,
            'more info'=>UserResource::make($request->user())
        ]);
    }
}
