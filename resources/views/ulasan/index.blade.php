<x-storefront-layout title="Ulasan Saya">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8" x-data="{ previewImage: null }">
        <p class="text-xs text-stone-500">
            <a href="{{ url('/') }}" class="hover:text-stone-900">Beranda</a>
            <span class="mx-1">›</span>
            <a href="{{ route('profile.edit') }}" class="hover:text-stone-900">Profil</a>
            <span class="mx-1">›</span>
            <span class="font-semibold text-stone-800">Ulasan Saya</span>
        </p>
        <p class="mt-4 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-widest text-stone-500">
            <span class="h-1.5 w-1.5 rounded-full bg-[#8C2F1B]"></span> Penilaian Produk Anda
        </p>
        <h1 class="font-serif-title mt-2 text-3xl font-extrabold text-stone-900 sm:text-4xl">Ulasan Saya ({{ $ulasans->count() }})</h1>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-12">
            <div class="space-y-5 lg:col-span-3">
                <div class="rounded-3xl border border-[#ECE4D8] bg-white p-6 text-center shadow-sm">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#201A17] text-lg font-extrabold text-[#E5C38E]">
                        {{ strtoupper(substr($user->name, 0, 1)).strtoupper(substr(strrchr($user->name, ' ') ?: '', 1, 1)) }}
                    </div>
                    <h2 class="mt-3 font-bold text-stone-900">{{ $user->name }}</h2>
                    <p class="text-xs text-stone-500">{{ $user->email }}</p>
                </div>
                <nav class="rounded-3xl border border-[#ECE4D8] bg-white p-3 shadow-sm">
                    <a href="{{ route('profile.edit') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-stone-600 transition hover:bg-stone-100"><span>Data Diri</span></a>
                    <a href="{{ route('alamat.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-stone-600 transition hover:bg-stone-100"><span>Daftar Alamat</span><span class="rounded-full bg-[#F5EDE1] px-2.5 py-0.5 text-[11px] font-bold text-stone-700">{{ $alamatCount }}</span></a>
                    <a href="{{ route('pesanan.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-stone-600 transition hover:bg-stone-100"><span>Pesanan Saya</span><span class="rounded-full bg-[#F5EDE1] px-2.5 py-0.5 text-[11px] font-bold text-stone-700">{{ $pesananAktif }} Aktif</span></a>
                    <a href="{{ route('ulasan.index') }}" class="flex items-center justify-between rounded-2xl bg-[#F5EDE1] px-4 py-3 text-sm font-bold text-stone-900"><span>Ulasan Saya</span><span class="h-1.5 w-1.5 rounded-full bg-[#201A17]"></span></a>
                    <div class="my-2 border-t border-stone-100"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center rounded-2xl px-4 py-3 text-left text-sm font-bold text-red-700 transition hover:bg-red-50">Keluar dari Akun</button>
                    </form>
                </nav>
            </div>

            <div class="space-y-4 lg:col-span-9">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-stone-500">Satu pembelian = satu ulasan. Beli lagi produk yang sama untuk mengulas lagi.</p>
                    <a href="{{ route('pesanan.index') }}" class="rounded-full bg-[#201A17] px-4 py-2 text-xs font-bold text-white transition hover:bg-stone-800">Beri Ulasan di Pesanan →</a>
                </div>
                @forelse ($ulasans as $ulasan)
                    <div class="rounded-3xl border border-[#ECE4D8] bg-white p-5 shadow-sm sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-sm font-bold text-stone-900">{{ $ulasan->produk->nama ?? 'Produk Batik' }}</span>
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold border
                                {{ $ulasan->status === 'Disetujui' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($ulasan->status === 'Ditolak' ? 'bg-rose-50 text-rose-800 border-rose-200' : 'bg-amber-50 text-amber-800 border-amber-200') }}">
                                {{ $ulasan->status }}
                            </span>
                        </div>
                        <div class="mt-1 text-base tracking-wide">
                            @for ($s = 1; $s <= 5; $s++)
                                <span class="{{ $s <= $ulasan->rating ? 'text-amber-500' : 'text-stone-300' }}">★</span>
                            @endfor
                        </div>
                        <p class="mt-1 text-sm leading-relaxed text-stone-600">"{{ $ulasan->comment }}"</p>

                        @php
                            $userReviewPhotos = [];
                            if (!empty($ulasan->image_path)) {
                                $userReviewPhotos[] = \App\Support\MediaUrl::resolve($ulasan->image_path);
                            }
                            if (!empty($ulasan->photos) && is_array($ulasan->photos)) {
                                foreach ($ulasan->photos as $p) {
                                    $resP = \App\Support\MediaUrl::resolve($p) ?: $p;
                                    if ($resP && !in_array($resP, $userReviewPhotos, true)) {
                                        $userReviewPhotos[] = $resP;
                                    }
                                }
                            }
                        @endphp

                        @if (count($userReviewPhotos) > 0)
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                @foreach ($userReviewPhotos as $pUrl)
                                    <button type="button"
                                            @click="previewImage = '{{ $pUrl }}'"
                                            class="group relative h-20 w-20 overflow-hidden rounded-xl border border-stone-200 bg-stone-100 hover:ring-2 hover:ring-[#B58742]/50 transition-all cursor-pointer hover:scale-105 shadow-2xs"
                                            title="Klik untuk melihat foto ukuran penuh">
                                        <img src="{{ $pUrl }}" alt="Foto ulasan" class="h-full w-full object-cover">
                                        <span class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                                            </svg>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <p class="mt-2 text-[11px] text-stone-400">{{ $ulasan->created_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-stone-300 bg-white p-8 text-center">
                        <p class="font-bold text-stone-900">Belum ada ulasan</p>
                        <p class="mt-1 text-xs text-stone-500">Selesaikan pesanan, lalu beri ulasan dari halaman Pesanan Saya.</p>
                        <a href="{{ route('pesanan.index') }}" class="mt-4 inline-block rounded-xl bg-[#201A17] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-stone-800">Ke Pesanan Saya</a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Lightbox Modal Foto Ulasan Saya -->
        <div x-cloak
             x-show="previewImage !== null"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="previewImage = null"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4"
             @click="previewImage = null">
            <button type="button"
                    @click="previewImage = null"
                    class="absolute top-5 right-5 z-20 w-10 h-10 rounded-full bg-black/60 hover:bg-black/90 text-white flex items-center justify-center transition border border-white/20 cursor-pointer"
                    title="Tutup (Esc)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="relative max-w-4xl max-h-[85vh] flex items-center justify-center" @click.stop>
                <img :src="previewImage" alt="Foto Ulasan Full" class="max-h-[82vh] max-w-[88vw] w-auto h-auto rounded-2xl object-contain shadow-2xl border border-white/10 select-none">
            </div>
        </div>
    </div>
</x-storefront-layout>
