<x-storefront-layout title="Profil Saya">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
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
                    <a href="{{ route('ulasan.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-stone-600 transition hover:bg-stone-100">
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
            </div>
        </div>
    </div>
</x-storefront-layout>
