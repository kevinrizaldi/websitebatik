<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    /**
     * Display payments & bank mutation reconciliation page.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $statusFilter = $request->input('status', 'all');

        $query = Order::with(['items.produk', 'user'])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'pending') {
            $query->whereIn('status', ['Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Verifikasi', 'Menunggu Konfirmasi']);
        } elseif ($statusFilter === 'success') {
            $query->whereIn('status', ['Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai']);
        } elseif ($statusFilter === 'failed') {
            $query->whereIn('status', ['Batal', 'Dibatalkan']);
        }

        $orders = $query->paginate(10)->withQueryString();

        // Calculate KPI values
        $pendingCount = Order::whereIn('status', ['Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Verifikasi', 'Menunggu Konfirmasi'])->count();
        $pendingAmount = (float) Order::whereIn('status', ['Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Verifikasi', 'Menunggu Konfirmasi'])->sum('total_price');

        $successCount = Order::whereIn('status', ['Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai'])->count();
        $successAmount = (float) Order::whereIn('status', ['Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai'])->sum('total_price');

        $failedCount = Order::whereIn('status', ['Batal', 'Dibatalkan'])->count();
        $failedAmount = (float) Order::whereIn('status', ['Batal', 'Dibatalkan'])->sum('total_price');

        $gatewayAmount = (float) Payment::whereIn('status', [Payment::STATUS_PAID, 'settlement', 'capture'])->sum('gross_amount');
        $totalPayments = Order::count();

        return view('admin.pembayaran.index', [
            'orders' => $orders,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'pendingCount' => $pendingCount,
            'pendingAmount' => $pendingAmount,
            'gatewayAmount' => $gatewayAmount,
            'successCount' => $successCount,
            'successAmount' => $successAmount,
            'failedCount' => $failedCount,
            'failedAmount' => $failedAmount,
            'totalPayments' => $totalPayments,
        ]);
    }

    /**
     * Sinkronisasi massal status transaksi Midtrans untuk pesanan pending/aktif.
     */
    public function sync(Request $request)
    {
        try {
            /** @var MidtransService $midtransService */
            $midtransService = app(MidtransService::class);
            $pendingOrders = Order::whereIn('status', ['Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Konfirmasi', 'Menunggu Verifikasi'])
                ->latest()
                ->take(20)
                ->get();

            $updatedCount = 0;
            foreach ($pendingOrders as $order) {
                if ($midtransService->refreshFromMidtrans($order)) {
                    $updatedCount++;
                }
            }

            return redirect()->back()->with('success', '✓ Sinkronisasi gateway Midtrans selesai diperiksa.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'sync' => 'Gagal menyinkronkan gateway Midtrans: '.$e->getMessage(),
            ]);
        }
    }
}
