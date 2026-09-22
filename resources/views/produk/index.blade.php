<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Kelola Produk & SKU') }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Kelola data produk batik, varian SKU, stok persediaan, dan informasi material
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('produk.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold uppercase tracking-wider rounded-md shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Produk</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Alert Notifikasi Sukses --}}
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg flex justify-between items-center text-sm shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold text-lg leading-none">&times;</button>
                </div>
            @endif

            {{-- Kartu Putih Utama --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">

                {{-- Toolbar Atas --}}
                <div class="p-4 sm:p-5 border-b border-gray-100 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-4 text-xs">
                        <label class="inline-flex items-center gap-2 cursor-pointer font-medium text-gray-700">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span>Pilih Semua</span>
                        </label>

                        <span class="h-4 w-px bg-gray-200"></span>

                        <div class="flex items-center gap-2">
                            <button type="button" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md font-medium transition text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                <span>Ubah Status</span>
                            </button>

                            <button type="button" class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-md font-medium transition text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Hapus Pilihan</span>
                            </button>
                        </div>
                    </div>

                    <div class="text-xs text-gray-500">
                        Menampilkan <span class="font-medium text-gray-900">{{ $produks->firstItem() ?? 0 }}</span> – <span class="font-medium text-gray-900">{{ $produks->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $produks->total() }}</span> produk
                    </div>
                </div>

                {{-- Tabel Utama Produk --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="w-10 px-4 py-3.5 text-center"></th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Produk &amp; SKU
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Kategori
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Harga Resmi
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Stok
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Deskripsi &amp; Material
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-gray-700">
                            @forelse($produks as $produk)
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    {{-- CHECKBOX --}}
                                    <td class="w-10 px-4 py-4 text-center align-top">
                                        <input type="checkbox" class="product-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" value="{{ $produk->id }}">
                                    </td>

                                    {{-- PRODUK & SKU --}}
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex items-start gap-3">
                                            @if($produk->gambar)
                                                <img src="{{ asset('storage/' . $produk->gambar) }}" alt="{{ $produk->nama }}" class="w-12 h-12 object-cover rounded-lg border border-gray-200 shrink-0">
                                            @else
                                                <div class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 text-gray-400 flex items-center justify-center shrink-0">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            @endif

                                            <div>
                                                <div class="font-bold text-gray-900 text-sm hover:text-indigo-600">
                                                    <a href="{{ route('produk.show', $produk) }}">{{ $produk->nama }}</a>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
                                                    <span class="font-mono bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded text-[11px] font-semibold">{{ $produk->sku }}</span>
                                                    <span class="text-gray-300">•</span>
                                                    <span class="text-gray-500">{{ $produk->material ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- KATEGORI --}}
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                            {{ $produk->kategori }}
                                        </span>
                                    </td>

                                    {{-- HARGA RESMI --}}
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        <div class="font-bold text-gray-900 text-sm">
                                            Rp {{ number_format($produk->harga, 0, ',', '.') }}
                                        </div>
                                    </td>

                                    {{-- STOK --}}
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        @if($produk->stok <= 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                0 pcs
                                            </span>
                                        @elseif($produk->stok <= 5)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                {{ $produk->stok }} pcs (Menipis)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200">
                                                {{ $produk->stok }} pcs
                                            </span>
                                        @endif
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        @if($produk->status === 'Tersedia')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Tersedia
                                            </span>
                                        @elseif($produk->status === 'Stok Menipis')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Stok Menipis
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Habis
                                            </span>
                                        @endif
                                    </td>

                                    {{-- DESKRIPSI & MATERIAL --}}
                                    <td class="px-6 py-4 align-top">
                                        <div class="text-xs text-gray-600 max-w-xs space-y-1">
                                            @if($produk->deskripsi)
                                                <p class="truncate" title="{{ $produk->deskripsi }}">{{ $produk->deskripsi }}</p>
                                            @endif
                                            @if($produk->material)
                                                <p class="text-gray-400">Bahan: {{ $produk->material }}</p>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-center align-top">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('produk.show', $produk) }}" title="Lihat Detail" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded-md transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>

                                            <a href="{{ route('produk.edit', $produk) }}" title="Edit Produk" class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-gray-100 rounded-md transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>

                                            <form action="{{ route('produk.destroy', $produk) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus produk ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Produk" class="p-1.5 text-gray-500 hover:text-rose-600 hover:bg-rose-50 rounded-md transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                            <p class="font-medium text-gray-900 text-sm">Belum ada produk</p>
                                            <p class="text-xs text-gray-500 mt-1">Silakan tambahkan produk batik baru ke dalam katalog.</p>
                                            <a href="{{ route('produk.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold rounded-md shadow-sm transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                <span>Tambah Produk Sekarang</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($produks->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-white">
                        {{ $produks->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>

    <script>
        document.getElementById('selectAll')?.addEventListener('change', function () {
            document.querySelectorAll('.product-checkbox').forEach(function (checkbox) {
                checkbox.checked = this.checked;
            }, this);
        });
    </script>
</x-app-layout>
