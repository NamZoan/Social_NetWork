<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;

class PostApiController extends Controller
{
    public function show($id)
    {
        $post = Post::findOrFail($id);

        return response()->json($post);
    }

    public function related($id)
    {
        $post = Post::findOrFail($id);

        $relatedPosts = $post->similarPosts(6);

        return response()->json($relatedPosts);
    }
}
