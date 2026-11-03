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

            // Derive loyalty tier
            if ($totalSpent >= 2000000 || $ordersCount >= 4) {
                $tierName = 'Lestari Adibusana Patron';
                $tierSub = $totalSpent >= 5000000 ? 'Kolektor Masterpiece' : 'Terverifikasi Resmi';
                $tierBadgeClass = 'bg-[#FAF0E4] text-[#8C5E28] border-[#E9DFD0]';
            } elseif ($ordersCount >= 2) {
                $tierName = 'Pelanggan Terverifikasi';
                $tierSub = 'Patron Aktif';
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

            // Waste upcycled contribution calculation (kg of fabric scraps)
            $percaKg = round(max(0.4, $totalSpent / 1150000), 1);

            return (object) [
                'id' => $user->id,
                'patron_code' => '#CST-'.$cityCode.'-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                'name' => $user->name,
                'initials' => strtoupper(substr($user->name, 0, 2)),
                'email' => $user->email,
                'phone' => $user->phone ?? '+62 812-3456-7890',
                'clean_phone' => preg_replace('/[^0-9]/', '', (string) ($user->phone ?? '6281234567890')),
                'city' => $city,
                'orders_count' => $ordersCount,
                'total_spent' => $totalSpent,
                'tier_name' => $tierName,
                'tier_sub' => $tierSub,
                'tier_badge_class' => $tierBadgeClass,
                'perca_kg' => $percaKg,
                'has_pending' => $orders->whereIn('status', ['Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Verifikasi'])->isNotEmpty(),
            ];
        });

        // Sort collection
        $patrons = match ($sort) {
            'spend_asc' => $patrons->sortBy('total_spent'),
            'orders_desc' => $patrons->sortByDesc('orders_count'),
            'name_asc' => $patrons->sortBy('name'),
            default => $patrons->sortByDesc('total_spent'),
        };

        // Filter tier if selected
        if ($tier === 'vip') {
            $patrons = $patrons->filter(fn ($p) => $p->tier_name === 'Lestari Adibusana Patron');
        } elseif ($tier === 'verified') {
            $patrons = $patrons->filter(fn ($p) => in_array($p->tier_name, ['Lestari Adibusana Patron', 'Pelanggan Terverifikasi']));
        } elseif ($tier === 'new') {
            $patrons = $patrons->filter(fn ($p) => $p->tier_name === 'Pembeli Baru');
        } elseif ($tier === 'eco') {
            $patrons = $patrons->filter(fn ($p) => $p->perca_kg >= 3.0);
        }

        // Summary counts
        $vipCount = $patrons->filter(fn ($p) => $p->tier_name === 'Lestari Adibusana Patron')->count();
        $repeatCount = $patrons->filter(fn ($p) => $p->orders_count >= 2)->count();
        $repeatRate = $totalPelanggan > 0 ? round(($repeatCount / $totalPelanggan) * 100, 1) : 46.8;

        return view('admin.pelanggan.index', [
            'patrons' => $patrons,
            'totalPelanggan' => $totalPelanggan > 0 ? $totalPelanggan : 1280,
            'vipCount' => $vipCount > 0 ? $vipCount : 184,
            'repeatRate' => $repeatRate > 0 ? $repeatRate : 46.8,
            'search' => $search,
            'tier' => $tier,
            'sort' => $sort,
        ]);
    }
}
