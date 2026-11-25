<x-app-layout>
    <div class="space-y-6">

        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-[#8C827A] mb-1 font-medium">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#1E1B18]">DASHBOARD</a>
                    <span>&gt;</span>
                    <a href="{{ route('produk.index') }}" class="hover:text-[#1E1B18]">PRODUK</a>
                    <span>&gt;</span>
                    <span class="text-[#B85D3B] font-semibold">TAMBAH PRODUK BARU</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#1E1B18] tracking-tight">
                        Tambah Produk Batik
                    </h1>
                </div>
            </div>
        </div>

        {{-- Error Alerts --}}
        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs sm:text-sm shadow-xs">
                <p class="font-bold mb-1">Harap perbaiki kesalahan berikut:</p>
                <ul class="list-disc pl-5 space-y-0.5 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $defaultSizes = [
                ['name' => 'S', 'stock' => 0],
                ['name' => 'M', 'stock' => 0],
                ['name' => 'L', 'stock' => 0],
                ['name' => 'XL', 'stock' => 0],
                ['name' => 'XXL', 'stock' => 0],
            ];
            $oldStokUkuran = old('stok_ukuran');
            if (is_array($oldStokUkuran) && !empty($oldStokUkuran)) {
                $initialSizes = [];
                foreach ($oldStokUkuran as $sName => $sStock) {
                    $initialSizes[] = ['name' => (string) $sName, 'stock' => (int) $sStock];
                }
            } else {
                $initialSizes = $defaultSizes;
            }
        @endphp
        {{-- Main Two-Column Form --}}
        <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data" id="produkForm" class="space-y-6" x-data="produkCreateHandler()" @submit="validateBeforeSubmit($event)">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- Left Column: Gallery & Visual Documentation (5 cols) --}}
                <div class="lg:col-span-5 space-y-6">

                    {{-- Galeri & Dokumentasi Visual Card --}}
                    <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-sm text-[#1E1B18] flex items-center gap-1">
                                    <span>Foto Produk</span>
                                    <span class="text-rose-500 font-bold">*</span>
                                </h3>
                            </div>
                            <svg class="w-5 h-5 text-[#8C6D58]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        {{-- Error Alert for Main Image --}}
                        @error('gambar')
                            <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-600 font-semibold flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror

                        {{-- Hidden File Inputs --}}
                        <input type="file" name="gambar" id="heroImageInput" accept="image/*" class="hidden" @change="handleHeroChange($event)">
                        <input type="file" name="gambar_lainnya[]" id="otherImagesInput" multiple accept="image/*" class="hidden" @change="handleOtherChange($event)">

                        {{-- Main Hero Image Slot --}}
                        <div class="relative rounded-2xl border-2 border-dashed transition overflow-hidden group"
                             :class="dragOverHero ? 'border-[#8C6D58] bg-[#F7F2EC]' : (heroPreview ? 'border-[#ECE5D8] bg-[#FAF7F2]' : 'border-[#D9D0C5] bg-[#FAF7F2] hover:border-[#8C6D58] hover:bg-[#F9F5EF]')"
                             @dragover.prevent="dragOverHero = true"
                             @dragleave.prevent="dragOverHero = false"
                             @drop.prevent="handleHeroDrop($event)">

                            {{-- When Hero Preview Exists --}}
                            <div x-show="heroPreview" class="relative aspect-4/3 sm:aspect-16/10 rounded-xl overflow-hidden bg-black/5 m-2 shadow-2xs">
                                <img :src="heroPreview" alt="Foto Utama" class="w-full h-full object-cover">

                                {{-- Pill Tag --}}
                                <div class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-full bg-white/95 backdrop-blur text-[11px] font-bold text-[#1E1B18] shadow-xs flex items-center gap-1.5 pointer-events-none">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Foto Utama Terpilih</span>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="absolute top-2.5 right-2.5 flex items-center gap-1.5">
                                    <button type="button" @click.stop="triggerHeroUpload()" class="px-2.5 py-1 rounded-lg bg-white/95 hover:bg-white text-[11px] font-semibold text-[#1E1B18] shadow-xs transition flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-[#8C6D58]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        <span>Ganti</span>
                                    </button>
                                    <button type="button" @click.stop="removeHero()" class="p-1 rounded-lg bg-rose-50/95 hover:bg-rose-100 text-rose-600 shadow-xs transition" title="Hapus foto utama">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- When Hero Empty Placeholder --}}
                            <div x-show="!heroPreview" @click="triggerHeroUpload()" class="py-7 px-4 text-center cursor-pointer space-y-2 select-none">
                                <div class="w-12 h-12 mx-auto rounded-full bg-[#F3EDE6] flex items-center justify-center text-[#8C6D58] group-hover:scale-105 transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div class="text-xs font-semibold text-[#1E1B18]">
                                    Klik atau Seret Foto Utama ke Sini <span class="text-rose-500 font-bold">*</span>
                                </div>
                                <p class="text-[11px] text-[#8C827A]">Format JPG, PNG, atau WEBP (Wajib diisi, Maksimal 2 MB)</p>
                            </div>
                        </div>

                        {{-- 4 Slots Detail --}}
                        <div>
                            <div class="flex items-center justify-between text-[11px] mb-2">
                                <span class="font-bold text-[#8C827A] uppercase tracking-wider">Foto pendukung</span>
                                <span class="text-[#A89F91]">Maks. 2 MB per berkas</span>
                            </div>
                            <div class="grid grid-cols-4 gap-2">
                                <template x-for="i in 4" :key="i">
                                    <div class="relative">
                                        {{-- If item exists at (i-1) --}}
                                        <div x-show="otherPreviews[i - 1]" class="relative aspect-square rounded-xl overflow-hidden border border-[#ECE5D8] bg-[#FAF7F2] group shadow-2xs">
                                            <img :src="otherPreviews[i - 1]?.url" class="w-full h-full object-cover">
                                            {{-- Delete Button --}}
                                            <button type="button" @click.stop="removeOther(i - 1)" class="absolute top-1 right-1 w-5 h-5 rounded-full bg-black/60 hover:bg-rose-600 text-white flex items-center justify-center text-xs transition leading-none" title="Hapus foto">
                                                &times;
                                            </button>
                                        </div>

                                        {{-- If slot is empty --}}
                                        <div x-show="!otherPreviews[i - 1]"
                                             @click="triggerOtherUpload()"
                                             class="aspect-square rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] hover:bg-[#F3EDE6] hover:border-[#8C6D58] transition cursor-pointer flex items-center justify-center text-center group select-none">
                                            <svg class="w-5 h-5 text-[#A89F91] group-hover:text-[#8C6D58] group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Overflow Grid for items 5-9 --}}
                        <div x-show="otherPreviews.length > 4" class="pt-1">
                            <div class="text-[10px] font-bold text-[#8C827A] uppercase tracking-wider mb-1.5">
                                Foto Tambahan (Slot 5 - 9)
                            </div>
                            <div class="grid grid-cols-5 gap-2">
                                <template x-for="(extra, idx) in otherPreviews.slice(4)" :key="extra.id || idx">
                                    <div class="relative aspect-square rounded-xl overflow-hidden border border-[#ECE5D8] bg-[#FAF7F2] group shadow-2xs">
                                        <img :src="extra.url" class="w-full h-full object-cover">
                                        <button type="button" @click.stop="removeOther(idx + 4)" class="absolute top-1 right-1 w-5 h-5 rounded-full bg-black/60 hover:bg-rose-600 text-white flex items-center justify-center text-xs transition leading-none" title="Hapus foto">
                                            &times;
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Multi file input dropzone --}}
                        <div class="relative rounded-xl border border-dashed transition p-4 text-center cursor-pointer select-none"
                             :class="dragOverOther ? 'border-[#8C6D58] bg-[#F7F2EC]' : 'border-[#D9D0C5] bg-[#FCFAF7] hover:bg-[#F8F4EE] hover:border-[#8C6D58]'"
                             @click="triggerOtherUpload()"
                             @dragover.prevent="dragOverOther = true"
                             @dragleave.prevent="dragOverOther = false"
                             @drop.prevent="handleOtherDrop($event)">
                            <div class="flex flex-col items-center justify-center space-y-1">
                                <svg class="w-6 h-6 text-[#8C6D58]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <span class="text-xs font-semibold text-[#1E1B18]">Tarik &amp; Lepaskan Berkas Foto Tambahan Di Sini</span>
                                <span class="text-[10px] text-[#8C827A]">Mendukung format JPG, PNG, atau WEBP (Rekomendasi rasio 4:5 atau 1:1)</span>
                                <div x-show="otherPreviews.length > 0" class="pt-1">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#FAF4ED] text-[#8C6D58] text-[10px] font-semibold border border-[#E5DDD0]">
                                        ✓ <span x-text="otherPreviews.length + ' Foto Tambahan Terpilih (Maks. 9)'"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right Column: Information, Specifications, Description & Status (7 cols) --}}
                <div class="lg:col-span-7 space-y-6">

                    {{-- Card 1: Informasi Dasar & Kategori --}}
                    <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-sm text-[#1E1B18]">Informasi Dasar &amp; Kategori</h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-[#FAF4ED] text-[#8C6D58] border border-[#E5DDD0]">
                                Kode Produk
                            </span>
                        </div>

                        {{-- Nama Produk Batik --}}
                        <div>
                            <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                Nama Produk Batik <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   name="nama"
                                   value="{{ old('nama') }}"
                                   placeholder="Contoh: Kemeja Batik Parang Lengan Panjang"
                                   required
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] placeholder-[#9C948A] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                        </div>

                        {{-- Kategori & SKU Row --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                    Kategori Produk <span class="text-rose-500">*</span>
                                </label>
                                <select name="kategori" x-model="kategori" required class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                                    <option value="">-- Pilih Kategori --</option>
                                    @if(isset($kategoris) && $kategoris->isNotEmpty())
                                        @foreach($kategoris as $k)
                                            <option value="{{ $k->nama_kategori }}" {{ old('kategori') == $k->nama_kategori ? 'selected' : '' }}>
                                                {{ $k->nama_kategori }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="Baju Batik" selected>Baju Batik (Kemeja &amp; Blus)</option>
                                        <option value="Kain Batik">Kain Batik</option>
                                        <option value="Olahan Kain Sisa">Olahan Kain Sisa</option>
                                    @endif
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                    Kode Produk <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       name="sku"
                                       value="{{ old('sku') }}"
                                       placeholder="# HSO-BJK-024"
                                       required
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] placeholder-[#9C948A] font-mono focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                            </div>
                        </div>

                        {{-- Bahan / Material --}}
                        <div>
                            <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                Bahan / Material <span class="text-[10px] font-normal text-stone-400">(Opsional)</span>
                            </label>
                            <input type="text"
                                   name="material"
                                   value="{{ old('material') }}"
                                   placeholder="Contoh: Katun Primissima, Sutra, Rayon Viscose, dll."
                                   maxlength="255"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] placeholder-[#9C948A] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                        </div>

                        {{-- Harga Jual, Harga Coret, Stok Awal Row --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                    Harga Jual (IDR) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number"
                                       name="harga"
                                       value="{{ old('harga') }}"
                                       placeholder="385000"
                                       min="0"
                                       max="1000000"
                                       required
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] placeholder-[#9C948A] font-semibold focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#7A7269] mb-1.5">
                                    Harga Coret (IDR) <span class="text-[10px] font-normal text-stone-400">(Opsional)</span>
                                </label>
                                <input type="number"
                                       name="harga_coret"
                                       value="{{ old('harga_coret') }}"
                                       placeholder="Kosongkan jika tidak ada"
                                       min="0"
                                       max="100000000"
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#7A7269] placeholder-[#9C948A] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                                <p class="text-[10px] text-stone-400 mt-1">Hanya ditampilkan bila diisi.</p>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-[#1E1B18]">
                                        <span x-text="isKain ? 'Stok Kain (Lembar) *' : 'Total Stok *'">Total Stok *</span>
                                    </label>
                                </div>
                                {{-- Jika Bahan Kain: Input manual jumlah lembar kain --}}
                                <div x-show="isKain">
                                    <input type="number"
                                           name="stok"
                                           x-model.number="stokKain"
                                           :disabled="!isKain"
                                           placeholder="0"
                                           min="0"
                                           max="100000"
                                           class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-bold focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                                    <p class="text-[10px] text-stone-400 mt-1">Jumlah lembar stok kain yang tersedia.</p>
                                </div>
                                {{-- Jika Bukan Kain (Busana): Dihitung otomatis dari alokasi stok per ukuran --}}
                                <div x-show="!isKain">
                                    <input type="number"
                                           name="stok"
                                           :value="totalStok"
                                           :disabled="isKain"
                                           readonly
                                           class="w-full py-2.5 px-3.5 rounded-xl border border-[#E8DFC8] bg-[#FAF0E4]/70 text-xs text-[#1E1B18] font-bold cursor-not-allowed select-none">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card 2: Ukuran Baju & Stok (Variasi) --}}
                    <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-sm text-[#1E1B18]">Variasi Ukuran &amp; Stok</h3>
                                <p class="text-[11px] text-[#8C827A] mt-0.5">Pilih atau tambahkan ukuran baju yang tersedia beserta jumlah stok masing-masing.</p>
                            </div>
                            <span class="text-[10px] font-semibold text-[#8C6D58] bg-[#FAF0E4] px-2.5 py-0.5 rounded-full border border-[#E8D9C5]"
                                  x-text="isKain ? 'Kain Lembaran' : (sizes.length + ' Variasi Aktif')">
                            </span>
                        </div>

                        {{-- Bagian Khusus Busana (Bukan Bahan Kain) --}}
                        <div x-show="!isKain" x-transition class="space-y-4">

                            {{-- 1. Pilihan / Tambah Opsi Ukuran (Gaya Shopee Variasi) --}}
                            <div class="rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-[#1E1B18]">
                                        Pilihan Opsi Ukuran <span class="text-rose-500">*</span>
                                    </label>
                                    <span class="text-[11px] text-stone-500">Klik untuk mengaktifkan / menonaktifkan ukuran</span>
                                </div>

                                {{-- Presets / Rekomendasi Ukuran Cepat --}}
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-[11px] font-semibold text-stone-600 mr-1">Rekomendasi:</span>
                                    @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', 'All Size'] as $presetSize)
                                        <button type="button"
                                                @click="toggleSize('{{ $presetSize }}')"
                                                :class="hasSize('{{ $presetSize }}')
                                                    ? 'bg-[#1E1B18] text-white border-[#1E1B18] shadow-xs'
                                                    : 'bg-white text-stone-700 border-[#D9D0C5] hover:border-[#8C6D58] hover:bg-[#FAF0E4]'"
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold border transition flex items-center gap-1 cursor-pointer">
                                            <span x-show="hasSize('{{ $presetSize }}')">✓</span>
                                            <span x-show="!hasSize('{{ $presetSize }}')">+</span>
                                            <span>{{ $presetSize }}</span>
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Input Tambah Ukuran Kustom --}}
                                <div class="pt-1 flex items-center gap-2">
                                    <div class="relative flex-1">
                                        <input type="text"
                                               x-model="newSizeInput"
                                               @keydown.enter.prevent="addSize()"
                                               placeholder="Tambah ukuran lain (misal: 5XL, Anak, Custom, LD 110)..."
                                               class="w-full py-2.5 px-3.5 rounded-xl border border-[#D9D0C5] bg-white text-xs text-[#1E1B18] placeholder-[#9C948A] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] transition">
                                    </div>
                                    <button type="button"
                                            @click="addSize()"
                                            style="background-color: #8C6D58 !important; color: #ffffff !important;"
                                            class="shrink-0 px-4 py-2.5 rounded-xl bg-[#8C6D58] hover:bg-[#735845] text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        <span class="text-white font-bold">Tambah Ukuran</span>
                                    </button>
                                </div>
                            </div>

                            {{-- 2. Daftar & Alokasi Stok Ukuran (Tabel Variasi Gaya Shopee) --}}
                            <div class="space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1 border-b border-[#ECE5D8]">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs font-bold text-[#1E1B18]">Daftar Stok Variasi</h4>
                                        <span class="text-[11px] text-stone-500">
                                            (Total: <strong class="text-[#8C6D58]" x-text="totalStok + ' unit'"></strong> dari <span x-text="sizes.length"></span> variasi)
                                        </span>
                                    </div>

                                    {{-- Fitur Cepat 'Terapkan Ke Semua' seperti Shopee --}}
                                    <div x-show="sizes.length > 1" class="flex items-center gap-1.5 bg-[#FAF7F2] p-1.5 rounded-xl border border-[#ECE5D8]">
                                        <span class="text-[10px] text-stone-600 font-medium pl-1">Isi serentak:</span>
                                        <input type="number"
                                               x-model.number="bulkStockInput"
                                               min="0"
                                               placeholder="Qty"
                                               class="w-16 py-1 px-2 text-center rounded-lg border border-[#D9D0C5] bg-white text-xs font-bold text-[#1E1B18] focus:outline-none focus:ring-1 focus:ring-[#8C6D58]">
                                        <button type="button"
                                                @click="applyBulkStock()"
                                                class="px-2.5 py-1 rounded-lg bg-stone-800 hover:bg-stone-900 text-white text-[10px] font-bold transition cursor-pointer">
                                            Terapkan ke Semua
                                        </button>
                                    </div>
                                </div>

                                {{-- Dynamic Variant Items Grid/Table --}}
                                <div x-show="sizes.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                    <template x-for="(s, index) in sizes" :key="s.name">
                                        <div class="rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] p-3 space-y-2 hover:border-[#8C6D58]/50 transition shadow-2xs">
                                            {{-- Size Title and Remove Button in Clean Top Row --}}
                                            <div class="flex items-center justify-between gap-1 pb-0.5">
                                                <span class="font-serif font-bold text-sm text-[#1E1B18] truncate" x-text="s.name"></span>
                                                <button type="button"
                                                        @click="removeSize(index)"
                                                        title="Hapus variasi ukuran ini"
                                                        class="shrink-0 w-6 h-6 rounded-md bg-stone-200/60 hover:bg-rose-100 text-stone-500 hover:text-rose-600 flex items-center justify-center text-sm font-bold transition cursor-pointer">
                                                    &times;
                                                </button>
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-semibold text-stone-500 mb-1 text-center">Stok Unit</label>
                                                <input type="number"
                                                       :name="'stok_ukuran[' + s.name + ']'"
                                                       x-model.number="s.stock"
                                                       :disabled="isKain"
                                                       min="0"
                                                       max="100000"
                                                       placeholder="0"
                                                       class="w-full text-center py-1.5 px-2 rounded-lg border border-[#D9D0C5] bg-white text-xs font-extrabold text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#8C6D58]">
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- Empty State when all sizes removed --}}
                                <div x-show="sizes.length === 0" class="p-6 text-center rounded-xl border border-dashed border-[#D9D0C5] bg-[#FAF7F2] space-y-2">
                                    <p class="text-xs text-stone-500">Belum ada variasi ukuran yang dipilih untuk produk ini.</p>
                                    <button type="button"
                                            @click="resetDefaultSizes()"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#FAF0E4] hover:bg-[#F3E5D4] text-[#8C6D58] border border-[#E8D9C5] text-xs font-bold transition cursor-pointer">
                                        <span>↺</span>
                                        <span>Gunakan Ukuran Standar (S, M, L, XL, XXL)</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Informasi Khusus Bahan Kain (Tanpa Ukuran S/M/L/XL/XXL) --}}
                        <div x-show="isKain" x-transition>
                            <div class="rounded-xl border border-[#E8D9C5] bg-[#FAF0E4]/70 p-4 flex items-start gap-3.5">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#1E1B18] text-[#C59A53] text-sm">
                                    🧵
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-xs font-bold text-[#1E1B18]">Produk Kain Lembaran</h4>
                                    <p class="text-xs text-[#5A544E] mt-1 leading-relaxed">
                                        Kategori kain batik dijual per lembar kain utuh sehingga tidak memiliki variasi ukuran baju (S, M, L, XL, XXL). Stok diatur langsung pada kolom <strong>Stok Kain</strong> di atas.
                                    </p>
                                </div>
                            </div>
                        </div>


                        {{-- Panduan Ukuran Produk --}}
                        <div x-show="!isKain" x-transition>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-[#1E1B18]">
                                    Panduan Ukuran Produk <span class="text-stone-400 font-normal">(Size Guide)</span>
                                </label>
                            </div>
                            <textarea name="panduan_ukuran"
                                      rows="3"
                                      placeholder="Contoh rincian dimensi (lingkar dada, panjang baju, panjang lengan):&#10;S: LD 104 cm, PB 72 cm, PL 60 cm&#10;M: LD 108 cm, PB 74 cm, PL 61 cm&#10;L: LD 112 cm, PB 76 cm, PL 62 cm&#10;XL: LD 116 cm, PB 78 cm, PL 63 cm&#10;XXL: LD 120 cm, PB 80 cm, PL 64 cm"
                                      class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] placeholder-[#9C948A] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition leading-relaxed">{{ old('panduan_ukuran') }}</textarea>
                            <p class="text-[10px] text-stone-400 mt-1">Panduan ini akan ditampilkan saat pembeli menekan tombol &ldquo;Panduan Ukuran&rdquo; di etalase toko.</p>
                        </div>
                    </div>

                    {{-- Card 3: Deskripsi Produk --}}
                    <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-sm text-[#1E1B18]">Deskripsi Produk</h3>
                                <p class="text-[11px] text-[#8C827A] mt-0.5">Tuliskan keterangan mengenai motif, bahan, atau keunggulan produk ini.</p>
                            </div>
                            <svg class="w-4 h-4 text-[#8C6D58]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>

                        <textarea name="deskripsi"
                                  rows="4"
                                  maxlength="255"
                                  placeholder="Tuliskan deskripsi lengkap mengenai produk batik ini..."
                                  class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] leading-relaxed placeholder-[#9C948A] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">{{ old('deskripsi') }}</textarea>
                    </div>

                    {{-- Card 4: Status Tampilan Produk --}}
                    <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-3">
                        <h3 class="font-bold text-sm text-[#1E1B18]">Status Tampilan Produk</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-[#D9D0C5] bg-[#FAF7F2] hover:bg-white cursor-pointer transition">
                                <input type="radio" name="publish_mode" value="publish" checked class="mt-0.5 text-[#1E1B18] focus:ring-[#8C6D58]">
                                <div>
                                    <div class="font-bold text-xs text-[#1E1B18]">Langsung Tampilkan di Katalog</div>
                                    <p class="text-[10px] text-[#7A7269] mt-0.5 leading-normal">
                                        Produk langsung dapat dilihat dan dibeli oleh pelanggan di website.
                                    </p>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] hover:bg-white cursor-pointer transition">
                                <input type="radio" name="publish_mode" value="draft" class="mt-0.5 text-[#1E1B18] focus:ring-[#8C6D58]">
                                <div>
                                    <div class="font-bold text-xs text-[#1E1B18]">Simpan Sebagai Draf</div>
                                    <p class="text-[10px] text-[#7A7269] mt-0.5 leading-normal">
                                        Produk tersimpan di sistem admin dan belum ditampilkan ke pembeli.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Bottom Sticky / Action Bar --}}
            <div class="bg-white rounded-2xl border border-[#ECE5D8] p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs text-[#8C827A]">
                    <svg class="w-4 h-4 text-[#B85D3B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Data siap disimpan</span>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('produk.index') }}" class="px-5 py-2.5 rounded-xl border border-[#D9D0C5] bg-[#FAF7F2] hover:bg-white text-xs font-semibold text-[#1E1B18] transition">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#1E1B18] hover:bg-black text-xs font-semibold text-white transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Produk</span>
                    </button>
                </div>
            </div>

        </form>

    </div>

    <script>
        function produkCreateHandler() {
            return {
                kategori: '{{ old('kategori', 'Baju Batik') }}',
                get isKain() {
                    return (this.kategori || '').toLowerCase().includes('kain');
                },
                sizes: @json($initialSizes),
                newSizeInput: '',
                bulkStockInput: '',
                addSize(name) {
                    const trimmed = (name || this.newSizeInput || '').trim();
                    if (!trimmed) return;
                    const exists = this.sizes.some(s => s.name.toUpperCase() === trimmed.toUpperCase());
                    if (!exists) {
                        this.sizes.push({ name: trimmed, stock: 0 });
                    }
                    this.newSizeInput = '';
                },
                hasSize(name) {
                    return this.sizes.some(s => s.name.toUpperCase() === name.toUpperCase());
                },
                toggleSize(name) {
                    const idx = this.sizes.findIndex(s => s.name.toUpperCase() === name.toUpperCase());
                    if (idx >= 0) {
                        this.sizes.splice(idx, 1);
                    } else {
                        this.sizes.push({ name: name, stock: 0 });
                    }
                },
                removeSize(index) {
                    this.sizes.splice(index, 1);
                },
                applyBulkStock() {
                    const val = parseInt(this.bulkStockInput);
                    if (isNaN(val) || val < 0) return;
                    this.sizes.forEach(s => s.stock = val);
                    this.bulkStockInput = '';
                },
                resetDefaultSizes() {
                    this.sizes = [
                        { name: 'S', stock: 0 },
                        { name: 'M', stock: 0 },
                        { name: 'L', stock: 0 },
                        { name: 'XL', stock: 0 },
                        { name: 'XXL', stock: 0 }
                    ];
                },
                heroPreview: '',
                dragOverHero: false,
                dragOverOther: false,
                handleHeroChange(event) {
                    const file = event.target.files[0];
                    if (file) {
                        if (file.size > 2 * 1024 * 1024) {
                            alert('Ukuran berkas melebihi batas 2 MB.');
                            event.target.value = '';
                            return;
                        }
                        this.heroPreview = URL.createObjectURL(file);
                    }
                },
                handleHeroDrop(event) {
                    this.dragOverHero = false;
                    if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files[0]) {
                        const file = event.dataTransfer.files[0];
                        if (!file.type.startsWith('image/')) return;
                        if (file.size > 2 * 1024 * 1024) {
                            alert('Ukuran berkas melebihi batas 2 MB.');
                            return;
                        }
                        const input = document.getElementById('heroImageInput');
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        if (input) input.files = dt.files;
                        this.heroPreview = URL.createObjectURL(file);
                    }
                },
                removeHero() {
                    this.heroPreview = '';
                    const input = document.getElementById('heroImageInput');
                    if (input) input.value = '';
                },
                triggerHeroUpload() {
                    document.getElementById('heroImageInput').click();
                },
                otherPreviews: [],
                slotLabels: ['Detail Serat', 'Pola Motif', 'Tampak Belakang', 'Ramah Lingkungan'],
                handleOtherChange(event) {
                    this.addOtherFiles(event.target.files);
                },
                handleOtherDrop(event) {
                    this.dragOverOther = false;
                    if (event.dataTransfer && event.dataTransfer.files) {
                        this.addOtherFiles(event.dataTransfer.files);
                    }
                },
                addOtherFiles(fileList) {
                    const files = Array.from(fileList);
                    for (const file of files) {
                        if (!file.type.startsWith('image/')) continue;
                        if (this.otherPreviews.length >= 9) {
                            alert('Maksimal foto tambahan yang dapat diunggah adalah 9 foto.');
                            break;
                        }
                        if (file.size > 2 * 1024 * 1024) {
                            alert('Ukuran file "' + file.name + '" melebihi 2 MB.');
                            continue;
                        }
                        this.otherPreviews.push({
                            id: Date.now() + Math.random().toString(36).substring(2, 7),
                            url: URL.createObjectURL(file),
                            file: file,
                            name: file.name
                        });
                    }
                    this.syncOtherFilesInput();
                },
                removeOther(index) {
                    this.otherPreviews.splice(index, 1);
                    this.syncOtherFilesInput();
                },
                syncOtherFilesInput() {
                    const dt = new DataTransfer();
                    this.otherPreviews.forEach(item => {
                        if (item.file) dt.items.add(item.file);
                    });
                    const input = document.getElementById('otherImagesInput');
                    if (input) input.files = dt.files;
                },
                triggerOtherUpload() {
                    document.getElementById('otherImagesInput').click();
                },
                validateBeforeSubmit(e) {
                    if (!this.heroPreview) {
                        e.preventDefault();
                        alert('Foto utama produk wajib diunggah sebelum menyimpan produk.');
                        this.triggerHeroUpload();
                        return false;
                    }
                    return true;
                },
                stokKain: {{ (int) old('stok', 0) }},
                get totalStok() {
                    if (this.isKain) {
                        return parseInt(this.stokKain) || 0;
                    }
                    return this.sizes.reduce((sum, s) => sum + (parseInt(s.stock) || 0), 0);
                }
            };
        }
    </script>
</x-app-layout>
