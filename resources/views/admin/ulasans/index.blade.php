<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Kelola Ulasan Pelanggan') }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Moderasi ulasan produk, kepuasan pembeli, dan testimoni batik
                </p>
            </div>
            <div class="inline-flex items-center gap-3 bg-white px-3.5 py-1.5 rounded-lg border border-gray-200 text-xs sm:text-sm text-gray-700 shadow-sm">
                <span class="flex items-center gap-1 font-bold text-amber-500">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span>{{ $counts['avg_rating'] }} / 5.0</span>
                </span>
                <span class="text-gray-300">|</span>
                <span>Total: <strong class="text-gray-900 font-bold">{{ $counts['all'] }} Ulasan</strong></span>
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

            {{-- Alert Error --}}
            @if ($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg flex justify-between items-center text-sm shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p class="font-medium text-xs sm:text-sm">• {{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-700 font-bold text-lg leading-none">&times;</button>
                </div>
            @endif

            {{-- 4 Kartu Statistik Ringkasan --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500 font-medium">Total Ulasan</div>
                        <div class="text-2xl font-extrabold text-gray-900 mt-1">{{ $counts['all'] }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500 font-medium">Rata-Rata Bintang</div>
                        <div class="text-2xl font-extrabold text-amber-500 mt-1">★ {{ $counts['avg_rating'] }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center text-amber-500">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs text-amber-700 font-medium">Menunggu Moderasi</div>
                        <div class="text-2xl font-extrabold text-amber-600 mt-1">{{ $counts['menunggu'] }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs text-emerald-700 font-medium">Disetujui / Tampil</div>
                        <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $counts['disetujui'] }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Kartu Putih Utama --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">

                {{-- Tab Filter Moderasi --}}
                <div class="border-b border-gray-200 bg-white px-4 sm:px-6 pt-4">
                    <div class="flex overflow-x-auto gap-2 pb-3 scrollbar-none text-xs sm:text-sm font-medium">
                        @php
                            $tabs = [
                                '' => ['label' => 'Semua', 'count' => $counts['all']],
                                'Menunggu' => ['label' => 'Menunggu Moderasi', 'count' => $counts['menunggu']],
                                'Disetujui' => ['label' => 'Disetujui', 'count' => $counts['disetujui']],
                                'Ditolak' => ['label' => 'Ditolak', 'count' => $counts['ditolak']],
                            ];
                            $activeStatus = request('status', '');
                        @endphp

                        @foreach ($tabs as $key => $tab)
                            <a href="{{ route('admin.ulasans.index', array_merge(request()->only(['search', 'rating']), $key !== '' ? ['status' => $key] : [])) }}"
                               class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full transition whitespace-nowrap {{ ($activeStatus === (string)$key) ? 'bg-gray-900 text-white font-semibold shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <span>{{ $tab['label'] }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ ($activeStatus === (string)$key) ? 'bg-white/20 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">
                                    {{ $tab['count'] }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Toolbar Pencarian & Filter Rating --}}
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-white flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <form action="{{ route('admin.ulasans.index') }}" method="GET" class="flex-1 flex flex-col sm:flex-row gap-3">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif

                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </span>
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Cari nama pembeli, nama batik, atau isi komentar..."
                                   class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        </div>

                        <div>
                            <select name="rating" class="w-full sm:w-auto text-sm bg-white border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                <option value="">Semua Bintang</option>
                                <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5 Bintang)</option>
                                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐ (4 Bintang)</option>
                                <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>⭐⭐⭐ (3 Bintang)</option>
                                <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>⭐⭐ (2 Bintang)</option>
                                <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>⭐ (1 Bintang)</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold uppercase tracking-wider rounded-md shadow-sm transition">
                                Filter
                            </button>

                            @if(request('search') || request('status') || request('rating'))
                                <a href="{{ route('admin.ulasans.index') }}"
                                   class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-md transition">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>

                    <div class="text-xs text-gray-500">
                        Menampilkan <span class="font-medium text-gray-900">{{ $ulasans->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $ulasans->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $ulasans->total() }}</span> ulasan
                    </div>
                </div>

                {{-- Tabel Utama Ulasan --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Pelanggan &amp; Waktu
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Produk Batik
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Rating
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Isi Ulasan
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Moderasi / Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-gray-700">
                            @forelse ($ulasans as $ulasan)
                                @php
                                    $statusStyle = match($ulasan->status) {
                                        'Disetujui' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-800', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                                        'Menunggu' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
                                        'Ditolak' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-800', 'border' => 'border-rose-200', 'dot' => 'bg-rose-500'],
                                        default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-800', 'border' => 'border-gray-200', 'dot' => 'bg-gray-500'],
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50/70 transition-colors">

                                    {{-- PELANGGAN & WAKTU --}}
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        <div class="font-bold text-gray-900 text-sm">
                                            {{ $ulasan->customer_name }}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>{{ $ulasan->created_at->format('d M Y, H:i') }} WIB</span>
                                        </div>
                                    </td>

                                    {{-- PRODUK BATIK --}}
                                    <td class="px-6 py-4 align-top">
                                        @if($ulasan->produk)
                                            <div class="flex items-start gap-3">
                                                @if($ulasan->produk->gambar)
                                                    <img src="{{ asset('storage/' . $ulasan->produk->gambar) }}" alt="{{ $ulasan->produk->nama }}" class="w-10 h-10 object-cover rounded-lg border border-gray-200 shrink-0">
                                                @else
                                                    <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 text-gray-400 flex items-center justify-center shrink-0">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-semibold text-gray-900 text-xs line-clamp-1 hover:text-indigo-600">
                                                        <a href="{{ route('produk.show', $ulasan->produk) }}">{{ $ulasan->produk->nama }}</a>
                                                    </div>
                                                    <div class="text-[11px] text-gray-400 mt-0.5">
                                                        {{ $ulasan->produk->kategori }} • <span class="font-mono">{{ $ulasan->produk->sku }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Produk telah dihapus</span>
                                        @endif
                                    </td>

                                    {{-- RATING BINTANG --}}
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        <div class="flex items-center gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $ulasan->rating ? 'text-amber-400 fill-current' : 'text-gray-200 fill-current' }}" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                            <span class="text-xs font-bold text-gray-800 ml-1">({{ $ulasan->rating }}.0)</span>
                                        </div>
                                    </td>

                                    {{-- ISI ULASAN --}}
                                    <td class="px-6 py-4 align-top max-w-sm">
                                        <p class="text-xs text-gray-700 leading-relaxed italic bg-gray-50/80 p-2.5 rounded-lg border border-gray-100">
                                            "{{ $ulasan->comment }}"
                                        </p>
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }} {{ $statusStyle['border'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $statusStyle['dot'] }}"></span>
                                            {{ $ulasan->status }}
                                        </span>
                                    </td>

                                    {{-- MODERASI / AKSI --}}
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        <div class="space-y-2">
                                            @if($ulasan->status === 'Disetujui')
                                                {{-- Status terkunci --}}
                                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-emerald-50 border border-emerald-200 rounded-md text-xs font-medium text-emerald-700">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                    Status terkunci
                                                </div>
                                            @else
                                                {{-- Tombol Setujui --}}
                                                <form action="{{ route('admin.ulasans.update-status', $ulasan) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="Disetujui">
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-medium shadow-sm transition w-full justify-center">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Setujui
                                                    </button>
                                                </form>

                                                {{-- Tombol Tolak (hanya tampil jika masih Menunggu) --}}
                                                @if($ulasan->status === 'Menunggu')
                                                    <form action="{{ route('admin.ulasans.update-status', $ulasan) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="Ditolak">
                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-white hover:bg-rose-50 text-rose-600 border border-rose-300 rounded-md font-medium shadow-sm transition w-full justify-center">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                            Tolak
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            {{-- Tombol Hapus --}}
                                            <form action="{{ route('admin.ulasans.destroy', $ulasan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan dari {{ addslashes($ulasan->customer_name) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-700 hover:underline w-full justify-center pt-1">
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
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                            </svg>
                                            <p class="font-medium text-gray-900 text-sm">Tidak ada ulasan ditemukan</p>
                                            <p class="text-xs text-gray-500 mt-1">Coba sesuaikan kata kunci pencarian atau filter status ulasan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($ulasans->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-white">
                        {{ $ulasans->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
