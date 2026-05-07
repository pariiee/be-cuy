<?php

namespace App\Http\Controllers\Api;

use App\Mail\SendOtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone'    => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone'    => $validated['phone'] ?? null,
        ]);

        $otp = OtpCode::generateFor($user);

        try {
            Mail::to($user->email)->send(new SendOtpMail($user, $otp));
        } catch (\Throwable $e) {
            $user->delete();
            return $this->error('Gagal mengirim email OTP. Coba daftar kembali.', 500);
        }

        return $this->success([
            'email' => $user->email,
        ], 'Registrasi berhasil. Silakan cek email Anda untuk kode OTP verifikasi.', 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (is_null($user->email_verified_at)) {
            return $this->error('Email Anda belum diverifikasi. Silakan cek email untuk kode OTP.', 403);
        }

        if ($user->isBanned()) {
            return $this->error('Akun Anda telah dibanned. Hubungi admin untuk informasi lebih lanjut.', 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'Login successful');
    }

    /**
     * POST /api/verify-otp
     *
     * Verify email OTP after registration.
     * Body: { "email": "...", "otp": "123456" }
     */
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return $this->error('Email tidak ditemukan.', 404);
        }

        if (! is_null($user->email_verified_at)) {
            return $this->error('Email sudah terverifikasi sebelumnya.', 422);
        }

        $otpRecord = OtpCode::where('user_id', $user->id)
            ->where('purpose', 'email_verification')
            ->where('code', $validated['otp'])
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $otpRecord) {
            return $this->error('Kode OTP tidak valid.', 422);
        }

        if ($otpRecord->isExpired()) {
            return $this->error('Kode OTP sudah kadaluarsa. Minta kirim ulang.', 422);
        }

        $otpRecord->update(['used_at' => now()]);
        $user->update(['email_verified_at' => now()]);

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user'  => $user->fresh(),
            'token' => $token,
        ], 'Email berhasil diverifikasi. Selamat datang!');
    }

    /**
     * POST /api/resend-otp
     *
     * Resend OTP to email (rate limited: max 3x per 10 minutes per email).
     * Body: { "email": "..." }
     */
    public function resendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $rateLimitKey = 'resend-otp:' . $validated['email'];

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return $this->error("Terlalu banyak permintaan. Coba lagi dalam {$seconds} detik.", 429);
        }

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return $this->error('Email tidak ditemukan.', 404);
        }

        if (! is_null($user->email_verified_at)) {
            return $this->error('Email sudah terverifikasi.', 422);
        }

        RateLimiter::hit($rateLimitKey, 600);

        $otp = OtpCode::generateFor($user);

        try {
            Mail::to($user->email)->send(new SendOtpMail($user, $otp));
        } catch (\Throwable $e) {
            return $this->error('Gagal mengirim email. Coba beberapa saat lagi.', 500);
        }

        return $this->success(['email' => $user->email], 'Kode OTP baru telah dikirim ke email Anda.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return $this->success([
            'id'                => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'phone'             => $user->phone,
            'role'              => $user->role,
            'balance'           => (float) $user->balance,
            'is_banned'         => (bool) $user->is_banned,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'created_at'        => $user->created_at->toIso8601String(),
        ]);
    }

    /**
     * PUT /api/me
     *
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
        ]);

        $user->update($validated);

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'balance' => (float) $user->balance,
        ], 'Profil berhasil diperbarui');
    }

    /**
     * PUT /api/me/password
     *
     * Change password (requires current password).
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->error('Password saat ini salah', 422);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return $this->success(null, 'Password berhasil diubah');
    }

    /**
     * POST /api/forgot-password
     *
     * Send OTP to email for password reset (rate limited: max 3x per 10 min).
     * Body: { "email": "..." }
     */
    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $rateLimitKey = 'forgot-password:' . $validated['email'];

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return $this->error("Terlalu banyak permintaan. Coba lagi dalam {$seconds} detik.", 429);
        }

        $user = User::where('email', $validated['email'])->first();

        // Always respond success to avoid email enumeration
        if (! $user) {
            return $this->success(null, 'Jika email terdaftar, kode OTP akan dikirim.');
        }

        RateLimiter::hit($rateLimitKey, 600);

        $otp = OtpCode::generateFor($user, 'password_reset');

        try {
            Mail::to($user->email)->send(new SendOtpMail($user, $otp));
        } catch (\Throwable $e) {
            return $this->error('Gagal mengirim email. Coba beberapa saat lagi.', 500);
        }

        return $this->success(null, 'Kode OTP reset password telah dikirim ke email Anda.');
    }

    /**
     * POST /api/reset-password
     *
     * Verify OTP and set new password in one step.
     * Body: { "email": "...", "otp": "123456", "password": "...", "password_confirmation": "..." }
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'otp'      => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return $this->error('Email tidak ditemukan.', 404);
        }

        $otpRecord = OtpCode::where('user_id', $user->id)
            ->where('purpose', 'password_reset')
            ->where('code', $validated['otp'])
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $otpRecord) {
            return $this->error('Kode OTP tidak valid.', 422);
        }

        if ($otpRecord->isExpired()) {
            return $this->error('Kode OTP sudah kadaluarsa. Minta kirim ulang.', 422);
        }

        $otpRecord->update(['used_at' => now()]);

        $user->update([
            'password'       => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ]);

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        return $this->success(null, 'Password berhasil direset. Silakan login kembali.');
    }
}
