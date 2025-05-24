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
     * Lấy danh sách comments của một bài viết
     *
     * @param Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Post $post)
    {
        try {
            $comments = $post->comments()
                ->with(['user', 'replies.user'])
                ->whereNull('parent_comment_id')
                ->orderBy('created_at', 'desc')
                ->paginate(2);

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
            $replies = $comment->replies()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

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

            // Load thông tin user và replies nếu có
            $comment->load(['user', 'replies.user']);

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
    public function update(Request $request, $commentId)
    {
        try {
            $comment = Comment::findOrFail($commentId);
            
            // Kiểm tra quyền sửa comment
            if ($comment->user_id !== Auth::id()) {
                return response()->json([
                    'error' => 'You do not have permission to edit this comment'
                ], 403);
            }

            $request->validate([
                'content' => 'required|string|max:1000'
            ]);

            $comment->update([
                'content' => $request->content
            ]);

            return response()->json([
                'message' => 'Comment updated successfully',
                'comment' => $comment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update comment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

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

            // Xóa tất cả replies của comment này
            $comment->replies()->delete();
            
            // Xóa comment
            $comment->delete();

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
