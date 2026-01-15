<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Events\NewComment;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /**
     * Build nested replies tree for consistent json shape.
     */
    private function buildRepliesTree(Comment $comment): Comment
    {
        $comment->loadMissing(['user', 'repliesRecursive.user']);
        $comment->loadCount('replies');

        $nestedReplies = $comment->repliesRecursive->map(function (Comment $reply) {
            return $this->buildRepliesTree($reply);
        });

        $comment->setRelation('replies', $nestedReplies);
        $comment->unsetRelation('repliesRecursive');

        return $comment;
    }
    /**
     * Lay danh sach comments cua mot bai viet
     *
     * @param Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Post $post)
    {
        try {
            $comments = $post->comments()
                ->with([
                    'user',
                    'repliesRecursive.user',
                ])
                ->withCount('replies')
                ->whereNull('parent_comment_id')
                ->orderBy('created_at', 'desc')
                ->paginate(2);

            $comments->getCollection()->transform(function ($comment) {
                return $this->buildRepliesTree($comment);
            });

            return response()->json($comments);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch comments',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy replies của một comment
     */
    public function getReplies(Comment $comment)
    {
        try {
            $replies = $comment->repliesRecursive()
                ->with('user')
                ->get()
                ->map(function ($reply) {
                    return $this->buildRepliesTree($reply);
                });

            return response()->json([
                'replies' => $replies,
                'message' => 'Replies retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch replies',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lưu comment mới
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'post_id' => 'required|exists:posts,id',
                'content' => 'required|string|max:1000',
                'parent_comment_id' => 'nullable|exists:comments,id'
            ]);

            $post = Post::findOrFail($request->post_id);
            
            // Kiểm tra quyền comment
            if (!$post->canComment(Auth::user())) {
                return response()->json([
                    'error' => 'You do not have permission to comment on this post'
                ], 403);
            }

            $comment = Comment::create([
                'user_id' => Auth::id(),
                'post_id' => $request->post_id,
                'content' => $request->content,
                'parent_comment_id' => $request->parent_comment_id,
                'created_at' => now()
            ]);

            // Load thong tin user va cay replies
            $comment = $this->buildRepliesTree($comment);

            // Tạo thông báo cho chủ bài viết
            if ($post->user_id !== Auth::id()) {
                Notification::create([
                    'user_id' => $post->user_id,
                    'type' => 'comment',
                    'reference_id' => $post->id,
                    'reference_type' => 'post',
                    'sender_id' => Auth::id(),
                    'message' => $request->content,
                    'created_at' => now(),
                    'is_read' => false,
                    'action_url' => "/posts/{$post->id}"
                ]);
            }

            // Broadcast event
            broadcast(new NewComment($comment, $post, Auth::user()))->toOthers();

            DB::commit();

            return response()->json([
                'message' => 'Comment created successfully',
                'comment' => $comment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create comment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật comment
     */

    /**
     * Xóa comment
     */
    public function destroy(Comment $comment)
    {
        try {
            DB::beginTransaction();
            
            // Kiểm tra quyền xóa comment
            if ($comment->user_id !== Auth::id() && $comment->post->user_id !== Auth::id()) {
                return response()->json([
                    'error' => 'You do not have permission to delete this comment'
                ], 403);
            }

            // XA3a comment va toan bo cay replies
            $comment->load('replies');
            $comment->deleteRecursively();

            DB::commit();

            return response()->json([
                'message' => 'Comment deleted successfully',
                'comment_id' => $comment->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to delete comment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ẩn/hiện comment
     */
    public function toggleVisibility($commentId)
    {
        try {
            $comment = Comment::findOrFail($commentId);
            
            // Kiểm tra quyền ẩn/hiện comment
            if ($comment->user_id !== Auth::id()) {
                return response()->json([
                    'error' => 'You do not have permission to toggle this comment'
                ], 403);
            }

            $comment->toggleVisibility();

            return response()->json([
                'message' => 'Comment visibility toggled successfully',
                'is_hidden' => $comment->is_hidden
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to toggle comment visibility',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

