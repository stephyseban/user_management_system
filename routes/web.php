<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerificationController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });


Auth::routes(['verify' => true]);

Route::get('/', [LoginController::class, 'showLoginForm'])->name('home');
Route::get('/email/verify',  [VerificationController::class,'show'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}',  [VerificationController::class,'verify'])->name('verification.verify')->middleware(['signed']);
Route::post('/email/resend', [VerificationController::class,'resend'] )->name('verification.resend');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::group(['middleware' => ['admin']], function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('user', [AdminController::class, 'userindex'])->name('user.list');
        Route::get('user-add', [AdminController::class, 'userAdd'])->name('user.add');
        Route::post('user-store', [AdminController::class, 'userStore']);
        Route::post('user-delete/{id}', [AdminController::class, 'destroy'])->name('user.delete');
        Route::get('user-edit/{id}', [AdminController::class, 'userEdit']);
        Route::post('user-update/{id}', [AdminController::class, 'userUpdate'])->name('user.update');
    });

    Route::group(['prefix' => 'user','middleware' => 'verified'], function () {
        Route::get('/dashboard', [UserController::class, 'userDashboard'])->name('user.dashboard');
        Route::get('list', [UserController::class, 'userindex'])->name('user.users.list');
        Route::get('profile-edit', [UserController::class, 'profileEdit'])->name('profile.edit');
        Route::post('profile-update', [UserController::class, 'profileUpdate'])->name('profile.update');
    });
});
