<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's profile details.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
        ], [
            'name.regex' => 'Nama tidak boleh menggunakan simbol.',
        ]);

        $user->fill($validated);
        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'role' => $user->role ?? 'user',
            ],
        ]);
    }

    /**
     * List the authenticated user's saved addresses.
     */
    public function addresses(Request $request): JsonResponse
    {
        $addresses = $request->user()
            ->addresses()
            ->orderByDesc('is_primary')
            ->latest()
            ->get()
            ->map(fn (Address $addr): array => $this->formatAddress($addr))
            ->values();

        return response()->json([
            'data' => $addresses,
        ]);
    }

    /**
     * Create a new address for the authenticated user.
     */
    public function storeAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'full_address' => ['required', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $isPrimary = $request->has('is_primary')
            ? $request->boolean('is_primary')
            : ($user->addresses()->count() === 0);

        if ($isPrimary) {
            $user->addresses()->update(['is_primary' => false, 'is_utama' => false]);
        }

        $address = $user->addresses()->create([
            'label' => $validated['label'] ?: 'Rumah',
            'recipient_name' => $validated['recipient_name'],
            'phone' => $validated['phone'],
            'full_address' => $validated['full_address'],
            'city' => $validated['city'] ?: 'Indonesia',
            'province' => $validated['province'] ?: 'Indonesia',
            'postal_code' => $validated['postal_code'] ?: '',
            'is_primary' => $isPrimary,
        ]);

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan.',
            'data' => $this->formatAddress($address),
        ], 201);
    }

    /**
     * Update an existing address owned by the authenticated user.
     */
    public function updateAddress(Request $request, Address $address): JsonResponse
    {
        if ((int) $address->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $validated = $request->validate([
            'label' => ['sometimes', 'string', 'max:100'],
            'recipient_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'full_address' => ['sometimes', 'string', 'max:1000'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'province' => ['sometimes', 'nullable', 'string', 'max:100'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:10'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        if ($request->boolean('is_primary')) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update([
                'is_primary' => false,
                'is_utama' => false,
            ]);
        }

        $address->fill($validated);
        $address->save();

        return response()->json([
            'message' => 'Alamat berhasil diperbarui.',
            'data' => $this->formatAddress($address),
        ]);
    }

    /**
     * Delete an address owned by the authenticated user.
     */
    public function destroyAddress(Request $request, Address $address): JsonResponse
    {
        if ((int) $address->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $wasPrimary = (bool) ($address->is_primary ?? $address->is_utama);
        $address->delete();

        if ($wasPrimary) {
            $next = $request->user()->addresses()->latest()->first();
            if ($next) {
                $next->is_primary = true;
                $next->save();
            }
        }

        return response()->json([
            'message' => 'Alamat berhasil dihapus.',
        ]);
    }

    private function formatAddress(Address $addr): array
    {
        return [
            'id' => $addr->id,
            'label' => $addr->label ?: $addr->label_alamat ?: 'Rumah',
            'recipient_name' => $addr->recipient_name ?: $addr->penerima,
            'phone' => $addr->phone ?: $addr->no_telepon,
            'full_address' => $addr->full_address ?: $addr->alamat_lengkap,
            'city' => $addr->city ?: $addr->kota,
            'province' => $addr->province ?: $addr->provinsi,
            'postal_code' => $addr->postal_code ?: $addr->kode_pos,
            'is_primary' => (bool) ($addr->is_primary ?? $addr->is_utama),
        ];
    }
}

