<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya - Hamzah Style Official</title>
    <meta name="description" content="Kelola profil, alamat, pesanan, dan ulasan akun pelanggan Hamzah Style Official.">
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
      x-data="{ mobileMenuOpen: false, cartCount: {{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }}, toastMessage: '{{ session('success') ?? (session('status') === 'profile-updated' ? 'Profil berhasil diperbarui.' : (session('status') === 'password-updated' ? 'Kata sandi berhasil diperbarui.' : '')) }}' }"
      x-init="if (toastMessage) { setTimeout(() => { toastMessage = ''; }, 3500); }">

    <div x-cloak x-show="toastMessage" class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-xl border border-stone-700 bg-[#1F1916] px-5 py-3 text-white shadow-xl">
        <svg class="h-5 w-5 shrink-0 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span x-text="toastMessage" class="text-sm font-medium"></span>
    </div>

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
        <p class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-widest text-stone-500">
            <span class="h-1.5 w-1.5 rounded-full bg-[#8C2F1B]"></span> Akun Pelanggan
        </p>
        <h1 class="font-serif-title mt-2 text-3xl font-extrabold text-stone-900 sm:text-4xl">Profil Saya</h1>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- Kolom kiri: kartu identitas + navigasi --}}
            <div class="space-y-5 lg:col-span-4">
                <div class="rounded-3xl border border-[#ECE4D8] bg-white p-6 text-center shadow-sm">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#201A17] text-xl font-extrabold text-[#E5C38E]">
                        {{ strtoupper(substr($user->name, 0, 1)).strtoupper(substr(strrchr($user->name, ' ') ?: '', 1, 1)) }}
                    </div>
                    <h2 class="mt-3 font-bold text-stone-900">{{ $user->name }}</h2>
                    <p class="text-xs text-stone-500">{{ $user->email }}</p>
                    <span class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-[#F5EDE1] px-3 py-1 text-[11px] font-semibold text-stone-700">
                        Pelanggan Hamzah Style • Sejak {{ $user->created_at->format('Y') }}
                    </span>
                </div>

                <nav class="rounded-3xl border border-[#ECE4D8] bg-white p-3 shadow-sm">
                    <a href="{{ route('profile.edit') }}" class="flex items-center justify-between rounded-2xl bg-[#F5EDE1] px-4 py-3 text-sm font-bold text-stone-900">
                        <span class="flex items-center gap-3">Data Diri</span>
                        <span class="h-1.5 w-1.5 rounded-full bg-[#201A17]"></span>
                    </a>
                    <a href="{{ route('alamat.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-stone-600 transition hover:bg-stone-100">
                        <span>Daftar Alamat</span>
                        <span class="rounded-full bg-[#F5EDE1] px-2.5 py-0.5 text-[11px] font-bold text-stone-700">{{ $alamats->count() }}</span>
                    </a>
                    <a href="{{ route('pesanan.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-stone-600 transition hover:bg-stone-100">
                        <span>Pesanan Saya</span>
                        <span class="rounded-full bg-[#F5EDE1] px-2.5 py-0.5 text-[11px] font-bold text-stone-700">{{ $pesananAktif }} Aktif</span>
                    </a>
                    <a href="{{ route('pesanan.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-stone-600 transition hover:bg-stone-100">
                        <span>Ulasan Saya</span>
                        <span class="rounded-full bg-[#F5EDE1] px-2.5 py-0.5 text-[11px] font-bold text-stone-700">{{ $ulasanCount }}</span>
                    </a>
                    <div class="my-2 border-t border-stone-100"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 rounded-2xl px-4 py-3 text-left text-sm font-bold text-red-700 transition hover:bg-red-50">
                            Keluar dari Akun
                        </button>
                    </form>
                </nav>
            </div>

            {{-- Kolom kanan --}}
            <div class="space-y-6 lg:col-span-8">
                {{-- Statistik belanja (data nyata, scope PRD) --}}
                <div class="rounded-3xl border border-[#ECE4D8] bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-stone-900">Statistik Belanja</h3>
                        <span class="text-[11px] text-stone-500">Berdasarkan pesanan Anda</span>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl bg-[#FBF3EA] p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-500">Pesanan Selesai</p>
                            <p class="mt-1 text-3xl font-extrabold text-stone-900">{{ $stats['selesai'] }}</p>
                            <p class="mt-1 text-[11px] text-stone-500">Pesanan diterima & selesai</p>
                        </div>
                        <div class="rounded-2xl bg-[#FBF3EA] p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-500">Sedang Diproses</p>
                            <p class="mt-1 text-3xl font-extrabold text-stone-900">{{ $stats['diproses'] + $stats['dikirim'] }}</p>
                            <p class="mt-1 text-[11px] text-stone-500">Dikemas & dalam pengiriman</p>
                        </div>
                        <div class="rounded-2xl bg-[#FBF3EA] p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-500">Menunggu Pembayaran</p>
                            <p class="mt-1 text-3xl font-extrabold text-stone-900">{{ $stats['menunggu'] }}</p>
                            <p class="mt-1 text-[11px] text-stone-500">Selesaikan via Midtrans</p>
                        </div>
                    </div>
                </div>

                {{-- Informasi akun --}}
                <div class="rounded-3xl border border-[#ECE4D8] bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-stone-900">Informasi Akun & Data Diri</h3>
                            <p class="mt-1 text-xs text-stone-500">Kelola nama, email, dan nomor telepon akun Anda.</p>
                        </div>
                    </div>
                    <dl class="mt-5 grid grid-cols-1 gap-4 border-t border-stone-100 pt-5 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-stone-500">Nama Lengkap</dt>
                            <dd class="mt-1 font-semibold text-stone-900">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-stone-500">Alamat Email</dt>
                            <dd class="mt-1 font-semibold text-stone-900">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-stone-500">Nomor Telepon</dt>
                            <dd class="mt-1 font-semibold text-stone-900">{{ $user->phone ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-stone-500">Status Email</dt>
                            <dd class="mt-1 font-semibold text-stone-900">{{ $user->email_verified_at ? 'Terverifikasi' : 'Belum verifikasi' }}</dd>
                        </div>
                    </dl>
                    <details class="mt-5 rounded-2xl border border-stone-200 bg-stone-50/60 p-4">
                        <summary class="cursor-pointer text-sm font-bold text-stone-900">Edit Profil</summary>
                        <form method="POST" action="{{ route('profile.update') }}" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @csrf
                            @method('PATCH')
                            <div class="sm:col-span-1">
                                <label for="name" class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-stone-500">Nama Lengkap</label>
                                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-1">
                                <label for="phone" class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-stone-500">Nomor Telepon</label>
                                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 081234567890" class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                                @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="email" class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-stone-500">Alamat Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <button type="submit" class="w-full rounded-xl bg-[#201A17] py-2.5 text-xs font-bold text-white transition hover:bg-stone-800 sm:w-auto sm:px-6">Simpan Perubahan</button>
                            </div>
                        </form>
                    </details>
                </div>

                {{-- Kata sandi --}}
                <div class="rounded-3xl border border-[#ECE4D8] bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-stone-900">Keamanan Akun</h3>
                    <p class="mt-1 text-xs text-stone-500">Perbarui kata sandi secara berkala untuk menjaga keamanan akun.</p>
                    <form method="POST" action="{{ route('password.update') }}" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="current_password" class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-stone-500">Sandi Saat Ini</label>
                            <input id="current_password" type="password" name="current_password" class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                            @error('current_password', 'updatePassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password" class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-stone-500">Sandi Baru</label>
                            <input id="password" type="password" name="password" class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                            @error('password', 'updatePassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-stone-500">Konfirmasi Sandi</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                        </div>
                        <div class="sm:col-span-3">
                            <button type="submit" class="rounded-xl border border-stone-300 bg-white px-5 py-2.5 text-xs font-bold text-stone-800 transition hover:bg-stone-100">Perbarui Kata Sandi</button>
                            @if (session('status') === 'password-updated')
                                <span class="ml-2 text-xs font-semibold text-emerald-700">Tersimpan.</span>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Alamat utama --}}
                <div class="rounded-3xl border border-[#ECE4D8] bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-stone-900">Alamat Utama</h3>
                            <p class="mt-1 text-xs text-stone-500">Tujuan default pengiriman paket pesanan Anda.</p>
                        </div>
                        <a href="{{ route('alamat.index') }}" class="rounded-full bg-[#F5EDE1] px-4 py-2 text-xs font-bold text-stone-800 transition hover:bg-[#EFE3D2]">Kelola Semua Alamat ({{ $alamats->count() }} Alamat) →</a>
                    </div>
                    @if ($alamatUtama)
                        <div class="mt-4 rounded-2xl bg-[#FBF3EA] p-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-stone-900">{{ $alamatUtama->label_alamat }}</span>
                                <span class="rounded bg-[#201A17] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Utama</span>
                            </div>
                            <p class="mt-1 text-sm font-semibold text-stone-800">{{ $alamatUtama->penerima }} • ({{ $alamatUtama->no_telepon }})</p>
                            <p class="mt-1 text-sm leading-relaxed text-stone-600">{{ $alamatUtama->alamat_lengkap }}{{ $alamatUtama->kota ? ', '.$alamatUtama->kota : '' }}{{ $alamatUtama->provinsi ? ', '.$alamatUtama->provinsi : '' }}{{ $alamatUtama->kode_pos ? ' '.$alamatUtama->kode_pos : '' }}</p>
                        </div>
                    @else
                        <div class="mt-4 rounded-2xl border border-dashed border-stone-300 p-5 text-center">
                            <p class="text-sm font-semibold text-stone-700">Belum ada alamat tersimpan</p>
                            <p class="mt-1 text-xs text-stone-500">Tambahkan alamat agar checkout lebih cepat.</p>
                            <a href="{{ route('alamat.index') }}" class="mt-3 inline-block rounded-xl bg-[#201A17] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-stone-800">Tambah Alamat</a>
                        </div>
                    @endif
                </div>
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
