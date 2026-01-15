<?php

namespace App\Http\Controllers;

use App\Mail\VerificationCodeMail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class VerifyEmailController extends Controller
{
    public function notice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        return Inertia::render('Auth/VerifyEmail', [
            'email' => $request->user()->email,
        ]);
    }

    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home')->with('status', 'Email already verified.');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->route('home')->with('status', 'Email verified successfully.');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ], [
            'code.required' => 'Vui lòng nhập mã xác thực.',
            'code.digits' => 'Mã xác thực phải gồm 6 chữ số.',
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Email đã được xác thực. Vui lòng đăng nhập.');
        }

        if (!$user->verification_code || !$user->verification_code_expires_at) {
            return back()->withErrors(['code' => 'Chưa có mã xác thực. Vui lòng gửi lại.']);
        }

        if (Carbon::now()->gt($user->verification_code_expires_at)) {
            return back()->withErrors(['code' => 'Mã xác thực đã hết hạn. Vui lòng gửi lại.']);
        }

        if (!hash_equals($user->verification_code, $request->code)) {
            return back()->withErrors(['code' => 'Mã xác thực không đúng. Vui lòng nhập lại.']);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->forceFill([
                'email_verified_at' => Carbon::now(),
                'verification_code' => null,
                'verification_code_expires_at' => null,
            ])->save();

            event(new Verified($user));
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Xác thực thành công. Vui lòng đăng nhập.');
    }

    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('status', 'Email đã được xác thực.');
        }

        $otpCode = sprintf('%06d', random_int(0, 999999));
        $request->user()->forceFill([
            'verification_code' => $otpCode,
            'verification_code_expires_at' => Carbon::now()->addMinutes(3),
        ])->save();

        Mail::to($request->user()->email)->send(new VerificationCodeMail($otpCode));

        return back()->with('status', 'Đã gửi lại mã xác thực.');
    }
}
