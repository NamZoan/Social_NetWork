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
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ConversationController;
use App\Models\Friendship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

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
        Route::get('/load-more-posts', [ProfileController::class, 'loadMore'])->name('profile.load-more-posts');
    });
    //post
    Route::post('/posts', [PostController::class, 'store']);
    Route::post('/posts/{id}', [PostController::class, 'destroy']);
    Route::post('/posts/{post}/media/delete', [PostController::class, 'deleteMedia'])->name('posts.media.delete');
    Route::post('/posts/{post}/privacy', [PostController::class, 'updatePrivacy'])->name('posts.privacy.update');
    Route::post('/posts/{post}/update', [PostController::class, 'update'])->name('posts.update');

    //reaction
    Route::post('/posts/reaction/{postId}', [PostController::class, 'likePost']);
    Route::get('/posts/check-reaction/{postId}', [PostController::class, 'checkReaction']);
    Route::post('/posts/remove-reaction/{postId}', [PostController::class, 'removeReaction']);
    Route::get('/posts/total-reaction/{postId}', [PostController::class, 'totalReaction']);

    //comment
    Route::get('/comments/{post}', [CommentController::class, 'index'])->name('comments.index');
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::get('/posts/{post}/comments-count', [PostController::class, 'getCommentsCount'])->name('posts.comments-count');

    //friendship
    Route::get('/friendship-status/{username}', [FriendshipController::class, 'checkFriendshipStatus']);
    Route::post('/send-friend-request', [FriendshipController::class, 'sendFriendRequest']);
    Route::post('/accept-friend-request', [FriendshipController::class, 'acceptFriendRequest']);
    Route::post('/unfriend', [FriendshipController::class, 'unfriend']);

    //group
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::post('/groups/{group}/join', [GroupController::class, 'join'])->name('groups.join');
    Route::post('/groups/{group}/leave', [GroupController::class, 'leave'])->name('groups.leave');

    // Group join request routes
    Route::post('/groups/{group}/reject-request/{user}', [GroupController::class, 'rejectJoinRequest'])->name('groups.reject-request');

    Route::get('/groups/{group}/posts', [GroupController::class, 'getPosts'])->name('groups.posts');
    Route::get('/groups/{group}/load-more', [GroupController::class, 'loadMorePosts'])->name('groups.load-more');
    Route::get('/groups/{group}/load-more-posts', [GroupController::class, 'loadMorePosts'])->name('groups.load-more-posts');

    // Group membership routes
    Route::get('/groups/{group}/pending-requests', [GroupController::class, 'showPendingRequests'])->name('groups.pending-requests');
    Route::post('/groups/{group}/members/{member}/approve', [GroupController::class, 'approveMember'])->name('groups.members.approve');
    Route::post('/groups/{group}/members/{member}/reject', [GroupController::class, 'rejectMember'])->name('groups.members.reject');

    // Group join request routes
    Route::post('/groups/{group}/reject-request/{user}', [GroupController::class, 'rejectJoinRequest'])->name('groups.reject-request');

    // Group pending posts routes
    Route::get('/groups/{group}/pending-posts', [GroupController::class, 'showPendingPosts'])->name('groups.pending-posts');
    Route::post('/groups/{group}/posts/{post}/approve', [GroupController::class, 'approvePost'])->name('groups.posts.approve');
    Route::post('/groups/{group}/posts/{post}/reject', [GroupController::class, 'rejectPost'])->name('groups.posts.reject');

    Route::get('message/getFriends', [FriendshipController::class, 'getFriends']);
    Route::post('/messages/send', [MessageController::class, 'sendMessage'])->name('messages.send');
    Route::post('/messages/createGroup', [MessageController::class, 'createGroup'])->name('messages.createGroup');

    // Message routes
    Route::get('/messages/{conversationId}', [MessageController::class, 'getMessages'])->name('messages.get');
});