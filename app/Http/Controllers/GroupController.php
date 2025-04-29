<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $createdGroups = Group::where('creator_id', $user->id)
            ->withCount('members')
            ->get();
        
        $joinedGroups = Group::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('membership_status', 'active');
        })
        ->where('creator_id', '!=', $user->id)
        ->withCount('members')
        ->get();

        return Inertia::render('Groups/Groups', [
            'createdGroups' => $createdGroups,
            'joinedGroups' => $joinedGroups
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'privacy_setting' => 'required|boolean',
            'post_approval_required' => 'required|boolean',
            'cover_photo_url' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_photo_url')) {
            $file = $request->file('cover_photo_url');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('images/client/group/thumbnail');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
        }

        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'privacy_setting' => $request->privacy_setting,
            'post_approval_required' => $request->post_approval_required,
            'cover_photo_url' => $filename ?? null,
            'creator_id' => Auth::id(),
        ]);

        // Add creator as admin member
        $group->members()->attach(Auth::id(), [
            'role' => 'admin',
            'joined_at' => now(),
            'membership_status' => 'active'
        ]);

        return back()->with('success', 'Tạo nhóm thành công!');
    }

    public function show(Group $group)
    {
        $posts = $group->posts()
            ->with(['user', 'media', 'likes', 'comments'])
            ->withCount('comments')
            ->latest()
            ->paginate(1);

        $group->posts = $posts;
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

    public function join($groupId)
    {
        $group = Group::findOrFail($groupId);
        $user = Auth::user();

        // Check if user is already a member
        if ($group->members()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Bạn đã là thành viên của nhóm này.');
        }

        // Check if there's already a pending request
        if ($group->members()->where('user_id', $user->id)->where('membership_status', 'pending')->exists()) {
            return back()->with('error', 'Bạn đã gửi yêu cầu tham gia nhóm. Vui lòng đợi phê duyệt.');
        }

        // Handle based on privacy setting
        if ($group->privacy_setting == 0) { // Private group - need admin approval
            // Create pending membership
            $group->members()->attach($user->id, [
                'role' => 'member',
                'joined_at' => now(),
                'membership_status' => 'pending'
            ]);

            return back()->with('success', 'Yêu cầu tham gia nhóm đã được gửi. Vui lòng đợi phê duyệt.');
        } else { // Public group - join directly
            // Directly add as member
            $group->members()->attach($user->id, [
                'role' => 'member',
                'joined_at' => now(),
                'membership_status' => 'active'
            ]);

            return back()->with('success', 'Bạn đã tham gia nhóm thành công!');
        }
    }

    public function getPendingRequests($groupId)
    {
        $group = Group::findOrFail($groupId);
        
        // Check if user is the creator
        if ($group->creator_id != Auth::id()) {
            return back()->with('error', 'Bạn không có quyền xem yêu cầu tham gia nhóm.');
        }

        $pendingRequests = $group->members()
            ->where('membership_status', 'pending')
            ->with(['user' => function($query) {
                $query->select('id', 'name', 'email', 'avatar');
            }])
            ->get();

        return Inertia::render('Groups/PendingRequests', [
            'group' => $group,
            'pendingRequests' => $pendingRequests
        ]);
    }

    public function approveJoinRequest($groupId, $userId)
    {
        $group = Group::findOrFail($groupId);
        
        // Check if user is the creator
        if ($group->creator_id != Auth::id()) {
            return back()->with('error', 'Bạn không có quyền phê duyệt yêu cầu tham gia nhóm.');
        }

        // Update membership status
        $group->members()->updateExistingPivot($userId, [
            'membership_status' => 'active',
            'joined_at' => now()
        ]);

        return back()->with('success', 'Đã phê duyệt yêu cầu tham gia nhóm.');
    }

    public function rejectJoinRequest($groupId, $userId)
    {
        $group = Group::findOrFail($groupId);
        
        // Check if user is the creator
        if ($group->creator_id != Auth::id()) {
            return back()->with('error', 'Bạn không có quyền từ chối yêu cầu tham gia nhóm.');
        }

        // Remove the membership request
        $group->members()->detach($userId);

        return back()->with('success', 'Đã từ chối yêu cầu tham gia nhóm.');
    }

    public function leave($groupId)
    {
        $group = Group::findOrFail($groupId);
        $user = Auth::user();

        // Check if user is a member
        if (!$group->members()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Bạn không phải là thành viên của nhóm này.');
        }

        // Remove user from group
        $group->members()->detach($user->id);

        return back()->with('success', 'Bạn đã rời nhóm thành công!');
    }

    public function getPosts(Group $group)
    {
        $posts = $group->posts()
            ->with(['user', 'images', 'likes', 'comments'])
            ->latest()
            ->paginate(1);

        return response()->json($posts);
    }

    public function loadMorePosts(Group $group, Request $request)
    {
        $page = $request->input('page', 1);
        
        $posts = $group->posts()
            ->with(['user', 'media', 'likes', 'comments'])
            ->withCount('comments')
            ->latest()
            ->paginate(1, ['*'], 'page', $page);

        return response()->json([
            'posts' => $posts,
            'next_page_url' => $posts->nextPageUrl()
        ]);
    }

    /**
     * Show pending membership requests for a group
     */
    public function showPendingRequests(Group $group)
    {
        if ($group->creator_id !== Auth::id()) {
            abort(403);
        }
        
        $pendingMembers = GroupMember::with('user:id,name,avatar')
            ->where('group_id', $group->id)
            ->where('membership_status', 'pending')
            ->latest('joined_at')
            ->paginate(10);

        return Inertia::render('Groups/Admin/PendingRequests', [
            'group' => $group,
            'pendingMembers' => $pendingMembers
        ]);
    }

    /**
     * Approve a pending membership request
     */
    public function approveMember(Group $group, $userId)
    {
        if ($group->creator_id !== Auth::id()) {
            abort(403);
        }
        
        $group->members()->updateExistingPivot($userId, [
            'membership_status' => 'active',
            'role' => 'member'
        ]);
        
        return redirect()->back();
    }

    /**
     * Reject a pending membership request
     */
    public function rejectMember(Group $group, $userId)
    {
        if ($group->creator_id !== Auth::id()) {
            abort(403);
        }
        
        $group->members()->detach($userId);
        
        return redirect()->back();
    }
}
