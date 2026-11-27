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
    $initials = $currentUser?->initials ?: 'A';
    $alamatCount = $alamatCount ?? ($currentUser ? $currentUser->alamats()->count() : 0);
    $pesananAktif = $pesananAktif ?? ($currentUser ? $currentUser->orders()->whereIn('status', [
        'Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Verifikasi', 'Menunggu Konfirmasi',
        'Diproses', 'Sudah Dibayar', 'Dikirim', 'sedang dikirim', 'sedang diproses'
    ])->count() : 0);
@endphp

<div class="space-y-4">
    {{-- Card Identitas Profil --}}
    <div class="relative overflow-hidden rounded-3xl border border-[#ECE4D8] bg-white p-7 text-center shadow-xs">
        {{-- Subtle top blush gradient --}}
        <div class="pointer-events-none absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-[#F7EFE7]/80 to-transparent"></div>

        <div class="relative">
            <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="relative inline-block">
                    @if($currentUser?->profile_photo_url)
                        <img src="{{ $currentUser->profile_photo_url }}" alt="Foto profil {{ $name }}" class="h-20 w-20 rounded-full border border-stone-200 object-cover shadow-sm">
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[#201A17] text-xl font-bold tracking-wider text-white shadow-sm">
                            {{ $initials }}
                        </div>
                    @endif
                    <label for="profile-avatar" aria-label="Ubah foto profil" title="Ubah foto profil"
                           class="absolute bottom-0 right-0 flex h-7 w-7 cursor-pointer items-center justify-center rounded-full border-2 border-white bg-[#201A17] text-white shadow-sm transition hover:bg-stone-700 focus-within:ring-2 focus-within:ring-[#C59A53]">
                        <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8.5h3l1.5-2h7l1.5 2h3A1.5 1.5 0 0121.5 10v8A1.5 1.5 0 0120 19.5H4A1.5 1.5 0 012.5 18v-8A1.5 1.5 0 014 8.5z" />
                            <circle cx="12" cy="13.5" r="3.25" />
                        </svg>
                    </label>
                    <input id="profile-avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" aria-label="Ubah foto profil" required
                           class="sr-only" onchange="this.form.submit()">
                </div>
            </form>
            @error('avatar')
                <p role="alert" class="mt-2 text-xs text-rose-600">{{ $message }}</p>
            @enderror
            <h2 class="mt-3.5 text-base font-bold text-stone-900">{{ $name }}</h2>
            <p class="mt-0.5 text-xs text-stone-500">{{ $email }}</p>
            @if($currentUser?->profile_photo_url)
                <form method="POST" action="{{ route('profile.avatar.destroy') }}" class="mt-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center rounded-lg border border-stone-200 bg-white px-3 py-1.5 text-xs font-medium text-stone-500 shadow-sm transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">
                        Hapus foto 
                    </button>
                </form>
            @endif
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

    <div class="rounded-3xl border border-rose-100 bg-white p-2.5 shadow-xs">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3-3H9m0 0l3-3m-3 3l3 3"/>
                </svg>
                <span>Keluar (Logout)</span>
            </button>
        </form>
    </div>
</div>

