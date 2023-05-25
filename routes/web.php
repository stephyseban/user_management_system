<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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


Auth::routes();

Route::get('/', [LoginController::class, 'showLoginForm'])->name('home');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');


    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('user', [AdminController::class, 'userindex'])->name('user.list');
    Route::get('user-add', [AdminController::class, 'userAdd'])->name('user.add');
    Route::post('user-store', [AdminController::class, 'userStore']);
    Route::post('user-delete/{id}', [AdminController::class, 'destroy'])->name('user.delete');
    Route::get('user-edit/{id}', [AdminController::class, 'userEdit']);
    Route::post('user-update/{id}', [AdminController::class, 'userUpdate'])->name('user.update');
    Route::group(['prefix' => 'user'], function () {
        Route::get('/dashboard', [UserController::class, 'userDashboard']);
    });
});
