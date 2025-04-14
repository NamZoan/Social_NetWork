<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Friendship;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
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

        $user = auth()->user();
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

        $posts = Post::where('user_id', $user->id)
            ->with([
                'user', // Thông tin người đăng
                'media', // Media của bài đăng
                'likes.user', // Người like và thông tin của họ
                'comments.user', // Bình luận và người bình luận
                'comments.replies.user', // Trả lời bình luận
                'originalPost.user' // Bài đăng gốc (nếu là bài chia sẻ)
            ])
            ->latest()
            ->paginate(10);

        // Chuyển đổi dữ liệu để Inertia có thể xử lý
        $posts->getCollection()->transform(function ($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'created_at' => $post->created_at->format('Y-m-d H:i:s'),
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
                    'id' => $post->originalPost->post_id,
                    'content' => $post->originalPost->content,
                    'user' => $post->originalPost->user
                ] : null
            ];
        });

        return Inertia::render('Profile/ListPost', [
            'user' => $user,
            'activeTab' => 'listpost',
            'posts' => $posts,
        ]);
    }

    public function loadMore($username, Request $request)
    {
        try {
            // Tìm user theo username
            $user = User::where('username', $username)->firstOrFail();

            // Validate tham số page
            $page = $request->input('page', 1);

            // Lấy bài viết của user, phân trang
            $posts = Post::where('user_id', $user->id)
                ->latest()
                ->paginate(2, ['*'], 'page', $page);

            // Trả về JSON response
            return response()->json($posts);
        } catch (\Exception $e) {
            // Xử lý lỗi nếu có
            return response()->json([
                'error' => 'Không thể tải bài viết',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function friend(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            abort(404, 'User not found');
        }

        $userId = $user->id;
        $currentUserId = auth()->id(); // ID của người dùng hiện tại đang đăng nhập

        // Lấy danh sách bạn bè của người dùng được truyền vào
        $friends = User::whereIn('id', function ($query) use ($userId) {
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

        foreach ($friends as $friend) {
            $friend->mutualFriendsCount = $this->countMutualFriends($currentUserId, $friend->id);
        }


        return Inertia::render('Profile/Friend', [
            'user' => $user,
            'activeTab' => 'friend',
            'friends' => $friends,

        ]);
    }

    public function countMutualFriends($userId1, $userId2)
    {
        // Lấy danh sách bạn bè của người dùng thứ nhất
        $friends1 = DB::table('friendships')
            ->where(function ($query) use ($userId1) {
                $query->where('user_id_1', $userId1)
                    ->orWhere('user_id_2', $userId1);
            })
            ->where('status', 'accepted')
            ->select(DB::raw('IF(user_id_1 = ' . $userId1 . ', user_id_2, user_id_1) as friend_id'))
            ->pluck('friend_id')
            ->toArray();

        // Lấy danh sách bạn bè của người dùng thứ hai
        $friends2 = DB::table('friendships')
            ->where(function ($query) use ($userId2) {
                $query->where('user_id_1', $userId2)
                    ->orWhere('user_id_2', $userId2);
            })
            ->where('status', 'accepted')
            ->select(DB::raw('IF(user_id_1 = ' . $userId2 . ', user_id_2, user_id_1) as friend_id'))
            ->pluck('friend_id')
            ->toArray();

        // Tìm giao điểm (bạn chung)
        $mutualFriends = array_intersect($friends1, $friends2);

        // Trả về số lượng bạn chung
        return count($mutualFriends);
    }

    public function group(Request $request, $user)
    {
        return Inertia::render('Profile/Group', [
            'user' => $user,
            'activeTab' => 'group'
        ]);
    }

}
