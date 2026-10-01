<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Midtrans\MidtransService;
use App\Services\Midtrans\PaymentAttemptProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OrderPayController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtransService,
        private readonly PaymentAttemptProcessor $processor,
    ) {}

    /**
     * Show the payment page for an order.
     *
     * GET /orders/{order}/pay
     */
    public function show(Request $request, Order $order): View
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        }

        $order->load('items.produk');

        /** @var Payment|null $activePayment */
        $activePayment = $order->activePayment();

        // Auto-sync: if there is an active pending attempt with no payment_type yet,
        // try to fetch latest status from Midtrans so payment_details are populated
        // (in case the webhook hasn't arrived yet — e.g. during local development).
        if ($activePayment !== null && $activePayment->payment_type === null) {
            $this->trySyncSilently($order);
            $order->refresh();
            $activePayment = $order->activePayment();
        }

        $clientKey = config('midtrans.client_key');
        $snapJsUrl = config('midtrans.is_production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';

        return view('pesanan.pay', compact('order', 'activePayment', 'clientKey', 'snapJsUrl'));
    }

    /**
     * Silently sync status from Midtrans without failing the page load.
     */
    private function trySyncSilently(Order $order): void
    {
        try {
            $activePayment = $order->activePayment();
            if ($activePayment === null) {
                return;
            }

            $statusData = $this->midtransService->getTransactionStatus($activePayment->midtrans_order_id);
            if ($statusData !== null) {
                $this->processor->apply($order, $activePayment, $statusData);
            }
        } catch (\Exception $e) {
            Log::info('OrderPayController: silent sync failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
