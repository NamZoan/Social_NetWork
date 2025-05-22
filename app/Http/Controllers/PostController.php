<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Repositories\PostRepositoryInterface;

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
            'privacy_setting' => 'required|in:public,friends,private,pending,rejected',
            'content' => 'required|string',
            'files.*' => 'nullable|file|mimes:jpg,png,gif,mp4,mp3,pdf|max:2048',
            'group_id' => 'nullable|exists:groups,id'
        ]);

        DB::beginTransaction();
        try {
            $postData = [
                'user_id' => Auth::id(),
                'content' => $request->content,
                'status' => 'approved'
            ];

            if ($request->has('group_id') && $request->group_id) {
                $group = Group::find($request->group_id);
                if (!$group) {
                    return back()->withErrors(['error' => 'Nhóm không tồn tại.']);
                }
                if (!$group->members()->where('user_id', Auth::id())->exists()) {
                    return back()->withErrors(['error' => 'Bạn không phải là thành viên của nhóm này.']);
                }
                $postData['privacy_setting'] = $group->post_approval_required ? 'public' : 'pending';
                $postData['status'] = $group->post_approval_required ? 'approved' : 'pending';
                $postData['group_id'] = $request->group_id;
            } else {
                $postData['privacy_setting'] = $request->privacy_setting;
            }

            $post = $this->postRepo->createPost($postData);
            $this->postRepo->attachMedia($post, $request->file('files'), Auth::id());

            DB::commit();

            if ($request->has('group_id') && $request->group_id) {
                return redirect()->route('groups.show', ['group' => $request->group_id]);
            } else {
                return redirect()->route('profile', ['username' => Auth::user()->username]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function likePost(Request $request, $postId)
    {
        $user = Auth::user();
        $reactionType = $request->reaction;
        $this->postRepo->likePost($user->id, $postId, $reactionType);

        return response()->json([
            'message' => 'Liked',
            'likes_count' => $this->postRepo->totalReaction($postId)
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
        $this->postRepo->removeReaction(Auth::id(), $postId);

        return response()->json([
            'message' => 'Reaction removed',
        ]);
    }

    public function totalReaction($postId)
    {
        $totalReaction = $this->postRepo->totalReaction($postId);
        return response()->json(['totalReaction' => $totalReaction]);
    }

    public function destroy($id)
    {
        $result = $this->postRepo->destroy($id, Auth::id());
        if (!$result) {
            return response()->json(['message' => 'Không có quyền xoá bài viết này'], 403);
        }
        return response()->json(['message' => 'Xoá thành công']);
    }

    public function deleteMedia(Request $request, $postId)
    {
        $mediaId = $request->input('id');
        $post = $this->postRepo->find($postId);
        $result = $this->postRepo->deleteMedia($mediaId, $post);

        if ($result) {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Không tìm thấy ảnh'], 404);
    }

    public function show(Group $group)
    {
        $group->load(['posts' => function($query) {
            $query->with(['user', 'media', 'likes', 'comments'])
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
            return response()->json(['message' => 'Không có quyền thay đổi quyền riêng tư của bài viết này'], 403);
        }

        return response()->json([
            'message' => 'Cập nhật quyền riêng tư thành công',
            'privacy_setting' => $result->privacy_setting
        ]);
    }

    public function update(Request $request, $postId)
    {
        $post = $this->postRepo->find($postId);
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $currentImages = $request->input('current_images', []);
            $newImages = $request->hasFile('new_images') ? $request->file('new_images') : [];
            $data = [
                'content' => $request->input('content')
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

    public function getCommentsCount($postId)
    {
        $post = $this->postRepo->find($postId);
        try {
            $totalCount = $this->postRepo->getCommentsCount($post);
            return response()->json([
                'count' => $totalCount,
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
