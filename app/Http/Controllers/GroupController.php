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

        $coverPath = null;
        if ($request->hasFile('cover_photo_url')) {
            $coverPath = $request->file('cover_photo_url')->store('group_covers', 'public');
        }

        Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'privacy_setting' => $request->privacy_setting,
            'post_approval_required' => $request->post_approval_required,
            'cover_photo_url' => $coverPath,
            'creator_id' => auth()->id(),
        ]);

        return back()->with('success', 'Tạo nhóm thành công!');
    }
}
