<?php

use App\Http\Controllers\AcademicRequestController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\QuizzController;
use App\Livewire\DynamicForm;
use App\Models\quizz_question;
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

// Route::get('/', function () {
//     return view('welcome');
// });


// Route::get('/login', [AcademicRequestController::class, 'login']);
// Route::post('/login', [AcademicRequestController::class, 'loginProcess']);

// Route::get('/form/{id}', [FormController::class, 'showForm']);
// Route::post('/field/update-order', [FormController::class, 'updateOrder']);



// Route::prefix('admin')->group(function () {
//     Route::get('/', [FormController::class, 'index'])->name('customFieldForm');
//     Route::post('/show', [FormController::class, 'store']);
// });




// Route::middleware([
//     'auth:sanctum',
//     config('jetstream.auth_session'),
//     'verified',
// ])->group(function () {
//     Route::get('/dashboard', function () {
//         return view('dashboard');
//     })->name('dashboard');
// });
