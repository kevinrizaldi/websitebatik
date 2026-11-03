<x-app-layout>
    <div class="space-y-6">

        {{-- Top Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-[#8C827A] mb-1 font-medium">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#1E1B18]">DASHBOARD</a>
                    <span>/</span>
                    <span class="text-[#B85D3B] font-semibold">AKUN &amp; PENGATURAN</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#1E1B18] tracking-tight">
                        Profil Kurator &amp; Keamanan Akun
                    </h1>
                    <span class="sr-only">Profil Admin</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FAF4ED] text-[#8C6D58] border border-[#E5DDD0] tracking-wider uppercase">
                        KURASI EDISI NO. #821
                    </span>
                </div>
                <p class="text-sm text-[#7A7269] mt-1">
                    Kelola identitas kurator utama, kredensial otentikasi dua langkah (2FA), serta hak akses staf atelier Hamzah Style Official.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="alert('Log audit aktivitas kurasi & akses studio tercatat rapi.')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#D9D0C5] bg-white hover:bg-[#FAF7F2] text-xs font-semibold text-[#1E1B18] transition shadow-xs">
                    <svg class="w-4 h-4 text-[#7A7269]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Log Aktivitas Keamanan</span>
                </button>
                <button type="button" onclick="document.getElementById('profileUpdateForm').submit()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#1E1B18] hover:bg-black text-xs font-semibold text-white transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </div>

        {{-- Alerts --}}
        @if (session('status') === 'profile-updated')
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs sm:text-sm shadow-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium">Profil kurator berhasil diperbarui.</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 font-bold">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs sm:text-sm shadow-xs">
                <p class="font-bold mb-1">Harap perbaiki kesalahan berikut:</p>
                <ul class="list-disc pl-5 space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Two-Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- Left Column: Curator Summary & Navigation (4 cols) --}}
            <div class="lg:col-span-4 space-y-6">

                {{-- Profile Identity Card --}}
                <div class="bg-white rounded-2xl border border-[#ECE5D8] p-6 text-center shadow-xs space-y-4">
                    <div class="relative w-24 h-24 mx-auto">
                        <div class="w-24 h-24 rounded-full border-2 border-[#B85D3B] p-1 bg-[#FAF7F2] overflow-hidden">
                            <div class="w-full h-full rounded-full bg-[#1E1B18] text-white flex items-center justify-center font-serif text-2xl font-bold">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        </div>
                        <span class="absolute bottom-1 right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center text-white text-[10px]">✓</span>
                    </div>

                    <div>
                        <h3 class="font-serif font-bold text-lg text-[#1E1B18]">{{ $user->name }}</h3>
                        <div class="flex items-center justify-center gap-1.5 mt-1.5 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#FAF4ED] text-[#8C6D58] border border-[#E5DDD0]">
                                Kepala Kurator &amp; Lead Admin
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#3D1E16] text-white">
                                TIER ADIBUSANA
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-[#F3EDE6] text-left space-y-2.5 text-xs text-[#5A544E]">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#8C6D58] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="font-mono">{{ $user->email }}</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#8C6D58] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>{{ $user->phone ?? '+62 811-2501-4789' }}</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#8C6D58] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Server Lisensi sejak Jan 2023</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#ECE5D8] text-[11px] text-[#8C6D58] flex items-center justify-between">
                        <div class="text-left">
                            <span class="block text-[9px] uppercase tracking-wider text-[#A86544] font-bold">Kemitraan Kurator</span>
                            <span class="font-bold text-[#1E1B18]">Zoni Wastra Atelier Borobudur</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">Aktif</span>
                    </div>

                    <button type="button" onclick="alert('Upload foto avatar kurator dibuka.')" class="w-full inline-flex items-center justify-center gap-2 py-2.5 rounded-xl border border-[#D9D0C5] bg-[#FAF7F2] hover:bg-white text-xs font-bold uppercase tracking-wider text-[#1E1B18] transition">
                        <svg class="w-4 h-4 text-[#7A7269]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>GANTI FOTO AVATAR</span>
                    </button>
                </div>

                {{-- Settings Sub-Navigation Menu --}}
                <div class="bg-white rounded-2xl border border-[#ECE5D8] p-3 shadow-xs space-y-1">
                    <div class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-[#8C827A]">
                        NAVIGASI PENGATURAN
                    </div>
                    <a href="#section-profil" class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-[#1E1B18] text-white text-xs font-semibold shadow-xs">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Informasi Profil</span>
                        </div>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="#section-password" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold text-[#5A544E] hover:bg-[#FAF7F2] hover:text-[#1E1B18] transition">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#8C827A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            <span>Kata Sandi &amp; Kunci</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-[#A89F91]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="#section-2fa" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold text-[#5A544E] hover:bg-[#FAF7F2] hover:text-[#1E1B18] transition">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#8C827A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Otentikasi 2FA</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">Aktif</span>
                    </a>

                    <a href="#section-staf" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold text-[#5A544E] hover:bg-[#FAF7F2] hover:text-[#1E1B18] transition">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#8C827A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Manajemen Staf</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#FAF4ED] text-[#8C6D58]">2 Kurator</span>
                    </a>

                    <a href="#section-notif" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold text-[#5A544E] hover:bg-[#FAF7F2] hover:text-[#1E1B18] transition">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#8C827A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span>Notifikasi Studio</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-[#A89F91]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="#section-2fa" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold text-[#5A544E] hover:bg-[#FAF7F2] hover:text-[#1E1B18] transition">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#8C827A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>Sesi Perangkat</span>
                        </div>
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    </a>
                </div>

                {{-- Security Callout Badge --}}
                <div class="bg-[#FAF7F2] rounded-2xl border border-[#ECE5D8] p-5 space-y-2 text-xs">
                    <div class="flex items-center gap-2 font-bold text-[#1E1B18]">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Keamanan Studio 100%</span>
                    </div>
                    <p class="text-[#7A7269] text-[11px] leading-relaxed">
                        Perangkat Anda terlindungi enkripsi end-to-end dengan otentikasi biometri di klaster private cloud Hamzah Style.
                    </p>
                    <a href="javascript:void(0)" onclick="alert('Kebijakan Privasi & Enkripsi Hamzah Style')" class="inline-block text-[11px] font-bold text-[#B85D3B] hover:underline pt-1">
                        Baca Kebijakan Privasi &amp; Arsip &rarr;
                    </a>
                </div>

            </div>

            {{-- Right Column: Main Form & Security Sections (8 cols) --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- BAGIAN 01: Informasi Pribadi & Identitas Kurator --}}
                <div id="section-profil" class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-[#F3EDE6]">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#8C827A]">BAGIAN 01</span>
                            <h3 class="font-bold text-sm text-[#1E1B18]">
                                Informasi Pribadi &amp; Identitas Kurator
                                <span class="sr-only">Informasi Akun</span>
                            </h3>
                        </div>
                        <span class="text-[10px] font-semibold text-[#8C827A] bg-[#FAF7F2] px-2.5 py-1 rounded-md border border-[#ECE5D8]">
                            Terakhir diubah: 12 Mei 2024
                        </span>
                    </div>

                    <form id="profileUpdateForm" method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">Nama Lengkap</label>
                                <div class="relative">
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                           class="w-full py-2.5 px-3.5 pr-10 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-medium focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                                    <svg class="w-4 h-4 text-[#8C827A] absolute right-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">Jabatan / Hak Akses</label>
                                <div class="relative">
                                    <input type="text" value="Master Artisan &amp; Lead Curator" disabled
                                           class="w-full py-2.5 px-3.5 pr-10 rounded-xl border border-[#ECE5D8] bg-[#F6F1EA] text-xs text-[#5A544E] font-medium cursor-not-allowed">
                                    <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[9px] font-bold text-[#B85D3B] bg-[#FAF4ED] px-1.5 py-0.5 rounded border border-[#E5DDD0]">
                                        Terkunci: Super Admin
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">Alamat Email Kurasi</label>
                                <div class="relative">
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                           class="w-full py-2.5 px-3.5 pr-20 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-mono focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[9px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                                        Terverifikasi
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">Nomor WhatsApp Operasional</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '+62 811-2501-4789') }}"
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-mono focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                                <p class="text-[10px] text-[#8C827A] mt-1">Digunakan untuk kirim alert stock kritis dan verifikasi order VIP.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">Lokasi Studio &amp; Sentra Kurasi</label>
                            <input type="text" name="lokasi" value="Surakarta &amp; D.I. Yogyakarta"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-[#1E1B18]">Bio Kurator &amp; Filosofi Karya</label>
                                <span class="text-[10px] text-[#8C827A]">98 / 240 Karakter</span>
                            </div>
                            <textarea rows="2" maxlength="240"
                                      class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">Menjaga pakem motif batik tradisional serta mengawal revolusi tekstil limbah kain perca nusantara.</textarea>
                        </div>

                        {{-- Capsule Banner --}}
                        <div class="p-3.5 rounded-xl bg-[#FAF7F2] border border-[#ECE5D8] flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-[#3D1E16] text-[#FFC4B2] flex items-center justify-center font-bold text-xs shrink-0">
                                CAPS
                            </div>
                            <div class="flex-1 text-xs">
                                <span class="text-[9px] font-bold text-[#B85D3B] uppercase tracking-wider">10 SEMESTER CAPSULE 2024</span>
                                <h4 class="font-bold text-[#1E1B18]">Soga Jawa &amp; Perca Daur Ulang Mandiri</h4>
                                <p class="text-[10px] text-[#7A7269]">Tercatat 14 koleksi eksklusif dipamerkan langsung dibawah profil kurator ini.</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="reset" class="px-4 py-2 rounded-xl border border-[#D9D0C5] text-xs font-semibold text-[#5A544E] hover:bg-[#FAF7F2] transition">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-[#1E1B18] hover:bg-black text-xs font-semibold text-white transition shadow-xs">
                                Simpan Semua Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                {{-- BAGIAN 02: Keamanan & Perubahan Kata Sandi --}}
                <div id="section-password" class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#F3EDE6]">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#8C827A]">BAGIAN 02</span>
                            <h3 class="font-bold text-sm text-[#1E1B18]">
                                Keamanan &amp; Perubahan Kata Sandi
                                <span class="sr-only">Ubah Password</span>
                            </h3>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">Kata Sandi Saat Ini</label>
                            <input type="password" name="current_password" required placeholder="••••••••••••"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">Kata Sandi Baru</label>
                                <input type="password" name="password" required placeholder="Min. 12 karakter kombinasi"
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi baru"
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                            </div>
                        </div>

                        {{-- Strength Indicator --}}
                        <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#ECE5D8] space-y-1.5">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="font-bold text-[#1E1B18]">KEKUATAN KATA SANDI:</span>
                                <span class="text-emerald-700 font-semibold">Kuat (12 Karakter + Simbol &amp; Angka)</span>
                            </div>
                            <div class="w-full bg-[#EBE2D7] h-1.5 rounded-full overflow-hidden">
                                <div class="bg-emerald-600 h-full rounded-full" style="width: 85%"></div>
                            </div>
                            <p class="text-[10px] text-[#8C827A] pt-1">
                                Gunakan kombinasi huruf kapital, angka, dan karakter simbolik unik demi melindungi data hak cipta motif batik.
                            </p>
                        </div>

                        <div class="flex justify-end pt-1">
                            <button type="submit" class="px-5 py-2.5 rounded-xl border border-[#D9D0C5] bg-[#FAF7F2] hover:bg-white text-xs font-semibold text-[#1E1B18] transition">
                                Perbarui Kata Sandi
                            </button>
                        </div>
                    </form>
                </div>

                {{-- BAGIAN 03: Otentikasi Dua Langkah (2FA) & Akses Studio --}}
                <div id="section-2fa" class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#F3EDE6]">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#8C827A]">BAGIAN 03</span>
                            <h3 class="font-bold text-sm text-[#1E1B18]">Otentikasi Dua Langkah (2FA) &amp; Akses Studio</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Aktif via Google Authenticator
                        </span>
                    </div>

                    <p class="text-xs text-[#7A7269] leading-relaxed">
                        Setiap login di perangkat atau jaringan baru memerlukan token 6-digit dari aplikasi otentikator pilihan Anda untuk memastikan perlindungan aset katalog privat.
                    </p>

                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-[#8C827A] mb-2">
                            SESI LOGIN &amp; PERANGKAT TERHUBUNG
                        </div>
                        <div class="space-y-2">
                            {{-- Device 1 --}}
                            <div class="p-3.5 rounded-xl bg-[#FAF7F2] border border-[#ECE5D8] flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-white border border-[#ECE5D8] flex items-center justify-center text-[#8C6D58]">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-[#1E1B18]">MacBook Pro M3 Max (Surakarta)</span>
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-[#1E1B18] text-white">UTAMA</span>
                                        </div>
                                        <p class="text-[10px] text-[#8C827A] mt-0.5">Surakarta, Indonesia • IP: 180.253.18.64 • Chrome Desktop 124</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Sedang Aktif
                                </span>
                            </div>

                            {{-- Device 2 --}}
                            <div class="p-3.5 rounded-xl bg-[#FAF7F2] border border-[#ECE5D8] flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-white border border-[#ECE5D8] flex items-center justify-center text-[#8C6D58]">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="text-xs">
                                        <span class="font-bold text-[#1E1B18]">iPad Pro Kurasi Gudang &amp; Stok</span>
                                        <p class="text-[10px] text-[#8C827A] mt-0.5">Surakarta, Indonesia • IP: 180.245.92.10 • Safari Mobile</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-[11px] text-[#8C827A]">Aktif 3 jam lalu</span>
                                    <button type="button" onclick="alert('Sesi perangkat iPad telah dihentikan.')" class="text-xs text-rose-600 hover:underline font-semibold">
                                        Keluar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="button" onclick="alert('Semua sesi perangkat lain berhasil dikeluarkan.')" class="px-4 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-xs font-semibold text-rose-700 transition">
                            Keluar Dari Semua Perangkat Lain
                        </button>
                    </div>
                </div>

                {{-- BAGIAN 04: Akses Delegasi Kurator Studio (Tim Terdaftar) --}}
                <div id="section-staf" class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#F3EDE6]">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#8C827A]">BAGIAN 04</span>
                            <h3 class="font-bold text-sm text-[#1E1B18]">Akses Delegasi Kurator Studio (Tim Terdaftar)</h3>
                        </div>
                        <button type="button" onclick="alert('Modal undang staf kurator baru dibuka.')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#D9D0C5] bg-[#FAF7F2] hover:bg-white text-xs font-semibold text-[#1E1B18] transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span>Undang Staf Baru</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="text-[10px] font-bold uppercase tracking-wider text-[#8C827A] border-b border-[#ECE5D8] pb-2">
                                    <th class="py-2">Anggota Tim</th>
                                    <th class="py-2">Peran &amp; Spesialisasi</th>
                                    <th class="py-2">Level Izin Akses</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F3EDE6]">
                                <tr>
                                    <td class="py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-[#FAF4ED] text-[#8C6D58] font-bold text-xs flex items-center justify-center border border-[#E5DDD0]">
                                                SR
                                            </div>
                                            <div>
                                                <div class="font-bold text-[#1E1B18]">Siti Rahayu</div>
                                                <div class="text-[10px] font-mono text-[#8C827A]">stok.logistik@hamzahstyle.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-[#5A544E]">Kurator Stok &amp; Pengiriman</td>
                                    <td class="py-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-[#FAF7F2] text-[#8C827A] border border-[#ECE5D8]">
                                            Akses Terbatas
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-[#FAF4ED] text-[#8C6D58] font-bold text-xs flex items-center justify-center border border-[#E5DDD0]">
                                                BP
                                            </div>
                                            <div>
                                                <div class="font-bold text-[#1E1B18]">Bimo Prasetyo</div>
                                                <div class="text-[10px] font-mono text-[#8C827A]">finance.dan@hamzahstyle.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-[#5A544E]">Finance &amp; Validasi Pembayaran</td>
                                    <td class="py-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#3D1E16] text-[#FFC4B2]">
                                            Akses Finansial
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#ECE5D8] flex items-center gap-2 text-xs text-[#8C6D58]">
                        <svg class="w-4 h-4 text-[#B85D3B] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Hanya Super Admin yang berhak merekrut kurator baru dan menghapus wewenang penandatanganan sertifikat resmi Hamzah Style.</span>
                    </div>
                </div>

                {{-- BAGIAN 05: Preferensi Notifikasi & Saluran Darurat --}}
                <div id="section-notif" class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-[#F3EDE6]">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#8C827A]">BAGIAN 05</span>
                        <h3 class="font-bold text-sm text-[#1E1B18]">Preferensi Notifikasi &amp; Saluran Darurat</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-[#FAF7F2] border border-[#ECE5D8]">
                            <div>
                                <h4 class="font-bold text-xs text-[#1E1B18]">Pemberitahuan Pesanan Adibusana Baru</h4>
                                <p class="text-[11px] text-[#7A7269] mt-0.5">Kirim pesan via WhatsApp otomatis untuk order senilai &gt; Rp 2.500.000 masuk.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-[#D9D0C5] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1E1B18]"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-[#FAF7F2] border border-[#ECE5D8]">
                            <div>
                                <h4 class="font-bold text-xs text-[#1E1B18]">Peringatan Resi Massal &amp; Sesi Baru</h4>
                                <p class="text-[11px] text-[#7A7269] mt-0.5">Dapatkan email verifikasi jika ada perangkat mencurigakan login ke konsol kurasi tengah malam.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-[#D9D0C5] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1E1B18]"></div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>
