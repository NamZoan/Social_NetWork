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

        $joinedGroups = Group::whereHas('members', function ($query) use ($user) {
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

        $isAdmin = $group->isAdmin(auth()->id());



        return Inertia::render('Groups/ListPost', [
            'group' => $group,
            'isMember' => $isMember,
            'isPending' => $isPending,
            'isAdmin' => $isAdmin,
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

        $pendingRequests = GroupMember::with('user:id,name,avatar,username')
            ->where('group_id', $group->id)
            ->where('membership_status', 'pending')
            ->latest('joined_at')
            ->get();

        $group->loadCount(['members', 'posts']);

        return Inertia::render('Groups/Admin/PendingRequests', [
            'group' => $group,
            'user_auth' => Auth::user(),
            'isMember' => $isMember,
            'isPending' => $isPending,
            'pendingRequests' => $pendingRequests
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

    public function showPendingPosts(Group $group)
    {
        if ($group->creator_id !== Auth::id()) {
            abort(403);
        }

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

        $pendingPosts = $group->posts()
            ->with(['user', 'media', 'likes', 'comments'])
            ->where('privacy_setting', 'pending')
            ->latest()
            ->get();

        $group->loadCount(['members', 'posts']);

        return Inertia::render('Groups/Admin/PendingPosts', [
            'group' => $group,
            'user_auth' => Auth::user(),
            'isMember' => $isMember,
            'isPending' => $isPending,
            'pendingPosts' => $pendingPosts
        ]);
    }

    public function approvePost(Group $group, $postId)
    {
        if ($group->creator_id !== Auth::id()) {
            abort(403);
        }

        $post = $group->posts()->findOrFail($postId);

        $post->update([
            'privacy_setting' => 'public',
            'approved_at' => now(),
            'approved_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Bài viết đã được duyệt thành công!');
    }

    public function rejectPost(Group $group, $postId)
    {
        if ($group->creator_id !== Auth::id()) {
            abort(403);
        }

        $post = $group->posts()->findOrFail($postId);

        $post->update([
            'privacy_setting' => 'rejected',
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Bài viết đã bị từ chối!');
    }

    public function edit(Group $group)
    {
        // Chỉ creator mới được sửa
        if ($group->creator_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền sửa nhóm này!');
        }
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

        $isAdmin = $group->isAdmin(auth()->id());
        return Inertia::render('Groups/GroupUpdate', [
            'group' => $group,
            'isMember' => $isMember,
            'isPending' => $isPending,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function update(Request $request, Group $group)
    {
        if ($group->creator_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền sửa nhóm này!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'privacy_setting' => 'required|boolean',
            'post_approval_required' => 'required|boolean',
            'cover_photo_url' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_photo_url')) {
            // Xóa ảnh cũ nếu có
            if ($group->cover_photo_url && file_exists(public_path('images/client/group/thumbnail/' . $group->cover_photo_url))) {
                unlink(public_path('images/client/group/thumbnail/' . $group->cover_photo_url));
            }
            $file = $request->file('cover_photo_url');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/client/group/thumbnail'), $filename);
            $group->cover_photo_url = $filename;
        }

        $group->name = $request->name;
        $group->description = $request->description;
        $group->privacy_setting = $request->privacy_setting;
        $group->post_approval_required = $request->post_approval_required;
        $group->save();

        return redirect()->route('groups.edit', $group->id)->with('success', 'Cập nhật nhóm thành công!');
    }

    public function myPosts(Group $group)
    {
        $user = Auth::user();

        // Lấy các bài viết của user trong group
        $myPosts = $group->posts()
            ->where('user_id', $user->id)
            ->with(['user', 'media', 'likes', 'comments'])
            ->latest()
            ->get();

        // Kiểm tra trạng thái thành viên
        $isMember = false;
        if ($group->members()->where('user_id', $user->id)->exists()) {
            $membership = $group->members()->where('user_id', $user->id)->first();
            $isMember = $membership->pivot->membership_status === 'active';
        }

        $isAdmin = $group->isAdmin($user->id);

        return Inertia::render('Groups/MyPostInGroup', [
            'group' => $group,
            'myPosts' => $myPosts,
            'user_auth' => $user,
            'isMember' => $isMember,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function members(Group $group)
    {
        $user = Auth::user();

        // Lấy danh sách thành viên với role
        $members = $group->members()->withPivot('role')->get();

        $isMember = false;
        if ($group->members()->where('user_id', $user->id)->exists()) {
            $membership = $group->members()->where('user_id', $user->id)->first();
            $isMember = $membership->pivot->membership_status === 'active';
        }

        $isAdmin = $group->isAdmin($user->id);
        return Inertia::render('Groups/Member', [
            'group' => $group,
            'members' => $members,
            'isMember' => $isMember,
            'isAdmin' => $isAdmin,
            'user_auth' => $user,
        ]);
    }

    public function removeMember(Group $group, $userId)
    {
        $user = Auth::user();

        // Chỉ admin mới được xóa
        $isAdmin = $group->members()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'admin')
            ->exists();

        if (!$isAdmin) {
            abort(403, 'Bạn không có quyền xóa thành viên!');
        }

        // Không cho phép admin tự xóa chính mình
        if ($user->id == $userId) {
            return back()->with('error', 'Không thể tự xóa mình!');
        }

        $group->members()->detach($userId);

        return back()->with('success', 'Đã xóa thành viên khỏi nhóm!');
    }
    public function destroy(Group $group)
    {
        $user = Auth::user();

        // Chỉ creator hoặc admin mới được xóa nhóm
        $isAdmin = $group->members()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'admin')
            ->exists();

        if ($group->creator_id !== $user->id && !$isAdmin) {
            abort(403, 'Bạn không có quyền xóa nhóm này!');
        }

        // Xóa ảnh nếu có
        if ($group->cover_photo_url && file_exists(public_path('images/client/group/thumbnail/' . $group->cover_photo_url))) {
            unlink(public_path('images/client/group/thumbnail/' . $group->cover_photo_url));
        }

        // Xóa các liên kết thành viên
        $group->members()->detach();

        // Xóa nhóm
        $group->delete();

        return redirect()->route('group')->with('success', 'Đã xóa nhóm thành công!');
    }
}
