<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ulasan Saya - Hamzah Style Official</title>
    <meta name="description" content="Daftar ulasan produk yang pernah Anda berikan di Hamzah Style Official.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FAF7F2; color: #26211D; }
        .font-serif-title { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="min-h-screen bg-[#FAF7F2] text-[#26211D] antialiased selection:bg-[#B58742] selection:text-white"
      x-data="{ mobileMenuOpen: false, cartCount: {{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }} }">

    <header class="sticky top-0 z-40 border-b border-[#ECE4D8] bg-[#FAF7F2]/90 backdrop-blur-md">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="group flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#201A17] text-[#E5C38E] shadow-sm transition group-hover:bg-stone-800">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" /></svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-sans text-sm font-bold uppercase tracking-wider text-stone-900 sm:text-base">HAMZAH STYLE</span>
                    <span class="-mt-1 text-[10px] font-medium uppercase tracking-widest text-stone-500">OFFICIAL</span>
                </div>
            </a>
            <nav class="hidden items-center gap-8 md:flex">
                <a href="{{ url('/') }}" class="text-sm font-medium text-stone-600 transition hover:text-stone-900">Home</a>
                <a href="{{ route('koleksi.index') }}" class="text-sm font-medium text-stone-600 transition hover:text-stone-900">Produk</a>
                <a href="{{ route('pesanan.index') }}" class="text-sm font-medium text-stone-600 transition hover:text-stone-900">Pesanan</a>
                <a href="{{ route('profile.edit') }}" class="border-b-2 border-stone-900 pb-0.5 text-sm font-medium text-stone-900">Akun</a>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('keranjang.index') }}" class="relative rounded-full p-2 text-stone-600 transition hover:bg-stone-200/50 hover:text-stone-900" title="Keranjang Belanja">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    <span class="absolute right-1 top-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#B58742] text-[10px] font-bold text-white" x-text="cartCount">{{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }}</span>
                </a>
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="rounded-full p-2 text-stone-600 hover:bg-stone-200/50 md:hidden"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
            </div>
        </div>
        <div x-cloak x-show="mobileMenuOpen" class="border-t border-[#ECE4D8] bg-[#FAF7F2] px-4 py-3 md:hidden">
            <div class="flex flex-col gap-1 text-sm font-medium">
                <a href="{{ url('/') }}" class="rounded-lg px-3 py-2 hover:bg-stone-200/50">Home</a>
                <a href="{{ route('koleksi.index') }}" class="rounded-lg px-3 py-2 hover:bg-stone-200/50">Produk</a>
                <a href="{{ route('pesanan.index') }}" class="rounded-lg px-3 py-2 hover:bg-stone-200/50">Pesanan</a>
                <a href="{{ route('profile.edit') }}" class="rounded-lg bg-stone-900 px-3 py-2 text-white">Akun Saya</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
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
    </main>

    <footer class="border-t border-[#ECE4D8] bg-[#FAF7F2] py-8 text-xs text-stone-600">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 sm:flex-row sm:px-6 lg:px-8">
            <div class="flex items-center gap-2"><span class="font-bold uppercase text-stone-900">HAMZAH STYLE OFFICIAL</span><span>&bull;</span><span>E-Commerce Batik &amp; Olahan Kain Sisa</span></div>
            <p>&copy; {{ date('Y') }} Hamzah Style Official. Hak Cipta Dilindungi.</p>
        </div>
    </footer>
</body>
</html>
