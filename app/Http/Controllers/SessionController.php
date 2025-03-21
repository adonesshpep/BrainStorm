<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function create (){
        return view('auth.login');
    }
    public function store (Request $request){
        $validated=$request->validate([
            'email'=>['required','email'],
            'password'=>['required']
        ]);
        if(!Auth::attempt($validated)){
            throw ValidationException::withMessages(['email' => 'sorry ! you are not in our DB '.$request->email." ".$request->password]);
        }
        $request->session()->regenerate();
        return redirect('dashboard');
    }
    public function destroy(){
        Auth::logout();
        return redirect('dashboard');
    }
}
