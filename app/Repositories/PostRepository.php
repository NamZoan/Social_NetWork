<?php

namespace App\Repositories;

use App\Models\Post;
use App\Models\Media;
use App\Models\Like;
use Illuminate\Support\Facades\DB;
use App\Repositories\PostRepositoryInterface;

class PostRepository implements PostRepositoryInterface
{
    public function createPost(array $data)
    {
        return Post::create($data);
    }

    public function attachMedia($post, $files, $userId)
    {
        $paths = [];
        if ($files) {
            foreach ($files as $file) {
                if ($file->isValid()) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $fileName;
                    $file->move(public_path('images/client/post'), $fileName);

                    Media::create([
                        'post_id' => $post->id,
                        'user_id' => $userId,
                        'media_type' => $this->getMediaType($file->getClientOriginalExtension()),
                        'media_url' => $filePath,
                    ]);
                    $paths[] = $filePath;
                }
            }
        }
        return $paths;
    }

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

    public function likePost($userId, $postId, $reactionType)
    {
        return Like::updateOrCreate(
            [
                'user_id' => $userId,
                'content_type' => 'post',
                'content_id' => $postId
            ],
            [
                'reaction_type' => $reactionType
            ]
        );
    }

    public function checkReaction($userId, $postId)
    {
        return Like::where([
            'user_id' => $userId,
            'content_type' => 'post',
            'content_id' => $postId,
        ])->first();
    }

    public function removeReaction($userId, $postId)
    {
        return Like::where('user_id', $userId)->where('content_id', $postId)->delete();
    }

    public function totalReaction($postId)
    {
        return Like::where('content_id', $postId)->count();
    }

    public function destroy($postId, $userId)
    {
        $post = Post::findOrFail($postId);
        if ($post->user_id !== $userId) {
            return false;
        }
        $post->delete();
        return true;
    }

    public function deleteMedia($mediaId, $post)
    {
        $media = $post->media()->where('id', $mediaId)->first();
        if ($media) {
            $path = public_path('images/client/post/' . $media->media_url);
            if (file_exists($path)) {
                unlink($path);
            }
            $media->delete();
            return true;
        }
        return false;
    }

    public function updatePrivacy($postId, $userId, $privacySetting)
    {
        $post = Post::findOrFail($postId);
        if ($post->user_id !== $userId) {
            return false;
        }
        $post->update(['privacy_setting' => $privacySetting]);
        return $post;
    }

    public function updatePost($post, array $data, $currentImages, $newImages, $userId)
    {
        DB::beginTransaction();
        try {
            $post->content = $data['content'];
            $post->save();

            $existingMedia = $post->media()->where('media_type', 'image')->get();
            foreach ($existingMedia as $media) {
                if (!in_array($media->media_url, $currentImages)) {
                    $filePath = public_path('images/client/post/' . $media->media_url);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $media->delete();
                }
            }

            if ($newImages) {
                foreach ($newImages as $file) {
                    if ($file->isValid()) {
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $file->move(public_path('images/client/post'), $filename);

                        $post->media()->create([
                            'user_id' => $userId,
                            'media_type' => 'image',
                            'media_url' => $filename
                        ]);
                    }
                }
            }

            DB::commit();
            $post->load('media');
            return $post;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getCommentsCount($post)
    {
        $count = $post->comments()->count();
        $repliesCount = $post->comments()->withCount('replies')->get()->sum('replies_count');
        return $count + $repliesCount;
    }

    public function find($postId)
    {
        return Post::findOrFail($postId);
    }
}