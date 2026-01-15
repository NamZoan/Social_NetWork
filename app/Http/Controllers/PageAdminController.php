<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageAdminController extends Controller
{
    public function store(Request $request, Page $page)
    {
        $this->authorizeManage($page);

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:' . implode(',', PageAdmin::roles()),
        ]);

        $page->admins()->syncWithoutDetaching([
            $data['user_id'] => ['role' => $data['role']],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm/quy định quyền admin cho trang.',
        ]);
    }

    public function update(Request $request, Page $page, User $user)
    {
        $this->authorizeManage($page);

        $data = $request->validate([
            'role' => 'required|in:' . implode(',', PageAdmin::roles()),
        ]);

        if (!$page->admins()->where('user_id', $user->id)->exists()) {
            abort(404, 'Người dùng không phải admin của trang.');
        }

        $page->admins()->updateExistingPivot($user->id, ['role' => $data['role']]);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật quyền cho admin.',
        ]);
    }

    public function destroy(Page $page, User $user)
    {
        $this->authorizeManage($page);

        $page->admins()->detach($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Đã xoá admin khỏi trang.',
        ]);
    }

    private function authorizeManage(Page $page): void
    {
        $userId = Auth::id();
        if (!$page->canManageAdmins($userId)) {
            abort(403, 'Bạn không có quyền quản trị admin trang này.');
        }
    }
}
