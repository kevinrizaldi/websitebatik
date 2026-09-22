<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ulasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UlasanController extends Controller
{
    /**
     * Menampilkan daftar kelola ulasan pelanggan
     */
    public function index(Request $request): View
    {
        $query = Ulasan::with('produk')->latest();

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter rating bintang
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Pencarian nama pelanggan, komentar, atau nama produk
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%")
                  ->orWhereHas('produk', function ($p) use ($search) {
                      $p->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $ulasans = $query->paginate(10)->withQueryString();

        // Hitungan ringkasan statistik
        $allCount = Ulasan::count();
        $avgRating = $allCount > 0 ? round(Ulasan::avg('rating'), 1) : 0;

        $counts = [
            'all' => $allCount,
            'menunggu' => Ulasan::where('status', 'Menunggu')->count(),
            'disetujui' => Ulasan::where('status', 'Disetujui')->count(),
            'ditolak' => Ulasan::where('status', 'Ditolak')->count(),
            'avg_rating' => $avgRating,
        ];

        return view('admin.ulasans.index', compact('ulasans', 'counts'));
    }

    /**
     * Memperbarui status moderasi ulasan (Disetujui, Menunggu, Ditolak)
     */
    public function updateStatus(Request $request, Ulasan $ulasan): RedirectResponse
    {
        // Jika ulasan sudah disetujui, status tidak boleh diubah lagi
        if ($ulasan->status === 'Disetujui') {
            return back()->withErrors([
                'error' => "Status ulasan dari {$ulasan->customer_name} sudah Disetujui dan tidak dapat diubah.",
            ]);
        }

        try {
            $validated = $request->validate([
                'status' => ['required', 'in:Disetujui,Ditolak'],
            ], [
                'status.required' => 'Harap pilih tindakan moderasi terlebih dahulu.',
                'status.in' => 'Tindakan moderasi tidak valid. Pilih Disetujui atau Ditolak.',
            ]);

            $ulasan->update([
                'status' => $validated['status'],
            ]);

            return back()->with('success', "Status ulasan dari {$ulasan->customer_name} berhasil diubah menjadi {$validated['status']}.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui status ulasan: ' . $e->getMessage()]);
        }
    }

    /**
     * Menghapus ulasan pelanggan
     */
    public function destroy(Ulasan $ulasan): RedirectResponse
    {
        try {
            $customerName = $ulasan->customer_name;
            $ulasan->delete();

            return back()->with('success', "Ulasan dari {$customerName} berhasil dihapus.");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Gagal menghapus ulasan: ' . $e->getMessage()]);
        }
    }
}
