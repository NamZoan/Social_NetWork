<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login()
    {
        if (auth()->check()) {
            return redirect(url()->previous());
        }

        return Inertia::render('Auth/Login');
    }
    public function register()
    {
        if (auth()->check()) {
            return redirect(url()->previous());
        }

        return Inertia::render('Auth/Register');
    }
    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Đảm bảo username và email là duy nhất
        if (User::where('username', $request->username)->exists()) {
            return back()->withErrors(['username' => 'Username đã tồn tại.'])->withInput();
        }
        if (User::where('email', $request->email)->exists()) {
            return back()->withErrors(['email' => 'Email đã tồn tại.'])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'birthday' => $request->day . '-' . $request->month . '-' . $request->year,
        ]);
        if ($user) {
            return to_route('login')->with('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
        }
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);


        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/')->with('success', 'Đăng nhập thành công!');
        }

        throw ValidationException::withMessages([
            'password' => 'Email hoặc mật khẩu không đúng.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return Inertia::location(route('login'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'birthday' => 'nullable|string|max:20',
            'avatar' => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $user = auth()->user();
        $file = $request->file('avatar');
        $filename = uniqid('avatar_') . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('images/client/avatar'), $filename);

        // Xóa ảnh cũ nếu cần
        if ($user->avatar && file_exists(public_path('images/client/avatar/' . $user->avatar))) {
            @unlink(public_path('images/client/avatar/' . $user->avatar));
        }

        $user->avatar = $filename;
        $user->save();

        return back()->with('success', 'Cập nhật ảnh đại diện thành công!');
    }
}
