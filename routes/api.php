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
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('activated');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/verifyemail',[AuthController::class, 'userActivation']);
Route::post('/sendverificationemail',[AuthController::class, 'sendEmailVerification']);
Route::post('/resetpasswordviacurrent',[AuthController::class, 'resetPasswordViaCurrnet'])->middleware('auth:sanctum');
Route::post('/resetpasswordviaemail',[AuthController::class, 'resetPasswordViaEmail']);
Route::post('/sendresetemail',[AuthController::class, 'sendResetEmail']);
Route::post('/updateuser',[AuthController::class,'update'])->middleware('auth:sanctum')->middleware('activated');
Route::delete('/deleteuser',[AuthController::class,'destroy'])->middleware('auth:sanctum')->middleware('activated');
Route::get('/puzzle',[PuzzleController::class,'index'])->middleware('auth:sanctum')->middleware('activated');
Route::get('/puzzle/{id}',[PuzzleController::class,'show'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/puzzle',[PuzzleController::class,'store'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/puzzle/{id}',[PuzzleController::class,'update'])->middleware('auth:sanctum')->middleware('activated');
Route::delete('/puzzle/{id}',[PuzzleController::class,'destroy'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/community',[CommunityController::class,'store'])->middleware('auth:sanctum')->middleware('activated')->middleware('activated');
Route::post('/communitypuzzle',[PuzzleController::class, 'storeToCommunity'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/joinrequest/{id}',[CommunityController::class,'join'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/leave/{id}',[CommunityController::class,'leave'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/adminresponse/{id}',[CommunityController::class,'adminResponse'])->middleware('auth:sanctum')->middleware('activated');
Route::delete('/community/{id}',[CommunityController::class,'destroy'])->middleware('auth:sanctum')->middleware('activated');
Route::get('/joinrequests/{id}',[CommunityController::class, 'getJoinRequests'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/community/{id}',[CommunityController::class,'update'])->middleware('auth:sanctum')->middleware('activated');
Route::get('/communitypuzzles/{id}',[PuzzleController::class,'getCommunityPuzzles'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/vote',[PuzzleController::class, 'arrowMoidify'])->middleware('auth:sanctum')->middleware(['throttle:5,1'])->middleware('activated');
Route::get('/puzzlevotes/{id}',[PuzzleController::class,'getVotes'])->middleware('activated');
Route::post('/puzzleapproval/{id}',[PuzzleController::class, 'handlePendingPuzzles'])->middleware('auth:sanctum')->middleware('activated');
Route::get('/communitypendingpuzzles/{id}',[PuzzleController::class, 'getCommunityPendingPuzzles'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/answer',[AnswerController::class,'store'])->middleware('auth:sanctum')->middleware('activated');
Route::get('/answers',[AnswerController::class,'showMine'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/follow/{id}',[FollowController::class, 'follow'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/unfollow/{id}',[FollowController::class,'unfollow'])->middleware('auth:sanctum')->middleware('activated');
Route::get('/myfollowers',[FollowController::class,'myFollowers'])->middleware('auth:sanctum')->middleware('activated');
Route::get('/myfollowings',[FollowController::class,'myFollowings'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/star/{id}',[CategoryController::class,'star'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/unstar/{id}',[CategoryController::class,'unstar'])->middleware('auth:sanctum')->middleware('activated');
Route::get('/starcategories',[CategoryController::class, 'myStaredCategories'])->middleware('auth:sanctum')->middleware('activated');
Route::post('/category',[CategoryController::class,'store'])->middleware('auth:sanctum')->middleware('activated');
Route::get('/categories',[CategoryController::class,'index'])->middleware('activated');
Route::get('/category/{id}',[CategoryController::class,'show'])->middleware('activated');
Route::post('/search',[SearchController::class,'search'])->middleware('auth:sanctum')->middleware('activated');
Route::get('/getuser/{id}',[AuthController::class, 'getAnotherUser'])->middleware('auth:sanctum')->middleware('activated');
Route::get('/getme',[AuthController::class, 'getMe'])->middleware('auth:sanctum')->middleware('activated');