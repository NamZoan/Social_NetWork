<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

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
        $user = User::create([
            'name' => $request->name,
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


}
