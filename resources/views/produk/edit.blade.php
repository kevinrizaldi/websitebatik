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
                    <span class="text-[#B85D3B] font-semibold">EDIT PRODUK</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#1E1B18] tracking-tight">
                        Edit Produk: {{ $produk->nama }}
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FCECE6] text-[#B85D3B] tracking-wider uppercase">
                        SKU: {{ $produk->sku }}
                    </span>
                </div>
                <p class="text-sm text-[#7A7269] mt-1">
                    Perbarui data busana wastra, spesifikasi bahan primissima, atau stok inventaris sirkular.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#FAF4ED] text-[#8C6D58] border border-[#E5DDD0]">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Status: Tersimpan
                </span>
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
            $stokUkuran = $produk->getStokUkuranArray();
            $oldStokUkuran = old('stok_ukuran');
            if (is_array($oldStokUkuran) && !empty($oldStokUkuran)) {
                $initialSizes = [];
                foreach ($oldStokUkuran as $sName => $sStock) {
                    $initialSizes[] = ['name' => (string) $sName, 'stock' => (int) $sStock];
                }
            } elseif (!empty($stokUkuran)) {
                $initialSizes = [];
                foreach ($stokUkuran as $sName => $sStock) {
                    $initialSizes[] = ['name' => (string) $sName, 'stock' => (int) $sStock];
                }
            } else {
                $initialSizes = [
                    ['name' => 'S', 'stock' => 4],
                    ['name' => 'M', 'stock' => 4],
                    ['name' => 'L', 'stock' => 4],
                    ['name' => 'XL', 'stock' => 4],
                    ['name' => 'XXL', 'stock' => 2],
                ];
            }
        @endphp
        {{-- Main Two-Column Form --}}
        <form action="{{ route('produk.update', $produk) }}" method="POST" enctype="multipart/form-data" class="space-y-6"
              x-data="{
                  kategori: '{{ old('kategori', $produk->kategori) }}',
                  get isKain() {
                      return (this.kategori || '').toLowerCase().includes('kain');
                  },
                  sizes: {{ json_encode($initialSizes) }},
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
                          { name: 'S', stock: 4 },
                          { name: 'M', stock: 4 },
                          { name: 'L', stock: 4 },
                          { name: 'XL', stock: 4 },
                          { name: 'XXL', stock: 2 }
                      ];
                  },
                  stokKain: {{ (int) old('stok', $produk->stok) }},
                  get totalStok() {
                      if (this.isKain) {
                          return parseInt(this.stokKain) || 0;
                      }
                      return this.sizes.reduce((sum, s) => sum + (parseInt(s.stock) || 0), 0);
                  }
              }">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- Left Column: Gallery & Visual Documentation (5 cols) --}}
                <div class="lg:col-span-5 space-y-6">

                    {{-- Galeri & Dokumentasi Visual Card --}}
                    <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-sm text-[#1E1B18]">Galeri &amp; Dokumentasi Visual</h3>
                                <p class="text-[11px] text-[#8C827A] mt-0.5">Unggah foto kurasi beresolusi tinggi dengan pencahayaan natural.</p>
                            </div>
                            <svg class="w-5 h-5 text-[#8C6D58]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        {{-- Main Hero Image Slot --}}
                        <div class="relative rounded-2xl border-2 border-dashed border-[#D9D0C5] bg-[#FAF7F2] p-4 text-center overflow-hidden group">
                            <input type="file" name="gambar" id="heroImageInput" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewHeroImage(this)">

                            <div id="heroPreviewContainer" class="{{ $produk->gambar ? '' : 'hidden' }} relative aspect-4/3 rounded-xl overflow-hidden bg-black/5">
                                <img id="heroPreviewImg" src="{{ $produk->gambar ? asset('storage/' . $produk->gambar) : '' }}" alt="{{ $produk->nama }}" class="w-full h-full object-cover">
                                <div class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-white/90 text-[10px] font-bold text-[#1E1B18] shadow-xs">
                                    ✓ Foto Utama Aktif
                                </div>
                            </div>

                            <div id="heroUploadPlaceholder" class="{{ $produk->gambar ? 'hidden' : '' }} py-6 space-y-2">
                                <div class="w-12 h-12 mx-auto rounded-full bg-[#F3EDE6] flex items-center justify-center text-[#8C6D58]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div class="text-xs font-semibold text-[#1E1B18]">Klik untuk Mengganti Foto Utama</div>
                                <p class="text-[11px] text-[#8C827A]">Format JPG, PNG, atau WEBP (Maksimal 2 MB)</p>
                            </div>
                        </div>

                        {{-- 4 Slots Detail --}}
                        <div>
                            <div class="flex items-center justify-between text-[11px] mb-2">
                                <span class="font-bold text-[#8C827A] uppercase tracking-wider">FOTO SUDUT &amp; DETAIL KAIN (4 SLOT)</span>
                                <span class="text-[#A89F91]">Maks. 2 MB per berkas</span>
                            </div>
                            <div class="grid grid-cols-4 gap-2">
                                <div class="aspect-square rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] flex flex-col items-center justify-center p-1 text-center">
                                    <span class="text-[9px] font-semibold text-[#8C827A]">Detail Serat</span>
                                </div>
                                <div class="aspect-square rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] flex flex-col items-center justify-center p-1 text-center">
                                    <span class="text-[9px] font-semibold text-[#8C827A]">Pola Motif</span>
                                </div>
                                <div class="aspect-square rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] flex flex-col items-center justify-center p-1 text-center">
                                    <span class="text-[9px] font-semibold text-[#8C827A]">Tampak Belakang</span>
                                </div>
                                <div class="aspect-square rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] flex flex-col items-center justify-center p-1 text-center">
                                    <span class="text-[9px] font-semibold text-[#8C827A]">Ramah Lingkungan</span>
                                </div>
                            </div>
                        </div>

                        {{-- Multi file input dropzone --}}
                        <div class="relative rounded-xl border border-dashed border-[#D9D0C5] bg-[#FCFAF7] p-4 text-center">
                            <input type="file" name="gambar_lainnya[]" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div class="flex flex-col items-center justify-center space-y-1">
                                <svg class="w-6 h-6 text-[#8C6D58]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <span class="text-xs font-semibold text-[#1E1B18]">Tarik &amp; Lepaskan Berkas Foto Tambahan</span>
                                <span class="text-[10px] text-[#8C827A]">Mendukung format JPG, PNG, atau WEBP</span>
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
                                SKU Terindeks
                            </span>
                        </div>

                        {{-- Nama Busana / Kriya --}}
                        <div>
                            <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                Nama Busana / Kriya <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   name="nama"
                                   value="{{ old('nama', $produk->nama) }}"
                                   required
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                        </div>

                        {{-- Kategori & SKU Row --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                    Kategori Wastra <span class="text-rose-500">*</span>
                                </label>
                                <select name="kategori" x-model="kategori" required class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                                    @if(isset($kategoris) && $kategoris->isNotEmpty())
                                        @foreach($kategoris as $k)
                                            <option value="{{ $k->nama_kategori }}" {{ old('kategori', $produk->kategori) == $k->nama_kategori ? 'selected' : '' }}>
                                                {{ $k->nama_kategori }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="Baju Batik" {{ old('kategori', $produk->kategori) == 'Baju Batik' ? 'selected' : '' }}>Baju Batik</option>
                                        <option value="Kain Batik" {{ old('kategori', $produk->kategori) == 'Kain Batik' ? 'selected' : '' }}>Kain Batik</option>
                                        <option value="Olahan Kain Sisa" {{ old('kategori', $produk->kategori) == 'Olahan Kain Sisa' ? 'selected' : '' }}>Olahan Kain Sisa</option>
                                    @endif
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                    Kode SKU Inventaris <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       name="sku"
                                       value="{{ old('sku', $produk->sku) }}"
                                       required
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-mono focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                            </div>
                        </div>

                        {{-- Harga Jual, Harga Coret, Stok Awal Row --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                    Harga Jual (IDR) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number"
                                       name="harga"
                                       value="{{ old('harga', $produk->harga) }}"
                                       min="0"
                                       max="1000000"
                                       required
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-semibold focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#7A7269] mb-1.5">
                                    Harga Coret (IDR)
                                </label>
                                <input type="number"
                                       name="harga_coret"
                                       value="{{ old('harga_coret', $produk->harga_coret) }}"
                                       min="0"
                                       max="100000000"
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#7A7269] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-[#1E1B18]">
                                        <span x-text="isKain ? 'Stok Kain (Lembar) *' : 'Total Stok (Akumulasi) *'">Total Stok *</span>
                                    </label>
                                </div>
                                
                                {{-- Jika Bahan Kain: Input manual jumlah lembar kain --}}
                                <div x-show="isKain">
                                    <input type="number"
                                           name="stok"
                                           x-model.number="stokKain"
                                           :disabled="!isKain"
                                           min="0"
                                           max="100000"
                                           class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-bold focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                                    <p class="text-[10px] text-stone-400 mt-1">Jumlah lembar kain wastra fisik.</p>
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

                    {{-- Card 2: Ukuran Baju & Alokasi Stok (Variasi) --}}
                    <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-sm text-[#1E1B18]">Variasi Ukuran &amp; Alokasi Stok</h3>
                                <p class="text-[11px] text-[#8C827A] mt-0.5">Pilih atau tambahkan ukuran busana yang tersedia beserta alokasi stok masing-masing.</p>
                            </div>
                            <span class="text-[10px] font-semibold text-[#8C6D58] bg-[#FAF0E4] px-2.5 py-0.5 rounded-full border border-[#E8D9C5]"
                                  x-text="isKain ? 'Bahan Lembaran Wastra' : (sizes.length + ' Variasi Aktif')">
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
                                    <p class="text-xs text-stone-500">Belum ada variasi ukuran yang dipilih untuk busana ini.</p>
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
                                    <h4 class="text-xs font-bold text-[#1E1B18]">Produk Bahan Kain Lembaran</h4>
                                    <p class="text-xs text-[#5A544E] mt-1 leading-relaxed">
                                        Kategori kain batik dijual per lembar potongan wastra utuh (standar 2,4m × 1,15m) sehingga tidak memiliki variasi ukuran busana (S, M, L, XL, XXL). Stok dikelola terpusat pada kolom <strong>Stok Kain</strong> di atas.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden inputs --}}
                        <input type="hidden" name="material" value="{{ old('material', $produk->material ?? 'Katun Primissima Halus 60s, Batik Cap kombinasi Tulis canting tembaga') }}">

                        {{-- Panduan Ukuran Produk --}}
                        <div x-show="!isKain" x-transition>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-[#1E1B18]">
                                    Panduan Ukuran Produk <span class="text-stone-400 font-normal">(Size Guide)</span>
                                </label>
                                <span class="text-[10px] text-[#8C6D58]">Tampil di pop-up Panduan Ukuran pembeli</span>
                            </div>
                            <textarea name="panduan_ukuran"
                                      rows="3"
                                      placeholder="Contoh rincian dimensi (lingkar dada, panjang baju, panjang lengan):&#10;S: LD 104 cm, PB 72 cm, PL 60 cm&#10;M: LD 108 cm, PB 74 cm, PL 61 cm&#10;L: LD 112 cm, PB 76 cm, PL 62 cm&#10;XL: LD 116 cm, PB 78 cm, PL 63 cm&#10;XXL: LD 120 cm, PB 80 cm, PL 64 cm"
                                      class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] placeholder-[#9C948A] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition leading-relaxed">{{ old('panduan_ukuran', $produk->panduan_ukuran) }}</textarea>
                            <p class="text-[10px] text-stone-400 mt-1">Panduan ini akan ditampilkan saat pembeli menekan tombol &ldquo;Panduan Ukuran&rdquo; di etalase toko.</p>
                        </div>
                    </div>

                    {{-- Card 3: Deskripsi Filosofis & Cerita Karya --}}
                    <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-sm text-[#1E1B18]">Deskripsi Filosofis &amp; Cerita Karya</h3>
                                <p class="text-[11px] text-[#8C827A] mt-0.5">Ceritakan asal motif, filosofi kepemimpinan, dan etika pengrajin lokal.</p>
                            </div>
                            <svg class="w-4 h-4 text-[#8C6D58]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>

                        <textarea name="deskripsi"
                                  rows="4"
                                  maxlength="255"
                                  class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] leading-relaxed focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                    </div>

                    {{-- Card 4: Status Publikasi Katalog --}}
                    <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-3">
                        <h3 class="font-bold text-sm text-[#1E1B18]">Status Publikasi Katalog</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-[#D9D0C5] bg-[#FAF7F2] hover:bg-white cursor-pointer transition">
                                <input type="radio" name="publish_mode" value="publish" checked class="mt-0.5 text-[#1E1B18] focus:ring-[#8C6D58]">
                                <div>
                                    <div class="font-bold text-xs text-[#1E1B18]">Langsung Terbitkan</div>
                                    <p class="text-[10px] text-[#7A7269] mt-0.5 leading-normal">
                                        Produk langsung terlihat di etalase web, aplikasi mobile, dan siap dipesan pembeli.
                                    </p>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] hover:bg-white cursor-pointer transition">
                                <input type="radio" name="publish_mode" value="draft" class="mt-0.5 text-[#1E1B18] focus:ring-[#8C6D58]">
                                <div>
                                    <div class="font-bold text-xs text-[#1E1B18]">Simpan Sebagai Draf</div>
                                    <p class="text-[10px] text-[#7A7269] mt-0.5 leading-normal">
                                        Hanya dapat diakses dan ditinjau oleh kurator internal di panel manajemen toko.
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
                    <span>Terakhir diperbarui: {{ $produk->updated_at ? $produk->updated_at->diffForHumans() : 'Baru saja' }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('produk.index') }}" class="px-5 py-2.5 rounded-xl border border-[#D9D0C5] bg-[#FAF7F2] hover:bg-white text-xs font-semibold text-[#1E1B18] transition">
                        Batal / Kembali ke Katalog
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#1E1B18] hover:bg-black text-xs font-semibold text-white transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Perubahan Produk</span>
                    </button>
                </div>
            </div>

        </form>

    </div>

    <script>
        function previewHeroImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('heroPreviewImg').src = e.target.result;
                    document.getElementById('heroPreviewContainer').classList.remove('hidden');
                    document.getElementById('heroUploadPlaceholder').classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-app-layout>
