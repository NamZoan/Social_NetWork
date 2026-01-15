<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class PasswordResetController extends Controller
{
    public function requestForm()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email không tồn tại.']);
        }

        $otpCode = sprintf('%06d', random_int(0, 999999));
        $user->forceFill([
            'password_reset_code' => $otpCode,
            'password_reset_code_expires_at' => Carbon::now()->addMinutes(3),
        ])->save();

        Mail::to($user->email)->send(new PasswordResetOtpMail($otpCode));

        return redirect()
            ->route('password.reset', ['email' => $user->email])
            ->with('status', 'Đã gửi mã OTP đến email của bạn.');
    }

    public function resetForm(Request $request)
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'code.required' => 'Vui lòng nhập mã OTP.',
            'code.digits' => 'Mã OTP phải gồm 6 chữ số.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password_confirmation.required' => 'Vui lòng nhập xác nhận mật khẩu.',
            'password_confirmation.same' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email không tồn tại.']);
        }

        if (!$user->password_reset_code || !$user->password_reset_code_expires_at) {
            return back()->withErrors(['code' => 'Chưa có mã OTP. Vui lòng gửi lại mã.']);
        }

        if (Carbon::now()->gt($user->password_reset_code_expires_at)) {
            return back()->withErrors(['code' => 'Mã OTP đã hết hạn. Vui lòng gửi lại mã.']);
        }

        if (!hash_equals($user->password_reset_code, $request->code)) {
            return back()->withErrors(['code' => 'Mã OTP không đúng. Vui lòng nhập lại.']);
        }

        $user->forceFill([
            'password' => $request->password,
            'password_reset_code' => null,
            'password_reset_code_expires_at' => null,
        ])->save();

        return redirect()
            ->route('login')
            ->with('success', 'Đổi mật khẩu thành công. Vui lòng đăng nhập.');
    }
}
