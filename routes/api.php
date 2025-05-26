<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RequestStudentController;
use App\Http\Controllers\RequestTypeController;
use App\Http\Controllers\StudentController;
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


Route::get("/get", [TemplateController::class, 'index']);
Route::get("/get/{id}", [TemplateController::class, 'show']);
Route::post("/post", [TemplateController::class, 'store']);

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

Route::get('/request-students', [RequestStudentController::class, 'index']);
Route::post('/request-students', [RequestStudentController::class, 'store']);
Route::get('/request-students/{id}', [RequestStudentController::class, 'show']);
Route::put('/request-students/{id}', [RequestStudentController::class, 'update']);
Route::delete('/request-students/{id}', [RequestStudentController::class, 'destroy']);
Route::get('/request-students/student/{student_id}', [RequestStudentController::class, 'showByStudentId']);



Route::prefix('request-students')->group(function () {
    Route::get('/', [RequestStudentController::class, 'index']);
    Route::post('/', [RequestStudentController::class, 'store']);
    Route::get('/{id}', [RequestStudentController::class, 'show']);
    Route::put('/{id}', [RequestStudentController::class, 'update']);
    Route::delete('/{id}', [RequestStudentController::class, 'destroy']);

    // Search
    Route::get('/search/by-id', [RequestStudentController::class, 'searchByID']);
    Route::get('/search/by-student-name', [RequestStudentController::class, 'searchByStudentName']);
    Route::get('/student/{student_id}', [RequestStudentController::class, 'showByStudentId']);
    Route::get('/request-type/{request_type_id}', [RequestStudentController::class, 'showByRequestTypeId']);
});
Route::prefix('students')->group(function () {
    Route::get('/', [StudentController::class, 'index']);
    Route::post('/', [StudentController::class, 'store']);
    Route::get('/{id}', [StudentController::class, 'show']);
    Route::put('/{id}', [StudentController::class, 'update']);
    Route::delete('/{id}', [StudentController::class, 'destroy']);
});

Route::prefix('request-types')->group(function () {
    Route::get('/', [RequestTypeController::class, 'index']);
    Route::post('/', [RequestTypeController::class, 'store']);
    Route::get('/{id}', [RequestTypeController::class, 'show']);
    Route::put('/{id}', [RequestTypeController::class, 'update']);
    Route::delete('/{id}', [RequestTypeController::class, 'destroy']);
    Route::get('/search/keyword', [RequestTypeController::class, 'search']);
});