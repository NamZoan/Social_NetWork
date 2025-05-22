<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Group;
use App\Models\Post;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');
        $type = $request->input('type', 'people');

        $users = $groups = $posts = collect();

        if ($type === 'people') {
            $authUser = auth()->user();
            $authFriendIds = $authUser ? $authUser->friendIds() : [];

            $usersQuery = User::where(function($query) use ($q) {
                $query->where('name', 'like', "%$q%")
                      ->orWhere('username', 'like', "%$q%");
            });

            if ($authUser) {
                $usersQuery->where('id', '!=', $authUser->id);
            }

            $users = $usersQuery
                ->limit(30)
                ->get()
                ->map(function ($user) use ($authFriendIds) {
                    $userFriendIds = method_exists($user, 'friendIds') ? $user->friendIds() : [];
                    $mutual = count(array_intersect($authFriendIds, $userFriendIds));
                    $user->mutual_friends_count = $mutual;
                    $user->is_friend = in_array($user->id, $authFriendIds); // Thêm dòng này
                    return $user;
                });
        }
        if ($type === 'group') {
            $groups = Group::where('name', 'like', "%$q%")
                ->limit(30)->get()
                ->map(function ($group) {
                    $group->is_joined = auth()->check() ? $group->members()->where('user_id', auth()->id())->wherePivot('membership_status', 'active')->exists() : false;
                    return $group;
                });
        }
        if ($type === 'post') {
            $posts = Post::with('user')
                ->where('content', 'like', "%$q%")
                ->limit(30)->get();
        }



        return Inertia::render('Search/Index', [
            'q' => $q,
            'type' => $type,
            'users' => $users,
            'groups' => $groups,
            'posts' => $posts,
        ]);
    }
}
