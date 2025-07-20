<?php

use App\Http\Controllers\Api\FormCustomController;
use App\Http\Controllers\Api\MailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocxController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\RequestStudentController;
use App\Http\Controllers\RequestTypeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TypeOfFormController;
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
Route::post('/send-email', [MailController::class, 'send']);
Route::post('/send-email-student', [MailController::class, 'sendMultipleByStudentCode']);

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


Route::prefix('request-types')->group(function () {
    Route::get('/', [RequestTypeController::class, 'index']);
    Route::post('/', [RequestTypeController::class, 'store']);
    Route::get('/{id}', [RequestTypeController::class, 'show']);
    Route::put('/{id}', [RequestTypeController::class, 'update']);
    Route::delete('/{id}', [RequestTypeController::class, 'destroy']);
    Route::get('/search/keyword', [RequestTypeController::class, 'search']);
});


Route::post('/upload-docx', [DocxController::class, 'upload']);
Route::post('/upload-docx1', [DocxController::class, 'uploadDocx']);
Route::post('/export-docx', [DocxController::class, 'export']);
// Route::get('/docx-url/{filename}', [DocxController::class, 'getFile']);
Route::get('/docx-html/{filename}', [DocxController::class, 'convertStoredDocxToHtml']);
Route::get('/docx-html', [DocxController::class, 'convertStoredDocxToHtml']);
Route::get('/test-convert', [DocxController::class, 'convertDocxStoredAndResend']);

Route::post('/onlyoffice/save-callback', [DocxController::class, 'handleSave']);

Route::get('/get-field-form/{id}', [FormController::class, 'showFieldForm']);

Route::prefix('admin')->group(function () {
    Route::post('/create-layout-form/{id}', [FormController::class, 'storeFormModel'])->name('storeFormModel');
    Route::get('/show-layout-form/{id}', [FormController::class, 'showFormModel'])->name('showFormModel');
});
Route::get('/forms', [FormCustomController::class, 'getTypeOfForms']);
Route::get('/forms/{formId}', [FormCustomController::class, 'getFormWithFields']);
Route::get('/form-value', [FormCustomController::class, 'getAllFormValue']);
Route::get('/form-value/{studentCode}', [FormCustomController::class, 'getAllFormValueByTemplate']);
Route::get('/form-values/{studentCode}/{folderId}/{createdAt?}', [FormCustomController::class, 'getAllFormValueByTemplateByFolderWithDate']);
Route::get('/form-value-folder/{studentCode}/{id}', [FormCustomController::class, 'getAllFormValueByTemplateByFolder']);

Route::get('/form-value-detail/{studentCode}/{formRequestId}', [FormCustomController::class, 'getFormValueDetail']);


Route::post('/submit-form/{formId}', [FormCustomController::class, 'submitForm']);
Route::get('/preview-form/{formRequestId}', [FormCustomController::class, 'previewForm']);
Route::post('/create-form', [FormController::class, 'storeForm']);
Route::delete('/forms/{id}', [FormController::class, 'deleteForm']);
Route::put('/forms/{id}', [FormController::class, 'updateForm']);
Route::put('/forms/create-layout/{id}', [FormController::class, 'updateForm']);
Route::post('/forms/dependency', [FormController::class, 'dependencyForm']);
Route::get('/forms/{id}/dependencies', [FormController::class, 'getDependencyForms']);


Route::get('/form-status', [FormController::class, 'statusForm']);




Route::post('/forms/{formId}', [FormCustomController::class, 'storeField']);
Route::put('/forms/{formId}/fields/{fieldId}', [FormCustomController::class, 'updateField']);
Route::delete('/forms/{formId}/fields/{fieldId}', [FormCustomController::class, 'deleteField']);
Route::post('/forms/{formId}/fields/reorder', [FormCustomController::class, 'reorder']);


Route::get('/preview-docx/{filename}', [DocxController::class, 'convertDocxToHtml']);
Route::get('/convert-docx-html-libre/{filename}', [DocxController::class, 'convertWithLibreOffice']);
Route::get('/convert-pandoc/{filename}', [DocxController::class, 'convertDocxWithPandoc']);
Route::get('/docx-to-html/{filename}', [DocxController::class, 'convertDocxToHtml1']);





Route::get('/google-drive/auth-url', [GoogleDriveController::class, 'redirectToGoogleAuth']);
Route::get('/google-drive/callback', [GoogleDriveController::class, 'handleGoogleCallback']);
Route::get('/google-drive/list', [GoogleDriveController::class, 'listFiles']);
Route::post('/google-drive/download-pdf', [GoogleDriveController::class, 'downloadPdf']);
Route::get('/google-drive/export-html', [GoogleDriveController::class, 'exportHtml']);
Route::get('/google-drive/export-pdf', [GoogleDriveController::class, 'exportPdfUrl']);

// Route::post('/google-drive/upload-docx', [GoogleDriveController::class, 'uploadDocxToDrive']);
Route::post('/google-drive/docx', [GoogleDriveController::class, 'generateDocx']);

Route::post('/google-drive/upload-docx', [GoogleDriveController::class, 'uploadDocxFromClient']);

Route::get('/google-drive/generate-upload/{studentCode}/{formRequestId}', [GoogleDriveController::class, 'generateThenUpload']);


Route::get('/scrape-element', [\App\Http\Controllers\ScrapeController::class, 'scrapeElement']);

Route::apiResource('student', StudentController::class);
Route::get('/student/search/{student_code}', [StudentController::class, 'searchByStudentCode']);


Route::apiResource('folder', FolderController::class);
Route::apiResource('type-of-forms', TypeOfFormController::class);
Route::get('type-of-forms/pdf/{id}', [TypeOfFormController::class,'getPdfUrl']);
Route::get('type-of-forms/word/{id}', [TypeOfFormController::class,'getWordUrl']);


Route::apiResource('notes', NoteController::class);
Route::get('notes/showParent/{folder_id}',[NoteController::class,'showIdFolder']);

Route::apiResource('request-students', RequestStudentController::class);
Route::post('/request-students/bulk-update-status', [RequestStudentController::class, 'bulkUpdateStatus']);
Route::get('/request-students/search/{student_code}', [RequestStudentController::class, 'searchByStudentCode']);
