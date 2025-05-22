<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Repositories\GroupRepositoryInterface;
use App\Models\Group;
use App\Models\GroupMember;

class GroupController extends Controller
{
    protected $groupRepo;

    public function __construct(GroupRepositoryInterface $groupRepo)
    {
        $this->groupRepo = $groupRepo;
    }

    public function index()
    {
        $user = Auth::user();
        $createdGroups = $this->groupRepo->getCreatedGroups($user->id);
        $joinedGroups = $this->groupRepo->getJoinedGroups($user->id);

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

        $group = $this->groupRepo->createGroup($request->only([
            'name', 'description', 'privacy_setting', 'post_approval_required'
        ]), $request->file('cover_photo_url'));

        // Add creator as admin member
        $this->groupRepo->addMember($group, Auth::id(), 'admin', 'active');

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

        $isMember = $this->groupRepo->isMember($group, Auth::id());
        $isPending = $this->groupRepo->isPending($group, Auth::id());
        $isAdmin = $group->isAdmin(Auth::id());

        return Inertia::render('Groups/ListPost', [
            'group' => $group,
            'isMember' => $isMember,
            'isPending' => $isPending,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function join($groupId)
    {
        $group = $this->groupRepo->find($groupId);
        $user = Auth::user();

        if ($this->groupRepo->isMember($group, $user->id)) {
            return back()->with('error', 'Bạn đã là thành viên của nhóm này.');
        }
        if ($this->groupRepo->isPending($group, $user->id)) {
            return back()->with('error', 'Bạn đã gửi yêu cầu tham gia nhóm. Vui lòng đợi phê duyệt.');
        }

        if ($group->privacy_setting == 0) { // Private group - need admin approval
            $this->groupRepo->addMember($group, $user->id, 'member', 'pending');
            return back()->with('success', 'Yêu cầu tham gia nhóm đã được gửi. Vui lòng đợi phê duyệt.');
        } else { // Public group - join directly
            $this->groupRepo->addMember($group, $user->id, 'member', 'active');
            return back()->with('success', 'Bạn đã tham gia nhóm thành công!');
        }
    }

    public function leave($groupId)
    {
        $group = $this->groupRepo->find($groupId);
        $user = Auth::user();

        if (!$this->groupRepo->isMember($group, $user->id)) {
            return back()->with('error', 'Bạn không phải là thành viên của nhóm này.');
        }

        $this->groupRepo->removeMember($group, $user->id);

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

    public function showPendingRequests(Group $group)
    {
        $isMember = $this->groupRepo->isMember($group, Auth::id());
        $isPending = $this->groupRepo->isPending($group, Auth::id());
        $isAdmin = $group->isAdmin(Auth::id());

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
            'pendingRequests' => $pendingRequests,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function approveMember(Group $group, $userId)
    {
        $group->members()->updateExistingPivot($userId, [
            'membership_status' => 'active',
            'role' => 'member'
        ]);
        return redirect()->back();
    }

    public function rejectMember(Group $group, $userId)
    {
        $group->members()->detach($userId);
        return redirect()->back();
    }

    public function showPendingPosts(Group $group)
    {
        $isMember = $this->groupRepo->isMember($group, Auth::id());
        $isPending = $this->groupRepo->isPending($group, Auth::id());
        $isAdmin = $group->isAdmin(Auth::id());

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
            'pendingPosts' => $pendingPosts,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function approvePost(Group $group, $postId)
    {
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
        $post = $group->posts()->findOrFail($postId);
        $post->update([
            'privacy_setting' => 'rejected',
            'updated_at' => now(),
        ]);
        return redirect()->back()->with('success', 'Bài viết đã bị từ chối!');
    }

    public function edit(Group $group)
    {
        $isMember = $this->groupRepo->isMember($group, Auth::id());
        $isPending = $this->groupRepo->isPending($group, Auth::id());
        $isAdmin = $group->isAdmin(Auth::id());

        return Inertia::render('Groups/Admin/GroupUpdate', [
            'group' => $group,
            'isMember' => $isMember,
            'isPending' => $isPending,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'privacy_setting' => 'required|boolean',
            'post_approval_required' => 'required|boolean',
            'cover_photo_url' => 'nullable|image|max:2048',
        ]);

        $this->groupRepo->updateGroup($group, $request->only([
            'name', 'description', 'privacy_setting', 'post_approval_required'
        ]), $request->file('cover_photo_url'));

        return redirect()->route('groups.edit', $group->id)->with('success', 'Cập nhật nhóm thành công!');
    }

    public function myPosts(Group $group)
    {
        $user = Auth::user();

        $myPosts = $group->posts()
            ->where('user_id', $user->id)
            ->with(['user', 'media', 'likes', 'comments'])
            ->latest()
            ->get();

        $isMember = $this->groupRepo->isMember($group, $user->id);
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
        $members = $this->groupRepo->getMembers($group);
        $isMember = $this->groupRepo->isMember($group, $user->id);
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
        if ($user->id == $userId) {
            return back()->with('error', 'Không thể tự xóa mình!');
        }
        $this->groupRepo->removeMember($group, $userId);
        return back()->with('success', 'Đã xóa thành viên khỏi nhóm!');
    }

    public function destroy(Group $group)
    {
        $user = Auth::user();
        $isAdmin = $group->members()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'admin')
            ->exists();

        if (!$isAdmin && $group->creator_id != $user->id) {
            return back()->with('error', 'Bạn không có quyền xóa nhóm này!');
        }

        $this->groupRepo->deleteGroup($group);

        return redirect()->route('group')->with('success', 'Đã xóa nhóm thành công!');
    }

    public function makeAdmin(Group $group, $userId)
    {
        $currentUser = Auth::user();
        $isAdmin = $group->members()
            ->where('user_id', $currentUser->id)
            ->wherePivot('role', 'admin')
            ->wherePivot('membership_status', 'active')
            ->exists();

        if (!$isAdmin) {
            return back()->with('error', 'Bạn không có quyền thực hiện thao tác này!');
        }

        $member = $group->members()->where('user_id', $userId)->first();
        if (!$member) {
            return back()->with('error', 'Thành viên không tồn tại trong nhóm!');
        }

        $group->members()->updateExistingPivot($userId, [
            'role' => 'admin'
        ]);

        return back()->with('success', 'Đã cấp quyền admin thành công!');
    }
}
