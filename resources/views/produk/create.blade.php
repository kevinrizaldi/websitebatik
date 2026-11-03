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
                        Tambah Produk Busana &amp; Kriya Baru
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FCECE6] text-[#B85D3B] tracking-wider uppercase">
                        KURASI EDISI NO. #2024-C
                    </span>
                </div>
                <p class="text-sm text-[#7A7269] mt-1">
                    Lengkapi katalog busana wastra nusantara dengan narasi filosofis, dokumentasi serat benang, dan transparansi metrik sirkular limbah tekstil.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#FAF4ED] text-[#8C6D58] border border-[#E5DDD0]">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Status: Draf Penyuntingan
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

        {{-- Main Two-Column Form --}}
        <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data" id="produkForm" class="space-y-6">
            @csrf

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
                            
                            <div id="heroPreviewContainer" class="hidden relative aspect-4/3 rounded-xl overflow-hidden bg-black/5">
                                <img id="heroPreviewImg" src="" alt="Preview" class="w-full h-full object-cover">
                                <div class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-white/90 text-[10px] font-bold text-[#1E1B18] shadow-xs">
                                    ✓ Foto Utama Terpilih
                                </div>
                            </div>

                            <div id="heroUploadPlaceholder" class="py-6 space-y-2">
                                <div class="w-12 h-12 mx-auto rounded-full bg-[#F3EDE6] flex items-center justify-center text-[#8C6D58]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div class="text-xs font-semibold text-[#1E1B18]">Klik atau Seret Foto Utama ke Sini</div>
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
                                <span class="text-xs font-semibold text-[#1E1B18]">Tarik &amp; Lepaskan Berkas Foto Tambahan Di Sini</span>
                                <span class="text-[10px] text-[#8C827A]">Mendukung format JPG, PNG, atau WEBP (Rekomendasi rasio 4:5 atau 1:1)</span>
                            </div>
                        </div>
                    </div>

                    {{-- Pedoman Standar Kurasi Visual Card --}}
                    <div class="bg-[#FAF7F2] rounded-2xl border border-[#ECE5D8] p-5 space-y-3">
                        <div class="flex items-center gap-2 text-xs font-bold text-[#1E1B18]">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Pedoman Standar Kurasi Visual</span>
                        </div>
                        <p class="text-[11px] text-[#7A7269]">Mempertahankan estetika mewah dan keaslian warna kain</p>

                        <ul class="space-y-2 text-xs text-[#5A544E]">
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-600 font-bold shrink-0">✓</span>
                                <span><strong>Pencahayaan Studio Hangat:</strong> Gunakan suhu warna 3200K - 4000K untuk menonjolkan pigmen alami soga &amp; indigo.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-600 font-bold shrink-0">✓</span>
                                <span><strong>Resolusi Tajam:</strong> Minimal 1200 x 800px untuk kejelasan zoom serat kain katun primissima.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-600 font-bold shrink-0">✓</span>
                                <span><strong>Komposisi Flat-lay Wastra:</strong> Sertakan elemen kriya pendukung (kancing tempurung kelapa, besek bambu, atau anyaman pandan).</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Inisiatif Sirkular Hamzah Style Card --}}
                    <div class="bg-[#F6F1EA] rounded-2xl border border-[#ECE5D8] p-4 text-xs text-[#5A544E] space-y-1">
                        <div class="flex items-center gap-2 font-bold text-[10px] text-[#8C6D58] uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5 text-[#B85D3B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>INISIATIF SIRKULAR HAMZAH STYLE</span>
                        </div>
                        <p class="leading-relaxed">
                            Setiap helai busana memanfaatkan pola potong efisien (&ldquo;zero-waste tailoring&rdquo;), meminimalkan potongan kain perca yang terbuang.
                        </p>
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
                                   value="{{ old('nama') }}"
                                   placeholder="Contoh: Kemeja Batik Parang Seling Lengan Panjang"
                                   required
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] placeholder-[#9C948A] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                        </div>

                        {{-- Kategori & SKU Row --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                    Kategori Wastra <span class="text-rose-500">*</span>
                                </label>
                                <select name="kategori" required class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
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
                                    Kode SKU Inventaris <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       name="sku"
                                       value="{{ old('sku') }}"
                                       placeholder="# HSO-BJK-024"
                                       required
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] placeholder-[#9C948A] font-mono focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
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
                                       value="{{ old('harga') }}"
                                       placeholder="385000"
                                       min="0"
                                       max="1000000"
                                       required
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] placeholder-[#9C948A] font-semibold focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#7A7269] mb-1.5">
                                    Harga Coret / Modal
                                </label>
                                <input type="number"
                                       name="harga_modal"
                                       value="{{ old('harga_modal') }}"
                                       placeholder="475000"
                                       min="0"
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#7A7269] placeholder-[#9C948A] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-[#1E1B18]">
                                        Stok Awal <span class="text-rose-500">*</span>
                                    </label>
                                    <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded">BATAS AMAN</span>
                                </div>
                                <input type="number"
                                       name="stok"
                                       value="{{ old('stok', 18) }}"
                                       placeholder="18"
                                       min="0"
                                       max="1000"
                                       required
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-bold focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                            </div>
                        </div>

                        {{-- Low stock warning callout --}}
                        <div class="p-3 rounded-xl bg-[#FAF4ED] border border-[#F3EDE6] flex items-center gap-2.5 text-xs text-[#8C6D58]">
                            <svg class="w-4 h-4 text-[#B85D3B] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Notifikasi otomatis akan dikirim ke tim kurator gudang ketika sisa persediaan mencapai di bawah 5 pcs.</span>
                        </div>
                    </div>

                    {{-- Card 2: Spesifikasi Material & Ukuran --}}
                    <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-4">
                        <h3 class="font-bold text-sm text-[#1E1B18]">Spesifikasi Material &amp; Ukuran</h3>

                        {{-- Ukuran Checkboxes --}}
                        <div>
                            <label class="block text-xs font-bold text-[#1E1B18] mb-2">
                                Pilihan Ukuran Tersedia <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                @php
                                    $sizes = [
                                        'S' => 'LD: 100cm',
                                        'M' => 'LD: 104cm',
                                        'L' => 'LD: 108cm',
                                        'XL' => 'LD: 114cm',
                                        'XXL' => 'LD: 120cm',
                                    ];
                                @endphp
                                @foreach($sizes as $sz => $ld)
                                    <label class="flex flex-col p-2.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] hover:bg-white cursor-pointer transition">
                                        <div class="flex items-center gap-1.5">
                                            <input type="checkbox" name="stok_ukuran[{{ $sz }}]" value="4" checked class="rounded border-gray-300 text-[#1E1B18] focus:ring-[#8C6D58]">
                                            <span class="font-bold text-xs text-[#1E1B18]">{{ $sz }}</span>
                                        </div>
                                        <span class="text-[10px] text-[#8C827A] mt-1">{{ $ld }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Material & Kain Diselamatkan Row --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                    Spesifikasi Bahan &amp; Teknik Canting
                                </label>
                                <input type="text"
                                       name="material"
                                       value="{{ old('material', 'Katun Primissima Halus 60s, Batik Cap kombinasi Tulis canting tembaga') }}"
                                       placeholder="Katun Primissima Halus 60s, Pewarna Alami Soga"
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] placeholder-[#9C948A] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#1E1B18] mb-1.5">
                                    Kain Diselamatkan
                                </label>
                                <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs font-bold text-[#8C6D58]">
                                    <span>0.35 kg</span>
                                    <svg class="w-4 h-4 text-[#B85D3B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                    </svg>
                                </div>
                            </div>
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

                        {{-- Rich Text Formatting Toolbar Mockup --}}
                        <div class="flex items-center justify-between py-1.5 px-3 rounded-lg bg-[#FAF7F2] border border-[#ECE5D8] text-[11px] text-[#5A544E]">
                            <div class="flex items-center gap-3 font-semibold">
                                <button type="button" class="hover:text-[#1E1B18] font-bold">B</button>
                                <button type="button" class="hover:text-[#1E1B18] italic">I</button>
                                <button type="button" class="hover:text-[#1E1B18] underline">U</button>
                                <span class="text-gray-300">|</span>
                                <button type="button" class="hover:text-[#1E1B18]">List</button>
                                <button type="button" class="hover:text-[#1E1B18]">&ldquo;Quote&rdquo;</button>
                                <button type="button" class="hover:text-[#1E1B18]">Link</button>
                            </div>
                            <span class="text-[10px] text-[#8C827A]" id="wordCounter">148 Kata</span>
                        </div>

                        <textarea name="deskripsi"
                                  rows="4"
                                  maxlength="255"
                                  placeholder="Terinspirasi dari ombak Samudra Selatan yang pantang surut, motif Parang Seling melambangkan kesinambungan, keteguhan hati, dan kearifan para pemimpin Jawa..."
                                  class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] leading-relaxed placeholder-[#9C948A] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">{{ old('deskripsi', 'Terinspirasi dari ombak Samudra Selatan yang pantang surut, motif Parang Seling melambangkan kesinambungan, keteguhan hati, dan kearifan para pemimpin Jawa.') }}</textarea>
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
                    <span>Terakhir disinkronkan otomatis: 12 menit lalu</span>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('produk.index') }}" class="px-5 py-2.5 rounded-xl border border-[#D9D0C5] bg-[#FAF7F2] hover:bg-white text-xs font-semibold text-[#1E1B18] transition">
                        Batal / Kembali ke Katalog
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#1E1B18] hover:bg-black text-xs font-semibold text-white transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan &amp; Publikasikan Produk</span>
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
