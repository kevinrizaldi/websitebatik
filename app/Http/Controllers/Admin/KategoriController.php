<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KategoriController extends Controller
{
    /**
     * Display a listing of product categories.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));

        $kategorisQuery = Kategori::withCount('produks');

        if ($search !== '') {
            $kategorisQuery->where(function ($q) use ($search) {
                $q->where('nama_kategori', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $kategoris = $kategorisQuery->latest()->paginate(10)->withQueryString();
        $totalKategori = Kategori::count();
        $totalProduk = Produk::count();

        return view('admin.kategori.index', compact('kategoris', 'search', 'totalKategori', 'totalProduk'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:30', 'regex:/^[\pL\s]+$/u', 'unique:kategoris,nama_kategori'],
            'deskripsi' => 'nullable|string|max:1000',
            'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max' => 'Nama kategori maksimal 30 karakter.',
            'nama_kategori.regex' => 'Nama kategori hanya boleh berupa huruf dan spasi tanpa angka atau simbol.',
            'nama_kategori.unique' => 'Kategori dengan nama tersebut sudah ada.',
            'gambar.image' => 'File yang diunggah harus berupa gambar.',
            'gambar.mimes' => 'Format gambar kategori harus jpeg, png, jpg, atau webp.',
            'gambar.max' => 'Ukuran gambar maksimal 3MB.',
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('kategori', 'public');
        }

        Kategori::create([
            'nama_kategori' => $validated['nama_kategori'],
            'slug' => Str::slug($validated['nama_kategori']),
            'deskripsi' => $validated['deskripsi'] ?? null,
            'gambar' => $gambarPath,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', "Kategori \"{$validated['nama_kategori']}\" berhasil ditambahkan.");
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:30', 'regex:/^[\pL\s]+$/u', 'unique:kategoris,nama_kategori,'.$kategori->id],
            'deskripsi' => 'nullable|string|max:1000',
            'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max' => 'Nama kategori maksimal 30 karakter.',
            'nama_kategori.regex' => 'Nama kategori hanya boleh berupa huruf dan spasi tanpa angka atau simbol.',
            'nama_kategori.unique' => 'Kategori dengan nama tersebut sudah ada.',
            'gambar.image' => 'File yang diunggah harus berupa gambar.',
            'gambar.mimes' => 'Format gambar kategori harus jpeg, png, jpg, atau webp.',
            'gambar.max' => 'Ukuran gambar maksimal 3MB.',
        ]);

        $data = [
            'nama_kategori' => $validated['nama_kategori'],
            'slug' => Str::slug($validated['nama_kategori']),
            'deskripsi' => $validated['deskripsi'] ?? null,
        ];

        if ($request->hasFile('gambar')) {
            if ($kategori->gambar && Storage::disk('public')->exists($kategori->gambar)) {
                Storage::disk('public')->delete($kategori->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('kategori', 'public');
        } elseif ($request->boolean('hapus_gambar')) {
            if ($kategori->gambar && Storage::disk('public')->exists($kategori->gambar)) {
                Storage::disk('public')->delete($kategori->gambar);
            }
            $data['gambar'] = null;
        }

        $kategori->update($data);

        return redirect()->route('admin.kategori.index')->with('success', "Kategori \"{$kategori->nama_kategori}\" berhasil diperbarui.");
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Kategori $kategori): RedirectResponse
    {
        $nama = $kategori->nama_kategori;

        if ($kategori->gambar && Storage::disk('public')->exists($kategori->gambar)) {
            Storage::disk('public')->delete($kategori->gambar);
        }

        $kategori->delete();

        return redirect()->route('admin.kategori.index')->with('success', "Kategori \"{$nama}\" berhasil dihapus.");
    }
}
