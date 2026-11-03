<x-app-layout>
    <div class="space-y-6">

        {{-- Top Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-[#F4EFEA] text-[11px] font-semibold text-[#8C6D58] tracking-wider uppercase mb-2">
                    <svg class="w-3.5 h-3.5 text-[#B85D3B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>KONFIGURASI ATELIER &amp; ALAMAT ASAL PENGIRIMAN</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#1E1B18] tracking-tight">
                    Pengaturan Toko &amp; Sentra Logistik
                </h1>
                <p class="text-sm text-[#7A7269] mt-1">
                    Atur identitas resmi atelier, nomor kontak layanan, dan titik asal pengiriman RajaOngkir untuk perhitungan ongkos kirim presisi.
                </p>
            </div>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center justify-between text-xs sm:text-sm shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 font-bold text-lg leading-none">&times;</button>
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

        {{-- Setting Form Card --}}
        <div class="bg-white rounded-2xl border border-[#ECE5D8] p-6 sm:p-8 shadow-xs max-w-4xl space-y-6">
            <form action="{{ route('admin.pengaturan.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PATCH')

                <div class="space-y-4">
                    <h3 class="font-bold text-sm text-[#1E1B18] pb-2 border-b border-[#F3EDE6]">
                        Identitas Resmi Atelier
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                Nama Toko / Atelier <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   name="nama_toko"
                                   value="{{ old('nama_toko', $setting->nama_toko) }}"
                                   required
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-medium focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                No. Telepon Operasional
                            </label>
                            <input type="text"
                                   name="no_telepon_toko"
                                   value="{{ old('no_telepon_toko', $setting->no_telepon_toko) }}"
                                   placeholder="Contoh: +62 811-2501-4789"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-mono focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                        </div>
                    </div>
                </div>

                <div class="space-y-4 pt-2">
                    <h3 class="font-bold text-sm text-[#1E1B18] pb-2 border-b border-[#F3EDE6]">
                        Titik Asal Ekspedisi (RajaOngkir)
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                Kota Asal Pengiriman <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   name="kota_asal"
                                   value="{{ old('kota_asal', $setting->kota_asal) }}"
                                   required
                                   placeholder="Contoh: Kota Surakarta (Solo)"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-medium focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                Provinsi Asal <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   name="provinsi_asal"
                                   value="{{ old('provinsi_asal', $setting->provinsi_asal) }}"
                                   required
                                   placeholder="Contoh: Jawa Tengah"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-medium focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                Kode Pos Sentra
                            </label>
                            <input type="text"
                                   name="kode_pos_asal"
                                   value="{{ old('kode_pos_asal', $setting->kode_pos_asal) }}"
                                   placeholder="Contoh: 57141"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-mono focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-[#FAF7F2] border border-[#ECE5D8] text-xs text-[#7A7269] flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-[#B85D3B] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Data kota asal digunakan secara langsung oleh API RajaOngkir untuk menghitung tarif pengiriman (JNE, POS, TIKI) saat pelanggan checkout.</span>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-[#F3EDE6]">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#1E1B18] hover:bg-black text-xs font-semibold text-white transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Pengaturan Toko</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
