<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * Hiển thị danh sách roles
     */
    public function index()
    {
        // Kiểm tra quyền admin
        if (!Auth::user()->hasRole('administrator')) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $roles = Role::withCount('users')->get();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Hiển thị form tạo role mới
     */
    public function create()
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        return Inertia::render('Admin/Roles/Create');
    }

    /**
     * Lưu role mới
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'display_name' => 'required|string',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $role = Role::create($validated);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Đã tạo vai trò thành công.');
    }

    /**
     * Hiển thị chi tiết role
     */
    public function show(Role $role)
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $role->load('users');

        return Inertia::render('Admin/Roles/Show', [
            'role' => $role,
        ]);
    }

    /**
     * Hiển thị form chỉnh sửa role
     */
    public function edit(Role $role)
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role,
        ]);
    }

    /**
     * Cập nhật role
     */
    public function update(Request $request, Role $role)
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'display_name' => 'required|string',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $role->update($validated);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Đã cập nhật vai trò thành công.');
    }

    /**
     * Xóa role
     */
    public function destroy(Role $role)
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Đã xóa vai trò thành công.');
    }

    /**
     * Gán role cho user
     */
    public function assignRole(Request $request, User $user)
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $user->assignRole($role->name);

        return back()->with('success', "Đã gán vai trò {$role->display_name} cho {$user->name}.");
    }

    /**
     * Xóa role khỏi user
     */
    public function removeRole(Request $request, User $user)
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $user->removeRole($role->name);

        return back()->with('success', "Đã xóa vai trò {$role->display_name} khỏi {$user->name}.");
    }

    /**
     * Lấy danh sách users có role cụ thể
     */
    public function users(Role $role)
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $users = $role->users()->paginate(20);

        return Inertia::render('Admin/Roles/Users', [
            'role' => $role,
            'users' => $users,
        ]);
    }
}

