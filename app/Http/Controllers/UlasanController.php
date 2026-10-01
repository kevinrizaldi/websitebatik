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

class UlasanController extends Controller
{
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
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ], [
            'image.required' => 'Foto ulasan wajib diunggah sebelum ulasan dikirim.',
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
        } else {
            $hasCompletedPurchase = Order::where('user_id', $user->id)
                ->where('status', 'Selesai')
                ->whereHas('items', function ($query) use ($validated): void {
                    $query->where('produk_id', $validated['produk_id']);
                })
                ->exists();

            if (! $hasCompletedPurchase) {
                return $this->denyReview($request, $deniedMessage);
            }
        }

        $imagePath = isset($validated['image'])
            ? $validated['image']->store('ulasan', 'public')
            : null;

        try {
            $ulasan = Ulasan::create([
                'produk_id' => $validated['produk_id'],
                'user_id' => $user->id,
                'customer_name' => $user->name ?? 'Pelanggan Batik',
                'rating' => (int) $validated['rating'],
                'comment' => $validated['comment'],
                'image_path' => $imagePath,
                'status' => 'Disetujui',
            ]);
        } catch (QueryException $exception) {
            report($exception);

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
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
                'message' => 'Terima kasih atas ulasan Anda!',
                'ulasan' => $ulasan,
            ]);
        }

        return back()->with('success', 'Terima kasih atas ulasan dan apresiasi Anda terhadap karya batik kami!');
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
