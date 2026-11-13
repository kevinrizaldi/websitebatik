<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class GoogleAuthService
{
    /**
     * Verify a Google ID token and resolve the user, then issue a Sanctum token.
     *
     * Resolves the user by google_id first, then falls back to email. Creates
     * a new user when the Google account has never signed in before.
     *
     * @return array{user: User, token: string}|null Null when the token is missing or invalid.
     */
    public function authenticate(?string $idToken): ?array
    {
        if (blank($idToken)) {
            return null;
        }

        $payload = $this->verifyIdToken($idToken);

        if ($payload === null) {
            return null;
        }

        $user = User::query()
            ->where('google_id', $payload['sub'])
            ->orWhere('email', strtolower($payload['email']))
            ->first();

        if ($user === null) {
            $user = User::create([
                'name' => $payload['name'] ?? 'Pelanggan Google',
                'email' => strtolower($payload['email']),
                'google_id' => $payload['sub'],
                'avatar' => $payload['picture'] ?? null,
            ]);

            $user->markEmailAsVerified();
        } elseif (blank($user->google_id)) {
            // Link an existing (e.g. email/password registered) account to Google.
            $user->update([
                'google_id' => $payload['sub'],
                'avatar' => $user->avatar ?: ($payload['picture'] ?? null),
            ]);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Verify a Google ID token (JWT) via Google's tokeninfo endpoint.
     *
     * @return array<string, mixed>|null
     */
    public function verifyIdToken(string $idToken): ?array
    {
        $response = Http::timeout(5)
            ->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

        if ($response->failed()) {
            return null;
        }

        $payload = $response->json();

        if (! is_array($payload) || empty($payload['sub']) || empty($payload['email'])) {
            return null;
        }

        if (filter_var($payload['email_verified'] ?? true, FILTER_VALIDATE_BOOL) === false) {
            return null;
        }

        $audience = $payload['aud'] ?? null;
        $clientId = config('services.google.client_id');

        if ($clientId && $audience && $audience !== $clientId) {
            return null;
        }

        return $payload;
    }
}
