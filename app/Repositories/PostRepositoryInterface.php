<?php

namespace App\Repositories;

interface PostRepositoryInterface
{
    public function createPost(array $data);
    public function attachMedia($post, $files, $userId);
    public function likePost($userId, $postId, $reactionType);
    public function checkReaction($userId, $postId);
    public function removeReaction($userId, $postId);
    public function totalReaction($postId);
    public function destroy($postId, $userId);
    public function deleteMedia($mediaId, $post);
    public function updatePrivacy($postId, $userId, $privacySetting);
    public function updatePost($post, array $data, $currentImages, $newImages, $userId);
    public function getCommentsCount($post);
    public function find($postId);
}