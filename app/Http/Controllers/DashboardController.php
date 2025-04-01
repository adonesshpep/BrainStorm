<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        $users = User::all();
        return view('dashboard.index', ['users' => $users]);
    }
    public function show(User $user){
        return view('dashboard.show',['user'=>$user]);
    }
    public function destroy(User $user){
        $user->delete();
        return redirect('/dashboard');
    }
}
