<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Pembayaran;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {}

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
        $snapData = $this->midtransService->createSnapTransaction($order);

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

        $snapData = $this->midtransService->createSnapTransaction($order);

        return response()->json([
            'success' => true,
            'snap_token' => $snapData['token'],
            'redirect_url' => $snapData['redirect_url'],
            'client_key' => config('midtrans.client_key'),
        ]);
    }

    /**
     * Midtrans Webhook Notification Callback.
     */
    public function notification(Request $request): JsonResponse
    {
        $payload = $request->all();
        $order = $this->midtransService->handleNotification($payload);

        if (! $order) {
            return response()->json(['message' => 'Order not found or invalid signature'], 400);
        }

        return response()->json(['status' => 'success', 'order_code' => $order->code]);
    }

    /**
     * Callback when user finishes payment in Midtrans Snap.
     */
    public function finish(Request $request): RedirectResponse
    {
        $orderId = $request->query('order_id') ?? $request->input('order_id');
        $isMock = $request->query('mock');

        if ($orderId) {
            $order = Order::where('code', $orderId)->first();
            if ($order) {
                // If in mock mode or returned from successful payment
                $order->update([
                    'status' => 'Diproses',
                    'payment_method' => $order->payment_method ?: 'Midtrans (QRIS / VA)',
                ]);

                Pembayaran::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'metode_pembayaran' => $order->payment_method ?: 'Midtrans (QRIS / VA)',
                        'status_pembayaran' => 'Berhasil',
                        'jumlah_bayar' => $order->total_price,
                        'tanggal_bayar' => now(),
                    ]
                );

                $order->getOrInitPengiriman();

                return redirect()->route('pesanan.index')
                    ->with('success', "Pembayaran untuk pesanan #{$order->code} berhasil dikonfirmasi secara otomatis via Midtrans! Pesanan Anda kini sedang diproses.");
            }
        }

        return redirect()->route('pesanan.index')
            ->with('info', 'Status transaksi Anda sedang diverifikasi secara otomatis.');
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
     */
    public function cancelOrder(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! in_array($order->status, ['Menunggu Pembayaran', 'Belum Dibayar'])) {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan pada status ini.');
        }

        $order->update(['status' => 'Dibatalkan']);

        return redirect()->route('pesanan.index')
            ->with('success', "Pesanan #{$order->code} berhasil dibatalkan.");
    }
}
