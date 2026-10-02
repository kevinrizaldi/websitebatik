<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
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

        if ($user->isAdmin()) {
            return view('profile.edit-admin', ['user' => $user]);
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
