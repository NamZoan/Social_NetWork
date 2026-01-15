<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\RelatedPost;
use Illuminate\Http\Request;

class RelatedPostController extends Controller
{
    // Lưu danh sách bài liên quan (sau khi bạn chạy TF-IDF offline)
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'related_posts' => 'required|array',
        ]);

        foreach ($request->related_posts as $item) {
            RelatedPost::updateOrCreate(
                [
                    'post_id' => $request->post_id,
                    'related_post_id' => $item['id']
                ],
                [
                    'similarity_score' => $item['score']
                ]
            );
        }

        return response()->json(['message' => 'Related posts saved.']);
    }

    // Lấy bài liên quan theo post
    public function getRelated($post_id)
    {
        $post = Post::findOrFail($post_id);
        
        $related = $post->getSimilarPosts(10);

        return response()->json($related);
    }
}
