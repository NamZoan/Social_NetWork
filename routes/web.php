<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/dang-nhap', [UserController::class, 'login'])->name('login');
Route::post('/dang-nhap', [UserController::class, 'authenticate']);
Route::get('/dang-ky', [UserController::class, 'register'])->name('register');
Route::post('/dang-ky', [UserController::class, 'store']);


Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::get('/',[PageController::class, 'index'])->name('home');
    Route::get('/profile',[ProfileController::class, 'index'])->name('profile');
    Route::get('/cai-dat',[SettingController::class, 'account'])->name('account');
    Route::post('/dang-xuat', [UserController::class, 'logout'])->name('logout');
    Route::get('/messages',[MessageController::class,'index'])->name('message');
});


