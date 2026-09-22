<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // 1. Hitung jumlah pesanan per status untuk badge di Tab Navigation
        $counts = [
            'all' => Order::count(),
            'menunggu' => Order::where('status', 'Menunggu Konfirmasi')->count(),
            'diproses' => Order::where('status', 'Diproses')->count(),
            'dikirim' => Order::where('status', 'Dikirim')->count(),
            'selesai' => Order::where('status', 'Selesai')->count(),
        ];

        // 2. Query data pesanan
        $query = Order::with('items.produk')->latest();

        // Filter berdasarkan tab status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter pencarian berdasarkan ID Order, Nama Pelanggan, atau No. HP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders', 'counts'));
    }

    public function show(Order $order)
    {
        $order->load('items.produk', 'user');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:Menunggu Konfirmasi,Diproses,Dikirim,Selesai,Dibatalkan',
        ]);

        $order->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status pesanan #' . $order->code . ' berhasil diperbarui!');
    }

    public function cancel(Order $order)
    {
        $order->update(['status' => 'Dibatalkan']);

        return redirect()->back()->with('success', 'Pesanan #' . $order->code . ' berhasil ditolak/dibatalkan.');
    }
}
