<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class ProfileRepository implements ProfileRepositoryInterface
{
    public function getUserByUsername($username)
    {
        return User::where('username', $username)->firstOrFail();
    }

    public function getUserFriends($userId)
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

    public function getFriendsIds($userId)
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

    public function countMutualFriends($userId1, $userId2)
    {
        $friends1 = $this->getFriendsIds($userId1);
        $friends2 = $this->getFriendsIds($userId2);

        return count(array_intersect($friends1, $friends2));
    }

    public function getUserPosts($user, $currentUser, $page = 1)
    {
        $posts = Post::where('user_id', $user->id)
            ->whereNull('group_id')
            ->where(function($query) use ($user, $currentUser) {
                if ($currentUser && $currentUser->id === $user->id) {
                    return;
                }
                $query->where('privacy_setting', Post::PRIVACY_PUBLIC);

                if ($currentUser) {
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
}