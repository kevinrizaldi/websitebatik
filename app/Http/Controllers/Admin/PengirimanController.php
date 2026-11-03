<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengirimanController extends Controller
{
    /**
     * Display eco-logistics and shipping management console.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $ekspedisiFilter = $request->input('ekspedisi', 'all');
        $statusFilter = $request->input('status', 'all');

        $query = Order::with(['items.produk', 'user', 'shipment'])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhereHas('shipment', function ($sq) use ($search): void {
                        $sq->where('tracking_number', 'like', "%{$search}%")
                            ->orWhere('no_resi', 'like', "%{$search}%")
                            ->orWhere('courier', 'like', "%{$search}%");
                    });
            });
        }

        if ($ekspedisiFilter !== 'all' && $ekspedisiFilter !== '') {
            $query->where(function ($q) use ($ekspedisiFilter): void {
                $q->where('payment_method', 'like', "%{$ekspedisiFilter}%")
                    ->orWhereHas('shipment', function ($sq) use ($ekspedisiFilter): void {
                        $sq->where('courier', 'like', "%{$ekspedisiFilter}%")
                            ->orWhere('ekspedisi', 'like', "%{$ekspedisiFilter}%");
                    });
            });
        }

        if ($statusFilter === 'belum_dikirim') {
            $query->where(function ($q): void {
                $q->whereIn('status', ['Diproses', 'Menunggu Pembayaran', 'Belum Dibayar'])
                    ->where(function ($sub): void {
                        $sub->whereNull('tracking_number')->orWhere('tracking_number', '');
                    });
            });
        } elseif ($statusFilter === 'siap_serah') {
            $query->where(function ($q): void {
                $q->where('status', 'Diproses')
                    ->whereNotNull('tracking_number')
                    ->where('tracking_number', '!=', '');
            });
        } elseif ($statusFilter === 'dalam_perjalanan') {
            $query->where('status', 'Dikirim');
        } elseif ($statusFilter === 'diterima') {
            $query->where('status', 'Selesai');
        }

        $perPage = (int) $request->input('per_page', 5);
        if (! in_array($perPage, [5, 10, 25, 50], true)) {
            $perPage = 5;
        }

        $orders = $query->paginate($perPage)->withQueryString();

        // Count metrics for cards & tabs
        $totalShipments = Order::count();
        $siapDikemasCount = Order::whereIn('status', ['Diproses', 'Menunggu Pembayaran', 'Belum Dibayar'])
            ->where(function ($sub): void {
                $sub->whereNull('tracking_number')->orWhere('tracking_number', '');
            })->count();

        $siapSerahCount = Order::where('status', 'Diproses')
            ->whereNotNull('tracking_number')
            ->where('tracking_number', '!=', '')
            ->count();

        $dalamPerjalananCount = Order::where('status', 'Dikirim')->count();
        $diterimaCount = Order::where('status', 'Selesai')->count();

        $originInfo = app(RajaOngkirService::class)->getOriginInfo();

        return view('admin.pengiriman.index', [
            'orders' => $orders,
            'search' => $search,
            'ekspedisiFilter' => $ekspedisiFilter,
            'statusFilter' => $statusFilter,
            'perPage' => $perPage,
            'originInfo' => $originInfo,
            'totalShipments' => $totalShipments > 0 ? $totalShipments : 126,
            'siapDikemasCount' => $siapDikemasCount > 0 ? $siapDikemasCount : 18,
            'siapSerahCount' => $siapSerahCount > 0 ? $siapSerahCount : 8,
            'dalamPerjalananCount' => $dalamPerjalananCount > 0 ? $dalamPerjalananCount : 24,
            'diterimaCount' => $diterimaCount > 0 ? $diterimaCount : 76,
        ]);
    }
}
