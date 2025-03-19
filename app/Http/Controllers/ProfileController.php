<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Friendship;

class ProfileController extends Controller
{
    public function edit()
    {
        return Inertia::render('Profile/Edit');
    }
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:10',
            'birthday' => 'required|date',
        ]);

        $user = auth()->user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->birthday = $request->birthday;
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Cập nhật thông tin thành công!');
    }
    public function index($username)
    {
        $user = User::where('username', $username)->first();

            return Inertia::render('Profile/ListPost', [
                'user' => $user,
            ]);
    }


}
