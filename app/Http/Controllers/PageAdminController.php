<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageAdminController extends Controller
{
    /**
     * Thêm hoặc cập nhật quyền admin cho page
     */
    public function store(Request $request, Page $page)
    {
        $this->authorizeManage($page);

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:' . implode(',', PageAdmin::roles()),
        ]);

        // Kiểm tra xem user đó có tồn tại không
        $user = User::findOrFail($data['user_id']);

        // Thêm hoặc cập nhật admin
        $page->admins()->syncWithoutDetaching([
            $data['user_id'] => ['role' => $data['role']],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã thêm {$user->name} làm quản trị viên.",
            'admin' => $user->load('pageAdmins')
        ]);
    }

    /**
     * Cập nhật quyền cho admin
     */
    public function update(Request $request, Page $page, User $user)
    {
        $this->authorizeManage($page);

        $data = $request->validate([
            'role' => 'required|in:' . implode(',', PageAdmin::roles()),
        ]);

        // Kiểm tra xem user có phải admin của page không
        if (!$page->admins()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không phải admin của trang.'
            ], 404);
        }

        // Cập nhật quyền
        $page->admins()->updateExistingPivot($user->id, ['role' => $data['role']]);

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật quyền cho {$user->name}.",
            'role' => $data['role']
        ]);
    }

    /**
     * Xóa admin khỏi page
     */
    public function destroy(Page $page, User $user)
    {
        $this->authorizeManage($page);

        // Kiểm tra xem user có phải admin không
        if (!$page->admins()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không phải admin của trang.'
            ], 404);
        }

        // Kiểm tra xem có ít nhất 1 admin còn lại không
        if ($page->admins()->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Trang phải có ít nhất 1 quản trị viên.'
            ], 422);
        }

        // Xóa admin
        $page->admins()->detach($user->id);

        return response()->json([
            'success' => true,
            'message' => "Đã xóa {$user->name} khỏi quản trị viên."
        ]);
    }

    /**
     * Kiểm tra quyền quản lý admins
     */
    private function authorizeManage(Page $page): void
    {
        $userId = Auth::id();
        
        // Chỉ admin mới có thể quản lý admins
        if (!$page->canManageAdmins($userId)) {
            abort(403, 'Bạn không có quyền quản lý admin của trang này.');
        }
    }
}
