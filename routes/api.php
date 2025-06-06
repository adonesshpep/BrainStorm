<?php

use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\PuzzleController;
use App\Http\Controllers\Api\SolutionController;
use App\Http\Controllers\api\CommunityController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\DeepseekController;
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
Route::post('/verifyemail',[AuthController::class, 'userActivation'])->middleware('auth:sanctum')->middleware(['throttle:verification']);
Route::post('/sendverificationemail',[AuthController::class, 'sendEmailVerification'])->middleware('auth:sanctum')->middleware(['throttle:verification']);
Route::post('/resetpassword/{token?}',[AuthController::class, 'resetPassword']);
Route::post('/sendresetemail',[AuthController::class, 'sendResetEmail']);
Route::apiResource('puzzle', PuzzleController::class)->middleware('auth:sanctum')->middleware(['throttle:verification']);
Route::post('/community',[CommunityController::class,'store'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/communitypuzzle',[PuzzleController::class, 'storeToCommunity'])->middleware('auth:sanctum');
Route::post('/joinrequest/{id}',[CommunityController::class,'join'])->middleware('auth:sanctum');
Route::get('/communitypuzzles/{id}',[PuzzleController::class,'getCommunityPuzzles'])->middleware('auth:sanctum');
Route::post('/vote',[PuzzleController::class, 'arrowMoidify'])->middleware('auth:sanctum')->middleware(['throttle:5,1']);
Route::get('/puzzlevotes/{id}',[PuzzleController::class,'getVotes']);
// Route::apiResource('solution',SolutionController::class);
Route::post('/solution',[SolutionController::class,'store'])->middleware('auth:sanctum');
Route::get('/solution/show/{id}',[SolutionController::class,'showForPuzzle']);
Route::post('/answer',[AnswerController::class,'store'])->middleware('auth:sanctum');
Route::get('/answers',[AnswerController::class,'showMine'])->middleware('auth:sanctum');
Route::post('/follow/{id}',[FollowController::class, 'follow'])->middleware('auth:sanctum');
Route::post('/unfollow/{id}',[FollowController::class,'unfollow'])->middleware('auth:sanctum');
Route::get('/myfollowers',[FollowController::class,'myFollowers'])->middleware('auth:sanctum');
Route::get('/myfollowings',[FollowController::class,'myFollowings'])->middleware('auth:sanctum');
Route::post('/star/{id}',[CategoryController::class,'star'])->middleware('auth:sanctum');
Route::post('/unstar/{id}',[CategoryController::class,'unstar'])->middleware('auth:sanctum');
Route::get('/starcategories',[CategoryController::class, 'myStaredCategories'])->middleware('auth:sanctum');
Route::post('/category',[CategoryController::class,'store'])->middleware('auth:sanctum');
Route::post('/search',[SearchController::class,'search'])->middleware('auth:sanctum');