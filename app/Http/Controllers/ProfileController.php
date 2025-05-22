<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ProfileRepositoryInterface;

class ProfileController extends Controller
{
    protected $profileRepo;

    public function __construct(ProfileRepositoryInterface $profileRepo)
    {
        $this->profileRepo = $profileRepo;
    }

    public function edit()
    {
        return Inertia::render('Profile/Edit');
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:10',
            'birthday' => 'required|date',
        ]);

        $user = Auth::user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->birthday = $request->birthday;
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Cập nhật thông tin thành công!');
    }

    public function index($username)
    {
        $user = $this->profileRepo->getUserByUsername($username);
        $currentUser = Auth::user();

        $posts = $this->profileRepo->getUserPosts($user, $currentUser);

        return Inertia::render('Profile/ListPost', [
            'user' => $user,
            'activeTab' => 'listpost',
            'posts' => $posts,
            'isOwnProfile' => $currentUser && $currentUser->id === $user->id,
            'isFriend' => $currentUser ? $user->isFriendWith($currentUser) : false
        ]);
    }

    public function loadMore($username, Request $request)
    {
        try {
            $user = $this->profileRepo->getUserByUsername($username);
            $currentUser = Auth::user();
            $page = $request->input('page', 1);

            $posts = $this->profileRepo->getUserPosts($user, $currentUser, $page);

            return response()->json([
                'data' => $posts->items(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'next_page_url' => $posts->nextPageUrl(),
                'total' => $posts->total(),
                'per_page' => $posts->perPage()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Không thể tải bài viết',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function friend($username)
    {
        $user = $this->profileRepo->getUserByUsername($username);

        if (!$user) {
            abort(404, 'User not found');
        }

        $currentUser = Auth::user();
        $friends = $this->profileRepo->getUserFriends($user->id);

        foreach ($friends as $friend) {
            $friend->mutualFriendsCount = $this->profileRepo->countMutualFriends($currentUser->id, $friend->id);
        }

        return Inertia::render('Profile/Friend', [
            'user' => $user,
            'activeTab' => 'friend',
            'friends' => $friends,
        ]);
    }

    public function group($username)
    {
        $user = $this->profileRepo->getUserByUsername($username);

        return Inertia::render('Profile/Group', [
            'user' => $user,
            'activeTab' => 'group'
        ]);
    }
}
