<x-storefront-layout title="Ulasan Saya">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
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
                        @if ($ulasan->image_path)
                            <img src="{{ asset('storage/'.$ulasan->image_path) }}" alt="Foto ulasan" class="mt-3 h-20 w-20 rounded-xl border border-stone-200 object-cover">
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
    </div>
</x-storefront-layout>
