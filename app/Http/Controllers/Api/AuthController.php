<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

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
