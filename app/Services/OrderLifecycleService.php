<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Midtrans\MidtransService;
use Illuminate\Support\Facades\DB;

/**
 * Shared order state transitions used by both the web (PaymentController)
 * and the mobile API (Api\OrderController) so the rules never drift.
 */
class OrderLifecycleService
{
    public function __construct(
        private readonly MidtransService $midtransService
    ) {}

    /**
     * Customer confirms the goods were received — order moves Dikirim → Selesai.
     *
     * Mirrors PaymentController::confirmReceived; returns false when the
     * order is not in a confirmable state so callers can pick their response.
     */
    public function confirmReceived(Order $order): bool
    {
        if ($order->status !== 'Dikirim') {
            return false;
        }

        $order->update(['status' => 'Selesai']);
        $order->getOrInitPengiriman()->update(['status_pengiriman' => 'Diterima']);

        return true;
    }

    /**
     * Customer cancels their own unpaid order — restocks inventory and
     * voids pending payment attempts. Mirrors PaymentController::cancelOrder;
     * returns false when the order is not cancellable.
     */
    public function cancelUnpaid(Order $order): bool
    {
        if (! in_array($order->status, ['Menunggu Pembayaran', 'Belum Dibayar'], true)) {
            return false;
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

            // Cancel pending payment attempts locally and on Midtrans to prevent phantom payments.
            $pendingPayments = $order->payments()->where('status', Payment::STATUS_PENDING)->get();
            foreach ($pendingPayments as $pendingPayment) {
                $pendingPayment->status = Payment::STATUS_CANCELLED;
                $pendingPayment->save();

                try {
                    $this->midtransService->cancelTransaction($pendingPayment->midtrans_order_id);
                } catch (\Throwable) {
                    // Ignore if the transaction is already closed or was never created on Midtrans.
                }
            }
        });

        return true;
    }
}
