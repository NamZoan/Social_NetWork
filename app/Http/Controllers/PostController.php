<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Media;
use App\Models\Like;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
class PostController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'privacy_setting' => 'required|in:public,friends,private,custom',
            'content' => 'required|string',
            'files.*' => 'nullable|file|mimes:jpg,png,gif,mp4,mp3,pdf|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Tạo bài viết mới
            $post = Post::create([
                'user_id' => auth()->id(),
                'content' => $request->content,
                'privacy_setting' => $request->privacy_setting,
            ]);

            $paths = [];

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    if ($file->isValid()) {
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $filePath = $fileName;

                        // Lưu file vào thư mục public
                        $file->move(public_path('images/client/post'), $fileName);

                        // Lưu vào bảng media
                        Media::create([
                            'post_id' => $post->id,
                            'user_id' => auth()->id(),
                            'media_type' => $this->getMediaType($file->getClientOriginalExtension()),
                            'media_url' => $filePath,
                        ]);

                        $paths[] = $filePath;
                    }
                }
            }

            DB::commit();

            return Inertia::location(route('profile', ['username' => auth()->user()->username]));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Đã xảy ra lỗi, vui lòng thử lại!']);
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
        $user = auth()->user();
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
        $user = auth()->user();
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
        Like::where('user_id', auth()->user()->id)->where('content_id', $postId)->delete();

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
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Không có quyền xoá bài viết này'], 403);
        }
        $post->delete();

        return response()->json(['message' => 'Xoá thành công']);
    }

}
