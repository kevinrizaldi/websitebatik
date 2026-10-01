<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSnapTokenRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Pembayaran;
use App\Services\Midtrans\MidtransService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtransService
    ) {}

    /**
     * Return a Snap token for the authenticated user's order.
     *
     * POST /payment/token
     */
    public function token(CreateSnapTokenRequest $request): JsonResponse
    {
        $order = Order::find($request->validated('order_id'));

        if (! $order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke pesanan ini.'], 403);
        }

        try {
            $snapToken = $this->midtransService->getOrCreateSnapToken($order);
        } catch (InvalidArgumentException $e) {
            Log::warning('Midtrans token: order not payable', [
                'order_id' => $order->id,
                'reason' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Pesanan ini tidak dapat dibayar saat ini.',
            ], 422);
        } catch (Exception $e) {
            Log::error('Midtrans Snap token request failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal menghubungi gateway pembayaran. Silakan coba lagi nanti.',
            ], 502);
        }

        return response()->json([
            'snap_token' => $snapToken,
            'client_key' => config('midtrans.client_key'),
        ]);
    }

    /**
     * Show the payment page for an order.
     * PRD: hanya pemilik pesanan (atau admin) yang boleh membuka halaman ini.
     */
    public function show(string $id): View|RedirectResponse
    {
        $order = Order::with(['items.produk', 'pembayaran', 'pengiriman', 'user'])
            ->where('code', $id)
            ->first();

        if (! $order) {
            return redirect()->route('pesanan.index')->with('error', "Pesanan #{$id} tidak ditemukan.");
        }

        if (! auth()->check()) {
            session()->put('url.intended', route('pembayaran', ['id' => $id]));

            return redirect()->route('login')->with('info', 'Silakan masuk terlebih dahulu untuk melanjutkan pembayaran.');
        }

        if ($order->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        }

        // Get Snap Token
        $legacyService = app(\App\Services\MidtransService::class);

        // Sinkronkan status terbaru dari Midtrans (webhook tak sampai ke localhost).
        $legacyService->refreshFromMidtrans($order);
        $order = $order->fresh(['items.produk', 'pembayaran', 'pengiriman', 'user']) ?? $order;

        $snapData = $legacyService->createSnapTransaction($order);

        return view('pembayaran', [
            'order' => $order,
            'snapToken' => $snapData['token'],
            'snapRedirectUrl' => $snapData['redirect_url'],
            'clientKey' => config('midtrans.client_key'),
            'snapJsUrl' => config('midtrans.snap_js_url'),
        ]);
    }

    /**
     * API to obtain/refresh Snap Token via AJAX.
     * Menerima kode pesanan (ORD-...) agar konsisten dengan route /pembayaran/{id}.
     */
    public function getSnapToken(Request $request, string $id): JsonResponse
    {
        $order = Order::where('code', $id)->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if (! $request->user() || ($order->user_id !== $request->user()->id && ! $request->user()->isAdmin())) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $legacyService = app(\App\Services\MidtransService::class);
        $snapData = $legacyService->createSnapTransaction($order);

        return response()->json([
            'success' => true,
            'snap_token' => $snapData['token'],
            'redirect_url' => $snapData['redirect_url'],
            'client_key' => config('midtrans.client_key'),
        ]);
    }

    /**
     * Payment finish callback page.
     *
     * GET /midtrans/finish
     * Tidak menampilkan halaman lagi: langsung kembali ke daftar pesanan.
     * Status pembayaran tetap dibaca dari database (webhook/sinkronisasi),
     * kecuali mode simulasi Sandbox (mock=1) yang menandai lunas untuk testing.
     */
    public function finish(Request $request): RedirectResponse
    {
        // Midtrans passes the *Midtrans* order_id (midtrans_order_id) as `order_id` query param.
        $midtransOrderId = $request->query('order_id');

        $order = null;
        if ($midtransOrderId) {
            $payment = Payment::where('midtrans_order_id', $midtransOrderId)
                ->with('order')
                ->first();

            if ($payment && $payment->order && $request->user() && $payment->order->user_id === $request->user()->id) {
                $order = $payment->order;
            } elseif (! $payment) {
                $foundOrder = Order::where('code', $midtransOrderId)->first();
                if ($foundOrder && $request->user() && $foundOrder->user_id === $request->user()->id) {
                    $order = $foundOrder;
                }
            }
        }

        if (! $order) {
            return redirect()->route('pesanan.index')
                ->with('info', 'Status transaksi Anda sedang diverifikasi secara otomatis.');
        }

        // Sinkronkan status terbaru dari Midtrans sebelum menampilkan hasil.
        app(\App\Services\MidtransService::class)->refreshFromMidtrans($order);
        $order->refresh();

        // Mode simulasi Sandbox untuk testing: tandai lunas lalu ke pesanan.
        if ($request->query('mock') && in_array($order->payment_status, [null, Order::PAYMENT_PENDING], true)) {
            $order->update(['status' => 'Diproses']);
            Pembayaran::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'metode_pembayaran' => $order->payment_method ?: 'Midtrans (Simulasi Sandbox)',
                    'status_pembayaran' => 'Berhasil',
                    'jumlah_bayar' => $order->total_price,
                    'tanggal_bayar' => now(),
                ]
            );
            $order->getOrInitPengiriman();

            return redirect()->route('pesanan.index')
                ->with('success', "Pembayaran simulasi untuk pesanan #{$order->code} berhasil. Pesanan sedang diproses.");
        }

        if ($order->payment_status === Order::PAYMENT_PAID || in_array($order->status, ['Diproses', 'Dikirim', 'Selesai'], true)) {
            return redirect()->route('pesanan.index')
                ->with('success', "Pembayaran untuk pesanan #{$order->code} berhasil dikonfirmasi. Pesanan Anda sedang diproses.");
        }

        return redirect()->route('pesanan.index')
            ->with('info', "Menunggu konfirmasi pembayaran untuk pesanan #{$order->code}. Status diperbarui otomatis.");
    }

    /**
     * Customer confirms order received — marks order as Selesai.
     */
    public function confirmReceived(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($order->status !== 'Dikirim') {
            return back()->with('error', 'Pesanan tidak dapat dikonfirmasi pada status saat ini.');
        }

        $order->update(['status' => 'Selesai']);
        $order->getOrInitPengiriman()->update(['status_pengiriman' => 'Diterima']);

        return redirect()->route('pesanan.index')
            ->with('success', "Pesanan #{$order->code} telah dikonfirmasi diterima. Terima kasih!");
    }

    /**
     * Customer cancels their own order.
     * Hanya pesanan yang belum dibayar yang boleh dibatalkan customer.
     */
    public function cancelOrder(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! in_array($order->status, ['Menunggu Pembayaran', 'Belum Dibayar'])) {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan pada status ini.');
        }

        DB::transaction(function () use ($order): void {
            // Kembalikan stok produk yang sempat terpotong saat checkout.
            foreach ($order->items as $item) {
                if ($item->produk) {
                    $extractedUkuran = null;
                    if (preg_match('/\((S|M|L|XL|XXL|All Size)\)$/i', (string) $item->produk_name, $m)) {
                        $extractedUkuran = strtoupper($m[1]);
                    }
                    $item->produk->incrementStokForUkuran($extractedUkuran, $item->quantity);
                }
            }

            $order->update(['status' => 'Dibatalkan']);

            if ($order->payment_status === Order::PAYMENT_PENDING) {
                $order->update(['payment_status' => Order::PAYMENT_CANCELLED]);
            }
        });

        return redirect()->route('pesanan.index')
            ->with('success', "Pesanan #{$order->code} berhasil dibatalkan.");
    }
}
