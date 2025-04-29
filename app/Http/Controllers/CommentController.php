<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

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
     * Lưu comment mới
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'post_id' => 'required|exists:posts,id',
                'content' => 'required|string',
                'parent_comment_id' => 'nullable|exists:comments,id'
            ]);

            $comment = Comment::create([
                'user_id' => Auth::id(),
                'post_id' => $request->post_id,
                'content' => $request->content,
                'parent_comment_id' => $request->parent_comment_id
            ]);

            // Load thông tin user và replies nếu có
            $comment->load(['user', 'replies.user']);

            return response()->json([
                'message' => 'Comment created successfully',
                'comment' => $comment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create comment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getComments($postId)
    {
        $comments = Comment::where('post_id', $postId)
            ->with(['user', 'replies.user'])
            ->whereNull('parent_comment_id')
            ->orderBy('created_at', 'desc')
            ->paginate(2);

        return response()->json($comments);
    }
}
