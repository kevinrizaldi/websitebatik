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
                                <option value="Baju Batik" {{ old('kategori', $produk->kategori) == 'Baju Batik' ? 'selected' : '' }}>Baju Batik</option>
                                <option value="Olahan Kain" {{ old('kategori', $produk->kategori) == 'Olahan Kain' ? 'selected' : '' }}>Olahan Kain</option>
                                <option value="Kain Batik" {{ old('kategori', $produk->kategori) == 'Kain Batik' ? 'selected' : '' }}>Kain Batik</option>
                                <option value="Wanita" {{ old('kategori', $produk->kategori) == 'Wanita' ? 'selected' : '' }}>Wanita</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Harga (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="harga" value="{{ old('harga', $produk->harga) }}" min="0" max="1000000" required
                                   placeholder="Maks. 1.000.000"
                                   class="w-full text-sm border border-gray-300 rounded-md py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-[11px] text-gray-500 mt-1">Maks. Rp 1.000.000 (tidak boleh minus)</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Stok (Pcs) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="stok" value="{{ old('stok', $produk->stok) }}" min="0" max="1000" required
                                   placeholder="Maks. 1000"
                                   class="w-full text-sm border border-gray-300 rounded-md py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-[11px] text-gray-500 mt-1">Maks. 1.000 unit (tidak boleh minus)</p>
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

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Foto Produk
                        </label>
                        @if($produk->gambar)
                            <div class="mb-3 flex items-center gap-3">
                                <img src="{{ asset('storage/' . $produk->gambar) }}" alt="{{ $produk->nama }}" class="w-16 h-16 object-cover rounded-lg border border-gray-200">
                                <span class="text-xs text-gray-500">Foto saat ini. Pilih file baru jika ingin mengganti.</span>
                            </div>
                        @endif
                        <input type="file" name="gambar" accept="image/*"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
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
