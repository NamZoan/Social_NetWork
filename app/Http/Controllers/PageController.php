<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $friendIds = $user->friendIds();
        $groupIds = $user->groups()->pluck('groups.id')->toArray();

        $posts = Post::with(['user', 'group'])
            ->where(function ($query) use ($friendIds, $groupIds) {
                $query->where(function ($q) use ($friendIds) {
                    $q->whereIn('user_id', $friendIds)
                        ->whereIn('privacy_setting', ['public', 'friends']);
                })->orWhere(function ($q) use ($groupIds) {
                    $q->whereIn('group_id', $groupIds)
                        ->where('privacy_setting', 'public');
                });
            })
            ->where('user_id', '!=', $user->id)
            ->latest()
            ->paginate(3)
            ->withPath(route('posts.load-more'));


        return Inertia::render('Home', [
            'posts' => $posts,
            'user' => $user,
        ]);
    }
    public function loadMore()
    {
        $user = Auth::user();
        $friendIds = $user->friendIds();
        $groupIds = $user->groups()->pluck('groups.id')->toArray();

        $posts = Post::with(['user', 'group'])
            ->where(function ($query) use ($friendIds, $groupIds) {
                $query->where(function ($q) use ($friendIds) {
                    $q->whereIn('user_id', $friendIds)
                        ->whereIn('privacy_setting', ['public', 'friends']);
                })->orWhere(function ($q) use ($groupIds) {
                    $q->whereIn('group_id', $groupIds)
                        ->where('privacy_setting', 'public');
                });
            })
            ->where('user_id', '!=', $user->id)
            ->latest()
            ->paginate(1); // có thể tăng để test

        // ✅ GÓI DỮ LIỆU DƯỚI "posts"

        return response()->json($posts);
    }



}
