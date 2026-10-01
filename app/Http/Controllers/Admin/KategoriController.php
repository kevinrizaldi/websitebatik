<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KategoriController extends Controller
{
    /**
     * Display a listing of product categories.
     */
    public function index(): View
    {
        $kategoris = Kategori::withCount('produks')->latest()->paginate(10);

        return view('admin.kategori.index', compact('kategoris'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u', 'unique:kategoris,nama_kategori'],
            'deskripsi' => 'nullable|string|max:1000',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.regex' => 'Nama kategori hanya boleh berupa huruf dan spasi tanpa angka atau simbol.',
            'nama_kategori.unique' => 'Kategori dengan nama tersebut sudah ada.',
        ]);

        Kategori::create([
            'nama_kategori' => $validated['nama_kategori'],
            'slug' => Str::slug($validated['nama_kategori']),
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', "Kategori \"{$validated['nama_kategori']}\" berhasil ditambahkan.");
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u', 'unique:kategoris,nama_kategori,'.$kategori->id],
            'deskripsi' => 'nullable|string|max:1000',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.regex' => 'Nama kategori hanya boleh berupa huruf dan spasi tanpa angka atau simbol.',
            'nama_kategori.unique' => 'Kategori dengan nama tersebut sudah ada.',
        ]);

        $kategori->update([
            'nama_kategori' => $validated['nama_kategori'],
            'slug' => Str::slug($validated['nama_kategori']),
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', "Kategori \"{$kategori->nama_kategori}\" berhasil diperbarui.");
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Kategori $kategori): RedirectResponse
    {
        $nama = $kategori->nama_kategori;
        $kategori->delete();

        return redirect()->route('admin.kategori.index')->with('success', "Kategori \"{$nama}\" berhasil dihapus.");
    }
}
