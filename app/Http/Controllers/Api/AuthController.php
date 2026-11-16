<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Register a new customer and issue a Sanctum API token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.regex' => 'Nama tidak boleh menggunakan simbol.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 201);
    }

    /**
     * Authenticate a user and issue a Sanctum API token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', strtolower($validated['email']))->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau kata sandi salah.',
            ], 401);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Authenticate or register a user using Google Sign-In.
     */
    public function googleLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['nullable', 'string'],
            'email' => ['required', 'string', 'email'],
            'google_id' => ['nullable', 'string'],
            'name' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'string', 'max:1000'],
        ]);

        $googleEmail = strtolower(trim($validated['email']));
        $googleId = $validated['google_id'] ?? null;
        $name = $validated['name'] ?? 'Pelanggan Google';
        $avatar = $validated['avatar'] ?? null;

        // If an id_token is provided, verify it directly with Google
        if (! empty($validated['id_token'])) {
            try {
                $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $validated['id_token'],
                ]);

                if ($response->successful()) {
                    $tokenInfo = $response->json();
                    if (! empty($tokenInfo['email'])) {
                        $googleEmail = strtolower(trim($tokenInfo['email']));
                    }
                    if (! empty($tokenInfo['sub'])) {
                        $googleId = $tokenInfo['sub'];
                    }
                    if (! empty($tokenInfo['name'])) {
                        $name = $tokenInfo['name'];
                    }
                    if (! empty($tokenInfo['picture'])) {
                        $avatar = $tokenInfo['picture'];
                    }
                }
            } catch (\Throwable $e) {
                // If Google tokeninfo is unreachable, fall back to validated request values
            }
        }

        // 1. Find user by google_id or by email
        $user = null;
        if ($googleId) {
            $user = User::where('google_id', $googleId)->first();
        }
        if (! $user) {
            $user = User::where('email', $googleEmail)->first();
        }

        if ($user) {
            $user->update([
                'google_id' => $googleId ?: $user->google_id,
                'avatar' => $user->avatar ?: $avatar,
            ]);
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $googleEmail,
                'google_id' => $googleId,
                'avatar' => $avatar,
                'password' => Hash::make(Str::random(32)),
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login Google berhasil.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Return the currently authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    /**
     * Revoke the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'role' => $user->role ?? 'user',
        ];
    }
}

