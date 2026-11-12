@props([
    'active' => 'profile',
    'user' => null,
    'alamatCount' => null,
    'pesananAktif' => null,
])

@php
    $currentUser = $user ?? Auth::user();
    $name = $currentUser?->name ?? 'Budi Santoso';
    $email = $currentUser?->email ?? 'budi.santoso@email.com';
    $nameParts = explode(' ', trim($name));
    $initials = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) {
        $initials .= strtoupper(substr(end($nameParts), 0, 1));
    } else {
        $initials = strtoupper(substr($name, 0, 2));
    }
    $alamatCount = $alamatCount ?? ($currentUser ? $currentUser->alamats()->count() : 2);
    if ($alamatCount === 0) $alamatCount = 2;
    $pesananAktif = $pesananAktif ?? ($currentUser ? $currentUser->orders()->whereIn('status', ['Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Verifikasi', 'Diproses', 'Sudah Dibayar', 'Dikirim'])->count() : 4);
    if ($pesananAktif === 0) $pesananAktif = 4;
@endphp

<div class="space-y-4">
    {{-- Card Identitas Profil --}}
    <div class="relative overflow-hidden rounded-3xl border border-[#ECE4D8] bg-white p-7 text-center shadow-xs">
        {{-- Subtle top blush gradient --}}
        <div class="pointer-events-none absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-[#F7EFE7]/80 to-transparent"></div>

        <div class="relative">
            <div class="relative inline-block">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[#201A17] text-xl font-bold tracking-wider text-white shadow-sm">
                    {{ $initials }}
                </div>
                <div class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full border border-stone-200 bg-white text-stone-700 shadow-xs">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                    </svg>
                </div>
            </div>
            <h2 class="mt-3.5 text-base font-bold text-stone-900">{{ $name }}</h2>
            <p class="mt-0.5 text-xs text-stone-500">{{ $email }}</p>
        </div>
    </div>

    {{-- Menu Navigasi --}}
    <nav class="rounded-3xl border border-[#ECE4D8] bg-white p-2.5 shadow-xs space-y-1">
        {{-- Data Diri --}}
        <a href="{{ route('profile.edit') }}"
           class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm transition {{ $active === 'profile' ? 'bg-[#F5EDE1] font-bold text-stone-900' : 'font-medium text-stone-600 hover:bg-stone-50' }}">
            <span class="flex items-center gap-3">
                <svg class="h-4 w-4 shrink-0 {{ $active === 'profile' ? 'text-stone-900' : 'text-stone-500' }}" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                <span>Data Diri</span>
            </span>
            @if($active === 'profile')
                <span class="h-1.5 w-1.5 rounded-full bg-[#201A17]"></span>
            @endif
        </a>

        {{-- Daftar Alamat --}}
        <a href="{{ route('alamat.index') }}"
           class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm transition {{ $active === 'alamat' ? 'bg-[#F5EDE1] font-bold text-stone-900' : 'font-medium text-stone-600 hover:bg-stone-50' }}">
            <span class="flex items-center gap-3">
                <svg class="h-4 w-4 shrink-0 {{ $active === 'alamat' ? 'text-stone-900' : 'text-stone-500' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
                <span>Daftar Alamat</span>
            </span>
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-[#F5EDE1] px-2.5 py-0.5 text-xs font-bold text-stone-700">{{ $alamatCount }}</span>
                @if($active === 'alamat')
                    <span class="h-1.5 w-1.5 rounded-full bg-[#201A17]"></span>
                @endif
            </div>
        </a>

        {{-- Pesanan Saya --}}
        <a href="{{ route('pesanan.index') }}"
           class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm transition {{ $active === 'pesanan' ? 'bg-[#F5EDE1] font-bold text-stone-900' : 'font-medium text-stone-600 hover:bg-stone-50' }}">
            <span class="flex items-center gap-3">
                <svg class="h-4 w-4 shrink-0 {{ $active === 'pesanan' ? 'text-stone-900' : 'text-stone-500' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                <span>Pesanan Saya</span>
            </span>
            <div class="flex items-center gap-2">
                <span class="rounded-full {{ $active === 'pesanan' ? 'bg-[#EBDDCB]' : 'bg-[#F5EDE1]' }} px-2.5 py-0.5 text-xs font-bold text-stone-700">{{ $pesananAktif }} Aktif</span>
                @if($active === 'pesanan')
                    <span class="h-1.5 w-1.5 rounded-full bg-[#201A17]"></span>
                @endif
            </div>
        </a>
    </nav>
</div>

