<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ulasan;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UlasanController extends Controller
{
    /**
     * Halaman daftar ulasan milik customer yang sedang masuk.
     */
    public function index(): View
    {
        $user = Auth::user();
        $ulasans = $user->ulasans()->with('produk')->latest()->get();
        $alamatCount = $user->alamats()->count();
        $pesananAktif = $user->orders()->whereIn('status', [
            'Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Konfirmasi',
            'Diproses', 'Sudah Dibayar', 'Menunggu Verifikasi', 'Dikirim',
        ])->count();

        return view('ulasan.index', compact('user', 'ulasans', 'alamatCount', 'pesananAktif'));
    }

    /**
     * Store customer product review.
     * PRD: ulasan hanya dapat diberikan setelah pesanan selesai (diterima)
     * dan produk tersebut ada dalam pesanan yang selesai.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'order_code' => 'nullable|string|exists:orders,code',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:3|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ], [
            'image.image' => 'File ulasan harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, JPEG, PNG, GIF, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 5 MB.',
        ]);

        $user = Auth::user();

        if (! $user) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan masuk terlebih dahulu untuk memberi ulasan.',
                ], 401);
            }

            return redirect()->route('login')->with('info', 'Silakan masuk terlebih dahulu untuk memberi ulasan.');
        }

        $deniedMessage = 'Ulasan hanya dapat diberikan setelah Anda menyelesaikan pembelian produk ini (pesanan berstatus Selesai).';

        // Tentukan pembelian (order Selesai) yang menjadi dasar ulasan ini.
        $targetOrder = null;

        if (! empty($validated['order_code'])) {
            $order = Order::with('items')->where('code', $validated['order_code'])->first();

            if (! $order || (int) $order->user_id !== (int) $user->id) {
                abort(403, 'Akses tidak diizinkan.');
            }

            if ($order->status !== 'Selesai') {
                return $this->denyReview($request, $deniedMessage);
            }

            if (! $order->items->contains('produk_id', (int) $validated['produk_id'])) {
                return $this->denyReview($request, 'Produk tersebut tidak ada dalam pesanan ini.');
            }

            $targetOrder = $order;
        } else {
            $targetOrder = Order::where('user_id', $user->id)
                ->where('status', 'Selesai')
                ->whereHas('items', function ($query) use ($validated): void {
                    $query->where('produk_id', $validated['produk_id']);
                })
                ->latest()
                ->first();

            if (! $targetOrder) {
                return $this->denyReview($request, $deniedMessage);
            }
        }

        // Satu pembelian (order × produk) hanya boleh diulas satu kali.
        // Beli lagi produk yang sama pada order lain = boleh ulas lagi.
        $alreadyReviewed = Ulasan::where('user_id', $user->id)
            ->where('produk_id', $validated['produk_id'])
            ->where('order_id', $targetOrder->id)
            ->exists();

        if ($alreadyReviewed) {
            return $this->denyReview($request, 'Anda sudah memberi ulasan untuk produk ini pada pesanan #'.$targetOrder->code.'.');
        }

        $imagePath = isset($validated['image'])
            ? $validated['image']->store('ulasan', 'public')
            : null;

        try {
            $ulasan = Ulasan::create([
                'order_id' => $targetOrder->id,
                'produk_id' => $validated['produk_id'],
                'user_id' => $user->id,
                'customer_name' => $user->name ?? 'Pelanggan Batik',
                'rating' => (int) $validated['rating'],
                'comment' => $validated['comment'],
                'image_path' => $imagePath,
                'status' => 'Menunggu',
            ]);
        } catch (QueryException $exception) {
            report($exception);

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            // Pelanggaran unique (user_id, produk_id): ulasan ganda.
            if ($exception->getCode() === '23000') {
                return $this->denyReview($request, 'Anda sudah pernah memberi ulasan untuk produk ini.');
            }

            $errorMessage = 'Ulasan belum berhasil dikirim. Silakan coba lagi beberapa saat.';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 500);
            }

            return back()->with('error', $errorMessage);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Terima kasih! Ulasan Anda menunggu persetujuan admin sebelum ditampilkan.',
                'ulasan' => $ulasan,
            ]);
        }

        return back()->with('success', 'Terima kasih! Ulasan Anda menunggu persetujuan admin sebelum ditampilkan.');
    }

    /**
     * Ubah ulasan milik sendiri selama masih Menunggu moderasi.
     */
    public function update(Request $request, Ulasan $ulasan): JsonResponse|RedirectResponse
    {
        if ((int) $ulasan->user_id !== (int) Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        if ($ulasan->status !== 'Menunggu') {
            return $this->denyReview($request, 'Ulasan yang sudah dimoderasi admin tidak dapat diubah.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:3|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ], [
            'image.image' => 'File ulasan harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, JPEG, PNG, GIF, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 5 MB.',
        ]);

        $payload = [
            'rating' => (int) $validated['rating'],
            'comment' => $validated['comment'],
        ];

        if (! empty($validated['image'])) {
            if ($ulasan->image_path) {
                Storage::disk('public')->delete($ulasan->image_path);
            }
            $payload['image_path'] = $validated['image']->store('ulasan', 'public');
        }

        $ulasan->update($payload);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ulasan berhasil diperbarui dan menunggu persetujuan admin.',
                'ulasan' => $ulasan->fresh(),
            ]);
        }

        return back()->with('success', 'Ulasan berhasil diperbarui dan menunggu persetujuan admin.');
    }

    /**
     * Hapus ulasan milik sendiri selama masih Menunggu moderasi.
     */
    public function destroy(Request $request, Ulasan $ulasan): JsonResponse|RedirectResponse
    {
        if ((int) $ulasan->user_id !== (int) Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        if ($ulasan->status !== 'Menunggu') {
            return $this->denyReview($request, 'Ulasan yang sudah dimoderasi admin tidak dapat dihapus.');
        }

        if ($ulasan->image_path) {
            Storage::disk('public')->delete($ulasan->image_path);
        }
        $ulasan->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ulasan berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Ulasan berhasil dihapus.');
    }

    /**
     * Tolak ulasan yang belum memenuhi syarat pembelian selesai.
     */
    protected function denyReview(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->with('error', $message);
    }
}
