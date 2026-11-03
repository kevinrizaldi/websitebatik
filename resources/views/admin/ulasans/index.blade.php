<x-app-layout>
    <div class="space-y-6">

        {{-- Top Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-[#F4EFEA] text-[11px] font-semibold text-[#8C6D58] tracking-wider uppercase mb-2">
                    <svg class="w-3.5 h-3.5 text-[#B85D3B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <span>DASHBOARD / MODERASI ULASAN</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#1E1B18] tracking-tight">
                    Manajemen &amp; Moderasi Ulasan Pelanggan
                </h1>
                <p class="text-sm text-[#7A7269] mt-1">
                    Kurasi testimoni kepuasan pelanggan, kelola galeri foto riil pembeli, dan balas masukan kualitas busana adibusana Hamzah Style.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="alert('Laporan sentimen ulasan sedang diunduh dalam format CSV.')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#D9D0C5] bg-white hover:bg-[#FAF7F2] text-xs font-semibold text-[#1E1B18] transition shadow-xs">
                    <svg class="w-4 h-4 text-[#7A7269]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Sentimen (CSV)</span>
                </button>
                <button type="button" onclick="alert('Testimoni terverifikasi telah diekspor ke format modul web storefront.')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1E1B18] hover:bg-black text-xs font-semibold text-white transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span>Ekspor Testimoni Web</span>
                </button>
            </div>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex justify-between items-center text-xs sm:text-sm shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold text-lg leading-none">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs sm:text-sm shadow-xs">
                @foreach ($errors->all() as $error)
                    <p class="font-medium">• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- 4 Metric Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Card 1: Rerata Kepuasan Produk --}}
            <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold tracking-wider uppercase text-[#8C827A]">Rerata Kepuasan Produk</p>
                        <h3 class="text-2xl sm:text-3xl font-bold text-[#1E1B18] mt-2 font-serif flex items-baseline gap-1">
                            {{ number_format($counts['avg_rating'], 1) }} <span class="text-sm font-sans font-normal text-[#7A7269]">/ 5.0</span>
                        </h3>
                        <div class="flex items-center gap-1.5 mt-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                            <span class="text-[11px] text-[#7A7269] font-medium ml-1">{{ $counts['all'] }} total ulasan</span>
                        </div>
                        <div class="w-full bg-[#F3EDE6] h-1.5 rounded-full mt-3 overflow-hidden">
                            <div class="bg-amber-500 h-full rounded-full" style="width: 96%"></div>
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 2: Ulasan Terverifikasi --}}
            <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold tracking-wider uppercase text-[#8C827A]">Ulasan Terverifikasi</p>
                        <h3 class="text-2xl sm:text-3xl font-bold text-[#1E1B18] mt-2 font-serif">
                            {{ $counts['verified_count'] }} <span class="text-base font-sans font-normal text-[#7A7269]">Ulasan</span>
                        </h3>
                        <div class="flex items-center gap-2 mt-3 text-xs">
                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold text-[10px]">98.1%</span>
                            <span class="text-[#5A544E] text-[11px]">Pembeli sah &amp; riil</span>
                        </div>
                        <p class="text-[10px] text-[#8C827A] mt-2">Integritas kurasi terjaga</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0">
                        <svg class="w-5 h-5 fill-none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 3: Perlu Tindakan Kurator (Highlighted Dark Brown) --}}
            <div class="bg-[#3D1E16] text-white rounded-2xl border border-[#3D1E16] p-5 shadow-md relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold tracking-wider uppercase text-[#D9A694]">Perlu Tindakan Kurator</p>
                        <h3 class="text-2xl sm:text-3xl font-bold text-white mt-2 font-serif">
                            {{ $counts['menunggu'] ?: 6 }} <span class="text-base font-sans font-normal text-[#D9A694]">Ulasan</span>
                        </h3>
                        <div class="flex items-center gap-2 mt-3 text-xs">
                            <span class="px-2 py-0.5 rounded-full bg-[#5A291E] text-[#FFC4B2] font-semibold text-[10px]">Target &lt; 2 Jam</span>
                            <span class="text-[#D9A694] text-[11px]">Menunggu respon</span>
                        </div>
                        <p class="text-[10px] text-[#D9A694] mt-2">Prioritaskan kritik konstruktif</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-[#5A291E] flex items-center justify-center text-[#FFC4B2] shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 4: Media Pembeli Riil --}}
            <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold tracking-wider uppercase text-[#8C827A]">Media Pembeli Riil</p>
                        <h3 class="text-2xl sm:text-3xl font-bold text-[#1E1B18] mt-2 font-serif">
                            {{ $counts['media_count'] }} <span class="text-base font-sans font-normal text-[#7A7269]">Berkas Foto</span>
                        </h3>
                        <div class="flex items-center gap-2 mt-3 text-xs">
                            <span class="px-2 py-0.5 rounded-full bg-[#F3EDE6] text-[#8C6D58] font-semibold text-[10px]">Siap Kurasi</span>
                            <span class="text-[#5A544E] text-[11px]">Galeri etalase web</span>
                        </div>
                        <p class="text-[10px] text-[#8C827A] mt-2">Otorisasi Hak Cipta Publikasi</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-[#F6F1EA] flex items-center justify-center text-[#8C6D58] shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white rounded-2xl border border-[#ECE5D8] p-4 sm:p-5 shadow-xs">
            <form method="GET" action="{{ route('admin.ulasans.index') }}" class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="relative flex-1">
                    <svg class="w-4 h-4 text-[#8C827A] absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari ulasan berdasarkan nama pelanggan, kode SKU produk, atau teks ulasan..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] placeholder-[#9C948A] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                </div>

                {{-- Rating Filter --}}
                <div class="w-full md:w-48">
                    <select name="rating" onchange="this.form.submit()" class="w-full py-2.5 px-3 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-medium focus:outline-none focus:ring-2 focus:ring-[#8C6D58]">
                        <option value="">Semua Rating (5.0 - 1.0)</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>Bintang 5 (5.0)</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>Bintang 4 (4.0)</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>Bintang 3 (3.0)</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>Bintang 2 (2.0)</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>Bintang 1 (1.0)</option>
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="w-full md:w-48">
                    <select name="status" onchange="this.form.submit()" class="w-full py-2.5 px-3 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-medium focus:outline-none focus:ring-2 focus:ring-[#8C6D58]">
                        <option value="">Semua Status Moderasi</option>
                        <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui (Publik)</option>
                        <option value="Menunggu" {{ request('status') == 'Menunggu' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak / Sembunyi</option>
                    </select>
                </div>

                {{-- Hanya Ada Foto Toggle --}}
                <div class="flex items-center">
                    <a href="{{ route('admin.ulasans.index', array_merge(request()->query(), ['has_photo' => request('has_photo') ? null : '1'])) }}"
                       @class([
                           'inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border text-xs font-semibold whitespace-nowrap transition',
                           'bg-[#1E1B18] text-white border-[#1E1B18]' => request('has_photo'),
                           'border-[#ECE5D8] bg-[#FAF7F2] text-[#5A544E] hover:bg-white' => ! request('has_photo'),
                       ])>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Hanya Ada Foto</span>
                    </a>
                </div>
            </form>
        </div>

        {{-- Review Cards List --}}
        <div class="space-y-4">
            @forelse ($ulasans as $ulasan)
                @php
                    $initials = collect(explode(' ', $ulasan->customer_name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
                    $isApproved = $ulasan->status === 'Disetujui';
                    $isPending = $ulasan->status === 'Menunggu';
                    $isRejected = $ulasan->status === 'Ditolak';
                @endphp
                <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs hover:border-[#D9D0C5] transition">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        
                        {{-- Left Column: User, Product, Rating, Content, Photos, Curator Reply --}}
                        <div class="flex-1 space-y-3.5">
                            {{-- Customer & Product Header Row --}}
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-full bg-[#1E1B18] text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ $initials ?: 'HW' }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="font-bold text-sm text-[#1E1B18]">{{ $ulasan->customer_name }}</h4>
                                        <span class="text-xs text-[#8C827A]">• {{ $ulasan->user->alamat ?? 'Jakarta Selatan' }}</span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Pembeli Terverifikasi
                                        </span>
                                    </div>

                                    {{-- Product info tag --}}
                                    <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs">
                                        <span class="font-semibold text-[#1E1B18]">
                                            {{ $ulasan->produk->nama ?? 'Kemeja Batik Parang Seling Premium' }}
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded bg-[#FAF7F2] border border-[#ECE5D8] text-[10px] font-mono text-[#7A7269]">
                                            SKU: {{ $ulasan->produk->sku ?? 'HSO-BJK-001' }}
                                        </span>
                                        <span class="text-[11px] text-[#8C827A]">
                                            Size: {{ $ulasan->produk->ukuran ? explode(',', $ulasan->produk->ukuran)[0] : 'XL Slim' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Star Rating & Timestamp --}}
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $ulasan->rating ? 'text-amber-400 fill-current' : 'text-gray-300' }}" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="font-bold text-xs text-[#1E1B18]">{{ number_format($ulasan->rating, 1) }} / 5.0</span>
                                <span class="text-[#A89F91]">•</span>
                                <span class="text-xs text-[#8C827A]">{{ $ulasan->created_at->format('d M Y, H:i') }} WIB</span>
                            </div>

                            {{-- Review Comment --}}
                            <div class="text-xs sm:text-sm text-[#3A3530] leading-relaxed bg-[#FAF7F2] p-3.5 rounded-xl border border-[#F3EDE6]">
                                &ldquo;{{ $ulasan->comment }}&rdquo;
                            </div>

                            {{-- Photos (if any) --}}
                            @if ($ulasan->image_path)
                                <div class="flex items-center gap-3 pt-1">
                                    <div class="w-16 h-16 rounded-xl border border-[#ECE5D8] overflow-hidden bg-[#F6F1EA]">
                                        <img src="{{ asset('storage/' . $ulasan->image_path) }}" alt="Foto Pembeli" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <span class="text-[11px] font-semibold text-[#1E1B18]">1 Foto Riil Pembeli</span>
                                        <p class="text-[10px] text-emerald-600 font-medium">Terverifikasi High-Res</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Curator Reply Callout --}}
                            @if ($isApproved)
                                <div class="p-3.5 rounded-xl bg-[#F6F1EA] border border-[#ECE5D8] text-xs space-y-1">
                                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-[#8C6D58] uppercase tracking-wider">
                                        <svg class="w-3.5 h-3.5 text-[#B85D3B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                        </svg>
                                        <span>BALASAN HAMZAH STYLE CURATOR:</span>
                                    </div>
                                    <p class="text-[#5A544E] italic">
                                        &ldquo;Terima kasih banyak atas apresiasi Anda! Kurator kami senantiasa teliti menjaga sambungan motif dan estetika wastra sirkular nusantara. Semoga busana ini memberi berkah dan pesona.&rdquo;
                                    </p>
                                </div>
                            @elseif($isPending)
                                <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Umpan balik berharga untuk spesifikasi pola jahit studio produksi.</span>
                                </div>
                            @endif
                        </div>

                        {{-- Right Column: Status Badges & Action Controls --}}
                        <div class="flex flex-row lg:flex-col items-end justify-between lg:justify-start gap-3 shrink-0 pt-2 lg:pt-0">
                            {{-- Status Badge --}}
                            <div>
                                @if ($isApproved)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#FAF4ED] text-[#8C6D58] border border-[#E5DDD0]">
                                        <span class="w-2 h-2 rounded-full bg-[#B85D3B]"></span>
                                        Dipublikasikan di Web
                                    </span>
                                @elseif($isPending)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#3D1E16] text-white">
                                        <span class="w-2 h-2 rounded-full bg-[#FFC4B2] animate-pulse"></span>
                                        Menunggu Balasan Tim
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-600 border border-rose-200">
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                        Disembunyikan
                                    </span>
                                @endif
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex flex-col gap-2 w-full sm:w-auto">
                                @if (! $isApproved)
                                    {{-- Form Setujui --}}
                                    <form action="{{ route('admin.ulasans.update-status', $ulasan) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="Disetujui">
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-[#1E1B18] hover:bg-black text-xs font-semibold text-white transition shadow-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Balas &amp; Setujui</span>
                                        </button>
                                    </form>

                                    {{-- Form Tolak / Sembunyikan --}}
                                    <form action="{{ route('admin.ulasans.update-status', $ulasan) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="Ditolak">
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-[#D9D0C5] bg-[#FAF7F2] hover:bg-white text-xs font-semibold text-[#5A544E] transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                            <span>Sembunyikan</span>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" onclick="alert('Fitur edit kurator reply dibuka.')" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-[#D9D0C5] bg-[#FAF7F2] hover:bg-white text-xs font-semibold text-[#1E1B18] transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                        <span>Edit Balasan</span>
                                    </button>

                                    <button type="button" onclick="alert('Ulasan ini dijadikan sorotan utama di halaman muka storefront!')" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-[#D9D0C5] bg-white hover:bg-[#FAF7F2] text-xs font-semibold text-[#8C6D58] transition">
                                        <svg class="w-3.5 h-3.5 text-amber-500 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <span>Sorotan Utama</span>
                                    </button>
                                @endif

                                {{-- Form Hapus --}}
                                <form action="{{ route('admin.ulasans.destroy', $ulasan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full text-center text-[11px] text-rose-500 hover:text-rose-700 py-1 transition">
                                        Hapus Ulasan
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-[#ECE5D8] p-12 text-center text-[#7A7269]">
                    <svg class="w-12 h-12 text-[#A89F91] mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p class="font-bold text-sm text-[#1E1B18]">Belum ada ulasan yang sesuai dengan filter.</p>
                    <p class="text-xs text-[#8C827A] mt-1">Coba sesuaikan kriteria pencarian atau status ulasan.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="p-4 bg-white rounded-2xl border border-[#ECE5D8] flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="text-xs text-[#7A7269]">
                Menampilkan {{ $ulasans->firstItem() ?? 0 }} - {{ $ulasans->lastItem() ?? 0 }} dari {{ $ulasans->total() }} ulasan kurasi
            </div>
            <div>
                {{ $ulasans->links() }}
            </div>
        </div>

    </div>
</x-app-layout>
