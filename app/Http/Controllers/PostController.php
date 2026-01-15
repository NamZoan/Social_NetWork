<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Repositories\PostRepositoryInterface;
use App\Events\NewReaction;
use App\Models\Notification;
use App\Models\Post;
use App\Models\UserInteraction;

class PostController extends Controller
{
    protected $postRepo;

    public function __construct(PostRepositoryInterface $postRepo)
    {
        $this->postRepo = $postRepo;
    }

    public function store(Request $request)
    {
        $request->validate([
            'privacy_setting' => 'nullable|in:public,friends,private,pending,rejected',
            'content' => 'required|string',
            'files.*' => 'nullable|file|mimes:jpg,png,gif,mp4,mp3,pdf|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'group_id' => 'nullable|exists:groups,id',
            'page_id' => 'nullable|exists:pages,id',
            'allow_comments' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $postData = [
                'user_id' => Auth::id(),
                'content' => $request->content,
                'allow_comments' => $request->boolean('allow_comments', true),
            ];

            if ($request->has('page_id') && $request->page_id) {
                $page = Page::find($request->page_id);
                if (!$page) {
                    return back()->withErrors(['error' => 'Trang khong ton tai.']);
                }
                if (!$page->isAdmin(Auth::id())) {
                    return back()->withErrors(['error' => 'Ban khong co quyen dang bai tren trang nay.']);
                }
                $postData['page_id'] = $page->id;
                $postData['privacy_setting'] = 'public';
            } elseif ($request->has('group_id') && $request->group_id) {
                $group = Group::find($request->group_id);
                if (!$group) {
                    return back()->withErrors(['error' => 'Nhom khong ton tai.']);
                }
                if (!$group->members()->where('user_id', Auth::id())->exists()) {
                    return back()->withErrors(['error' => 'Ban khong phai thanh vien cua nhom nay.']);
                }
                $postData['privacy_setting'] = $group->post_approval_required ? 'pending' : 'public';
                $postData['group_id'] = $request->group_id;
            } else {
                $postData['privacy_setting'] = $request->input('privacy_setting', Post::PRIVACY_PUBLIC);
            }

            $post = $this->postRepo->createPost($postData);

            $files = $request->hasFile('files') ? $request->file('files') : [];
            $images = $request->hasFile('images') ? $request->file('images') : [];
            $allFiles = array_merge($files, $images);

            $this->postRepo->attachMedia($post, $allFiles, Auth::id());

            DB::commit();

            // Inertia requests must receive Inertia/redirect responses, not plain JSON
            $isInertiaRequest = $request->header('X-Inertia');
            $shouldReturnJson = !$isInertiaRequest && ($request->expectsJson() || $request->has('page_id'));

            if ($shouldReturnJson) {
                $post->load(['user', 'media', 'page:id,name,username,profile_picture_url']);
                return response()->json([
                    'success' => true,
                    'post' => $post
                ]);
            }

            if ($request->has('group_id') && $request->group_id) {
                return redirect()->route('groups.show', ['group' => $request->group_id]);
            }
            if ($request->has('page_id') && $request->page_id) {
                return redirect()->route('pages.show', ['identifier' => $request->page_id]);
            }
            return redirect()->route('profile', ['username' => Auth::user()->username]);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function share(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'nullable|string',
            'privacy_setting' => 'required|in:public,friends,private',
        ]);

        $user = Auth::user();

        if (!$post->canView($user)) {
            return response()->json(['message' => 'Khong co quyen chia se bai viet nay'], 403);
        }

        $sharedPost = $this->postRepo->sharePost(
            $user->id,
            $post,
            $request->input('content') ?? '',
            $request->input('privacy_setting', 'public')
        );

        $post->increment('shares_count');

        $sharedPost->load([
            'user',
            'media',
            'originalPost.user',
            'originalPost.media',
        ])->loadCount(['comments', 'shares']);

        return response()->json([
            'message' => 'Chia se bai viet thanh cong',
            'post' => $sharedPost,
            'shares_count' => (int) ($post->shares_count ?? 0)
        ]);
    }

    public function likePost(Request $request, $postId)
    {
        $user = Auth::user();
        $reactionType = $request->reaction;
        $post = $this->postRepo->find($postId);
        $like = $this->postRepo->likePost($user->id, $postId, $reactionType);

        if ($like->wasRecentlyCreated) {
            $post->increment('likes_count');
        }

        if ($post->user_id !== $user->id) {
            Notification::create([
                'user_id' => $post->user_id,
                'type' => 'reaction',
                'reference_id' => $post->id,
                'reference_type' => 'post',
                'sender_id' => $user->id,
                'message' => $reactionType,
                'created_at' => now(),
                'is_read' => false,
                'action_url' => "/posts/{$post->id}"
            ]);
        }

        broadcast(new NewReaction($like, $post, $user))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Liked',
            'reaction' => $reactionType,
            'likes_count' => (int) ($post->likes_count ?? 0)
        ]);
    }

    public function checkReaction($postId)
    {
        $user = Auth::user();
        $reaction = $this->postRepo->checkReaction($user->id, $postId);

        return response()->json([
            'reaction' => $reaction ? $reaction->reaction_type : null
        ]);
    }

    public function removeReaction($postId)
    {
        $post = $this->postRepo->find($postId);
        $deleted = $this->postRepo->removeReaction(Auth::id(), $postId);

        if ($deleted > 0 && ($post->likes_count ?? 0) > 0) {
            $post->decrement('likes_count');
        }

        return response()->json([
            'success' => true,
            'message' => 'Reaction removed',
            'likes_count' => (int) ($post->likes_count ?? 0),
        ]);
    }

    public function totalReaction($postId)
    {
        $post = $this->postRepo->find($postId);
        $likesCount = (int) ($post->likes_count ?? 0);
        return response()->json([
            'totalReaction' => $likesCount,
            'likes_count' => $likesCount
        ]);
    }

    public function destroy($id)
    {
        $result = $this->postRepo->destroy($id, Auth::id());
        if (!$result) {
            return response()->json(['message' => 'Khong co quyen xoa bai viet nay'], 403);
        }
        return response()->json(['message' => 'Xoa thanh cong']);
    }

    public function deleteMedia(Request $request, $postId)
    {
        $mediaId = $request->input('id');
        $post = $this->postRepo->find($postId);
        $result = $this->postRepo->deleteMedia($mediaId, $post);

        if ($result) {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Khong tim thay anh'], 404);
    }

    public function show(Group $group)
    {
        $group->load(['posts' => function ($query) {
            $query->with(['user', 'media', 'likes', 'comments'])
                ->withCount(['comments', 'likes'])
                ->latest()
                ->take(10);
        }]);
        $group->loadCount(['members', 'posts']);

        $isMember = false;
        $isPending = false;
        if (Auth::check()) {
            $membership = $group->members()
                ->where('user_id', Auth::id())
                ->first();

            if ($membership) {
                $isMember = $membership->pivot->membership_status === 'active';
                $isPending = $membership->pivot->membership_status === 'pending';
            }
        }

        return Inertia::render('Groups/GroupDetail', [
            'group' => $group,
            'isMember' => $isMember,
            'isPending' => $isPending
        ]);
    }

    public function updatePrivacy(Request $request, $postId)
    {
        $request->validate([
            'privacy_setting' => 'required|in:public,friends,private'
        ]);
        $result = $this->postRepo->updatePrivacy($postId, Auth::id(), $request->privacy_setting);

        if (!$result) {
            return response()->json(['message' => 'Khong co quyen thay doi quyen rieng tu cua bai viet nay'], 403);
        }

        return response()->json([
            'message' => 'Cap nhat quyen rieng tu thanh cong',
            'privacy_setting' => $result->privacy_setting
        ]);
    }

    public function update(Request $request, $postId)
    {
        $post = $this->postRepo->find($postId);
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'nullable|string',
            'allow_comments' => 'nullable|boolean',
        ]);

        try {
            $currentImages = $request->input('current_images', []);
            $newImages = $request->hasFile('new_images') ? $request->file('new_images') : [];
            $data = [
                'content' => $request->input('content', $post->content),
                'allow_comments' => $request->boolean('allow_comments', $post->allow_comments),
            ];

            $updatedPost = $this->postRepo->updatePost($post, $data, $currentImages, $newImages, Auth::id());

            return response()->json([
                'message' => 'Post updated successfully',
                'post' => $updatedPost
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating post: ' . $e->getMessage()], 500);
        }
    }

    public function logInteraction(Request $request, Post $post)
    {
        $request->validate([
            'interaction_type' => 'required|in:view,click,like,share,comment,skip,profile_visit',
        ]);

        try {
            UserInteraction::create([
                'user_id' => Auth::id(),
                'post_id' => $post->id,
                'interaction_type' => $request->interaction_type,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Interaction logged',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCommentsCount($postId)
    {
        $post = $this->postRepo->find($postId);
        try {
            $totalCount = $post->comments()
                ->where(function ($query) {
                    $query->where('is_hidden', false)
                        ->orWhereNull('is_hidden');
                })
                ->count();

            if ($post->comments_count !== $totalCount) {
                $post->comments_count = $totalCount;
                $post->save();
            }

            $commentsCount = (int) ($post->comments_count ?? 0);
            return response()->json([
                'count' => $commentsCount,
                'comments_count' => $commentsCount,
                'message' => 'Comments count retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get comments count',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
