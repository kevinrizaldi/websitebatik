<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StokController extends Controller
{
    /**
     * Tampilkan halaman Manajemen Stok & Inventaris Sirkular
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $kategoriFilter = $request->input('kategori');
        $statusFilter = $request->input('status', 'semua');

        $query = Produk::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('material', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if ($kategoriFilter && $kategoriFilter !== 'Semua Kategori') {
            $query->where('kategori', $kategoriFilter);
        }

        if ($statusFilter === 'aman') {
            $query->where('stok', '>', 5);
        } elseif ($statusFilter === 'menipis') {
            $query->whereBetween('stok', [1, 5]);
        } elseif ($statusFilter === 'habis') {
            $query->where('stok', '<=', 0);
        }

        $produks = $query->latest()->paginate(10)->withQueryString();

        // Hitung metrik ringkasan
        $allProducts = Produk::all();
        $totalItemFisik = (int) $allProducts->sum('stok');
        $totalSku = $allProducts->count();
        $stokAmanCount = $allProducts->where('stok', '>', 5)->count();
        $stokMenipisCount = $allProducts->whereBetween('stok', [1, 5])->count();
        $stokHabisCount = $allProducts->where('stok', '<=', 0)->count();

        // Kategori list
        $kategorisList = Schema::hasTable('categories')
            ? Kategori::orderBy('nama_kategori')->get()
            : collect();

        return view('admin.stok.index', compact(
            'produks',
            'search',
            'kategoriFilter',
            'statusFilter',
            'totalItemFisik',
            'totalSku',
            'stokAmanCount',
            'stokMenipisCount',
            'stokHabisCount',
            'kategorisList'
        ));
    }
}
