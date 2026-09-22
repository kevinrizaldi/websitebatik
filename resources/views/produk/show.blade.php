<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $produk->nama }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Detail informasi produk dan spesifikasi bahan
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('produk.edit', $produk) }}" class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-semibold uppercase tracking-wider transition shadow-sm">
                    Edit Produk
                </a>
                <a href="{{ route('produk.index') }}" class="inline-flex items-center px-3.5 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-md text-xs font-semibold transition">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-6 sm:p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        @if($produk->gambar)
                            <img src="{{ asset('storage/' . $produk->gambar) }}" alt="{{ $produk->nama }}" class="w-full aspect-square object-cover rounded-xl border border-gray-200 shadow-sm">
                        @else
                            <div class="w-full aspect-square rounded-xl bg-gray-100 border border-gray-200 flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs">Tidak ada foto</span>
                            </div>
                        @endif
                    </div>

                    <div class="md:col-span-2 space-y-4">
                        <div>
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200 mb-2">
                                {{ $produk->kategori }}
                            </span>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $produk->nama }}</h1>
                            <div class="text-2xl font-extrabold text-gray-900 mt-2">
                                Rp {{ number_format($produk->harga, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="border-t border-b border-gray-100 py-4 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-xs text-gray-500 block">SKU / Kode</span>
                                <span class="font-mono font-bold text-gray-800">{{ $produk->sku }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 block">Sisa Stok</span>
                                <span class="font-bold text-gray-800">{{ $produk->stok }} pcs</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 block">Status</span>
                                <span class="font-semibold text-gray-800">{{ $produk->status }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 block">Material</span>
                                <span class="text-gray-800">{{ $produk->material ?? '-' }}</span>
                            </div>
                        </div>

                        <div>
                            <span class="text-xs text-gray-500 block mb-1">Deskripsi</span>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $produk->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
