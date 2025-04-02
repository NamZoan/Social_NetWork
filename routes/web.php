<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\PostController;

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
    Route::prefix('{username}')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile');
        Route::get('/friend', [ProfileController::class, 'friend'])->name('profile.friend');
        Route::get('/group', [ProfileController::class, 'group'])->name('profile.group');
        Route::get('/media', [ProfileController::class, 'media'])->name('profile.media');
        Route::get('/load-more', [ProfileController::class, 'loadMore'])->name('posts.load-more');
    });
    Route::post('/posts', [PostController::class, 'store']);

    Route::post('/posts/reaction/{postId}', [PostController::class, 'likePost']);
    Route::get('/posts/check-reaction/{postId}', [PostController::class, 'checkReaction']);
    Route::post('/posts/remove-reaction/{postId}', [PostController::class, 'removeReaction']);

    Route::get('/friendship-status/{username}', [FriendshipController::class, 'checkFriendshipStatus']);
    Route::post('/send-friend-request', [FriendshipController::class, 'sendFriendRequest']);
    Route::post('/accept-friend-request', [FriendshipController::class, 'acceptFriendRequest']);
    Route::post('/unfriend', [FriendshipController::class, 'unfriend']);
});


