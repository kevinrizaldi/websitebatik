<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page.
     * Admin memakai halaman gaya dashboard, customer memakai storefront.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // Admin menggunakan layout admin, bukan storefront customer
        if ($user->isAdmin()) {
            return view('profile.admin', compact('user'));
        }

        $alamats = $user->alamats()->latest()->get();
        $alamatUtama = $alamats->firstWhere('is_utama', true) ?: $alamats->first();

        $orderQuery = $user->orders();
        $stats = [
            'selesai' => (clone $orderQuery)->where('status', 'Selesai')->count(),
            'diproses' => (clone $orderQuery)->whereIn('status', ['Diproses', 'Sudah Dibayar', 'Menunggu Verifikasi'])->count(),
            'menunggu' => (clone $orderQuery)->whereIn('status', ['Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Konfirmasi'])->count(),
            'dikirim' => (clone $orderQuery)->where('status', 'Dikirim')->count(),
        ];
        $ulasanCount = $user->ulasans()->count();
        $pesananAktif = $stats['menunggu'] + $stats['diproses'] + $stats['dikirim'];

        return view('profile.edit', [
            'user' => $user,
            'alamats' => $alamats,
            'alamatUtama' => $alamatUtama,
            'stats' => $stats,
            'ulasanCount' => $ulasanCount,
            'pesananAktif' => $pesananAktif,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.required' => 'Pilih foto profil terlebih dahulu.',
            'avatar.image' => 'Foto profil harus berupa gambar.',
            'avatar.mimes' => 'Format foto profil harus JPG, JPEG, PNG, atau WEBP.',
            'avatar.max' => 'Ukuran foto profil maksimal 2 MB.',
        ]);

        $user = $request->user();
        $oldAvatar = $user->avatar;
        $newAvatar = $validated['avatar']->store('avatars/'.$user->id, 'public');

        try {
            $user->avatar = $newAvatar;
            $user->save();
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($newAvatar);

            throw $exception;
        }

        $this->deleteUploadedAvatar($oldAvatar, (int) $user->id);

        return Redirect::route('profile.edit')->with('status', 'avatar-updated');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        $oldAvatar = $user->avatar;
        $user->avatar = null;
        $user->save();

        $this->deleteUploadedAvatar($oldAvatar, (int) $user->id);

        return Redirect::route('profile.edit')->with('status', 'avatar-removed');
    }

    private function deleteUploadedAvatar(?string $avatar, int $userId): void
    {
        $avatarDirectory = 'avatars/'.$userId.'/';

        if ($avatar === null || ! str_starts_with($avatar, $avatarDirectory)) {
            return;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($avatar) && ! $disk->delete($avatar)) {
            throw new \RuntimeException('Foto profil lama tidak dapat dihapus dari penyimpanan.');
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
