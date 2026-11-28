<x-storefront-layout title="Daftar Alamat — Hamzah Style">
    <x-slot name="extraHead">
        <!-- Leaflet CSS & JS for OpenStreetMap Pin Point -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    </x-slot>

    <div class="min-h-screen bg-[#FAF7F2] py-8 sm:py-10" x-data="addressManager()">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12 lg:gap-8">
                {{-- Kolom Kiri: Sidebar Profil & Navigasi --}}
                <div class="lg:col-span-4">
                    <x-customer-sidebar active="alamat" :user="$user" :alamatCount="$alamats->count()" :pesananAktif="$pesananAktif" />
                </div>

                {{-- Kolom Kanan: Daftar Alamat Tersimpan --}}
                <div class="space-y-6 lg:col-span-8">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl">Daftar Alamat</h1>
                            <p class="mt-1 text-xs text-stone-500 sm:text-sm">Kelola buku alamat untuk kemudahan pengiriman pesanan Anda.</p>
                        </div>
                        <button @click="openAdd()" type="button" class="inline-flex items-center gap-2 rounded-xl bg-[#201A17] px-4 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-stone-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span>Tambah Alamat Baru</span>
                        </button>
                    </div>

                    {{-- Daftar Alamat Tersimpan --}}
                    <div class="space-y-4">
                        @forelse ($alamats as $alamat)
                            <div class="rounded-3xl border {{ $alamat->is_utama ? 'border-[#201A17] bg-[#FAF3EA]' : 'border-[#ECE4D8] bg-white' }} p-6 shadow-xs transition hover:border-[#DECDB8]">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <h3 class="font-bold text-stone-900 text-base">{{ $alamat->label_alamat }}</h3>
                                        @if ($alamat->is_utama)
                                            <span class="rounded-full bg-[#201A17] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Utama</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="mt-2 text-sm font-semibold text-stone-800">{{ $alamat->penerima }}</p>
                                <p class="text-xs text-stone-500 mt-0.5">{{ $alamat->no_telepon }}</p>
                                <p class="mt-2 text-xs sm:text-sm leading-relaxed text-stone-600">{{ $alamat->alamat_lengkap }}{{ ($alamat->kecamatan ?? $alamat->district) ? ', Kec. '.($alamat->kecamatan ?? $alamat->district) : '' }}{{ $alamat->kota ? ', '.$alamat->kota : '' }}{{ $alamat->provinsi ? ', '.$alamat->provinsi : '' }}{{ $alamat->kode_pos ? ' '.$alamat->kode_pos : '' }}</p>

                                @if ($alamat->latitude && $alamat->longitude)
                                    <div class="mt-2 flex items-center gap-1.5 text-xs text-[#8C6226]">
                                        <svg class="h-4 w-4 shrink-0 text-[#B58742]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                        <span class="font-medium">Titik Maps Terpasang (<span class="font-mono text-[11px]">{{ number_format($alamat->latitude, 5) }}, {{ number_format($alamat->longitude, 5) }}</span>)</span>
                                        <a href="https://www.google.com/maps?q={{ $alamat->latitude }},{{ $alamat->longitude }}" target="_blank" rel="noopener noreferrer" class="ml-1 inline-flex items-center gap-0.5 font-bold text-stone-800 underline hover:text-[#B58742]">
                                            <span>Buka di Maps</span>
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                        </a>
                                    </div>
                                @endif

                                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-stone-200/60 pt-3">
                                    <button @click="openEdit({{ $alamat->id }})" type="button" class="rounded-xl bg-[#F5EDE1] px-4 py-2 text-xs font-semibold text-stone-800 transition hover:bg-[#EFE3D2]">Ubah</button>
                                    <form action="{{ route('alamat.destroy', $alamat) }}" method="POST" onsubmit="return confirm('Hapus alamat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-50">Hapus</button>
                                    </form>
                                    @if (! $alamat->is_utama)
                                        <form action="{{ route('alamat.setDefault', $alamat) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-xl bg-stone-100 px-3 py-2 text-xs font-semibold text-stone-700 transition hover:bg-stone-200">Jadikan Utama</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="rounded-3xl border border-dashed border-[#ECE4D8] bg-white p-12 text-center shadow-xs">
                                <p class="font-bold text-stone-900 text-sm">Belum ada alamat tersimpan</p>
                                <p class="mt-1 text-xs text-stone-500">Tambahkan alamat pertama lewat tombol Tambah Alamat Baru.</p>
                                <button @click="openAdd()" type="button" class="mt-4 rounded-xl bg-[#201A17] px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-stone-800">+ Tambah Alamat</button>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= MODAL TAMBAH / UBAH ALAMAT ================= --}}
        <div x-cloak x-show="addressModalOpen"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="addressModalOpen = false"></div>

            <div class="relative w-full max-w-lg rounded-3xl border border-[#ECE4D8] bg-white p-6 shadow-2xl sm:p-8"
                 @click.stop>
                <div class="flex items-center justify-between border-b border-stone-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900" x-text="modalMode === 'add' ? 'Tambah Alamat Baru' : 'Ubah Alamat'">Tambah Alamat Baru</h3>
                        <p class="text-xs text-stone-500 mt-0.5">Lengkapi data alamat untuk pengiriman pesanan.</p>
                    </div>
                    <button @click="addressModalOpen = false" class="rounded-full p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3 max-h-[70vh] overflow-y-auto pr-1">
                    <div x-show="formErrors.length > 0" class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800">
                        <template x-for="(err, i) in formErrors" :key="i">
                            <p x-text="'• ' + err"></p>
                        </template>
                    </div>
                    {{-- Baris 1: Nama Lengkap & Nomor Telepon --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-stone-500">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   x-model="addrForm.penerima" 
                                   @input="addrForm.penerima = addrForm.penerima.replace(/[^a-zA-Z\s\'.()\-]/g, '')"
                                   placeholder="Nama Lengkap" 
                                   class="w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-stone-500">Nomor Telepon <span class="text-rose-500">*</span></label>
                            <input type="tel" 
                                   inputmode="numeric"
                                   maxlength="15"
                                   x-model="addrForm.phone" 
                                   @input="addrForm.phone = addrForm.phone.replace(/\D/g, '')"
                                   placeholder="Nomor Telepon (08xxxxxxxxxx)" 
                                   class="w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                        </div>
                    </div>

                    {{-- Baris 2: Pemilih Wilayah Terpadu ala Shopee (Provinsi, Kota, Kecamatan, Kode Pos) --}}
                    <div class="space-y-1.5" @click.outside="if (regionPickerOpen && addrForm.kota) regionPickerOpen = false">
                        <div class="flex items-center justify-between mb-0.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-500">
                                Provinsi, Kota, Kabupaten, Kecamatan, Kode Pos <span class="text-rose-500">*</span>
                            </label>
                            <button type="button"
                                    x-show="addrForm.provinsi"
                                    @click="addrForm.province_id = ''; addrForm.provinsi = ''; addrForm.city_id = ''; addrForm.kota = ''; addrForm.district_id = ''; addrForm.kecamatan = ''; addrForm.kodepos = ''; regionPickerOpen = true; activeRegionTab = 'provinsi';"
                                    class="text-[11px] font-medium text-stone-400 hover:text-stone-700">
                                Reset
                            </button>
                        </div>
                        
                        <div @click="regionPickerOpen = !regionPickerOpen; if (regionPickerOpen) { loadProvinces(); if (addrForm.city_id && !addrForm.kecamatan) { activeRegionTab = 'kecamatan'; loadDistricts(addrForm.city_id); } else if (addrForm.province_id && !addrForm.kota) { activeRegionTab = 'kota'; } else if (!addrForm.province_id) { activeRegionTab = 'provinsi'; } searchRegion = ''; }"
                             class="flex items-center justify-between w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm cursor-pointer hover:border-stone-400 transition select-none">
                            <span :class="regionDisplay ? 'text-stone-900 font-medium' : 'text-stone-400'"
                                  x-text="regionDisplay || 'Pilih Provinsi, Kota, Kabupaten, Kecamatan, Kode Pos'"></span>
                            <div class="flex items-center gap-1.5 text-stone-400">
                                <svg class="h-4 w-4 transition-transform duration-200"
                                     :class="regionPickerOpen ? 'rotate-180 text-stone-700' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Panel Tab Selector Terbuka secara IN-FLOW / Inline (Mendorong form ke bawah, TANPA menimpa peta Leaflet!) --}}
                        <div x-show="regionPickerOpen"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="w-full rounded-2xl border border-stone-200 bg-[#FAF7F2]/70 p-3 shadow-xs">
                            
                            {{-- Input Pencarian di dalam Dropdown --}}
                            <div class="relative mb-2">
                                <input type="text"
                                       x-model="searchRegion"
                                       :placeholder="'Cari ' + (activeRegionTab === 'provinsi' ? 'provinsi' : (activeRegionTab === 'kota' ? 'kota atau kabupaten' : (activeRegionTab === 'kecamatan' ? 'kecamatan' : 'kode pos'))) + '...'"
                                       class="w-full rounded-xl border border-stone-200 bg-white px-3 py-1.5 text-xs text-stone-800 placeholder-stone-400 focus:border-[#201A17] focus:bg-white focus:outline-none">
                                <svg class="absolute right-2.5 top-2.5 h-3.5 w-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>

                            {{-- Header Tab: Provinsi | Kota/Kabupaten | Kecamatan | Kode Pos --}}
                            <div class="grid grid-cols-4 border-b border-stone-200/80 text-xs font-semibold">
                                <button type="button"
                                        @click="activeRegionTab = 'provinsi'; searchRegion = '';"
                                        :class="activeRegionTab === 'provinsi' ? 'border-b-2 border-[#201A17] text-[#201A17] font-bold' : 'text-stone-400 hover:text-stone-600'"
                                        class="pb-2 px-1 text-center transition">
                                    Provinsi
                                </button>
                                <button type="button"
                                        @click="if (addrForm.province_id) { activeRegionTab = 'kota'; searchRegion = ''; }"
                                        :disabled="!addrForm.province_id"
                                        :class="activeRegionTab === 'kota' ? 'border-b-2 border-[#201A17] text-[#201A17] font-bold' : (addrForm.province_id ? 'text-stone-400 hover:text-stone-600' : 'text-stone-300 cursor-not-allowed')"
                                        class="pb-2 px-1 text-center transition">
                                    <span class="hidden sm:inline">Kota/Kabupaten</span>
                                    <span class="sm:hidden">Kota/Kab</span>
                                </button>
                                <button type="button"
                                        @click="if (addrForm.city_id) { activeRegionTab = 'kecamatan'; searchRegion = ''; }"
                                        :disabled="!addrForm.city_id"
                                        :class="activeRegionTab === 'kecamatan' ? 'border-b-2 border-[#201A17] text-[#201A17] font-bold' : (addrForm.city_id ? 'text-stone-400 hover:text-stone-600' : 'text-stone-300 cursor-not-allowed')"
                                        class="pb-2 px-1 text-center transition">
                                    Kecamatan
                                </button>
                                <button type="button"
                                        @click="if (addrForm.city_id) { activeRegionTab = 'kodepos'; }"
                                        :disabled="!addrForm.city_id"
                                        :class="activeRegionTab === 'kodepos' ? 'border-b-2 border-[#201A17] text-[#201A17] font-bold' : (addrForm.city_id ? 'text-stone-400 hover:text-stone-600' : 'text-stone-300 cursor-not-allowed')"
                                        class="pb-2 px-1 text-center transition">
                                    Kode Pos
                                </button>
                            </div>

                            {{-- Tab Provinsi --}}
                            <div x-show="activeRegionTab === 'provinsi'" class="mt-2 max-h-48 overflow-y-auto overscroll-contain space-y-0.5 pr-1">
                                <div x-show="loadingProvinces" class="py-6 text-center text-xs text-stone-400">
                                    Memuat daftar provinsi...
                                </div>
                                <template x-for="p in filteredProvinces" :key="p.province_id">
                                    <button type="button"
                                            @click="selectProvince(p)"
                                            :class="String(addrForm.province_id) === String(p.province_id) ? 'bg-[#FAF3EA] text-[#8C6226] font-bold' : 'text-stone-700 hover:bg-stone-100/70 bg-white'"
                                            class="w-full text-left px-3 py-2 text-xs rounded-lg transition flex items-center justify-between border border-transparent hover:border-stone-200">
                                        <span x-text="p.province"></span>
                                        <span x-show="String(addrForm.province_id) === String(p.province_id)" class="text-[#8C6226]">✓</span>
                                    </button>
                                </template>
                                <div x-show="!loadingProvinces && filteredProvinces.length === 0" class="py-4 text-center text-xs text-stone-400">
                                    Tidak ada provinsi yang cocok.
                                </div>
                            </div>

                            {{-- Tab Kota/Kabupaten --}}
                            <div x-show="activeRegionTab === 'kota'" class="mt-2 max-h-48 overflow-y-auto overscroll-contain space-y-0.5 pr-1">
                                <div x-show="loadingCities" class="py-6 text-center text-xs text-stone-400">
                                    Memuat daftar kota & kabupaten...
                                </div>
                                <template x-for="c in filteredCities" :key="c.city_id + '-' + c.type + '-' + c.city_name">
                                    <button type="button"
                                            @click="selectCity(c)"
                                            :class="String(addrForm.city_id) === String(c.city_id) && addrForm.kota === ((c.type ? c.type + ' ' : '') + c.city_name) ? 'bg-[#FAF3EA] text-[#8C6226] font-bold' : 'text-stone-700 hover:bg-stone-100/70 bg-white'"
                                            class="w-full text-left px-3 py-2 text-xs rounded-lg transition flex items-center justify-between border border-transparent hover:border-stone-200">
                                        <span x-text="(c.type ? c.type + ' ' : '') + c.city_name"></span>
                                        <span class="text-[10px] text-stone-400 font-mono" x-show="c.postal_code" x-text="c.postal_code"></span>
                                    </button>
                                </template>
                                <div x-show="!loadingCities && filteredCities.length === 0" class="py-4 text-center text-xs text-stone-400">
                                    Tidak ada kota atau kabupaten yang cocok.
                                </div>
                            </div>

                            {{-- Tab Kecamatan --}}
                            <div x-show="activeRegionTab === 'kecamatan'" class="mt-2 max-h-48 overflow-y-auto overscroll-contain space-y-0.5 pr-1">
                                <div x-show="loadingDistricts" class="py-6 text-center text-xs text-stone-400">
                                    Memuat daftar kecamatan...
                                </div>
                                <template x-for="d in filteredDistricts" :key="d.district_id">
                                    <button type="button"
                                            @click="selectDistrict(d)"
                                            :class="String(addrForm.district_id) === String(d.district_id) ? 'bg-[#FAF3EA] text-[#8C6226] font-bold' : 'text-stone-700 hover:bg-stone-100/70 bg-white'"
                                            class="w-full text-left px-3 py-2 text-xs rounded-lg transition flex items-center justify-between border border-transparent hover:border-stone-200">
                                        <span x-text="d.district_name"></span>
                                        <span x-show="String(addrForm.district_id) === String(d.district_id)" class="text-[#8C6226]">✓</span>
                                    </button>
                                </template>
                                <div x-show="!loadingDistricts && filteredDistricts.length === 0" class="py-3 text-center space-y-2">
                                    <p class="text-xs text-stone-500">Kecamatan tidak ditemukan dalam daftar otomatis.</p>
                                    <div class="flex items-center gap-2 px-2">
                                        <input type="text"
                                               x-model="addrForm.kecamatan"
                                               placeholder="Ketik nama kecamatan..."
                                               class="w-full rounded-xl border border-stone-200 bg-white px-3 py-1.5 text-xs focus:border-stone-400 focus:outline-none">
                                        <button type="button"
                                                @click="activeRegionTab = 'kodepos'; searchAddressLocation(false);"
                                                class="rounded-xl bg-[#201A17] text-white px-3 py-1.5 text-xs font-semibold hover:bg-stone-800 transition whitespace-nowrap">
                                            Pilih
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Tab Kode Pos Otomatis ala Shopee --}}
                            <div x-show="activeRegionTab === 'kodepos'" class="mt-2 space-y-2">
                                <div x-show="loadingPostalCodes" class="py-6 text-center text-xs text-stone-400">
                                    <svg class="w-4 h-4 animate-spin inline mr-1.5 text-[#B58742]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    Memuat daftar kode pos...
                                </div>

                                <div x-show="!loadingPostalCodes && postalCodesList.length > 0" class="max-h-48 overflow-y-auto overscroll-contain space-y-0.5 pr-1">
                                    <template x-for="item in filteredPostalCodes" :key="item.postal_code + '-' + (item.subdistrict || '')">
                                        <button type="button"
                                                @click="selectPostalCode(item.postal_code)"
                                                :class="addrForm.kodepos === item.postal_code ? 'bg-[#FAF3EA] text-[#8C6226] font-bold' : 'text-stone-700 hover:bg-stone-100/70 bg-white'"
                                                class="w-full text-left px-3 py-2 text-xs rounded-lg transition flex items-center justify-between border border-transparent hover:border-stone-200">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-bold" x-text="item.postal_code"></span>
                                                <span class="text-[11px] font-normal" :class="addrForm.kodepos === item.postal_code ? 'text-[#8C6226]' : 'text-stone-500'" x-show="item.subdistrict" x-text="'(' + item.subdistrict + ')'"></span>
                                            </div>
                                            <span x-show="addrForm.kodepos === item.postal_code" class="text-[#8C6226]">✓</span>
                                        </button>
                                    </template>
                                </div>

                                <div x-show="!loadingPostalCodes && filteredPostalCodes.length === 0 && postalCodesList.length > 0" class="py-4 text-center text-xs text-stone-400">
                                    Tidak ada kode pos yang cocok dengan pencarian.
                                </div>

                                <div x-show="!loadingPostalCodes && postalCodesList.length === 0" class="py-3 text-center space-y-2">
                                    <p class="text-xs text-stone-500">Kode pos tidak ditemukan dalam daftar otomatis.</p>
                                    <div class="flex items-center gap-2 px-2">
                                        <input type="text"
                                               x-model="addrForm.kodepos"
                                               maxlength="5"
                                               placeholder="Ketik 5 digit kode pos..."
                                               class="w-full rounded-xl border border-stone-200 bg-white px-3 py-1.5 text-xs focus:border-stone-400 focus:outline-none">
                                        <button type="button"
                                                @click="regionPickerOpen = false;"
                                                class="rounded-xl bg-[#201A17] text-white px-3 py-1.5 text-xs font-semibold hover:bg-stone-800 transition whitespace-nowrap">
                                            Pilih
                                        </button>
                                    </div>
                                </div>

                                <div x-show="!loadingPostalCodes && postalCodesList.length > 0" class="pt-2 border-t border-stone-200/60 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-stone-500 text-[11px]">Kode Pos:</span>
                                        <span class="font-mono font-bold text-stone-900 bg-stone-100 px-2 py-0.5 rounded" x-text="addrForm.kodepos || '-'"></span>
                                    </div>
                                    <button type="button"
                                            @click="regionPickerOpen = false;"
                                            class="rounded-xl bg-[#201A17] text-white px-4 py-1.5 text-xs font-semibold hover:bg-stone-800 transition shadow-xs">
                                        OK / Selesai
                                    </button>
                                </div>
                            </div>

                            {{-- Footer Panel: Tombol Tutup --}}
                            <div class="mt-2 pt-2 border-t border-stone-200/80 flex items-center justify-between text-[11px]">
                                <span class="text-stone-400">Pilih wilayah dari tab di atas</span>
                                <button type="button"
                                        @click="regionPickerOpen = false"
                                        class="font-semibold text-stone-600 hover:text-stone-900 transition">
                                    Tutup Pilihan ✕
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Baris 3: Nama Jalan, Gedung, No. Rumah --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-500">
                                Nama Jalan, Gedung, No. Rumah <span class="text-rose-500">*</span>
                            </label>
                            <button type="button" 
                                    @click="searchAddressLocation(true)" 
                                    :disabled="isSearchingLocation || !addrForm.lengkap" 
                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#8C6226] hover:text-[#B58742] disabled:opacity-40 transition">
                                <svg class="w-3 h-3 animate-spin" x-show="isSearchingLocation" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <svg class="w-3 h-3" x-show="!isSearchingLocation" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                                <span x-text="isSearchingLocation ? 'Mencari...' : 'Cari di Peta'"></span>
                            </button>
                        </div>
                        <textarea x-model="addrForm.lengkap" 
                                  @input="onAddressInput()" 
                                  rows="3" 
                                  placeholder="Nama Jalan, Nomor Gedung/Rumah, RT/RW, Patokan" 
                                  class="w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm focus:border-stone-400 focus:outline-none"></textarea>
                        
                        {{-- Notifikasi Otomatis ala Shopee --}}
                        <div x-show="isSearchingLocation" class="mt-1 flex items-center gap-1.5 text-[11px] text-[#8C6226]">
                            <svg class="h-3 w-3 animate-spin text-[#B58742]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Menyesuaikan titik peta ke alamat jalan...</span>
                        </div>
                        <div x-show="locationAutoFound && !isSearchingLocation" class="mt-1 flex items-center gap-1.5 text-[11px] text-emerald-700">
                            <svg class="h-3.5 w-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Titik peta otomatis disesuaikan ke alamat jalan. Geser pin jika ingin lebih presisi.</span>
                        </div>
                    </div>

                    {{-- Baris 4: Titik Pin Peta (OpenStreetMap via Leaflet) --}}
                    <div class="space-y-2 pt-2 border-t border-stone-200/80">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-stone-500">
                                    Titik Pin Lokasi (Peta)
                                </label>
                                <p class="text-[11px] text-stone-500">
                                    Klik peta atau geser pin merah ke lokasi rumah yang tepat.
                                </p>
                            </div>
                            <button type="button" 
                                    @click="getCurrentLocation()" 
                                    :disabled="geoLocating" 
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-[#FAF3EA] border border-[#DECDB8] px-2.5 py-1 text-[11px] font-semibold text-[#8C6226] hover:bg-[#F2E5D3] transition">
                                <svg class="w-3.5 h-3.5 animate-spin" x-show="geoLocating" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <svg class="w-3.5 h-3.5 text-[#B58742]" x-show="!geoLocating" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                <span x-text="geoLocating ? 'Mencari...' : 'Gunakan Lokasi Saya'"></span>
                            </button>
                        </div>

                        {{-- Leaflet Map Container --}}
                        <div class="relative z-0 isolate overflow-hidden rounded-2xl border border-stone-200 bg-stone-100" style="isolation: isolate;">
                            <div id="leaflet-map-picker" style="height: 240px; width: 100%; min-height: 220px;" class="w-full z-0"></div>
                            <div x-show="resolvingAddress" class="absolute top-2 right-2 bg-white/90 backdrop-blur-xs px-2 py-1 rounded text-[10px] font-semibold text-stone-600 shadow-xs border border-stone-200 z-10 flex items-center gap-1">
                                <span class="animate-pulse">●</span> Mencari nama jalan...
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-1 text-[11px]">
                            <div class="space-y-0.5">
                                <template x-if="addrForm.latitude && addrForm.longitude">
                                    <p class="text-stone-700">
                                        📍 Koordinat: <span class="font-mono font-semibold text-stone-900" x-text="Number(addrForm.latitude).toFixed(6) + ', ' + Number(addrForm.longitude).toFixed(6)"></span>
                                    </p>
                                </template>
                                <template x-if="pinAddressHint">
                                    <p class="text-[10px] text-stone-500 line-clamp-1 italic" x-text="'Patokan: ' + pinAddressHint"></p>
                                </template>
                            </div>
                            <button type="button" 
                                    x-show="addrForm.latitude && addrForm.longitude" 
                                    @click="clearPin()" 
                                    class="text-xs font-medium text-rose-600 hover:underline">
                                Hapus Pin
                            </button>
                        </div>

                        {{-- Pesan Status Geolocation Non-blocking --}}
                        <div x-show="geoErrorMsg" class="mt-1.5 p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-800 flex items-start justify-between gap-2">
                            <div class="flex items-start gap-1.5">
                                <span class="text-amber-600 font-bold shrink-0">ℹ️</span>
                                <span class="whitespace-pre-line leading-relaxed" x-text="geoErrorMsg"></span>
                            </div>
                            <button type="button" @click="geoErrorMsg = ''" class="text-amber-500 hover:text-amber-800 font-bold shrink-0">✕</button>
                        </div>
                    </div>

                    {{-- Baris 5: Tandai Sebagai (Rumah / Kantor) ala Shopee --}}
                    <div class="pt-1">
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-stone-500">Tandai Sebagai</label>
                        <div class="flex items-center gap-2.5">
                            <button type="button" 
                                    @click="addrForm.label = 'Rumah'" 
                                    :class="addrForm.label === 'Rumah' ? 'border-[#201A17] bg-[#201A17] text-white shadow-xs font-bold' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300'"
                                    class="inline-flex items-center justify-center rounded-xl border px-4 py-2 text-xs font-semibold transition">
                                Rumah
                            </button>
                            <button type="button" 
                                    @click="addrForm.label = 'Kantor'" 
                                    :class="addrForm.label === 'Kantor' ? 'border-[#201A17] bg-[#201A17] text-white shadow-xs font-bold' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300'"
                                    class="inline-flex items-center justify-center rounded-xl border px-4 py-2 text-xs font-semibold transition">
                                Kantor
                            </button>
                        </div>
                    </div>

                    {{-- Baris 6: Atur Sebagai Alamat Utama --}}
                    <label class="flex items-center gap-2.5 text-xs text-stone-700 pt-1 cursor-pointer select-none">
                        <input type="checkbox" x-model="addrForm.utama" class="h-4 w-4 rounded border-stone-300 text-stone-900 focus:ring-0">
                        <span class="font-medium">Atur sebagai Alamat Utama</span>
                    </label>
                </div>

                {{-- Footer Modal: Nanti Saja / Simpan Alamat --}}
                <div class="mt-6 flex items-center justify-end gap-3 border-t border-stone-100 pt-3">
                    <button @click="addressModalOpen = false" type="button" class="rounded-xl px-4 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition">Nanti Saja</button>
                    <button @click="submitModalForm()" type="button" :disabled="saving" class="rounded-xl bg-[#201A17] px-6 py-2.5 text-xs font-semibold text-white hover:bg-stone-800 disabled:opacity-50 transition shadow-xs">
                        <span x-text="saving ? 'Menyimpan...' : (modalMode === 'add' ? 'Simpan Alamat' : 'Simpan Perubahan')"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function addressManager() {
            return {
                addressModalOpen: false,
                modalMode: 'add',
                editingId: null,
                saving: false,
                formErrors: [],
                provinces: [],
                cities: [],
                districts: [],
                postalCodesList: [],
                loadingProvinces: false,
                loadingCities: false,
                loadingDistricts: false,
                loadingPostalCodes: false,
                geoErrorMsg: '',
                regionPickerOpen: false,
                activeRegionTab: 'provinsi',
                searchRegion: '',
                get regionDisplay() {
                    const parts = [];
                    if (this.addrForm.provinsi) parts.push(this.addrForm.provinsi);
                    if (this.addrForm.kota) parts.push(this.addrForm.kota);
                    if (this.addrForm.kecamatan) parts.push(this.addrForm.kecamatan);
                    if (this.addrForm.kodepos) parts.push(this.addrForm.kodepos);
                    return parts.join(', ');
                },
                get filteredProvinces() {
                    if (!this.searchRegion) return this.provinces;
                    const s = this.searchRegion.toLowerCase();
                    return this.provinces.filter(p => (p.province || '').toLowerCase().includes(s));
                },
                get filteredCities() {
                    if (!this.searchRegion) return this.cities;
                    const s = this.searchRegion.toLowerCase();
                    return this.cities.filter(c => ((c.type || '') + ' ' + (c.city_name || '')).toLowerCase().includes(s));
                },
                get filteredDistricts() {
                    if (!this.searchRegion) return this.districts;
                    const s = this.searchRegion.toLowerCase();
                    return this.districts.filter(d => (d.district_name || '').toLowerCase().includes(s));
                },
                get filteredPostalCodes() {
                    if (!this.searchRegion) return this.postalCodesList;
                    const s = this.searchRegion.toLowerCase();
                    return this.postalCodesList.filter(item => 
                        (item.postal_code || '').toLowerCase().includes(s) || 
                        (item.subdistrict || '').toLowerCase().includes(s)
                    );
                },
                selectProvince(p) {
                    this.addrForm.province_id = String(p.province_id);
                    this.addrForm.provinsi = p.province;
                    this.addrForm.city_id = '';
                    this.addrForm.kota = '';
                    this.addrForm.district_id = '';
                    this.addrForm.kecamatan = '';
                    this.addrForm.kodepos = '';
                    this.postalCodesList = [];
                    this.searchRegion = '';
                    this.activeRegionTab = 'kota';
                    this.loadCities(p.province_id);
                },
                selectCity(c) {
                    this.addrForm.city_id = String(c.city_id);
                    this.addrForm.kota = (c.type ? c.type + ' ' : '') + c.city_name;
                    this.addrForm.district_id = '';
                    this.addrForm.kecamatan = '';
                    this.postalCodesList = [];
                    if (c.postal_code) {
                        this.addrForm.kodepos = c.postal_code;
                    }
                    this.searchRegion = '';
                    this.activeRegionTab = 'kecamatan';
                    this.loadDistricts(c.city_id);
                },
                selectDistrict(d) {
                    this.addrForm.district_id = String(d.district_id);
                    this.addrForm.kecamatan = d.district_name;
                    this.searchRegion = '';
                    this.activeRegionTab = 'kodepos';
                    this.loadPostalCodes(d.district_name, this.addrForm.kota, this.addrForm.city_id);
                    this.searchAddressLocation(false);
                },
                async loadPostalCodes(districtName, cityName, cityId) {
                    this.loadingPostalCodes = true;
                    try {
                        const params = new URLSearchParams();
                        if (districtName) params.append('district_name', districtName);
                        if (cityName) params.append('city_name', cityName);
                        if (cityId) params.append('city_id', cityId);

                        const res = await fetch('{{ route('rajaongkir.postal-codes') }}?' + params.toString());
                        const data = await res.json();
                        if (data.success && Array.isArray(data.items)) {
                            this.postalCodesList = data.items;
                            if (data.default_postal_code) {
                                this.addrForm.kodepos = data.default_postal_code;
                            }
                        }
                    } catch (e) {
                        console.error('Failed loading postal codes', e);
                    } finally {
                        this.loadingPostalCodes = false;
                    }
                },
                selectPostalCode(code) {
                    this.addrForm.kodepos = code;
                    this.regionPickerOpen = false;
                },
                geoLocating: false,
                resolvingAddress: false,
                isSearchingLocation: false,
                locationAutoFound: false,
                autoFoundLabel: '',
                searchDebounceTimer: null,
                pinAddressHint: '',
                leafletMap: null,
                leafletMarker: null,
                addrForm: { label: '', penerima: '', phone: '', provinsi: '', kota: '', kecamatan: '', province_id: '', city_id: '', district_id: '', kodepos: '', lengkap: '', utama: false, latitude: null, longitude: null },
                addressList: [
                    @foreach($alamats as $alamat)
                    {
                        id: {{ $alamat->id }},
                        label: {!! json_encode($alamat->label_alamat) !!},
                        penerima: {!! json_encode($alamat->penerima) !!},
                        phone: {!! json_encode($alamat->no_telepon) !!},
                        provinsi: {!! json_encode($alamat->provinsi ?? '') !!},
                        kota: {!! json_encode($alamat->kota) !!},
                        kecamatan: {!! json_encode($alamat->kecamatan ?? $alamat->district ?? '') !!},
                        province_id: '{{ $alamat->province_id ?? '' }}',
                        city_id: '{{ $alamat->city_id ?? '' }}',
                        district_id: '{{ $alamat->district_id ?? '' }}',
                        kodepos: {!! json_encode($alamat->kode_pos ?? '') !!},
                        lengkap: {!! json_encode($alamat->alamat_lengkap) !!},
                        utama: {{ $alamat->is_utama ? 'true' : 'false' }},
                        latitude: {{ $alamat->latitude !== null ? (float)$alamat->latitude : 'null' }},
                        longitude: {{ $alamat->longitude !== null ? (float)$alamat->longitude : 'null' }}
                    }@if(!$loop->last),@endif
                    @endforeach
                ],
                init() {
                    this.$watch('addressModalOpen', val => {
                        if (val) {
                            setTimeout(() => this.initOrRefreshMap(), 150);
                            setTimeout(() => this.initOrRefreshMap(), 400);
                        }
                    });
                },
                async loadProvinces() {
                    if (this.provinces.length > 0) return;
                    this.loadingProvinces = true;
                    try {
                        const res = await fetch('{{ route('rajaongkir.provinces') }}');
                        const data = await res.json();
                        if (data.success && Array.isArray(data.provinces)) {
                            this.provinces = data.provinces;
                        }
                    } catch (e) {
                        console.error('Failed loading provinces', e);
                    } finally {
                        this.loadingProvinces = false;
                    }
                },
                async loadCities(provinceId) {
                    if (!provinceId) {
                        this.cities = [];
                        return;
                    }
                    this.loadingCities = true;
                    try {
                        const res = await fetch('{{ route('rajaongkir.cities') }}?province_id=' + provinceId);
                        const data = await res.json();
                        if (data.success && Array.isArray(data.cities)) {
                            this.cities = data.cities;
                        }
                    } catch (e) {
                        console.error('Failed loading cities', e);
                    } finally {
                        this.loadingCities = false;
                    }
                },
                async loadDistricts(cityId) {
                    if (!cityId) {
                        this.districts = [];
                        return;
                    }
                    this.loadingDistricts = true;
                    try {
                        const res = await fetch('{{ route('rajaongkir.districts') }}?city_id=' + cityId);
                        const data = await res.json();
                        if (data.success && Array.isArray(data.districts)) {
                            this.districts = data.districts;
                        }
                    } catch (e) {
                        console.error('Failed loading districts', e);
                    } finally {
                        this.loadingDistricts = false;
                    }
                },
                openAdd() {
                    this.modalMode = 'add';
                    this.editingId = null;
                    this.formErrors = [];
                    this.pinAddressHint = '';
                    this.isSearchingLocation = false;
                    this.locationAutoFound = false;
                    this.autoFoundLabel = '';
                    this.regionPickerOpen = false;
                    this.activeRegionTab = 'provinsi';
                    this.searchRegion = '';
                    this.postalCodesList = [];
                    this.geoErrorMsg = '';
                    clearTimeout(this.searchDebounceTimer);
                    this.addrForm = {
                        label: 'Rumah',
                        penerima: '',
                        phone: '',
                        provinsi: '',
                        kota: '',
                        kecamatan: '',
                        province_id: '',
                        city_id: '',
                        district_id: '',
                        kodepos: '',
                        lengkap: '',
                        utama: this.addressList.length === 0,
                        latitude: null,
                        longitude: null
                    };
                    this.loadProvinces();
                    this.addressModalOpen = true;
                },
                openEdit(id) {
                    const a = this.addressList.find(x => x.id === id);
                    if (!a) return;
                    this.modalMode = 'edit';
                    this.editingId = id;
                    this.formErrors = [];
                    this.pinAddressHint = '';
                    this.isSearchingLocation = false;
                    this.locationAutoFound = false;
                    this.autoFoundLabel = '';
                    this.regionPickerOpen = false;
                    this.activeRegionTab = 'provinsi';
                    this.searchRegion = '';
                    this.postalCodesList = [];
                    this.geoErrorMsg = '';
                    clearTimeout(this.searchDebounceTimer);
                    this.addrForm = {
                        label: a.label || 'Rumah',
                        penerima: a.penerima,
                        phone: a.phone,
                        provinsi: a.provinsi,
                        kota: a.kota,
                        kecamatan: a.kecamatan || '',
                        province_id: a.province_id ? String(a.province_id) : '',
                        city_id: a.city_id ? String(a.city_id) : '',
                        district_id: a.district_id ? String(a.district_id) : '',
                        kodepos: a.kodepos,
                        lengkap: a.lengkap,
                        utama: a.utama,
                        latitude: a.latitude !== null && a.latitude !== undefined ? parseFloat(a.latitude) : null,
                        longitude: a.longitude !== null && a.longitude !== undefined ? parseFloat(a.longitude) : null
                    };
                    this.loadProvinces().then(() => {
                        if (a.province_id) {
                            this.loadCities(a.province_id).then(() => {
                                if (a.city_id) this.loadDistricts(a.city_id);
                            });
                        }
                    });
                    this.addressModalOpen = true;
                },
                initOrRefreshMap() {
                    const el = document.getElementById('leaflet-map-picker');
                    if (!el || typeof L === 'undefined') return;

                    const hasCoords = this.addrForm.latitude !== null && this.addrForm.longitude !== null && !isNaN(this.addrForm.latitude);
                    const defaultLat = hasCoords ? parseFloat(this.addrForm.latitude) : -7.5666;
                    const defaultLng = hasCoords ? parseFloat(this.addrForm.longitude) : 110.8290;

                    if (!this.leafletMap) {
                        this.leafletMap = L.map(el, {
                            center: [defaultLat, defaultLng],
                            zoom: hasCoords ? 16 : 13,
                            scrollWheelZoom: false
                        });

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors',
                            maxZoom: 19
                        }).addTo(this.leafletMap);

                        this.leafletMarker = L.marker([defaultLat, defaultLng], {
                            draggable: true
                        }).addTo(this.leafletMap);

                        if (!hasCoords) {
                            this.leafletMarker.setOpacity(0.4);
                        }

                        this.leafletMarker.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            this.setCoordinates(pos.lat, pos.lng);
                        });

                        this.leafletMap.on('click', (e) => {
                            this.setCoordinates(e.latlng.lat, e.latlng.lng);
                            this.leafletMarker.setLatLng(e.latlng);
                            this.leafletMarker.setOpacity(1);
                        });
                    } else {
                        this.leafletMap.setView([defaultLat, defaultLng], hasCoords ? 16 : 13);
                        this.leafletMarker.setLatLng([defaultLat, defaultLng]);
                        this.leafletMarker.setOpacity(hasCoords ? 1 : 0.4);
                    }

                    setTimeout(() => {
                        if (this.leafletMap) {
                            this.leafletMap.invalidateSize();
                        }
                    }, 250);
                },
                async setCoordinates(lat, lng) {
                    this.addrForm.latitude = Number(lat.toFixed(7));
                    this.addrForm.longitude = Number(lng.toFixed(7));
                    if (this.leafletMarker) {
                        this.leafletMarker.setOpacity(1);
                    }
                    this.resolvingAddress = true;
                    try {
                        const res = await fetch(`{{ route('alamat.reverse-geocode') }}?lat=${lat}&lng=${lng}`);
                        const data = await res.json();
                        if (data && data.success) {
                            if (data.display_name) {
                                this.pinAddressHint = data.display_name;
                            }
                            if (data.matched_region) {
                                const mr = data.matched_region;
                                if (mr.province_id && mr.province) {
                                    this.addrForm.province_id = mr.province_id;
                                    this.addrForm.provinsi = mr.province;
                                    await this.loadCities(mr.province_id);
                                }
                                if (mr.city_id && mr.city) {
                                    this.addrForm.city_id = mr.city_id;
                                    this.addrForm.kota = mr.city;
                                }
                                if (mr.district) {
                                    this.addrForm.kecamatan = mr.district;
                                }
                                if (mr.postal_code) {
                                    this.addrForm.kodepos = mr.postal_code;
                                }
                            } else if (!this.addrForm.kodepos && data.postcode) {
                                this.addrForm.kodepos = data.postcode;
                            }
                        }
                    } catch (err) {
                        // Non-blocking reverse geocoding
                    } finally {
                        this.resolvingAddress = false;
                    }
                },
                getCurrentLocation() {
                    if (!navigator.geolocation) {
                        alert('Browser Anda tidak mendukung deteksi lokasi otomatis.');
                        return;
                    }
                    this.geoLocating = true;

                    const onSuccess = (pos) => {
                        this.geoLocating = false;
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        this.setCoordinates(lat, lng);
                        if (this.leafletMap) {
                            this.leafletMap.setView([lat, lng], 17);
                            if (this.leafletMarker) {
                                this.leafletMarker.setLatLng([lat, lng]);
                                this.leafletMarker.setOpacity(1);
                            }
                            setTimeout(() => {
                                if (this.leafletMap) this.leafletMap.invalidateSize();
                            }, 100);
                        }
                    };

                    const onFinalError = (err) => {
                        this.geoLocating = false;
                        let msg = 'Tidak dapat mendeteksi lokasi otomatis.';
                        if (err && err.code === 1) {
                            msg = 'Izin akses lokasi ditolak oleh browser.\n\nCara mengizinkan:\n1. Klik ikon pengaturan / gembok di sebelah kiri address bar (localhost:8000).\n2. Ubah izin "Location" (Lokasi) menjadi "Allow" (Izinkan).\n3. Muat ulang halaman.';
                        } else if (err && err.code === 2) {
                            msg = 'Layanan lokasi tidak tersedia di perangkat Anda (fitur Location Windows tidak aktif atau PC tidak memiliki modul WiFi/GPS).\n\nTips: Anda tetap bisa menentukan titik dengan langsung mengklik atau menggeser pin merah pada peta.';
                        } else if (err && err.code === 3) {
                            msg = 'Waktu pencarian lokasi habis.\n\nTips: Anda dapat langsung mengklik atau menggeser pin merah pada peta di lokasi rumah Anda.';
                        } else {
                            msg = 'Gagal mendeteksi lokasi otomatis. Anda dapat langsung mengklik atau menggeser pin pada peta.';
                        }
                        this.geoErrorMsg = msg;
                    };

                    // Coba high accuracy dulu, jika unavailable/timeout fallback ke normal accuracy
                    navigator.geolocation.getCurrentPosition(
                        onSuccess,
                        (err) => {
                            if (err && (err.code === 2 || err.code === 3)) {
                                navigator.geolocation.getCurrentPosition(
                                    onSuccess,
                                    onFinalError,
                                    { enableHighAccuracy: false, timeout: 8000, maximumAge: 60000 }
                                );
                            } else {
                                onFinalError(err);
                            }
                        },
                        { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
                    );
                },
                clearPin() {
                    this.addrForm.latitude = null;
                    this.addrForm.longitude = null;
                    this.pinAddressHint = '';
                    if (this.leafletMarker) {
                        this.leafletMarker.setOpacity(0.4);
                    }
                },
                onProvinceChange() {
                    const selected = this.provinces.find(p => String(p.province_id) === String(this.addrForm.province_id));
                    this.addrForm.provinsi = selected ? selected.province : '';
                    this.addrForm.city_id = '';
                    this.addrForm.kota = '';
                    if (this.addrForm.province_id) {
                        this.loadCities(this.addrForm.province_id);
                    } else {
                        this.cities = [];
                    }
                },
                onCityChange() {
                    const selected = this.cities.find(c => String(c.city_id) === String(this.addrForm.city_id));
                    if (selected) {
                        this.addrForm.kota = (selected.type ? selected.type + ' ' : '') + selected.city_name;
                        if (selected.postal_code && !this.addrForm.kodepos) {
                            this.addrForm.kodepos = selected.postal_code;
                        }
                        this.searchAddressLocation(false);
                    } else {
                        this.addrForm.kota = '';
                    }
                },
                onAddressInput() {
                    clearTimeout(this.searchDebounceTimer);
                    this.locationAutoFound = false;
                    const text = (this.addrForm.lengkap || '').trim();
                    if (text.length < 3) return;

                    this.searchDebounceTimer = setTimeout(() => {
                        this.searchAddressLocation(false);
                    }, 700);
                },
                async searchAddressLocation(isManual = false) {
                    const jalan = (this.addrForm.lengkap || '').trim();
                    const kec = (this.addrForm.kecamatan || '').trim();
                    const kota = (this.addrForm.kota || '').trim();
                    const prov = (this.addrForm.provinsi || '').trim();

                    if (!jalan && !kec && !kota) return;

                    this.isSearchingLocation = true;
                    this.locationAutoFound = false;

                    try {
                        const params = new URLSearchParams();
                        if (jalan) params.append('jalan', jalan);
                        if (kec) params.append('kecamatan', kec);
                        if (kota) params.append('kota', kota);
                        if (prov) params.append('provinsi', prov);

                        const res = await fetch('{{ route('alamat.geocode') }}?' + params.toString());
                        const data = await res.json();

                        if (data && data.success && data.found) {
                            const lat = parseFloat(data.latitude);
                            const lon = parseFloat(data.longitude);
                            this.addrForm.latitude = Number(lat.toFixed(7));
                            this.addrForm.longitude = Number(lon.toFixed(7));
                            this.pinAddressHint = data.display_name;
                            this.autoFoundLabel = data.display_name;
                            this.locationAutoFound = true;

                            if (this.leafletMap) {
                                const targetZoom = jalan ? 16 : 13;
                                this.leafletMap.setView([lat, lon], targetZoom);
                                if (this.leafletMarker) {
                                    this.leafletMarker.setLatLng([lat, lon]);
                                    this.leafletMarker.setOpacity(1);
                                }
                                setTimeout(() => {
                                    if (this.leafletMap) this.leafletMap.invalidateSize();
                                }, 150);
                            }
                        } else if (isManual) {
                            this.geoErrorMsg = 'Lokasi jalan belum ditemukan otomatis di peta. Anda dapat langsung mengklik atau menggeser pin merah pada peta.';
                        }
                    } catch (e) {
                        console.error('Geocoding search failed', e);
                    } finally {
                        this.isSearchingLocation = false;
                    }
                },
                submitModalForm() {
                    this.formErrors = [];
                    const p = (this.addrForm.penerima || '').trim();
                    const ph = (this.addrForm.phone || '').trim();
                    const l = (this.addrForm.lengkap || '').trim();
                    const provId = this.addrForm.province_id;
                    const cityId = this.addrForm.city_id;

                    if (!p) this.formErrors.push('Nama penerima wajib diisi.');
                    if (!ph) {
                        this.formErrors.push('Nomor telepon wajib diisi.');
                    } else if (!/^[0-9]{10,15}$/.test(ph)) {
                        this.formErrors.push('Nomor telepon hanya boleh berisi angka (10 - 15 digit).');
                    }
                    if (!provId) this.formErrors.push('Provinsi wajib dipilih.');
                    if (!cityId) this.formErrors.push('Kabupaten/Kota wajib dipilih.');
                    if (!l) this.formErrors.push('Alamat lengkap wajib diisi.');

                    if (this.formErrors.length > 0) return;

                    this.saving = true;
                    const isEdit = this.modalMode === 'edit' && this.editingId;
                    const url = isEdit ? '{{ url('/alamat') }}/' + this.editingId : '{{ route('alamat.store') }}';
                    const method = isEdit ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            label_alamat: this.addrForm.label || 'Rumah',
                            penerima: this.addrForm.penerima,
                            no_telepon: this.addrForm.phone,
                            provinsi: this.addrForm.provinsi,
                            kota: this.addrForm.kota,
                            kecamatan: this.addrForm.kecamatan || null,
                            district_id: this.addrForm.district_id ? parseInt(this.addrForm.district_id) : null,
                            province_id: this.addrForm.province_id ? parseInt(this.addrForm.province_id) : null,
                            city_id: this.addrForm.city_id ? parseInt(this.addrForm.city_id) : null,
                            kode_pos: this.addrForm.kodepos,
                            alamat_lengkap: this.addrForm.lengkap,
                            latitude: this.addrForm.latitude ? parseFloat(this.addrForm.latitude) : null,
                            longitude: this.addrForm.longitude ? parseFloat(this.addrForm.longitude) : null,
                            is_utama: !!this.addrForm.utama
                        })
                    })
                    .then(async res => {
                        const data = await res.json().catch(() => ({}));
                        this.saving = false;
                        if (res.ok && data.success) {
                            window.location.reload();
                        } else if (data.errors) {
                            this.formErrors = Object.values(data.errors).flat();
                        } else {
                            this.formErrors = [data.message || 'Gagal menyimpan alamat.'];
                        }
                    })
                    .catch(() => {
                        this.saving = false;
                        this.formErrors = ['Gagal menghubungi server.'];
                    });
                }
            };
        }
    </script>
</x-storefront-layout>
