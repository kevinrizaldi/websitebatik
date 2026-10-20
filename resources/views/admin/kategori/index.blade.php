<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-stone-900 text-[#E5C38E]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </span>
                    <h2 class="text-xl font-bold leading-tight text-gray-900">
                        {{ __('Kelola Kategori Produk') }}
                    </h2>
                </div>
                <p class="mt-1 text-xs text-gray-500 sm:text-sm">
                    Atur kategori produk batik, upload foto cover katalog untuk beranda, dan kelola produk terkait.
                </p>
            </div>
            
            {{-- Quick Stats Pills --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-stone-900"></span>
                    <span class="text-gray-500">Total Kategori:</span>
                    <strong class="font-bold text-gray-900">{{ $totalKategori ?? $kategoris->total() }}</strong>
                </div>
                <div class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                    <span class="text-gray-500">Total Produk:</span>
                    <strong class="font-bold text-gray-900">{{ $totalProduk ?? 0 }}</strong>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            
            {{-- Alert Sukses --}}
            @if (session('success'))
                <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50/90 p-4 text-sm text-emerald-800 shadow-xs backdrop-blur-xs">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.closest('.flex').remove()" class="text-lg font-bold leading-none text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @endif

            {{-- Alert Error Validasi --}}
            @if ($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50/90 p-4 text-sm text-rose-800 shadow-xs">
                    <div class="flex items-center gap-2 mb-2 font-semibold">
                        <svg class="h-5 w-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Terjadi kesalahan pada input:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs sm:text-sm pl-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">
                
                {{-- FORM TAMBAH KATEGORI (Kiri) --}}
                <div class="lg:col-span-4 lg:sticky lg:top-24">
                    <div class="rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                            <div>
                                <h3 class="font-bold text-gray-900 text-base">Tambah Kategori Baru</h3>
                                <p class="mt-0.5 text-xs text-gray-500">Tampil di katalog dan beranda pelanggan</p>
                            </div>
                            <span class="p-2 rounded-lg bg-amber-50 text-amber-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </span>
                        </div>

                        <form action="{{ route('admin.kategori.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                            @csrf
                            
                            {{-- Nama Kategori --}}
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700">
                                    Nama Kategori <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="nama_kategori" value="{{ old('nama_kategori') }}" required maxlength="30"
                                    pattern="[a-zA-Z\s]+" title="Nama kategori hanya boleh berupa huruf dan spasi"
                                    oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                                    placeholder="Contoh: Seragam ASN"
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm transition focus:border-stone-900 focus:outline-none focus:ring-2 focus:ring-stone-900/10">
                                <p class="mt-1 text-[11px] text-gray-400">Maks. 30 karakter huruf & spasi.</p>
                            </div>

                            {{-- Deskripsi --}}
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700">
                                    Deskripsi Koleksi
                                </label>
                                <textarea name="deskripsi" rows="3"
                                    placeholder="Deskripsi singkat koleksi batik ini..."
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm transition focus:border-stone-900 focus:outline-none focus:ring-2 focus:ring-stone-900/10">{{ old('deskripsi') }}</textarea>
                                <p class="mt-1 text-[11px] text-gray-400">Deskripsi akan muncul pada kartu koleksi di halaman depan.</p>
                            </div>

                            {{-- Upload Foto Kategori --}}
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700">
                                    Foto Cover Kategori
                                </label>
                                
                                <div class="relative">
                                    {{-- Dropzone / File Picker Container --}}
                                    <div id="createDropzone"
                                         class="group relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50/60 p-5 text-center transition hover:border-stone-500 hover:bg-stone-50/50 cursor-pointer">
                                        
                                        <input type="file" id="createGambarInput" name="gambar" accept="image/jpeg,image/png,image/jpg,image/webp"
                                               class="absolute inset-0 h-full w-full opacity-0 cursor-pointer z-10"
                                               onchange="handleCreateImagePreview(this)">
                                        
                                        <div id="createPlaceholder">
                                            <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-white text-stone-700 shadow-xs border border-gray-200 group-hover:scale-105 transition">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <p class="mt-2.5 text-xs font-semibold text-gray-700">
                                                Klik atau seret foto cover ke sini
                                            </p>
                                            <p class="mt-1 text-[11px] text-gray-400">
                                                JPG, PNG, WEBP (Maks. 3MB)
                                            </p>
                                        </div>

                                        {{-- Preview Box (Hidden by default) --}}
                                        <div id="createPreviewBox" class="hidden w-full flex flex-col items-center">
                                            <div class="relative w-28 h-36 rounded-xl overflow-hidden shadow-md border border-gray-200 bg-stone-100">
                                                <img id="createPreviewImg" src="#" alt="Preview Foto" class="w-full h-full object-cover">
                                            </div>
                                            <p id="createFileName" class="mt-2 text-xs font-medium text-gray-700 truncate max-w-full px-2"></p>
                                            <button type="button" onclick="clearCreateImagePreview()" class="mt-1 inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600 hover:text-rose-800 z-20">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Hapus Foto
                                            </button>
                                        </div>

                                    </div>
                                    <p class="mt-1.5 text-[11px] text-stone-500">
                                        💡 Tips: Gunakan foto berorientasi tegak (rasio 4:5) atau persegi untuk tampilan terbaik di bagian "Jelajahi Koleksi".
                                    </p>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-stone-900 py-3 text-xs font-semibold uppercase tracking-wider text-white shadow-md transition hover:bg-black hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-stone-900/30">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Simpan Kategori</span>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- TABEL DAFTAR KATEGORI (Kanan) --}}
                <div class="lg:col-span-8">
                    <div class="rounded-2xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
                        
                        {{-- Search & Filter Toolbar --}}
                        <div class="p-4 sm:p-5 border-b border-gray-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <form action="{{ route('admin.kategori.index') }}" method="GET" class="flex flex-1 items-center gap-2 max-w-md">
                                <div class="relative flex-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle cx="11" cy="11" r="7" stroke-width="2"/>
                                            <path stroke-linecap="round" stroke-width="2" d="m16 16 4 4"/>
                                        </svg>
                                    </div>
                                    <input type="search" name="search" value="{{ $search ?? '' }}"
                                           placeholder="Cari nama kategori atau deskripsi..."
                                           class="w-full rounded-xl border border-gray-300 py-2 pl-9 pr-3 text-xs sm:text-sm shadow-2xs focus:border-stone-900 focus:outline-none focus:ring-2 focus:ring-stone-900/10">
                                </div>
                                <button type="submit" class="inline-flex h-[38px] items-center justify-center rounded-xl bg-stone-900 px-4 text-xs font-semibold text-white transition hover:bg-black">
                                    Cari
                                </button>
                                @if (!empty($search))
                                    <a href="{{ route('admin.kategori.index') }}" class="inline-flex h-[38px] items-center justify-center rounded-xl border border-gray-300 px-3 text-xs font-medium text-gray-700 transition hover:bg-gray-50">
                                        Reset
                                    </a>
                                @endif
                            </form>

                            <div class="text-xs text-gray-500 shrink-0">
                                Menampilkan <span class="font-bold text-gray-900">{{ $kategoris->total() }}</span> kategori
                            </div>
                        </div>

                        {{-- Table List --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead class="bg-gray-50/75">
                                    <tr>
                                        <th class="px-5 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Cover</th>
                                        <th class="px-5 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Nama & URL</th>
                                        <th class="px-5 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Deskripsi</th>
                                        <th class="px-5 py-3.5 text-center text-[11px] font-bold uppercase tracking-wider text-gray-500">Produk</th>
                                        <th class="px-5 py-3.5 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white text-gray-700">
                                    @forelse ($kategoris as $kategori)
                                        <tr class="transition-colors hover:bg-stone-50/50">
                                            
                                            {{-- Thumbnail Foto --}}
                                            <td class="px-5 py-4 align-middle whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div class="relative w-14 h-16 rounded-xl overflow-hidden bg-stone-100 border border-gray-200 shrink-0 shadow-2xs group">
                                                        <img src="{{ $kategori->gambar_url }}" 
                                                             alt="{{ $kategori->nama_kategori }}" 
                                                             class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                                    </div>
                                                    <div>
                                                        @if ($kategori->has_custom_gambar)
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 border border-emerald-200">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                                Foto Kustom
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-medium text-stone-600 border border-stone-200" title="Menggunakan foto bawaan beranda">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-stone-400"></span>
                                                                Bawaan
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Nama & Slug --}}
                                            <td class="px-5 py-4 align-middle">
                                                <div class="font-bold text-gray-900 text-sm">{{ $kategori->nama_kategori }}</div>
                                                <div class="font-mono text-[11px] text-gray-400 mt-0.5">/{{ $kategori->slug }}</div>
                                            </td>

                                            {{-- Deskripsi --}}
                                            <td class="px-5 py-4 align-middle max-w-xs">
                                                <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">
                                                    {{ $kategori->deskripsi ?: '—' }}
                                                </p>
                                            </td>

                                            {{-- Produk Terkait --}}
                                            <td class="px-5 py-4 align-middle text-center whitespace-nowrap">
                                                <a href="{{ route('produk.index', ['search' => $kategori->nama_kategori]) }}"
                                                   class="inline-flex items-center gap-1.5 rounded-full bg-stone-100 hover:bg-stone-200 px-3 py-1 text-xs font-semibold text-stone-800 transition"
                                                   title="Lihat produk dengan kategori ini">
                                                    <span>{{ $kategori->produks_count }}</span>
                                                    <span class="text-stone-500 font-normal">produk</span>
                                                    <svg class="w-3 h-3 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </a>
                                            </td>

                                            {{-- Aksi --}}
                                            <td class="px-5 py-4 align-middle text-right whitespace-nowrap">
                                                <div class="inline-flex items-center justify-end gap-2">
                                                    <button type="button"
                                                        onclick="openEditModal({{ $kategori->id }}, '{{ addslashes($kategori->nama_kategori) }}', '{{ addslashes($kategori->deskripsi ?? '') }}', '{{ $kategori->gambar_url }}', {{ $kategori->has_custom_gambar ? 'true' : 'false' }})"
                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-2xs transition hover:bg-stone-50 hover:text-stone-900">
                                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        <span>Ubah</span>
                                                    </button>

                                                    <form action="{{ route('admin.kategori.destroy', $kategori) }}" method="POST"
                                                        onsubmit="return confirm('Hapus kategori {{ addslashes($kategori->nama_kategori) }}? Produk terkait tidak akan terhapus.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100"
                                                                title="Hapus Kategori">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                            <span>Hapus</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 mb-3">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                                    </svg>
                                                </div>
                                                <p class="font-medium text-gray-700">Tidak ada kategori ditemukan.</p>
                                                <p class="text-xs text-gray-400 mt-1">
                                                    @if(!empty($search))
                                                        Tidak ada hasil yang cocok dengan kata kunci "{{ $search }}". Silakan reset pencarian.
                                                    @else
                                                        Mulai dengan menambahkan kategori batik pertama lewat formulir di sebelah kiri.
                                                    @endif
                                                </p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination Links --}}
                        @if ($kategoris->hasPages())
                            <div class="border-t border-gray-100 px-6 py-4 bg-gray-50/50">
                                {{ $kategoris->links() }}
                            </div>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL EDIT KATEGORI --}}
    <div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-stone-950/60 backdrop-blur-xs p-4">
        <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl transition-all">
            
            <div class="mb-5 flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-lg bg-stone-100 text-stone-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Ubah Kategori Batik</h3>
                        <p class="text-xs text-gray-500">Perbarui data nama, deskripsi, atau foto cover</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                
                {{-- Nama Kategori --}}
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700">
                        Nama Kategori <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="editNamaKategori" name="nama_kategori" required maxlength="30"
                        pattern="[a-zA-Z\s]+" title="Nama kategori hanya boleh berupa huruf dan spasi"
                        oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-stone-900 focus:outline-none focus:ring-2 focus:ring-stone-900/10">
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700">
                        Deskripsi
                    </label>
                    <textarea id="editDeskripsi" name="deskripsi" rows="3"
                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-stone-900 focus:outline-none focus:ring-2 focus:ring-stone-900/10"></textarea>
                </div>

                {{-- Preview Foto Saat Ini & Opsi Foto Baru --}}
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-700">
                        Foto Cover Kategori
                    </label>

                    <div class="rounded-xl border border-gray-200 bg-stone-50/60 p-4 space-y-3">
                        <div class="flex items-center gap-4">
                            <div class="relative w-16 h-20 rounded-xl overflow-hidden bg-white border border-gray-200 shadow-2xs shrink-0">
                                <img id="editCurrentImagePreview" src="#" alt="Foto Kategori" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <span id="editCustomBadge" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold"></span>
                                <p class="text-xs text-gray-600 mt-1">Ganti dengan mengunggah foto baru di bawah.</p>
                                
                                {{-- Checkbox hapus foto kustom (jika memiliki foto kustom) --}}
                                <div id="editHapusCheckboxWrapper" class="mt-2 hidden">
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-rose-700 hover:text-rose-900">
                                        <input type="checkbox" name="hapus_gambar" value="1" id="editHapusGambarCheckbox" class="rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                                        <span class="font-medium">Hapus foto kustom (kembali ke foto bawaan)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- File Input Pengganti --}}
                        <div class="pt-2 border-t border-gray-200/60">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Upload Foto Baru (Opsional):</label>
                            <input type="file" name="gambar" accept="image/jpeg,image/png,image/jpg,image/webp"
                                   id="editGambarInput"
                                   onchange="handleEditImagePreview(this)"
                                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-900 file:text-white hover:file:bg-black file:cursor-pointer cursor-pointer border border-gray-300 rounded-xl bg-white p-1">
                            <p class="mt-1 text-[11px] text-gray-400">JPG, PNG, WEBP (Maksimal 3MB). Biarkan kosong jika tidak ingin mengubah foto.</p>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 rounded-xl border border-gray-300 py-2.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 rounded-xl bg-stone-900 py-2.5 text-xs font-semibold text-white transition hover:bg-black shadow-md">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const baseEditUrl = '{{ url("/admin/kategori") }}';

        // Preview saat memilih gambar di form tambah
        function handleCreateImagePreview(input) {
            const placeholder = document.getElementById('createPlaceholder');
            const previewBox = document.getElementById('createPreviewBox');
            const previewImg = document.getElementById('createPreviewImg');
            const fileName = document.getElementById('createFileName');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();

                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    fileName.textContent = file.name;
                    placeholder.classList.add('hidden');
                    previewBox.classList.remove('hidden');
                }

                reader.readAsDataURL(file);
            }
        }

        function clearCreateImagePreview() {
            const input = document.getElementById('createGambarInput');
            const placeholder = document.getElementById('createPlaceholder');
            const previewBox = document.getElementById('createPreviewBox');
            const previewImg = document.getElementById('createPreviewImg');
            const fileName = document.getElementById('createFileName');

            input.value = '';
            previewImg.src = '#';
            fileName.textContent = '';
            previewBox.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }

        // Preview saat memilih gambar pengganti di modal edit
        function handleEditImagePreview(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('editCurrentImagePreview').src = e.target.result;
                    const hapusCheckbox = document.getElementById('editHapusGambarCheckbox');
                    if (hapusCheckbox) {
                        hapusCheckbox.checked = false;
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Open Modal Edit
        function openEditModal(id, nama, deskripsi, gambarUrl, hasCustom) {
            document.getElementById('editNamaKategori').value = nama;
            document.getElementById('editDeskripsi').value = deskripsi;
            document.getElementById('editForm').action = baseEditUrl + '/' + id;
            document.getElementById('editCurrentImagePreview').src = gambarUrl;
            document.getElementById('editGambarInput').value = '';

            const badge = document.getElementById('editCustomBadge');
            const hapusWrapper = document.getElementById('editHapusCheckboxWrapper');
            const hapusCheckbox = document.getElementById('editHapusGambarCheckbox');

            if (hapusCheckbox) {
                hapusCheckbox.checked = false;
            }

            if (hasCustom) {
                badge.className = 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
                badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Foto Kustom Terpasang';
                hapusWrapper.classList.remove('hidden');
            } else {
                badge.className = 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium bg-stone-100 text-stone-600 border border-stone-200';
                badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-stone-400"></span> Gambar Bawaan Beranda';
                hapusWrapper.classList.add('hidden');
            }

            const modal = document.getElementById('editModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        // Close Modal Edit
        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        // Tutup modal jika klik di luar box
        document.getElementById('editModal').addEventListener('click', function (e) {
            if (e.target === this) closeEditModal();
        });

        // Tutup modal dengan tombol Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeEditModal();
        });
    </script>
</x-app-layout>
