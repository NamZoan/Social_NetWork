<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Media;
use App\Models\Like;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'privacy_setting' => 'required|in:public,friends,private',
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

            // Only add group_id and check group membership if group_id is provided
            if ($request->has('group_id') && $request->group_id) {
                $group = Group::find($request->group_id);

                if (!$group) {
                    return back()->withErrors(['error' => 'Nhóm không tồn tại.']);
                }

                // Check if user is a member of the group
                if (!$group->members()->where('user_id', Auth::id())->exists()) {
                    return back()->withErrors(['error' => 'Bạn không phải là thành viên của nhóm này.']);
                }

                // Set privacy_setting based on group's post_approval_required
                $postData['privacy_setting'] = $group->post_approval_required ? 'public' : 'private';

                // Check if group requires post approval
                if ($group->post_approval_required) {
                    $postData['status'] = 'approved';
                } else {
                    $postData['status'] = 'pending';
                }

                $postData['group_id'] = $request->group_id;
            } else {
                // If not posting in group, use the privacy_setting from request
                $postData['privacy_setting'] = $request->privacy_setting;
            }

            // Create the post
            $post = Post::create($postData);

            $paths = [];

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    if ($file->isValid()) {
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $filePath = $fileName;

                        // Save file to public directory
                        $file->move(public_path('images/client/post'), $fileName);

                        // Save to media table
                        Media::create([
                            'post_id' => $post->id,
                            'user_id' => Auth::id(),
                            'media_type' => $this->getMediaType($file->getClientOriginalExtension()),
                            'media_url' => $filePath,
                        ]);

                        $paths[] = $filePath;
                    }
                }
            }

            DB::commit();

            // Redirect based on whether it's a group post or not
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

    /**
     * Xác định loại file dựa trên phần mở rộng
     */
    private function getMediaType($extension)
    {
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $videoTypes = ['mp4', 'avi', 'mov'];
        $audioTypes = ['mp3', 'wav', 'ogg'];
        $documentTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

        if (in_array($extension, $imageTypes)) {
            return 'image';
        } elseif (in_array($extension, $videoTypes)) {
            return 'video';
        } elseif (in_array($extension, $audioTypes)) {
            return 'audio';
        } elseif (in_array($extension, $documentTypes)) {
            return 'document';
        }

        return 'other';
    }


    public function likePost(Request $request, $postId)
    {
        $user = Auth::user();
        $reactionType = $request->reaction;

        Like::updateOrCreate(
            [
                'user_id' => $user->id,
                'content_type' => 'post',
                'content_id' => $postId
            ],
            [
                'reaction_type' => $reactionType
            ]
        );

        return response()->json([
            'message' => 'Liked',
            'likes_count' => Post::find($postId)->likes()->count()
        ]);
    }


    public function checkReaction($postId)
    {
        $user = Auth::user();
        $reaction = Like::where([
            'user_id' => $user->id,
            'content_type' => 'post',
            'content_id' => $postId,
        ])->first();

        if ($reaction) {
            return response()->json(['reaction' => $reaction->reaction_type]);
        }

        return response()->json(['reaction' => null]);

    }
    // ✅ Xóa reaction của người dùng
    public function removeReaction($postId)
    {
        Like::where('user_id', Auth::id())->where('content_id', $postId)->delete();

        return response()->json([
            'message' => 'Reaction removed',
        ]);
    }

    public function totalReaction($postId)
    {
        $totalReaction = Like::where('content_id', $postId)->count();
        return response()->json(['totalReaction' => $totalReaction]);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền xoá bài viết này'], 403);
        }
        $post->delete();

        return response()->json(['message' => 'Xoá thành công']);
    }


    public function deleteMedia(Request $request, Post $post)
    {
        $mediaId = $request->input('id');
        $media = $post->media()->where('id', $mediaId)->first();

        if ($media) {
            // Xoá file vật lý
            $path = public_path('images/client/post/' . $media->media_url);
            if (file_exists($path)) {
                unlink($path);
            }

            // Xoá record trong database
            $media->delete();

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

        // Check if user is a member with active status
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

        $post = Post::findOrFail($postId);

        // Kiểm tra quyền sở hữu
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền thay đổi quyền riêng tư của bài viết này'], 403);
        }

        $post->update([
            'privacy_setting' => $request->privacy_setting
        ]);

        return response()->json([
            'message' => 'Cập nhật quyền riêng tư thành công',
            'privacy_setting' => $post->privacy_setting
        ]);
    }

    public function update(Request $request, Post $post)
    {
        // Kiểm tra quyền sửa bài viết
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // Cập nhật nội dung
            $post->content = $request->input('content');
            $post->save();

            // Xử lý ảnh hiện tại
            $currentImages = $request->input('current_images', []);
            $existingMedia = $post->media()->where('media_type', 'image')->get();

            // Xóa các ảnh không còn trong danh sách hiện tại
            foreach ($existingMedia as $media) {
                if (!in_array($media->media_url, $currentImages)) {
                    // Xóa file ảnh
                    $filePath = public_path('images/client/post/' . $media->media_url);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $media->delete();
                }
            }

            // Xử lý ảnh mới
            if ($request->hasFile('new_images')) {
                foreach ($request->file('new_images') as $file) {
                    if ($file->isValid()) {
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $file->move(public_path('images/client/post'), $filename);

                        $post->media()->create([
                            'user_id' => Auth::id(),
                            'media_type' => 'image',
                            'media_url' => $filename
                        ]);
                    }
                }
            }

            DB::commit();

            // Load lại media để trả về
            $post->load('media');

            return response()->json([
                'message' => 'Post updated successfully',
                'post' => $post
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating post: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get the total number of comments for a post
     *
     * @param Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommentsCount(Post $post)
    {
        try {
            // Lấy tổng số comment của bài viết
            $count = $post->comments()->count();

            // Lấy tổng số reply của tất cả comments
            $repliesCount = $post->comments()->withCount('replies')->get()->sum('replies_count');

            // Tổng số comment bao gồm cả replies
            $totalCount = $count + $repliesCount;

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
