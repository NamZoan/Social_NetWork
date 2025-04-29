<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Friendship;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
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
        $user = User::where('username', $username)->firstOrFail();
        $currentUser = Auth::user();

        $posts = $this->getUserPosts($user, $currentUser);

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
            $user = User::where('username', $username)->firstOrFail();
            $currentUser = Auth::user();
            $page = $request->input('page', 1);

            $posts = $this->getUserPosts($user, $currentUser, $page);

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

    /**
     * Lấy danh sách bài viết của người dùng với kiểm tra quyền truy cập
     */
    private function getUserPosts($user, $currentUser, $page = 1)
    {
        $posts = Post::where('user_id', $user->id)
            ->whereNull('group_id')
            ->where(function($query) use ($user, $currentUser) {
                // Người dùng luôn xem được bài viết của chính mình
                if ($currentUser && $currentUser->id === $user->id) {
                    return;
                }

                // Bài viết public
                $query->where('privacy_setting', Post::PRIVACY_PUBLIC);

                if ($currentUser) {
                    // Bài viết của bạn bè
                    $query->orWhere(function($q) use ($user, $currentUser) {
                        $q->where('privacy_setting', Post::PRIVACY_FRIENDS)
                            ->whereExists(function($subQuery) use ($user, $currentUser) {
                                $subQuery->select(DB::raw(1))
                                    ->from('friendships')
                                    ->where(function($q) use ($user, $currentUser) {
                                        $q->where(function($q) use ($user, $currentUser) {
                                            $q->where('user_id_1', $currentUser->id)
                                                ->where('user_id_2', $user->id);
                                        })->orWhere(function($q) use ($user, $currentUser) {
                                            $q->where('user_id_1', $user->id)
                                                ->where('user_id_2', $currentUser->id);
                                        });
                                    })
                                    ->where('status', 'accepted');
                            });
                    });
                }
            })
            ->with([
                'user',
                'media',
                'likes.user',
                'comments.user',
                'comments.replies.user',
                'originalPost.user'
            ])
            ->latest()
            ->paginate(2, ['*'], 'page', $page);

        // Chuyển đổi dữ liệu để Inertia có thể xử lý
        $posts->getCollection()->transform(function ($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'created_at' => $post->created_at->format('Y-m-d H:i:s'),
                'privacy_setting' => $post->privacy_setting,
                'user' => $post->user,
                'media' => $post->media,
                'likes' => $post->likes->map(function ($like) {
                    return [
                        'id' => $like->id,
                        'reaction_type' => $like->reaction_type,
                        'user' => $like->user
                    ];
                }),
                'comments' => $post->comments->whereNull('parent_comment_id')->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                        'user' => $comment->user,
                        'replies' => $comment->replies->map(function ($reply) {
                            return [
                                'id' => $reply->id,
                                'content' => $reply->content,
                                'created_at' => $reply->created_at->format('Y-m-d H:i:s'),
                                'user' => $reply->user
                            ];
                        })
                    ];
                }),
                'likes_count' => $post->likes->count(),
                'comments_count' => $post->comments->count(),
                'original_post' => $post->originalPost ? [
                    'id' => $post->originalPost->id,
                    'content' => $post->originalPost->content,
                    'user' => $post->originalPost->user
                ] : null
            ];
        });

        return $posts;
    }

    public function friend(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            abort(404, 'User not found');
        }

        $currentUser = Auth::user();
        $friends = $this->getUserFriends($user->id);

        foreach ($friends as $friend) {
            $friend->mutualFriendsCount = $this->countMutualFriends($currentUser->id, $friend->id);
        }

        return Inertia::render('Profile/Friend', [
            'user' => $user,
            'activeTab' => 'friend',
            'friends' => $friends,
        ]);
    }

    /**
     * Lấy danh sách bạn bè của người dùng
     */
    private function getUserFriends($userId)
    {
        return User::whereIn('id', function ($query) use ($userId) {
            $query->select('user_id_2')
                ->from('friendships')
                ->where('user_id_1', $userId)
                ->where('status', 'accepted')
                ->union(
                    DB::table('friendships')
                        ->select('user_id_1')
                        ->where('user_id_2', $userId)
                        ->where('status', 'accepted')
                );
        })->get();
    }

    /**
     * Đếm số bạn chung giữa hai người dùng
     */
    private function countMutualFriends($userId1, $userId2)
    {
        $friends1 = $this->getFriendsIds($userId1);
        $friends2 = $this->getFriendsIds($userId2);

        return count(array_intersect($friends1, $friends2));
    }

    /**
     * Lấy danh sách ID bạn bè của một người dùng
     */
    private function getFriendsIds($userId)
    {
        return DB::table('friendships')
            ->where(function ($query) use ($userId) {
                $query->where('user_id_1', $userId)
                    ->orWhere('user_id_2', $userId);
            })
            ->where('status', 'accepted')
            ->select(DB::raw('IF(user_id_1 = ' . $userId . ', user_id_2, user_id_1) as friend_id'))
            ->pluck('friend_id')
            ->toArray();
    }

    public function group(Request $request, $user)
    {
        return Inertia::render('Profile/Group', [
            'user' => $user,
            'activeTab' => 'group'
        ]);
    }
}
