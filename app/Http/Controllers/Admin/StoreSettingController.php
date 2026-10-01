<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreSettingController extends Controller
{
    /**
     * Display the store origin settings page.
     */
    public function index(): View
    {
        $setting = StoreSetting::current();

        return view('admin.pengaturan.index', compact('setting'));
    }

    /**
     * Update the store origin settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_toko' => 'required|string|max:255',
            'kota_asal' => 'required|string|max:255',
            'provinsi_asal' => 'required|string|max:255',
            'kode_pos_asal' => 'nullable|string|max:20',
            'alamat_asal' => 'required|string|max:1000',
            'no_telepon_toko' => 'nullable|string|max:30',
            'email_toko' => 'nullable|email|max:255',
            'deskripsi_toko' => 'nullable|string|max:1000',
        ]);

        $setting = StoreSetting::current();
        $setting->update($validated);

        return redirect()->route('admin.pengaturan.index')->with('success', 'Pengaturan kota & asal toko pengiriman berhasil disimpan.');
    }
}
