<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSnapTokenRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Midtrans\MidtransService;
use App\Services\Midtrans\PaymentAttemptProcessor;
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
        private readonly MidtransService $midtransService,
        private readonly PaymentAttemptProcessor $processor,
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
     * Show the payment page for an order (route: /pembayaran/{id}).
     * Hanya pemilik pesanan (atau admin) yang boleh membuka halaman ini.
     */
    public function show(string $id): View|RedirectResponse
    {
        $order = Order::with(['items.produk', 'pengiriman', 'user'])
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

        // Sinkronkan status terbaru dari Midtrans jika ada payment aktif
        $activePayment = $order->activePayment();
        if ($activePayment) {
            try {
                $statusData = $this->midtransService->getTransactionStatus($activePayment->midtrans_order_id);
                if ($statusData) {
                    $this->processor->apply($activePayment, $statusData);
                }
            } catch (Exception $e) {
                Log::warning('PaymentController@show: sync failed', ['order' => $order->code, 'error' => $e->getMessage()]);
            }
            $order->refresh();
            $activePayment = $order->activePayment();
        }

        // Ambil atau buat Snap token
        $snapToken = null;
        $clientKey = config('midtrans.client_key');
        $snapJsUrl = config('midtrans.snap_js_url');

        if ($order->payment_status === Order::PAYMENT_PENDING && ! $order->isPastDeadline()) {
            try {
                $snapToken = $this->midtransService->getOrCreateSnapToken($order);
            } catch (Exception $e) {
                Log::warning('PaymentController@show: getOrCreateSnapToken failed', [
                    'order' => $order->code,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('pembayaran', compact('order', 'activePayment', 'snapToken', 'clientKey', 'snapJsUrl'));
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

        try {
            $snapToken = $this->midtransService->getOrCreateSnapToken($order);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Exception $e) {
            Log::error('getSnapToken via AJAX failed', ['order' => $id, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Gagal mendapatkan token pembayaran.'], 502);
        }

        return response()->json([
            'success' => true,
            'snap_token' => $snapToken,
            'client_key' => config('midtrans.client_key'),
        ]);
    }

    /**
     * Payment finish callback page.
     *
     * GET /payment/finish atau /midtrans/finish
     */
    public function finish(Request $request): RedirectResponse
    {
        $midtransOrderId = $request->query('order_id');

        $order = null;

        if ($midtransOrderId) {
            // Cari lewat tabel payments (sistem baru)
            $payment = Payment::where('midtrans_order_id', $midtransOrderId)
                ->with('order')
                ->first();

            if ($payment?->order && $request->user() && $payment->order->user_id === $request->user()->id) {
                $order = $payment->order;

                // Sinkronkan status dari Midtrans
                try {
                    $statusData = $this->midtransService->getTransactionStatus($midtransOrderId);
                    if ($statusData) {
                        $this->processor->apply($payment, $statusData);
                    }
                } catch (Exception $e) {
                    Log::warning('PaymentController@finish: sync failed', ['midtrans_order_id' => $midtransOrderId]);
                }

                $order->refresh();
            }
        }

        if (! $order) {
            return redirect()->route('pesanan.index')
                ->with('info', 'Status transaksi Anda sedang diverifikasi secara otomatis.');
        }

        if (in_array($order->payment_status, [Order::PAYMENT_PAID], true)
            || in_array($order->status, ['Diproses', 'Dikirim', 'Selesai'], true)) {
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
        if ($order->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
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
