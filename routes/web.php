<?php

use App\Http\Controllers\AvatarController;
use App\Http\Controllers\DashboardController;
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

Route::get('/dashboard', [DashboardController::class,'index'])->middleware('auth')->can('access');
Route::get('/dashboard/{user}', [DashboardController::class,'show'])->middleware('auth')->can('access');
Route::delete('/dashboard/{user}', [DashboardController::class,'destroy'])->middleware('auth')->can('access');
Route::get('/login',[SessionController::class,'create'])->name('login');
Route::post('/login',[SessionController::class,'store']);
Route::post('/logout',[SessionController::class,'destroy']);
Route::get('/avatar',[AvatarController::class,'index'])->middleware('auth')->can('access');
Route::post('/avatar',[AvatarController::class,'store'])->middleware('auth')->can('access');
Route::get('/avatar/{avatar}',[AvatarController::class,'destroy'])->middleware('auth')->can('access');