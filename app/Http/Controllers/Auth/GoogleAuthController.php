<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            return redirect()->route('login')->with('error', 'Fitur Login Google belum aktif karena GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET belum diisi di file .env.');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google and authenticate them.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // 1. Cari user berdasarkan google_id
            $user = User::where('google_id', $googleUser->getId())->first();

            if (! $user) {
                // 2. Jika tidak ada berdasarkan google_id, cari berdasarkan email
                $user = User::where('email', $googleUser->getEmail())->first();

                if ($user) {
                    // Hubungkan akun yang sudah ada dengan google_id
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'avatar' => $user->avatar ?: $googleUser->getAvatar(),
                    ]);
                } else {
                    // 3. Buat user baru
                    $user = User::create([
                        'name' => $googleUser->getName() ?: 'Pelanggan Google',
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                    ]);
                }
            }

            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            Auth::login($user, remember: true);
            request()->session()->regenerate();

            if ($user->isAdmin()) {
                return redirect()->intended(route('dashboard', absolute: false));
            }

            return redirect()->intended('/');
        } catch (Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal masuk dengan Google. Silakan pastikan akun dan koneksi Google Anda aktif.',
            ]);
        }
    }
}
