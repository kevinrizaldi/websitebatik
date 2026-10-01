<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AlamatController extends Controller
{
    /**
     * Halaman kelola alamat + API daftar alamat untuk checkout.
     * Menyesuaikan tampilan storefront yang sudah ada bila diakses via browser.
     */
    public function index(): JsonResponse|View
    {
        $user = Auth::user();
        $alamats = $user->alamats()->latest()->get();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'alamats' => $alamats,
            ]);
        }

        $pesananAktif = $user->orders()->whereIn('status', [
            'Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Konfirmasi',
            'Diproses', 'Sudah Dibayar', 'Menunggu Verifikasi', 'Dikirim',
        ])->count();
        $ulasanCount = $user->ulasans()->count();

        return view('alamat.index', compact('alamats', 'user', 'pesananAktif', 'ulasanCount'));
    }

    /**
     * Store new address.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'label_alamat' => 'nullable|string|max:100',
            'penerima' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:30',
            'alamat_lengkap' => 'required|string|max:1000',
            'kota' => 'required|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'kode_pos' => 'nullable|string|max:20',
            'is_utama' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        // If this is the user's first address, or marked as utama, adjust others
        $isUtama = ! empty($validated['is_utama']) || $user->alamats()->count() === 0;

        if ($isUtama) {
            $user->alamats()->update(['is_utama' => false]);
        }

        $alamat = $user->alamats()->create([
            'label_alamat' => $validated['label_alamat'] ?: 'Rumah',
            'penerima' => $validated['penerima'],
            'no_telepon' => $validated['no_telepon'],
            'alamat_lengkap' => $validated['alamat_lengkap'],
            'kota' => $validated['kota'],
            'provinsi' => $validated['provinsi'] ?? 'Indonesia',
            'kode_pos' => $validated['kode_pos'] ?? '',
            'is_utama' => $isUtama,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil disimpan.',
                'alamat' => $alamat,
            ]);
        }

        return back()->with('success', 'Alamat berhasil disimpan.');
    }

    /**
     * Update address.
     */
    public function update(Request $request, Alamat $alamat): JsonResponse|RedirectResponse
    {
        if ($alamat->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'label_alamat' => 'nullable|string|max:100',
            'penerima' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:30',
            'alamat_lengkap' => 'required|string|max:1000',
            'kota' => 'required|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'kode_pos' => 'nullable|string|max:20',
            'is_utama' => 'nullable|boolean',
        ]);

        if (! empty($validated['is_utama'])) {
            Auth::user()->alamats()->where('id', '!=', $alamat->id)->update(['is_utama' => false]);
        }

        $alamat->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil diperbarui.',
                'alamat' => $alamat,
            ]);
        }

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    /**
     * Set address as primary.
     */
    public function setDefault(Alamat $alamat): JsonResponse|RedirectResponse
    {
        if ($alamat->user_id !== Auth::id()) {
            abort(403);
        }

        Auth::user()->alamats()->update(['is_utama' => false]);
        $alamat->update(['is_utama' => true]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat utama berhasil diubah.',
            ]);
        }

        return back()->with('success', 'Alamat utama berhasil diubah.');
    }

    /**
     * Delete address.
     */
    public function destroy(Alamat $alamat): JsonResponse|RedirectResponse
    {
        if ($alamat->user_id !== Auth::id()) {
            abort(403);
        }

        $alamat->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Alamat berhasil dihapus.');
    }
}
