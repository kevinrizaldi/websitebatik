<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Edit Produk: ' . $produk->nama) }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Perbarui data produk, stok persediaan, atau informasi harga
                </p>
            </div>
            <a href="{{ route('produk.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-600 hover:text-gray-900">
                &larr; Kembali ke Kelola Produk
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-6 sm:p-8">

                @if($errors->any())
                    <div class="p-4 mb-6 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm">
                        <p class="font-bold mb-1">Harap perbaiki kesalahan berikut:</p>
                        <ul class="list-disc pl-5 space-y-0.5 text-xs">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('produk.update', $produk) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Nama Produk <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama" value="{{ old('nama', $produk->nama) }}" placeholder="Contoh: Kemeja Batik Parang Kusumo Pria" required
                               class="w-full text-sm border border-gray-300 rounded-md py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                SKU / Kode Produk <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="sku" value="{{ old('sku', $produk->sku) }}" required
                                   class="w-full text-sm border border-gray-300 rounded-md py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Kategori <span class="text-rose-500">*</span>
                            </label>
                            <select name="kategori" required
                                    class="w-full text-sm border border-gray-300 rounded-md py-2.5 px-3 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @if (isset($kategoris) && $kategoris->isNotEmpty())
                                    @foreach ($kategoris as $kategori)
                                        <option value="{{ $kategori->nama_kategori }}" {{ old('kategori', $produk->kategori) == $kategori->nama_kategori ? 'selected' : '' }}>{{ $kategori->nama_kategori }}</option>
                                    @endforeach
                                @else
                                    <option value="Baju Batik" {{ old('kategori', $produk->kategori) == 'Baju Batik' ? 'selected' : '' }}>Baju Batik</option>
                                    <option value="Olahan Kain" {{ old('kategori', $produk->kategori) == 'Olahan Kain' ? 'selected' : '' }}>Olahan Kain</option>
                                    <option value="Kain Batik" {{ old('kategori', $produk->kategori) == 'Kain Batik' ? 'selected' : '' }}>Kain Batik</option>
                                    <option value="Wanita" {{ old('kategori', $produk->kategori) == 'Wanita' ? 'selected' : '' }}>Wanita</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    @php
                        $currentStokUkuran = $produk->getStokUkuranArray();
                        $hasSizes = !isset($currentStokUkuran['All Size']) && count($currentStokUkuran) > 0;
                    @endphp

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Harga (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="harga" value="{{ old('harga', $produk->harga) }}" min="0" max="1000000" required
                               placeholder="Maks. 1.000.000"
                               class="w-full text-sm border border-gray-300 rounded-md py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-[11px] text-gray-500 mt-1">Maks. Rp 1.000.000 (tidak boleh minus)</p>
                    </div>

                    <!-- Pengaturan Stok Produk (Per Ukuran atau Tunggal) -->
                    <div x-data="{
                        useSizeStock: {{ $hasSizes ? 'true' : 'false' }},
                        sizes: {
                            'S': '{{ old('stok_ukuran.S', $currentStokUkuran['S'] ?? 0) }}',
                            'M': '{{ old('stok_ukuran.M', $currentStokUkuran['M'] ?? 0) }}',
                            'L': '{{ old('stok_ukuran.L', $currentStokUkuran['L'] ?? 0) }}',
                            'XL': '{{ old('stok_ukuran.XL', $currentStokUkuran['XL'] ?? 0) }}',
                            'XXL': '{{ old('stok_ukuran.XXL', $currentStokUkuran['XXL'] ?? 0) }}'
                        },
                        singleStock: '{{ old('stok', $produk->stok) }}',
                        get totalStock() {
                            if (!this.useSizeStock) {
                                return Math.min(1000, Math.max(0, parseInt(this.singleStock) || 0));
                            }
                            let sum = 0;
                            for (let s in this.sizes) {
                                sum += (parseInt(this.sizes[s]) || 0);
                            }
                            return Math.min(1000, Math.max(0, sum));
                        }
                    }" class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 pb-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">
                                    Pengaturan Stok Produk <span class="text-rose-500">*</span>
                                </label>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    Pilih opsi stok per ukuran pakaian atau stok tunggal (All Size / Non-Pakaian)
                                </p>
                            </div>
                            <label class="inline-flex items-center gap-2 cursor-pointer bg-white px-3 py-1.5 rounded-lg border border-gray-300 shadow-2xs hover:bg-gray-50 text-xs">
                                <input type="checkbox" x-model="useSizeStock" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="font-semibold text-gray-700">Stok per Ukuran (S, M, L, XL, XXL)</span>
                            </label>
                        </div>

                        <!-- Grid Stok per Ukuran -->
                        <div x-show="useSizeStock" class="space-y-3">
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                                <template x-for="sz in ['S', 'M', 'L', 'XL', 'XXL']" :key="sz">
                                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-2xs text-center space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-extrabold text-gray-700" x-text="'Size ' + sz"></span>
                                            <span x-show="parseInt(sizes[sz]) === 0" class="text-[10px] font-bold text-rose-500 uppercase">Habis</span>
                                        </div>
                                        <input type="number" 
                                               :name="'stok_ukuran[' + sz + ']'" 
                                               x-model="sizes[sz]" 
                                               :disabled="!useSizeStock"
                                               min="0" max="1000" 
                                               placeholder="0"
                                               class="w-full text-center text-sm font-bold border border-gray-300 rounded-md py-1.5 px-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <span class="text-[10px] text-gray-400 block">Pcs</span>
                                    </div>
                                </template>
                            </div>
                            <p class="text-[11px] text-gray-500 italic">
                                * Isi 0 jika ukuran tersebut sedang kosong/habis. Di sisi pembeli, ukuran dengan stok 0 otomatis abu-abu & tidak dapat dipilih.
                            </p>
                        </div>

                        <!-- Input Stok Tunggal (Bila bukan per ukuran) -->
                        <div x-show="!useSizeStock" x-cloak class="bg-white p-3.5 rounded-lg border border-gray-200 shadow-2xs space-y-2">
                            <label class="block text-xs font-semibold text-gray-700">
                                Jumlah Stok (All Size / Produk Tunggal)
                            </label>
                            <input type="number" 
                                   x-model="singleStock" 
                                   :disabled="useSizeStock"
                                   min="0" max="1000" 
                                   placeholder="Contoh: 20"
                                   class="w-full text-sm border border-gray-300 rounded-md py-2 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-[11px] text-gray-500">Cocok untuk Kain Batik meteran, aksesoris, atau tas.</p>
                        </div>

                        <!-- Hidden Total Stock Input yang dikirimkan ke form -->
                        <input type="hidden" name="stok" :value="totalStock">

                        <!-- Footer Total Stok Real-time -->
                        <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Total Stok Tersedia:</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-base font-extrabold text-indigo-700" x-text="totalStock"></span>
                                <span class="text-xs font-medium text-gray-500">pcs</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Material / Bahan
                        </label>
                        <input type="text" name="material" value="{{ old('material', $produk->material) }}" placeholder="Contoh: Katun Prima 100%"
                               class="w-full text-sm border border-gray-300 rounded-md py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Deskripsi Produk
                        </label>
                        <textarea name="deskripsi" rows="3"
                                  class="w-full text-sm border border-gray-300 rounded-md py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                    </div>

                    <div x-data="{
                        mainPreview: null,
                        otherPreviews: [],
                        handleMain(e) {
                            const file = e.target.files[0];
                            if (!file) { this.mainPreview = null; return; }
                            const reader = new FileReader();
                            reader.onload = ev => this.mainPreview = ev.target.result;
                            reader.readAsDataURL(file);
                        },
                        handleOthers(e) {
                            const files = Array.from(e.target.files);
                            files.forEach(file => {
                                const reader = new FileReader();
                                reader.onload = ev => this.otherPreviews.push({ src: ev.target.result });
                                reader.readAsDataURL(file);
                            });
                        }
                    }" class="space-y-5">

                        {{-- Gambar Utama --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Foto Utama Produk
                            </label>
                            <div class="flex items-start gap-4">
                                @if($produk->gambar)
                                    <div class="relative shrink-0">
                                        <img src="{{ $produk->gambar_url }}" alt="{{ $produk->nama }}"
                                             class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                                        <span class="absolute -bottom-1 left-0 right-0 text-center text-[9px] text-gray-500">Saat ini</span>
                                    </div>
                                @endif
                                <template x-if="mainPreview">
                                    <div class="relative shrink-0">
                                        <img :src="mainPreview" class="w-20 h-20 object-cover rounded-lg border-2 border-indigo-400">
                                        <span class="absolute -bottom-1 left-0 right-0 text-center text-[9px] text-indigo-600 font-bold">Baru</span>
                                    </div>
                                </template>
                                <div class="flex-1">
                                    <input type="file" name="gambar" accept="image/jpg,image/jpeg,image/png,image/webp"
                                           @change="handleMain($event)"
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                                    <p class="text-[11px] text-gray-400 mt-1">Pilih file baru untuk mengganti foto utama. JPG, PNG, WEBP. Maks 2MB.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Gambar Tambahan yang sudah ada --}}
                        @if($produk->gambar_lainnya && count($produk->gambar_lainnya) > 0)
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Foto Tambahan Saat Ini
                                <span class="font-normal text-gray-400">(centang untuk dihapus)</span>
                            </label>
                            <div class="flex flex-wrap gap-3">
                                @foreach($produk->gambar_lainnya as $idx => $path)
                                <label class="relative cursor-pointer group" title="Centang untuk hapus">
                                    <input type="checkbox" name="hapus_gambar[]" value="{{ $path }}"
                                           class="sr-only peer">
                                    <img src="{{ asset('storage/' . $path) }}" alt="Foto {{ $idx + 1 }}"
                                         class="w-16 h-16 object-cover rounded-lg border-2 border-gray-200 peer-checked:border-rose-500 peer-checked:opacity-50 group-hover:border-gray-400 transition">
                                    <span class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100">
                                        <span class="bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">Hapus</span>
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Upload Gambar Tambahan Baru --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Tambah Foto Baru <span class="font-normal text-gray-400">(opsional, maks. 3 foto total)</span>
                            </label>

                            <div x-show="otherPreviews.length > 0" class="flex flex-wrap gap-3 mb-3">
                                <template x-for="(img, i) in otherPreviews" :key="i">
                                    <img :src="img.src" class="w-16 h-16 object-cover rounded-lg border-2 border-indigo-400">
                                </template>
                            </div>

                            <input type="file" name="gambar_lainnya[]" accept="image/jpg,image/jpeg,image/png,image/webp"
                                   multiple @change="handleOthers($event)"
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="text-[11px] text-gray-400 mt-1">Pilih beberapa foto sekaligus (tahan Ctrl/Cmd). JPG, PNG, WEBP. Maks. 3 foto, 2MB per foto.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('produk.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2 bg-gray-900 hover:bg-black text-white rounded-md text-xs font-semibold uppercase tracking-wider shadow-sm transition">
                            Perbarui Produk
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
