<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController;

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


Route::get("/get",[TemplateController::class,'index']);
Route::get("/get/{id}",[TemplateController::class,'show']);
Route::post("/post",[TemplateController::class,'store']);

Route::middleware('auth:sanctum')->group(function () {
    // Lấy thông tin user đã đăng nhập
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Ví dụ API lấy profile user
    Route::get('/profile', [UserProfileController::class, 'show']);
    
    // Đăng xuất
    Route::post('/logout', [AuthController::class, 'logout']);
});

// API đăng nhập, đăng ký không cần auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/test-token', function () {
    $user = \App\Models\User::first();
    $token = $user->createToken('api-token')->plainTextToken;
    return response()->json(['token' => $token]);
});