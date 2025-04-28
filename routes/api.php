<?php

use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PuzzleController;
use App\Http\Controllers\Api\SolutionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/verifyemail',[AuthController::class, 'userActivation'])->middleware('auth:sanctum');
Route::apiResource('puzzle', PuzzleController::class)->except(['index', 'show'])->middleware('auth:sanctum');
Route::apiResource('puzzle', PuzzleController::class)->only(['index', 'show']);
// Route::apiResource('solution',SolutionController::class);
Route::post('/solution',[SolutionController::class,'store'])->middleware('auth:sanctum');
Route::get('/solution/show/{id}',[SolutionController::class,'showForPuzzle']);
Route::post('/answer',[AnswerController::class,'store'])->middleware('auth:sanctum');
Route::get('/answers',[AnswerController::class,'showMine'])->middleware('auth:sanctum');