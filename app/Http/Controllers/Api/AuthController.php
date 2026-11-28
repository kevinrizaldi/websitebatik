<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use App\Notifications\ResetPasswordOtpNotification;
use App\Services\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new customer and issue a Sanctum API token.
     *
     * Response carries the canonical `{data: {user, token}}` wrapper while
     * keeping legacy top-level `token`/`user` keys for the current Flutter app.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;
        $formatted = $this->formatUser($user);

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'data' => [
                'user' => $formatted,
                'token' => $token,
            ],
            'token' => $token,
            'user' => $formatted,
        ], 201);
    }

    /**
     * Authenticate a user and issue a Sanctum API token.
     *
     * Response carries the canonical `{data: {user, token}}` wrapper while
     * keeping legacy top-level `token`/`user` keys for the current Flutter app.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', strtolower($validated['email']))->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            $message = 'Email atau kata sandi salah.';

            return response()->json([
                'message' => $message,
                'errors' => [
                    'email' => [$message],
                ],
            ], 422);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;
        $formatted = $this->formatUser($user);

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'user' => $formatted,
                'token' => $token,
            ],
            'token' => $token,
            'user' => $formatted,
        ]);
    }

    /**
     * Authenticate a user via a Google ID token and issue a Sanctum token.
     *
     * Response carries the canonical `{data: {user, token}}` wrapper while
     * keeping legacy top-level `token`/`user` keys for the current Flutter app.
     */
    public function google(Request $request, GoogleAuthService $googleAuth): JsonResponse
    {
        $result = $googleAuth->authenticate($request->input('id_token'));

        if ($result === null) {
            $message = 'Token Google tidak valid atau sudah kadaluarsa.';

            return response()->json([
                'message' => $message,
                'errors' => [
                    'id_token' => [$message],
                ],
            ], 401);
        }

        $token = $result['token'];
        $formatted = $this->formatUser($result['user']);

        return response()->json([
            'message' => 'Login dengan Google berhasil.',
            'data' => [
                'user' => $formatted,
                'token' => $token,
            ],
            'token' => $token,
            'user' => $formatted,
        ], 200);
    }

    /**
     * Return the currently authenticated user.
     *
     * Canonical path is GET /api/me; GET /api/user remains an alias for Flutter.
     */
    public function me(Request $request): JsonResponse
    {
        $formatted = $this->formatUser($request->user());

        return response()->json([
            'data' => ['user' => $formatted],
            'user' => $formatted,
        ]);
    }

    /**
     * Revoke the current access token.
     */
    public function logout(Request $request): Response
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->noContent();
    }

    /**
     * Send a 6-digit OTP to the given user via email (mobile flow).
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::where('email', strtolower($request->email))->first();

        if ($user) {
            // Cegah kirim ulang OTP sebelum 60 detik sejak OTP terakhir dikirim
            $existing = DB::table('password_reset_otps')
                ->where('email', $user->email)
                ->where('used', false)
                ->where('created_at', '>=', now()->subSeconds(60))
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'Mohon tunggu 1 menit sebelum mengirim ulang kode OTP.',
                    'errors'  => [
                        'email' => ['Mohon tunggu 1 menit sebelum mengirim ulang kode OTP.'],
                    ],
                ], 429);
            }

            // Hapus OTP lama untuk email ini
            DB::table('password_reset_otps')
                ->where('email', $user->email)
                ->delete();

            // Buat OTP 6 digit baru
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

        // Selalu return sukses (keamanan: tidak bocorkan apakah email terdaftar)
        return response()->json([
            'message' => 'Jika email terdaftar, kode OTP telah dikirim ke email Anda.',
        ]);
    }

    /**
     * Validate a 6-digit OTP and return a password reset token (mobile flow).
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'otp'   => ['required', 'string', 'size:6'],
        ]);

        $record = DB::table('password_reset_otps')
            ->where('email', strtolower($request->email))
            ->where('otp', $request->otp)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) {
            return response()->json([
                'message' => 'Kode OTP tidak valid atau sudah kadaluarsa.',
                'errors'  => [
                    'otp' => ['Kode OTP tidak valid atau sudah kadaluarsa.'],
                ],
            ], 422);
        }

        // Tandai OTP sudah dipakai
        DB::table('password_reset_otps')
            ->where('email', strtolower($request->email))
            ->update(['used' => true, 'updated_at' => now()]);

        // Buat Laravel password reset token untuk dipakai di langkah selanjutnya
        $user  = User::where('email', strtolower($request->email))->first();
        $token = Password::createToken($user);

        return response()->json([
            'message' => 'OTP valid. Silakan buat password baru.',
            'token'   => $token,
            'email'   => $user->email,
        ]);
    }

    /**
     * Reset the given user's password using the token received in email.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user) use ($validated) {
                $user->forceFill([
                    'password' => Hash::make($validated['password']),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru Anda.',
            ]);
        }

        return response()->json([
            'message' => __($status),
            'errors' => [
                'email' => [__($status)],
            ],
        ], 422);
    }

    private function formatUser(User $user): array
    {
        $activeOrdersCount = $user->orders()->whereIn('status', [
            'Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Verifikasi', 'Menunggu Konfirmasi',
            'Diproses', 'Sudah Dibayar', 'Dikirim', 'sedang dikirim', 'sedang diproses',
        ])->count();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'jenis_kelamin' => $user->jenis_kelamin,
            'tanggal_lahir' => $user->tanggal_lahir,
            'avatar' => $user->avatar,
            'role' => $user->role ?? 'user',
            'active_orders_count' => $activeOrdersCount,
            'pesanan_aktif' => $activeOrdersCount,
        ];
    }
}
