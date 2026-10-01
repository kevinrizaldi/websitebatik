<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Kelola Kategori') }}
                </h2>
                <p class="mt-1 text-xs text-gray-500 sm:text-sm">
                    Kelompokkan produk batik: Baju Batik, Kain Batik, Seragam ASN, dan Olahan Kain Sisa
                </p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs text-gray-700 shadow-sm sm:text-sm">
                <span>Total: <strong class="font-bold text-gray-900">{{ $kategoris->total() }} Kategori</strong></span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 shadow-sm">
                    <span class="font-medium">{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-lg font-bold leading-none text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 shadow-sm">
                    @foreach ($errors->all() as $error)
                        <p class="text-xs sm:text-sm">• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="font-bold text-gray-900">Tambah Kategori Baru</h3>
                        <p class="mt-1 text-xs text-gray-500">Nama kategori tampil di katalog pelanggan.</p>
                        <form action="{{ route('admin.kategori.store') }}" method="POST" class="mt-4 space-y-4">
                            @csrf
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700">Nama Kategori <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_kategori" value="{{ old('nama_kategori') }}" required pattern="[a-zA-Z\s]+" title="Nama kategori hanya boleh berupa huruf dan spasi" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')" placeholder="Contoh: Seragam ASN" class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700">Deskripsi</label>
                                <textarea name="deskripsi" rows="3" placeholder="Deskripsi singkat kategori..." class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('deskripsi') }}</textarea>
                            </div>
                            <button type="submit" class="w-full rounded-md bg-gray-900 py-2.5 text-xs font-semibold uppercase tracking-wider text-white shadow-sm transition hover:bg-black">Simpan Kategori</button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Nama Kategori</th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Deskripsi</th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Produk</th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white text-gray-700">
                                    @forelse ($kategoris as $kategori)
                                        <tr class="transition-colors hover:bg-gray-50/70">
                                            <td class="px-6 py-4 align-top">
                                                <div class="font-bold text-gray-900">{{ $kategori->nama_kategori }}</div>
                                                <div class="text-[11px] text-gray-400">/{{ $kategori->slug }}</div>
                                            </td>
                                            <td class="max-w-xs px-6 py-4 align-top text-xs text-gray-600">{{ $kategori->deskripsi ?: '—' }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 align-top">
                                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-700">{{ $kategori->produks_count }} produk</span>
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 align-top">
                                                <div class="flex items-center gap-2">
                                                    <details class="relative">
                                                        <summary class="cursor-pointer list-none rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">Ubah</summary>
                                                        <form action="{{ route('admin.kategori.update', $kategori) }}" method="POST" class="absolute right-0 z-10 mt-2 w-72 space-y-2 rounded-xl border border-gray-200 bg-white p-4 shadow-xl">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="text" name="nama_kategori" value="{{ $kategori->nama_kategori }}" required pattern="[a-zA-Z\s]+" title="Nama kategori hanya boleh berupa huruf dan spasi" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                                                            <textarea name="deskripsi" rows="2" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">{{ $kategori->deskripsi }}</textarea>
                                                            <button type="submit" class="w-full rounded-md bg-gray-900 py-2 text-xs font-bold text-white hover:bg-black">Simpan</button>
                                                        </form>
                                                    </details>
                                                    <form action="{{ route('admin.kategori.destroy', $kategori) }}" method="POST" onsubmit="return confirm('Hapus kategori {{ $kategori->nama_kategori }}? Produk terkait tidak ikut terhapus.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="rounded-md border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">Belum ada kategori. Tambahkan kategori pertama lewat formulir di samping.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($kategoris->hasPages())
                            <div class="border-t border-gray-100 px-6 py-4">{{ $kategoris->links() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
