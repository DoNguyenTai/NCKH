<?php

<<<<<<< HEAD
use App\Http\Controllers\Api\FormCustomController;
use App\Http\Controllers\FormController;
=======
use App\Http\Controllers\TemplateController;
>>>>>>> b5920b2dd136f095db7e7f3f241546c9cd350980
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

Route::get('/get-field-form/{id}', [FormController::class, 'showFieldForm']);

Route::prefix('admin')->group(function () {
    Route::post('/create-form', [FormController::class, 'storeFormModel'])->name('storeFormModel');
    Route::get('/show-form/{id}', [FormController::class, 'showFormModel'])->name('showFormModel');
});
Route::get('/type-of-forms', [FormCustomController::class, 'getTypeOfForms']);
Route::get('/forms/{formId}', [FormCustomController::class, 'getFormWithFields']);

Route::post('/forms/{formId}', [FormCustomController::class, 'storeField']);
Route::put('/forms/{formId}/fields/{fieldId}', [FormCustomController::class, 'updateField']);
Route::delete('/forms/{formId}/fields/{fieldId}', [FormCustomController::class, 'deleteField']);
Route::post('/forms/{formId}/fields/reorder', [FormCustomController::class, 'reorder']);

Route::get("/get",[TemplateController::class,'index']);
Route::get("/get/{id}",[TemplateController::class,'show']);
Route::post("/post",[TemplateController::class,'store']);
