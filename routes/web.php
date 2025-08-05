<?php

use App\Http\Controllers\AcademicRequestController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\QuizzController;
use App\Livewire\DynamicForm;
use App\Models\quizz_question;
use Illuminate\Support\Facades\Route;

use Google\Client;
use Google\Service\Drive;

Route::get('/oauth', function () {
    $client = new Client();
    $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
    $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
    $client->setRedirectUri(env('APP_URL') . '/oauth');
    $client->addScope(Drive::DRIVE); // Hoặc các scope khác bạn đã chọn
    $client->setAccessType('offline'); // <-- Đảm bảo dòng này tồn tại
    $client->setPrompt('select_account consent'); // <-- Đảm bảo dòng này tồn tại

    if (!request()->has('code')) {
        $authUrl = $client->createAuthUrl();
        return redirect($authUrl);
    } else {
        $accessToken = $client->fetchAccessTokenWithAuthCode(request('code'));

        if (isset($accessToken['refresh_token'])) {
            dd($accessToken['refresh_token']);
        } else {
            dd('Refresh Token không được trả về. Vui lòng đảm bảo bạn đã:
                1. Đặt setAccessType("offline") cho client.
                2. Đặt setPrompt("select_account consent") để buộc người dùng cấp lại quyền.
                3. Xóa quyền đã cấp cho ứng dụng trong tài khoản Google của bạn (nếu đã cấp trước đó)
                   tại: myaccount.google.com/connections');
        }
    }
});

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
