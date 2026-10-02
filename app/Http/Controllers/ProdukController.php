<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProdukController extends Controller
{
    /**
     * Menampilkan semua produk
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'stok_menipis' => 'nullable|in:1',
            'stok_habis' => 'nullable|in:1',
        ]);
        $search = trim($validated['search'] ?? '');
        $lowStockOnly = $request->boolean('stok_menipis');
        $outOfStockOnly = $request->boolean('stok_habis');

        $produks = Produk::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    foreach (['nama', 'sku', 'kategori', 'jenis_produk', 'deskripsi', 'material', 'ukuran', 'status'] as $column) {
                        $query->orWhere($column, 'like', '%'.$search.'%');
                    }
                });
            })
            ->when($lowStockOnly, fn (Builder $query): Builder => $query->whereBetween('stok', [1, Produk::LOW_STOCK_THRESHOLD]))
            ->when($outOfStockOnly, fn (Builder $query): Builder => $query->where('stok', '<=', 0))
            ->latest()
            ->paginate(5)
            ->withQueryString();
        $lowStockCount = Produk::whereBetween('stok', [1, Produk::LOW_STOCK_THRESHOLD])->count();
        $outOfStockCount = Produk::where('stok', '<=', 0)->count();

        return view('produk.index', compact('produks', 'search', 'lowStockOnly', 'lowStockCount', 'outOfStockOnly', 'outOfStockCount'));
    }

    /**
     * Form tambah produk
     */
    public function create()
    {
        $kategoris = Schema::hasTable('kategoris') ? Kategori::orderBy('nama_kategori')->get() : collect();

        return view('produk.create', compact('kategoris'));
    }

    /**
     * Menyimpan produk baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:produks,nama|regex:/^[a-zA-Z0-9\s]+$/',
            'sku' => 'required|string|max:50|unique:produks,sku',
            'kategori' => 'required|string|max:100',
            'kategori_id' => 'nullable|exists:kategoris,id',
            'harga' => 'required|numeric|min:0|max:1000000',
            'stok' => 'nullable|integer|min:0|max:1000',
            'stok_ukuran' => 'nullable|array',
            'stok_ukuran.*' => 'nullable|integer|min:0|max:1000',
            'deskripsi' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gambar_lainnya' => 'nullable|array|max:9',
            'gambar_lainnya.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nama.unique' => 'Nama produk sudah digunakan, tidak boleh sama.',
            'nama.regex' => 'Nama produk tidak boleh menggunakan simbol.',
            'harga.required' => 'Harga produk wajib diisi.',
            'harga.numeric' => 'Harga produk harus berupa angka.',
            'harga.min' => 'Harga produk tidak boleh minus (minimal Rp 0).',
            'harga.max' => 'Harga produk maksimal adalah Rp 1.000.000.',
            'stok.integer' => 'Stok produk harus berupa bilangan bulat.',
            'stok.min' => 'Stok produk tidak boleh minus (minimal 0).',
            'stok.max' => 'Stok produk maksimal adalah 1.000 unit.',
        ]);

        if ($request->has('stok_ukuran') && is_array($request->input('stok_ukuran'))) {
            $filtered = [];
            $totalStok = 0;
            foreach ($request->input('stok_ukuran') as $size => $val) {
                if ($val !== null && $val !== '') {
                    $intVal = max(0, (int) $val);
                    $filtered[(string) $size] = $intVal;
                    $totalStok += $intVal;
                }
            }
            if (! empty($filtered)) {
                $validated['stok_ukuran'] = $filtered;
                $validated['stok'] = $totalStok;
                $validated['ukuran'] = implode(', ', array_keys($filtered));
            }
        }

        $validated['stok'] = (int) ($validated['stok'] ?? 0);
        $validated['status'] = Produk::statusForStock((int) $validated['stok']);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] =
                $request->file('gambar')->store('produk', 'public');
        }

        // Handle gambar tambahan (multiple)
        if ($request->hasFile('gambar_lainnya')) {
            $paths = [];
            foreach ($request->file('gambar_lainnya') as $file) {
                $paths[] = $file->store('produk', 'public');
            }
            $validated['gambar_lainnya'] = $paths;
        }

        if (! empty($validated['kategori_id']) && Schema::hasTable('kategoris')) {
            $kategori = Kategori::find($validated['kategori_id']);
            if ($kategori) {
                $validated['kategori'] = $kategori->nama_kategori;
            }
        } elseif (Schema::hasTable('kategoris')) {
            $kategori = Kategori::where('nama_kategori', $validated['kategori'])->first();
            if ($kategori) {
                $validated['kategori_id'] = $kategori->id;
            }
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
        $kategoris = Schema::hasTable('kategoris') ? Kategori::orderBy('nama_kategori')->get() : collect();

        return view('produk.edit', compact('produk', 'kategoris'));
    }

    /**
     * Update produk
     */
    public function update(Request $request, Produk $produk)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:produks,nama,'.$produk->id.'|regex:/^[a-zA-Z0-9\s]+$/',
            'sku' => 'required|string|max:50|unique:produks,sku,'.$produk->id,
            'kategori' => 'required|string|max:100',
            'kategori_id' => 'nullable|exists:kategoris,id',
            'harga' => 'required|numeric|min:0|max:1000000',
            'stok' => 'nullable|integer|min:0|max:1000',
            'stok_ukuran' => 'nullable|array',
            'stok_ukuran.*' => 'nullable|integer|min:0|max:1000',
            'deskripsi' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gambar_lainnya' => 'nullable|array|max:9',
            'gambar_lainnya.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'hapus_gambar' => 'nullable|array',
            'hapus_gambar.*' => 'nullable|string',
        ], [
            'nama.unique' => 'Nama produk sudah digunakan, tidak boleh sama.',
            'nama.regex' => 'Nama produk tidak boleh menggunakan simbol.',
            'harga.required' => 'Harga produk wajib diisi.',
            'harga.numeric' => 'Harga produk harus berupa angka.',
            'harga.min' => 'Harga produk tidak boleh minus (minimal Rp 0).',
            'harga.max' => 'Harga produk maksimal adalah Rp 1.000.000.',
            'stok.integer' => 'Stok produk harus berupa bilangan bulat.',
            'stok.min' => 'Stok produk tidak boleh minus (minimal 0).',
            'stok.max' => 'Stok produk maksimal adalah 1.000 unit.',
        ]);

        if ($request->has('stok_ukuran') && is_array($request->input('stok_ukuran'))) {
            $filtered = [];
            $totalStok = 0;
            foreach ($request->input('stok_ukuran') as $size => $val) {
                if ($val !== null && $val !== '') {
                    $intVal = max(0, (int) $val);
                    $filtered[(string) $size] = $intVal;
                    $totalStok += $intVal;
                }
            }
            if (! empty($filtered)) {
                $validated['stok_ukuran'] = $filtered;
                $validated['stok'] = $totalStok;
                $validated['ukuran'] = implode(', ', array_keys($filtered));
            } else {
                $validated['stok_ukuran'] = null;
            }
        } else {
            $validated['stok_ukuran'] = null;
        }

        $validated['stok'] = (int) ($validated['stok'] ?? $produk->stok);
        $validated['status'] = Produk::statusForStock((int) $validated['stok']);

        if ($request->hasFile('gambar')) {

            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }

            $validated['gambar'] =
                $request->file('gambar')->store('produk', 'public');
        }

        // Handle hapus gambar tambahan yang dipilih
        $existingOthers = $produk->gambar_lainnya ?? [];
        if ($request->filled('hapus_gambar')) {
            foreach ($request->input('hapus_gambar') as $pathToDelete) {
                Storage::disk('public')->delete($pathToDelete);
                $existingOthers = array_values(array_filter(
                    $existingOthers,
                    fn ($p) => $p !== $pathToDelete
                ));
            }
        }

        // Handle upload gambar tambahan baru
        if ($request->hasFile('gambar_lainnya')) {
            foreach ($request->file('gambar_lainnya') as $file) {
                $existingOthers[] = $file->store('produk', 'public');
            }
        }

        $validated['gambar_lainnya'] = ! empty($existingOthers) ? $existingOthers : null;

        if (! empty($validated['kategori_id']) && Schema::hasTable('kategoris')) {
            $kategori = Kategori::find($validated['kategori_id']);
            if ($kategori) {
                $validated['kategori'] = $kategori->nama_kategori;
            }
        } elseif (Schema::hasTable('kategoris')) {
            $kategori = Kategori::where('nama_kategori', $validated['kategori'])->first();
            if ($kategori) {
                $validated['kategori_id'] = $kategori->id;
            }
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
