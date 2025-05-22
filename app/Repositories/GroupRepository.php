<?php

namespace App\Repositories;

use App\Models\Group;
use Illuminate\Support\Facades\Auth;

class GroupRepository implements GroupRepositoryInterface
{
    public function getCreatedGroups($userId)
    {
        return Group::where('creator_id', $userId)->withCount('members')->get();
    }

    public function getJoinedGroups($userId)
    {
        return Group::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->where('membership_status', 'active');
        })
        ->where('creator_id', '!=', $userId)
        ->withCount('members')
        ->get();
    }

    public function createGroup(array $data, $coverPhotoFile = null)
    {
        if ($coverPhotoFile) {
            $filename = uniqid() . '.' . $coverPhotoFile->getClientOriginalExtension();
            $destinationPath = public_path('images/client/group/thumbnail');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $coverPhotoFile->move($destinationPath, $filename);
            $data['cover_photo_url'] = $filename;
        }
        $data['creator_id'] = Auth::id();
        return Group::create($data);
    }

    public function addMember($group, $userId, $role, $status = 'active')
    {
        $group->members()->attach($userId, [
            'role' => $role,
            'joined_at' => now(),
            'membership_status' => $status
        ]);
    }

    public function isMember($group, $userId)
    {
        $membership = $group->members()->where('user_id', $userId)->first();
        return $membership && $membership->pivot->membership_status === 'active';
    }

    public function isPending($group, $userId)
    {
        $membership = $group->members()->where('user_id', $userId)->first();
        return $membership && $membership->pivot->membership_status === 'pending';
    }

    public function removeMember($group, $userId)
    {
        $group->members()->detach($userId);
    }

    public function find($groupId)
    {
        return Group::findOrFail($groupId);
    }

    public function getMembers($group)
    {
        return $group->members()->withPivot('role', 'membership_status')->wherePivot('membership_status', 'active')->get();
    }

    public function updateGroup($group, array $data, $coverPhotoFile = null)
    {
        if ($coverPhotoFile) {
            // Xóa ảnh cũ nếu có
            if ($group->cover_photo_url && file_exists(public_path('images/client/group/thumbnail/' . $group->cover_photo_url))) {
                unlink(public_path('images/client/group/thumbnail/' . $group->cover_photo_url));
            }
            $filename = uniqid() . '.' . $coverPhotoFile->getClientOriginalExtension();
            $coverPhotoFile->move(public_path('images/client/group/thumbnail'), $filename);
            $data['cover_photo_url'] = $filename;
        }
        $group->update($data);
        return $group;
    }

    public function deleteGroup($group)
    {
        // Xóa ảnh nếu có
        if ($group->cover_photo_url && file_exists(public_path('images/client/group/thumbnail/' . $group->cover_photo_url))) {
            unlink(public_path('images/client/group/thumbnail/' . $group->cover_photo_url));
        }
        $group->members()->detach();
        $group->delete();
    }
}