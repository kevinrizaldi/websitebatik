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
        if ($request->has('kode_pos') && trim((string) $request->input('kode_pos')) === '') {
            $request->merge(['kode_pos' => null]);
        }
        if ($request->has('provinsi') && trim((string) $request->input('provinsi')) === '') {
            $request->merge(['provinsi' => null]);
        }
        if ($request->has('label_alamat') && trim((string) $request->input('label_alamat')) === '') {
            $request->merge(['label_alamat' => null]);
        }

        $validated = $request->validate([
            'label_alamat' => ['nullable', 'string', 'max:100', 'regex:/^[\pL0-9\s]+$/u'],
            'penerima' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'.]+$/u'],
            'no_telepon' => ['required', 'string', 'regex:/^[0-9+\- ]{10,20}$/'],
            'alamat_lengkap' => ['required', 'string', 'max:1000', 'regex:/^[\pL0-9\s.,\/\-]+$/u'],
            'kota' => ['required', 'string', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'provinsi' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'kode_pos' => ['nullable', 'string', 'regex:/^[0-9]{5}$/'],
            'is_utama' => 'nullable|boolean',
        ], [
            'penerima.required' => 'Nama penerima wajib diisi.',
            'penerima.regex' => 'Nama penerima hanya boleh berupa huruf, spasi, titik, atau tanda petik.',
            'label_alamat.regex' => 'Label alamat hanya boleh berupa huruf, angka, dan spasi.',
            'no_telepon.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'no_telepon.regex' => 'Nomor WhatsApp / HP harus berupa angka 10 sampai 15 digit.',
            'alamat_lengkap.required' => 'Alamat lengkap wajib diisi.',
            'alamat_lengkap.regex' => 'Alamat lengkap tidak boleh mengandung simbol khusus yang tidak wajar.',
            'kota.required' => 'Kabupaten / Kota wajib diisi.',
            'kota.regex' => 'Kabupaten / Kota hanya boleh berupa huruf dan spasi.',
            'provinsi.regex' => 'Provinsi hanya boleh berupa huruf dan spasi.',
            'kode_pos.regex' => 'Kode pos harus berupa 5 digit angka.',
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

        if ($request->has('kode_pos') && trim((string) $request->input('kode_pos')) === '') {
            $request->merge(['kode_pos' => null]);
        }
        if ($request->has('provinsi') && trim((string) $request->input('provinsi')) === '') {
            $request->merge(['provinsi' => null]);
        }
        if ($request->has('label_alamat') && trim((string) $request->input('label_alamat')) === '') {
            $request->merge(['label_alamat' => null]);
        }

        $validated = $request->validate([
            'label_alamat' => ['nullable', 'string', 'max:100', 'regex:/^[\pL0-9\s]+$/u'],
            'penerima' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'.]+$/u'],
            'no_telepon' => ['required', 'string', 'regex:/^[0-9+\- ]{10,20}$/'],
            'alamat_lengkap' => ['required', 'string', 'max:1000', 'regex:/^[\pL0-9\s.,\/\-]+$/u'],
            'kota' => ['required', 'string', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'provinsi' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'kode_pos' => ['nullable', 'string', 'regex:/^[0-9]{5}$/'],
            'is_utama' => 'nullable|boolean',
        ], [
            'penerima.required' => 'Nama penerima wajib diisi.',
            'penerima.regex' => 'Nama penerima hanya boleh berupa huruf, spasi, titik, atau tanda petik.',
            'label_alamat.regex' => 'Label alamat hanya boleh berupa huruf, angka, dan spasi.',
            'no_telepon.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'no_telepon.regex' => 'Nomor WhatsApp / HP harus berupa angka 10 sampai 15 digit.',
            'alamat_lengkap.required' => 'Alamat lengkap wajib diisi.',
            'alamat_lengkap.regex' => 'Alamat lengkap tidak boleh mengandung simbol khusus yang tidak wajar.',
            'kota.required' => 'Kabupaten / Kota wajib diisi.',
            'kota.regex' => 'Kabupaten / Kota hanya boleh berupa huruf dan spasi.',
            'provinsi.regex' => 'Provinsi hanya boleh berupa huruf dan spasi.',
            'kode_pos.regex' => 'Kode pos harus berupa 5 digit angka.',
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
