<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Group;

class GroupController extends Controller
{
    public function index()
    {
        return Inertia::render('Groups/Groups');
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

        Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'privacy_setting' => $request->privacy_setting,
            'post_approval_required' => $request->post_approval_required,
            'cover_photo_url' => $filename,
            'creator_id' => auth()->id(),
        ]);

        return back()->with('success', 'Tạo nhóm thành công!');
    }

    public function show($groupId)
    {
        $group = Group::findOrFail($groupId);
        return Inertia::render('Groups/GroupDetail', [
            'group' => $group,
        ]);
    }

}
