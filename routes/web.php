<?php

use App\Http\Controllers\SessionController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('dashboard',function(){
    $users=User::all();
    return view('dashboard.index',['users'=>$users]);
})->middleware('auth')->can('isadmin');
Route::get('dashboard/login',[SessionController::class,'create'])->name('login');
Route::post('dashboard/login',[SessionController::class,'store']);
Route::post('dashboard/logout',[SessionController::class,'destroy']);
