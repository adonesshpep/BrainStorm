<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $atts=$request->validate(['email'=>'nullable|email|']);
        if($request->user()){
            $user =$request->user();
        }else{
            $user=User::where('email',$atts['email'])->first();
            if(!$user){
                return response()->json(['message'=>'email not found'],404);
            }
        }

        if (!$user->is_activated) {
            return response()->json(['error' => 'Please verify your email before accessing this feature'], 403);
        }
        return $next($request);
    }
}
