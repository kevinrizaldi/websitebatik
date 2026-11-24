<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordOtpNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset OTP request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if ($user) {
            // Cek cooldown 60 detik untuk kirim ulang OTP
            $existing = DB::table('password_reset_otps')
                ->where('email', $user->email)
                ->where('used', false)
                ->where('created_at', '>=', now()->subSeconds(60))
                ->first();

            if ($existing) {
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => 'Mohon tunggu 1 menit sebelum meminta kode OTP baru.']);
            }

            // Hapus OTP lama yang belum terpakai untuk email ini
            DB::table('password_reset_otps')
                ->where('email', $user->email)
                ->delete();

            // Generate 6 digit OTP baru
            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('password_reset_otps')->insert([
                'email'      => $user->email,
                'otp'        => $otp,
                'expires_at' => now()->addMinutes(10),
                'used'       => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->notify(new ResetPasswordOtpNotification($otp));
        }

        // Simpan email ke session untuk tahap verifikasi OTP
        session(['reset_password_email' => $email]);

        return redirect()->route('password.otp')
            ->with('status', 'Kode OTP 6 digit telah dikirim ke email Anda.');
    }

    /**
     * Tampilkan halaman formulir input kode OTP di website.
     */
    public function showOtpForm(): View|RedirectResponse
    {
        $email = session('reset_password_email');

        if (! $email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Silakan masukkan email Anda terlebih dahulu.']);
        }

        return view('auth.verify-otp', compact('email'));
    }

    /**
     * Validasi kode OTP yang dimasukkan user di website.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp'   => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
            'email' => ['nullable', 'string', 'email'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size'     => 'Kode OTP harus berupa 6 digit angka.',
            'otp.regex'    => 'Kode OTP hanya boleh berisi angka.',
        ]);

        $email = strtolower(trim($request->input('email') ?? session('reset_password_email') ?? ''));

        if (! $email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Sesi reset password berakhir. Silakan masukkan email kembali.']);
        }

        $record = DB::table('password_reset_otps')
            ->where('email', $email)
            ->where('otp', $request->otp)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) {
            return back()->withInput()->withErrors([
                'otp' => 'Kode OTP tidak valid atau sudah kadaluarsa. Silakan periksa kembali atau kirim ulang.',
            ]);
        }

        // Tandai OTP telah digunakan
        DB::table('password_reset_otps')
            ->where('email', $email)
            ->update([
                'used'       => true,
                'updated_at' => now(),
            ]);

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Pengguna tidak ditemukan.']);
        }

        // Buat token reset password resmi Laravel
        $token = Password::createToken($user);

        session([
            'password_reset_token'          => $token,
            'password_reset_verified_email' => $user->email,
        ]);

        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);
    }
}
