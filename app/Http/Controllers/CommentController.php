<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string',
            'parent_comment_id' => 'nullable|exists:comments,id',
            'attachment_url' => 'nullable|string|max:255'
        ]);

        $comment = Comment::create([
            'post_id' => $request->post_id,
            'user_id' => auth()->id(),
            'content' => $request->content,
            'parent_comment_id' => $request->parent_comment_id,
            'attachment_url' => $request->attachment_url
        ]);

        return response()->json(['comment' => $comment->load('user')]);
    }

    public function getComments($postId)
    {
        $comments = Comment::where('post_id', $postId)
            ->with(['user', 'replies.user'])
            ->whereNull('parent_comment_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }


}
