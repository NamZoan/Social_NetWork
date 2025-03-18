<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FriendshipController;


Route::get('/dang-nhap', [UserController::class, 'login'])->name('login');
Route::post('/dang-nhap', [UserController::class, 'authenticate']);
Route::get('/dang-ky', [UserController::class, 'register'])->name('register');
Route::post('/dang-ky', [UserController::class, 'store']);


Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::get('/', [PageController::class, 'index'])->name('home');
    Route::get('/cai-dat', [SettingController::class, 'account'])->name('account');
    Route::post('/dang-xuat', [UserController::class, 'logout'])->name('logout');
    Route::get('/messages', [MessageController::class, 'index'])->name('message');
    Route::get('/groups', [GroupController::class, 'index'])->name('group');
    Route::get('{user}', [ProfileController::class, 'index'])->name('profile');
    Route::get('/friendship-status/{username}', [FriendshipController::class, 'checkFriendshipStatus']);
    Route::post('/send-friend-request', [FriendshipController::class, 'sendFriendRequest']);
    Route::post('/accept-friend-request', [FriendshipController::class, 'acceptFriendRequest']);
    Route::post('/unfriend', [FriendshipController::class, 'unfriend']);

});


