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
    Route::get('user', [AdminController::class, 'user']);
    Route::get('user-add', [AdminController::class, 'userAdd'])->name('user.add');
    Route::group(['prefix' => 'user'], function () {
        Route::get('/dashboard', [UserController::class, 'userDashboard']);
    });
   
});
