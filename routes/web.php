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
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MetaBusinessController;
use App\Http\Controllers\UserPageController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\SignalController;
Route::get('/dang-nhap', [UserController::class, 'login'])->name('login');
Route::post('/dang-nhap', [UserController::class, 'authenticate']);
Route::get('/dang-ky', [UserController::class, 'register'])->name('register');
Route::post('/dang-ky', [UserController::class, 'store']);

// Route::get('/verify-email', )->middleware('auth')->name('verification.notice');
// Route::post('/verify-email', [VerifyEmailController::class, 'verify'])->name('verify.submit');

Route::middleware('auth')->group(function () {
    // Trang nhắc xác thực
    Route::get('/email/verify', [VerifyEmailController::class, 'notice'])
        ->name('verification.notice');

    // Link xác thực (ký số)
    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1', 'auth'])
        ->name('verification.verify');

    // Gửi lại email xác thực
    Route::post('/email/verification-notification', [VerifyEmailController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});



Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [PageController::class, 'index'])->name('home');
    Route::get('/posts/load-more', [PageController::class, 'loadMore'])->name('posts.load-more');
    Route::get('/cai-dat', [SettingController::class, 'account'])->name('account');
    Route::post('/dang-xuat', [UserController::class, 'logout'])->name('logout');
    Route::get('/messages', [MessageController::class, 'index'])->name('message');
    Route::get('/groups', [GroupController::class, 'index'])->name('group');
    Route::get('/friend-requests', [FriendshipController::class, 'index']);

    //search
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    //notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::get('/message-notifications', [NotificationController::class, 'messageNotifications'])->name('message.notifications');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::get('/thong-bao', [NotificationController::class, 'indexNotification'])->name('notifications.index');
    //update user
    Route::post('/user/update', [UserController::class, 'update'])->name('user.update');
    Route::post('/user/update-password', [UserController::class, 'updatePassword'])->name('user.update-password');
    Route::get('doi-mat-khau', [SettingController::class, 'changePassword'])->name('user.change-password');
    Route::post('/user/update-avatar', [UserController::class, 'updateAvatar'])->name('user.update-avatar');

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
    Route::get('/comments/{comment}/replies', [CommentController::class, 'getReplies'])->name('comments.replies');
    Route::get('/posts/{post}/comments-count', [PostController::class, 'getCommentsCount'])->name('posts.comments-count');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::put('/comments/{comment}/toggle-visibility', [CommentController::class, 'toggleVisibility'])->name('comments.toggle-visibility');

    //friendship
    Route::get('/friendship-status/{username}', [FriendshipController::class, 'checkFriendshipStatus']);
    Route::post('/send-friend-request', [FriendshipController::class, 'sendFriendRequest']);
    Route::post('/accept-friend-request', [FriendshipController::class, 'acceptFriendRequest']);
    Route::post('/unfriend', [FriendshipController::class, 'unfriend']);

    //group
    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::post('/groups/{group}/join', [GroupController::class, 'join'])->name('groups.join');
    Route::post('/groups/{group}/leave', [GroupController::class, 'leave'])->name('groups.leave');
    Route::get('/groups/{group}/edit', [GroupController::class, 'edit'])->name('groups.edit');
    Route::post('/groups/{group}/update', [GroupController::class, 'update'])->name('groups.update');
    Route::get('/groups/{group}/my-posts', [GroupController::class, 'myPosts'])->name('groups.my-posts');

    // Group join request routes
    Route::post('/groups/{group}/reject-request/{user}', [GroupController::class, 'rejectJoinRequest'])->name('groups.reject-request');

    Route::get('/groups/{group}/posts', [GroupController::class, 'getPosts'])->name('groups.posts');
    Route::get('/groups/{group}/load-more', [GroupController::class, 'loadMorePosts'])->name('groups.load-more');
    Route::get('/groups/{group}/load-more-posts', [GroupController::class, 'loadMorePosts'])->name('groups.load-more-posts');

    // Group membership routes
    Route::get('/groups/{group}/pending-requests', [GroupController::class, 'showPendingRequests'])->name('groups.pending-requests');
    Route::post('/groups/{group}/members/{member}/approve', [GroupController::class, 'approveMember'])->name('groups.members.approve');
    Route::post('/groups/{group}/members/{member}/reject', [GroupController::class, 'rejectMember'])->name('groups.members.reject');
    Route::get('/groups/{group}/members', [GroupController::class, 'members'])->name('groups.members');
    // Xóa thành viên khỏi nhóm
    Route::post('/groups/{group}/members/{user}', [GroupController::class, 'removeMember'])->name('groups.members.remove');
    // Group join request routes
    Route::post('/groups/{group}/reject-request/{user}', [GroupController::class, 'rejectJoinRequest'])->name('groups.reject-request');
    Route::post('/groups/{group}/members/{user}/make-admin', [GroupController::class, 'makeAdmin'])->name('groups.members.makeAdmin');
    // Group pending posts routes
    Route::get('/groups/{group}/pending-posts', [GroupController::class, 'showPendingPosts'])->name('groups.pending-posts');
    Route::post('/groups/{group}/posts/{post}/approve', [GroupController::class, 'approvePost'])->name('groups.posts.approve');
    Route::post('/groups/{group}/posts/{post}/reject', [GroupController::class, 'rejectPost'])->name('groups.posts.reject');



    // Message routes

    Route::get('message/getFriends', [FriendshipController::class, 'getFriends']);
    Route::post('/messages/send', [MessageController::class, 'sendMessage'])->middleware('validate.image')->name('messages.send');
    Route::post('/messages/createGroup', [MessageController::class, 'createGroup'])->name('messages.createGroup');

    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::get('messages/{conversation}/data', [MessageController::class, 'getMessages'])->name('messages.conversation.data');
    Route::get('/messages/{conversation}/load-messages', [MessageController::class, 'getMessagesPaginated'])->name('messages.load');

    Route::get('/conversations/{conversation}/members', [MessageController::class, 'getConversationMembers']);
    Route::post('/conversations/{conversation}/add-members', [MessageController::class, 'addMembersToConversation']);
    Route::post('/conversations/{conversation}/leave', [MessageController::class, 'leaveConversation']);
    Route::delete('/conversations/{conversation}/members/{user}', [MessageController::class, 'removeMemberFromConversation']);
    Route::post('/conversations/{conversation}/delete', [MessageController::class, 'deleteConversation'])->name('conversations.delete');
    Route::post('/conversations/{conversation}/update', [MessageController::class, 'updateConversation']);
    Route::delete('/messages/{message}', [MessageController::class, 'deleteMessage']);

    //notifications

    // Admin Routes - Role Management
    Route::prefix('admin')->middleware(['role:administrator'])->group(function () {
        Route::resource('roles', RoleController::class)->names([
            'index' => 'admin.roles.index',
            'create' => 'admin.roles.create',
            'store' => 'admin.roles.store',
            'show' => 'admin.roles.show',
            'edit' => 'admin.roles.edit',
            'update' => 'admin.roles.update',
            'destroy' => 'admin.roles.destroy',
        ]);

        Route::post('/roles/{role}/users/{user}/assign', [RoleController::class, 'assignRole'])->name('admin.roles.assign');
        Route::delete('/roles/{role}/users/{user}/remove', [RoleController::class, 'removeRole'])->name('admin.roles.remove');
        Route::get('/roles/{role}/users', [RoleController::class, 'users'])->name('admin.roles.users');
    });

    Route::prefix('calls')->name('calls.')->group(function () {
        Route::get('/{id}',     [CallController::class, 'show'])->name('show');
        Route::post('/invite',  [CallController::class, 'invite'])->name('invite');
        Route::post('/accept',  [CallController::class, 'accept'])->name('accept');
        Route::post('/reject',  [CallController::class, 'reject'])->name('reject');   // nên có
        Route::post('/end',     [CallController::class, 'end'])->name('end');

        // WebRTC signaling
        Route::post('/offer',   [SignalController::class, 'offer'])->name('offer');
        Route::post('/answer',  [SignalController::class, 'answer'])->name('answer');
        Route::post('/ice',     [SignalController::class, 'ice'])->name('ice');
    });

    // Meta Business Suite Routes
    Route::prefix('meta-business')->middleware(['role:administrator,advertiser'])->group(function () {
        Route::get('/', [MetaBusinessController::class, 'index'])->name('meta.index');
        Route::get('/connect', [MetaBusinessController::class, 'create'])->name('meta.connect');
        Route::get('/callback', [MetaBusinessController::class, 'callback'])->name('meta.callback');
        Route::get('/{account}', [MetaBusinessController::class, 'show'])->name('meta.show');
        Route::post('/{account}/sync', [MetaBusinessController::class, 'sync'])->name('meta.sync');
        Route::post('/{account}/disconnect', [MetaBusinessController::class, 'disconnect'])->name('meta.disconnect');
        Route::get('/pages/{page}/insights', [MetaBusinessController::class, 'getPageInsights'])->name('meta.pages.insights');
    });

    // Pages Routes
    Route::prefix('pages')->group(function () {
        Route::get('/create', [UserPageController::class, 'create'])->name('pages.create');
        Route::post('/', [UserPageController::class, 'store'])->name('pages.store');
        Route::get('/{identifier}', [UserPageController::class, 'show'])->name('pages.show');
        Route::post('/{page}/update', [UserPageController::class, 'update'])->name('pages.update');
        Route::post('/{page}/follow', [UserPageController::class, 'toggleFollow'])->name('pages.follow');
        Route::get('/{page}/insights', [UserPageController::class, 'insights'])->name('pages.insights');
        Route::get('/{page}/posts', [UserPageController::class, 'getPosts'])->name('pages.posts');
    });

});
