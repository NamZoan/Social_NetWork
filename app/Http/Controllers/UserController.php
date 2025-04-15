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
        if (Auth::check()) {
            return redirect(url()->previous());
        }

        return Inertia::render('Auth/Login');
    }
    public function register()
    {
        if (Auth::check()) {
            return redirect(url()->previous());
        }

        return Inertia::render('Auth/Register');
    }
   
        public function store(RegisterRequest $request): RedirectResponse
    {
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

        // Trường hợp lỗi, trả về trang đăng ký với thông báo lỗi
        return redirect()->route('register')->with('error', 'Đăng ký không thành công. Vui lòng thử lại.');
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
    // Lấy thông tin người dùng hiện tại
    $user = auth()->user();

    // Kiểm tra nếu người dùng chưa đăng nhập
    if (!$user) {
        return response()->json([
            'message' => 'Người dùng chưa đăng nhập.',
        ], 401);
    }

    // Xác thực dữ liệu đầu vào
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'username' => 'required|string|max:255|unique:users,username,' . $user->id,
        'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        'phone' => 'nullable|string|max:15',
        'birthday' => 'nullable|date',
    ]);

    // Cập nhật thông tin người dùng
    try {
        $user->update($validatedData);

        return response()->json([
            'message' => 'Thông tin tài khoản đã được cập nhật thành công!',
            'user' => $user,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Đã xảy ra lỗi khi cập nhật thông tin tài khoản.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
