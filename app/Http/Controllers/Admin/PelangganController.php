<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganController extends Controller
{
    /**
     * Display customer & patron directory.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $tier = $request->input('tier', 'all');
        $sort = $request->input('sort', 'spend_desc');

        $query = User::query()
            ->where(function ($q): void {
                $q->where('role', '!=', 'admin')->orWhereNull('role');
            })
            ->with(['orders', 'alamats']);

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $allCustomers = $query->get();

        // Calculate metrics
        $totalPelanggan = User::where(function ($q): void {
            $q->where('role', '!=', 'admin')->orWhereNull('role');
        })->count();

        // Attach computed values
        $patrons = $allCustomers->map(function (User $user) {
            $orders = $user->orders;
            $ordersCount = $orders->count();
            $totalSpent = (float) $orders->where('status', '!=', 'Dibatalkan')->sum('total_price');
            $primaryAddress = $user->alamats->first();
            $city = $primaryAddress ? ($primaryAddress->kota.', '.$primaryAddress->provinsi) : 'Indonesia';
            $cityCode = $primaryAddress ? strtoupper(substr($primaryAddress->kota, 0, 3)) : 'IDN';

            // Derive customer status
            if ($ordersCount >= 2) {
                $tierName = 'Pelanggan Terverifikasi';
                $tierSub = 'Pelanggan Aktif';
                $tierBadgeClass = 'bg-[#F2F4F7] text-[#344054] border-[#E4E7EC]';
            } elseif ($ordersCount === 1) {
                $tierName = 'Pembeli Baru';
                $tierSub = 'Pesanan Pertama';
                $tierBadgeClass = 'bg-[#F8EDE2] text-[#A86544] border-[#F2DECD]';
            } else {
                $tierName = 'Pelanggan Reguler';
                $tierSub = 'Terdaftar';
                $tierBadgeClass = 'bg-stone-100 text-stone-600 border-stone-200';
            }

            return (object) [
                'id' => $user->id,
                'patron_code' => '#CST-'.$cityCode.'-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                'name' => $user->name,
                'initials' => strtoupper(substr($user->name, 0, 2)),
                'email' => $user->email,
                'phone' => $user->phone ?: '-',
                'clean_phone' => $user->phone ? preg_replace('/[^0-9]/', '', (string) $user->phone) : null,
                'city' => $city,
                'orders_count' => $ordersCount,
                'total_spent' => $totalSpent,
                'tier_name' => $tierName,
                'tier_sub' => $tierSub,
                'tier_badge_class' => $tierBadgeClass,
                'has_pending' => $orders->whereIn('status', ['Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Verifikasi'])->isNotEmpty(),
            ];
        });

        // Summary counts (computed before tier filter)
        $verifiedCount = $patrons->filter(fn ($p) => $p->tier_name === 'Pelanggan Terverifikasi')->count();
        $newCount = $patrons->filter(fn ($p) => $p->tier_name === 'Pembeli Baru')->count();
        $repeatCount = $patrons->filter(fn ($p) => $p->orders_count >= 2)->count();
        $repeatRate = $totalPelanggan > 0 ? round(($repeatCount / $totalPelanggan) * 100, 1) : 0;
        $totalOmzet = (float) $patrons->sum('total_spent');

        // Sort collection
        $patrons = match ($sort) {
            'spend_asc' => $patrons->sortBy('total_spent'),
            'orders_desc' => $patrons->sortByDesc('orders_count'),
            'name_asc' => $patrons->sortBy('name'),
            default => $patrons->sortByDesc('total_spent'),
        };

        // Filter status if selected
        if ($tier === 'verified') {
            $patrons = $patrons->filter(fn ($p) => $p->tier_name === 'Pelanggan Terverifikasi');
        } elseif ($tier === 'new') {
            $patrons = $patrons->filter(fn ($p) => $p->tier_name === 'Pembeli Baru');
        }

        // Customer growth calculation
        $startThisMonth = now()->startOfMonth();
        $startLastMonth = now()->subMonth()->startOfMonth();
        $endLastMonth = now()->subMonth()->endOfMonth();

        $customersThisMonth = User::where(function ($q): void {
            $q->where('role', '!=', 'admin')->orWhereNull('role');
        })->where('created_at', '>=', $startThisMonth)->count();

        $customersLastMonth = User::where(function ($q): void {
            $q->where('role', '!=', 'admin')->orWhereNull('role');
        })->whereBetween('created_at', [$startLastMonth, $endLastMonth])->count();

        $growthPercent = $customersLastMonth > 0
            ? round((($customersThisMonth - $customersLastMonth) / $customersLastMonth) * 100)
            : ($customersThisMonth > 0 ? 100 : 0);

        return view('admin.pelanggan.index', [
            'patrons' => $patrons,
            'totalPelanggan' => $totalPelanggan,
            'customersThisMonth' => $customersThisMonth,
            'growthPercent' => $growthPercent,
            'verifiedCount' => $verifiedCount,
            'newCount' => $newCount,
            'repeatRate' => $repeatRate,
            'totalOmzet' => $totalOmzet,
            'search' => $search,
            'tier' => $tier,
            'sort' => $sort,
        ]);
    }
}
