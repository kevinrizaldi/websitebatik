<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pengiriman;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * State machine transisi status pesanan (PRD: Menunggu Pembayaran, Diproses, Dikirim, Selesai, Dibatalkan).
     * Status lama (Belum Dibayar/Sudah Dibayar/dll) tetap ditoleransi sebagai alias.
     */
    protected array $allowedTransitions = [
        'Menunggu Pembayaran' => ['Diproses', 'Dibatalkan'],
        'Belum Dibayar' => ['Menunggu Pembayaran', 'Diproses', 'Dibatalkan'],
        'Menunggu Konfirmasi' => ['Menunggu Pembayaran', 'Diproses', 'Dibatalkan'],
        'Menunggu Verifikasi' => ['Diproses', 'Dibatalkan'],
        'Sudah Dibayar' => ['Diproses', 'Dibatalkan'],
        'Diproses' => ['Dikirim', 'Dibatalkan'],
        'Dikirim' => ['Selesai'],
        'Selesai' => [],
        'Batal' => [],
        'Dibatalkan' => [],
    ];

    protected function normalizeStatus(string $status): string
    {
        return match ($status) {
            'Belum Dibayar', 'Menunggu Konfirmasi' => 'Menunggu Pembayaran',
            'Sudah Dibayar', 'Menunggu Verifikasi' => 'Diproses',
            'Batal' => 'Dibatalkan',
            default => $status,
        };
    }

    public function index(Request $request)
    {
        $counts = [
            'all' => Order::count(),
            'menunggu_pembayaran' => Order::whereIn('status', ['Belum Dibayar', 'Menunggu Pembayaran', 'Menunggu Konfirmasi'])->count(),
            'diproses' => Order::whereIn('status', ['Diproses', 'Sudah Dibayar', 'Menunggu Verifikasi'])->count(),
            'dikirim' => Order::where('status', 'Dikirim')->count(),
            'selesai' => Order::where('status', 'Selesai')->count(),
            'dibatalkan' => Order::whereIn('status', ['Batal', 'Dibatalkan'])->count(),
        ];

        $query = Order::with(['items.produk', 'pembayaran', 'pengiriman'])->latest();

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'Menunggu Pembayaran') {
                $query->whereIn('status', ['Belum Dibayar', 'Menunggu Pembayaran', 'Menunggu Konfirmasi']);
            } elseif ($status === 'Diproses') {
                $query->whereIn('status', ['Diproses', 'Sudah Dibayar', 'Menunggu Verifikasi']);
            } elseif ($status === 'Dibatalkan') {
                $query->whereIn('status', ['Batal', 'Dibatalkan']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(10)->withQueryString();
        $allowedTransitions = $this->allowedTransitions;

        return view('admin.orders.index', compact('orders', 'counts', 'allowedTransitions'));
    }

    public function show(Order $order)
    {
        $order->load(['items.produk', 'user', 'pembayaran', 'pengiriman']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        try {
            if (! $request->filled('status') || empty(trim($request->status))) {
                return redirect()->back()->withErrors([
                    'status' => "Silakan pilih status tujuan terlebih dahulu untuk pesanan #{$order->code} sebelum menekan tombol Ubah!",
                ]);
            }

            $request->validate([
                'status' => 'required|string',
                'tracking_number' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9\-]+$/'],
            ], [
                'tracking_number.regex' => 'Nomor resi hanya boleh berisi huruf dan angka tanpa simbol khusus.',
            ]);

            $newStatus = $this->normalizeStatus($request->status);
            $currentStatus = $order->status;

            if ($newStatus === $currentStatus) {
                if ($request->filled('tracking_number')) {
                    $cleanTracking = strtoupper(trim((string) $request->tracking_number));
                    $order->update(['tracking_number' => $cleanTracking]);

                    // Update pengiriman table
                    Pengiriman::updateOrCreate(
                        ['order_id' => $order->id],
                        ['no_resi' => $cleanTracking]
                    );

                    return redirect()->back()->with('success', "✓ Nomor resi untuk pesanan #{$order->code} berhasil diperbarui.");
                }

                return redirect()->back()->withErrors([
                    'status' => "Pesanan #{$order->code} saat ini sudah berstatus \"{$currentStatus}\". Silakan pilih status yang berbeda untuk mengubahnya.",
                ]);
            }

            $allowed = $this->allowedTransitions[$currentStatus] ?? [];

            if (empty($allowed)) {
                return redirect()->back()->withErrors([
                    'status' => "Pesanan #{$order->code} berstatus \"{$currentStatus}\" dan sudah final, tidak dapat diubah lagi.",
                ]);
            }

            if (! in_array($newStatus, $allowed)) {
                $allowedList = implode(', ', $allowed);

                return redirect()->back()->withErrors([
                    'status' => "Pesanan #{$order->code}: status \"{$currentStatus}\" hanya boleh diubah ke → {$allowedList}.",
                ]);
            }

            // Validasi wajib nomor resi jika status diubah ke 'Dikirim'
            if ($newStatus === 'Dikirim' && empty(trim($request->tracking_number ?? '')) && empty(trim($order->tracking_number ?? ''))) {
                return redirect()->back()->withErrors([
                    'tracking_number' => "Untuk mengubah status pesanan #{$order->code} ke \"Dikirim\", Anda harus memasukkan Nomor Resi pengiriman terlebih dahulu!",
                ]);
            }

            $payload = ['status' => $newStatus];
            if ($request->filled('tracking_number')) {
                $payload['tracking_number'] = strtoupper(trim((string) $request->tracking_number));
            }

            $order->update($payload);

            // Sync Pengiriman Record
            $pengirimanStatus = match ($newStatus) {
                'Dikirim' => 'Dikirim',
                'Selesai' => 'Diterima',
                'Diproses' => 'Diproses',
                default => 'Menunggu Pengiriman',
            };

            Pengiriman::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'no_resi' => $payload['tracking_number'] ?? $order->tracking_number,
                    'status_pengiriman' => $pengirimanStatus,
                    'tanggal_kirim' => $newStatus === 'Dikirim' ? now() : null,
                ]
            );

            return redirect()->back()->with(
                'success',
                "✓ Status pesanan #{$order->code} berhasil diubah: \"{$currentStatus}\" → \"{$newStatus}\"."
            );
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'status' => "Gagal mengubah status pesanan #{$order->code}: ".$e->getMessage(),
            ]);
        }
    }

    /**
     * Update Shipping Details specifically (ekspedisi, resi, tanggal kirim, status).
     */
    public function updatePengiriman(Request $request, Order $order)
    {
        $validated = $request->validate([
            'ekspedisi' => 'required|string|max:100',
            'no_resi' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\-]+$/'],
            'tanggal_kirim' => 'nullable|date',
            'status_pengiriman' => 'required|string|in:Menunggu Pengiriman,Diproses,Dikirim,Diterima',
            'catatan' => 'nullable|string|max:500',
        ], [
            'no_resi.required' => 'Nomor resi wajib diisi.',
            'no_resi.regex' => 'Nomor resi hanya boleh berisi huruf dan angka tanpa simbol khusus.',
        ]);

        $cleanResi = strtoupper(trim((string) $validated['no_resi']));
        $order->update(['tracking_number' => $cleanResi]);

        // If shipping status is Dikirim, ensure order status is at least Dikirim
        if ($validated['status_pengiriman'] === 'Dikirim' && $order->status !== 'Dikirim') {
            $order->update(['status' => 'Dikirim']);
        } elseif ($validated['status_pengiriman'] === 'Diterima' && $order->status !== 'Selesai') {
            $order->update(['status' => 'Selesai']);
        }

        Pengiriman::updateOrCreate(
            ['order_id' => $order->id],
            [
                'ekspedisi' => $validated['ekspedisi'],
                'no_resi' => $cleanResi,
                'tanggal_kirim' => $validated['tanggal_kirim'] ?? now(),
                'status_pengiriman' => $validated['status_pengiriman'],
                'catatan' => $validated['catatan'] ?? null,
            ]
        );

        return redirect()->back()->with('success', "✓ Informasi pengiriman & nomor resi pesanan #{$order->code} berhasil diperbarui.");
    }

    public function cancel(Order $order)
    {
        try {
            $allowed = $this->allowedTransitions[$order->status] ?? [];

            if (! in_array('Dibatalkan', $allowed) && ! in_array('Batal', $allowed)) {
                return redirect()->back()->withErrors([
                    'status' => "Pesanan #{$order->code} berstatus \"{$order->status}\" dan tidak dapat dibatalkan.",
                ]);
            }

            $order->update(['status' => 'Dibatalkan']);

            return redirect()->back()->with('success', "✓ Pesanan #{$order->code} berhasil dibatalkan.");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'status' => "Gagal membatalkan pesanan #{$order->code}: ".$e->getMessage(),
            ]);
        }
    }
}
