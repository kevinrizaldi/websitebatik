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

        // Real Sales keseluruhan (dari pesanan non-batal dan bukan belum bayar)
        $totalTerjualKeseluruhan = (int) \App\Models\OrderItem::whereHas('order', function ($q) {
            $q->whereNotIn('status', ['Dibatalkan', 'Belum Dibayar']);
        })->sum('quantity');

        // Real Total Nilai Aset Stok (harga * stok seluruh produk)
        $totalNilaiAset = (float) (Produk::selectRaw('SUM(harga * stok) as total')->value('total') ?? 0);

        // Ambil data penjualan per kategori
        $salesPerCategory = \App\Models\OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', ['Dibatalkan', 'Belum Dibayar'])
            ->selectRaw('products.category_id, products.kategori, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('products.category_id', 'products.kategori')
            ->get();

        // Ambil data stok & aset per kategori
        $stockPerCategory = Produk::query()
            ->selectRaw('category_id, kategori, SUM(stok) as total_stock, SUM(harga * stok) as total_asset')
            ->groupBy('category_id', 'kategori')
            ->get();

        foreach ($kategoris as $kat) {
            $sales = $salesPerCategory->first(function ($s) use ($kat) {
                return ($s->category_id !== null && $s->category_id == $kat->id)
                    || (!empty($s->kategori) && strcasecmp($s->kategori, $kat->nama_kategori) === 0);
            });

            $stock = $stockPerCategory->first(function ($s) use ($kat) {
                return ($s->category_id !== null && $s->category_id == $kat->id)
                    || (!empty($s->kategori) && strcasecmp($s->kategori, $kat->nama_kategori) === 0);
            });

            $kat->real_terjual = (int) ($sales->total_qty ?? 0);
            $kat->real_pendapatan = (float) ($sales->total_revenue ?? 0);
            $kat->real_stok = (int) ($stock->total_stock ?? 0);
            $kat->real_nilai_aset = (float) ($stock->total_asset ?? 0);
        }

        return view('admin.kategori.index', compact('kategoris', 'search', 'totalKategori', 'totalProduk', 'totalTerjualKeseluruhan', 'totalNilaiAset'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:30', 'regex:/^[\pL\s]+$/u', 'unique:categories,nama_kategori'],
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
            'nama_kategori' => ['required', 'string', 'max:30', 'regex:/^[\pL\s]+$/u', 'unique:categories,nama_kategori,'.$kategori->id],
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
