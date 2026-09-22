<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Daftar transisi status yang diizinkan (state machine).
     * Key = status saat ini, Value = array status yang boleh dituju.
     *
     * Aturan bisnis:
     * - Belum Dibayar  → Sudah Dibayar, Batal
     * - Sudah Dibayar  → Diproses, Batal
     * - Diproses       → Dikirim, Batal
     * - Dikirim        → Selesai
     * - Selesai        → (final, tidak bisa diubah)
     * - Batal          → (final, tidak bisa diubah)
     */
    protected array $allowedTransitions = [
        'Belum Dibayar'       => ['Sudah Dibayar', 'Batal'],
        'Menunggu Konfirmasi' => ['Sudah Dibayar', 'Batal'],
        'Sudah Dibayar'       => ['Diproses', 'Batal'],
        'Diproses'            => ['Dikirim', 'Batal'],
        'Dikirim'             => ['Selesai'],
        'Selesai'             => [],
        'Batal'               => [],
        'Dibatalkan'          => [],
    ];

    public function index(Request $request)
    {
        // Hitung jumlah pesanan per status untuk badge Tab Navigation
        $counts = [
            'all'           => Order::count(),
            'belum_dibayar' => Order::whereIn('status', ['Belum Dibayar', 'Menunggu Konfirmasi'])->count(),
            'sudah_dibayar' => Order::where('status', 'Sudah Dibayar')->count(),
            'diproses'      => Order::where('status', 'Diproses')->count(),
            'dikirim'       => Order::where('status', 'Dikirim')->count(),
            'selesai'       => Order::where('status', 'Selesai')->count(),
            'batal'         => Order::whereIn('status', ['Batal', 'Dibatalkan'])->count(),
        ];

        $query = Order::with('items.produk')->latest();

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'Belum Dibayar') {
                $query->whereIn('status', ['Belum Dibayar', 'Menunggu Konfirmasi']);
            } elseif ($status === 'Batal') {
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

        // Kirim peta transisi ke view agar dropdown hanya tampilkan opsi valid
        $allowedTransitions = $this->allowedTransitions;

        return view('admin.orders.index', compact('orders', 'counts', 'allowedTransitions'));
    }

    public function show(Order $order)
    {
        $order->load('items.produk', 'user');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        try {
            // Pengecekan jika status belum dipilih / kosong
            if (!$request->filled('status') || empty(trim($request->status))) {
                return redirect()->back()->withErrors([
                    'status' => "Silakan pilih status tujuan terlebih dahulu untuk pesanan #{$order->code} sebelum menekan tombol Ubah!",
                ]);
            }

            $request->validate([
                'status'          => 'required|string',
                'tracking_number' => 'nullable|string|max:100',
            ], [
                'status.required' => "Silakan pilih status tujuan terlebih dahulu untuk pesanan #{$order->code} sebelum menekan tombol Ubah!",
            ]);

            $newStatus     = $request->status;
            $currentStatus = $order->status;

            // Jika hanya ingin memperbarui nomor resi tanpa mengubah status
            if ($newStatus === $currentStatus) {
                if ($request->filled('tracking_number')) {
                    $order->update(['tracking_number' => trim($request->tracking_number)]);
                    return redirect()->back()->with(
                        'success',
                        "✓ Nomor resi untuk pesanan #{$order->code} berhasil diperbarui."
                    );
                }
                return redirect()->back()->withErrors([
                    'status' => "Pesanan #{$order->code} saat ini sudah berstatus \"{$currentStatus}\". Silakan pilih status yang berbeda untuk mengubahnya.",
                ]);
            }

            // Ambil daftar status yang boleh dituju dari status sekarang
            $allowed = $this->allowedTransitions[$currentStatus] ?? [];

            // Status sudah final (Selesai / Batal)
            if (empty($allowed)) {
                return redirect()->back()->withErrors([
                    'status' => "Pesanan #{$order->code} berstatus \"{$currentStatus}\" dan sudah final, tidak dapat diubah lagi.",
                ]);
            }

            // Status tujuan tidak diizinkan
            if (!in_array($newStatus, $allowed)) {
                $allowedList = implode(', ', $allowed);
                return redirect()->back()->withErrors([
                    'status' => "Pesanan #{$order->code}: status \"{$currentStatus}\" hanya boleh diubah ke → {$allowedList}. Tidak bisa langsung ke \"{$newStatus}\".",
                ]);
            }

            // Validasi wajib nomor resi jika status diubah ke 'Dikirim'
            if ($newStatus === 'Dikirim' && empty(trim($request->tracking_number ?? ''))) {
                return redirect()->back()->withErrors([
                    'tracking_number' => "Untuk mengubah status pesanan #{$order->code} ke \"Dikirim\", Anda harus memasukkan Nomor Resi pengiriman terlebih dahulu!",
                ]);
            }

            $payload = ['status' => $newStatus];
            if ($request->filled('tracking_number')) {
                $payload['tracking_number'] = trim($request->tracking_number);
            }

            $order->update($payload);

            return redirect()->back()->with(
                'success',
                "✓ Status pesanan #{$order->code} berhasil diubah: \"{$currentStatus}\" → \"{$newStatus}\"."
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'status' => "Gagal mengubah status pesanan #{$order->code}: " . $e->getMessage(),
            ]);
        }
    }

    public function cancel(Order $order)
    {
        try {
            $allowed = $this->allowedTransitions[$order->status] ?? [];

            if (!in_array('Batal', $allowed)) {
                return redirect()->back()->withErrors([
                    'status' => "Pesanan #{$order->code} berstatus \"{$order->status}\" dan tidak dapat dibatalkan.",
                ]);
            }

            $order->update(['status' => 'Batal']);

            return redirect()->back()->with('success', "✓ Pesanan #{$order->code} berhasil dibatalkan.");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'status' => "Gagal membatalkan pesanan #{$order->code}: " . $e->getMessage(),
            ]);
        }
    }
}
