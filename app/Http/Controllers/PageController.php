<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PageController extends Controller
{
    
    /**
     * Cập nhật thông tin Page (admin/editor).
     */
    public function update(Request $request, Page $page)
    {
        $user = Auth::user();

        if (!$page->canUpdateBy($user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:100|unique:pages,username,' . $page->id,
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Profile picture
        if ($request->hasFile('profile_picture')) {
            if (!empty($page->profile_picture_url)) {
                $old = public_path($page->profile_picture_url);
                if (File::exists($old)) {
                    File::delete($old);
                }
            }

            $dir = public_path('images/client/pages/profile_pictures');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0775, true);
            }

            $file = $request->file('profile_picture');
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $validated['profile_picture_url'] = 'images/client/pages/profile_pictures/' . $filename;
        }

        // Cover photo
        if ($request->hasFile('cover_photo')) {
            if (!empty($page->cover_photo_url)) {
                $old = public_path($page->cover_photo_url);
                if (File::exists($old)) {
                    File::delete($old);
                }
            }

            $dir = public_path('images/client/pages/cover_photos');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0775, true);
            }

            $file = $request->file('cover_photo');
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $validated['cover_photo_url'] = 'images/client/pages/cover_photos/' . $filename;
        }

        $page->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trang thành công.',
                'page' => $page->fresh(),
            ]);
        }

        return back()->with('success', 'Cập nhật trang thành công.');
    }
}
