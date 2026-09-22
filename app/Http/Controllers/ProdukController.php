<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    /**
     * Menampilkan semua produk
     */
    public function index()
    {
        $produks = Produk::latest()->paginate(5);

        return view('produk.index', compact('produks'));
    }

    /**
     * Form tambah produk
     */
    public function create()
    {
        return view('produk.create');
    }

    /**
     * Menyimpan produk baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:produks,sku',
            'kategori' => 'required|string|max:100',
            'harga' => 'required|numeric|min:40000|max:10000000',
            'stok' => 'required|integer|min:0',
            'status' => 'required|in:Tersedia,Stok Menipis,Habis',
            'deskripsi' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] =
                $request->file('gambar')->store('produk', 'public');
        }

        Produk::create($validated);

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail produk
     */
    public function show(Produk $produk)
    {
        return view('produk.show', compact('produk'));
    }

    /**
     * Form edit produk
     */
    public function edit(Produk $produk)
    {
        return view('produk.edit', compact('produk'));
    }

    /**
     * Update produk
     */
    public function update(Request $request, Produk $produk)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:produks,sku,' . $produk->id,
            'kategori' => 'required|string|max:100',
            'harga' => 'required|numeric|min:40000',
            'stok' => 'required|integer|min:0',
            'status' => 'required|in:Tersedia,Stok Menipis,Habis',
            'deskripsi' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('gambar')) {

            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }

            $validated['gambar'] =
                $request->file('gambar')->store('produk', 'public');
        }

        $produk->update($validated);

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Hapus produk
     */
    public function destroy(Produk $produk)
    {
        if ($produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
        }

        $produk->delete();

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
