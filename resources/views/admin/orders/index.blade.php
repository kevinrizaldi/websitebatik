<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Kelola Pesanan') }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Daftar seluruh transaksi pesanan batik dan manajemen status pengiriman
                </p>
            </div>
            <div class="inline-flex items-center gap-2 bg-white px-3.5 py-1.5 rounded-lg border border-gray-200 text-xs sm:text-sm text-gray-700 shadow-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span>Total: <strong class="text-gray-900 font-bold">{{ $counts['all'] }} Pesanan</strong></span>
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

            {{-- Alert Error Transisi Status & Resi --}}
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

            {{-- Kartu Putih Utama --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">

                {{-- Tab Filter Status --}}
                <div class="border-b border-gray-200 bg-white px-4 sm:px-6 pt-4">
                    <div class="flex overflow-x-auto gap-2 pb-3 scrollbar-none text-xs sm:text-sm font-medium">
                        @php
                            $tabs = [
                                '' => ['label' => 'Semua', 'count' => $counts['all']],
                                'Belum Dibayar' => ['label' => 'Belum Dibayar', 'count' => $counts['belum_dibayar']],
                                'Sudah Dibayar' => ['label' => 'Sudah Dibayar', 'count' => $counts['sudah_dibayar']],
                                'Diproses' => ['label' => 'Diproses', 'count' => $counts['diproses']],
                                'Dikirim' => ['label' => 'Dikirim', 'count' => $counts['dikirim']],
                                'Selesai' => ['label' => 'Selesai', 'count' => $counts['selesai']],
                                'Batal' => ['label' => 'Batal', 'count' => $counts['batal']],
                            ];
                            $activeStatus = request('status', '');
                        @endphp

                        @foreach ($tabs as $key => $tab)
                            @php $isActive = ($activeStatus === (string)$key); @endphp
                            <a href="{{ route('admin.orders.index', array_merge(request()->only('search'), $key !== '' ? ['status' => $key] : [])) }}"
                               @class([
                                   'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full transition whitespace-nowrap',
                                   'bg-gray-900 text-white font-semibold shadow-sm' => $isActive,
                                   'bg-gray-100 text-gray-700 hover:bg-gray-200' => !$isActive,
                               ])>
                                <span>{{ $tab['label'] }}</span>
                                <span @class([
                                    'text-xs px-2 py-0.5 rounded-full',
                                    'bg-white/20 text-white' => $isActive,
                                    'bg-white text-gray-600 border border-gray-200' => !$isActive,
                                ])>
                                    {{ $tab['count'] }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Toolbar Pencarian --}}
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-white flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <form action="{{ route('admin.orders.index') }}" method="GET" class="flex-1 flex flex-col sm:flex-row gap-3">
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
                                   placeholder="Cari ID pesanan, nama pembeli, nomor telepon..."
                                   class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold uppercase tracking-wider rounded-md shadow-sm transition">
                                Cari
                            </button>

                            @if(request('search') || request('status'))
                                <a href="{{ route('admin.orders.index') }}"
                                   class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-md transition">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>

                    <div class="text-xs text-gray-500">
                        Menampilkan <span class="font-medium text-gray-900">{{ $orders->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $orders->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $orders->total() }}</span> pesanan
                    </div>
                </div>

                {{-- Tabel Utama Pesanan --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    No. Pesanan & Waktu
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Pelanggan & Alamat
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Item Pesanan
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Total Tagihan
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Ubah Status / Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-gray-700">
                            @forelse ($orders as $order)
                                @php
                                    $statusStyle = match($order->status) {
                                        'Belum Dibayar', 'Menunggu Konfirmasi' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
                                        'Sudah Dibayar' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'border' => 'border-blue-200', 'dot' => 'bg-blue-500'],
                                        'Diproses' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-800', 'border' => 'border-indigo-200', 'dot' => 'bg-indigo-500'],
                                        'Dikirim' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-800', 'border' => 'border-sky-200', 'dot' => 'bg-sky-500'],
                                        'Selesai' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-800', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                                        'Batal', 'Dibatalkan' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-800', 'border' => 'border-rose-200', 'dot' => 'bg-rose-500'],
                                        default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-800', 'border' => 'border-gray-200', 'dot' => 'bg-gray-500'],
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50/70 transition-colors">

                                    {{-- NO. PESANAN & WAKTU --}}
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-gray-900 font-mono text-xs hover:text-indigo-600 flex items-center gap-1">
                                            #{{ $order->code }}
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $order->created_at->format('d M Y') }}
                                            <span class="text-gray-400">• {{ $order->created_at->format('H:i') }} WIB</span>
                                        </div>
                                    </td>

                                    {{-- DATA PELANGGAN --}}
                                    <td class="px-6 py-4 align-top">
                                        <div class="font-medium text-gray-900 text-sm">
                                            {{ $order->customer_name }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                            <span>📞 {{ $order->phone }}</span>
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1 max-w-xs truncate" title="{{ $order->address }}">
                                            📍 {{ $order->address }}
                                        </div>
                                    </td>

                                    {{-- ITEM PESANAN --}}
                                    <td class="px-6 py-4 align-top">
                                        <div class="space-y-1.5">
                                            @foreach ($order->items as $item)
                                                <div class="flex items-center gap-2 text-xs">
                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-gray-100 text-gray-700 text-[10px] font-semibold">
                                                        {{ $item->quantity }}x
                                                    </span>
                                                    <span class="font-medium text-gray-800">{{ $item->produk_name }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>

                                    {{-- TOTAL TAGIHAN --}}
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        <div class="font-bold text-gray-900 text-sm">
                                            Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            <span class="inline-block bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-[11px] font-medium">
                                                {{ $order->payment_method ?? 'Transfer Bank' }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }} {{ $statusStyle['border'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $statusStyle['dot'] }}"></span>
                                            {{ $order->status }}
                                        </span>
                                        @if ($order->tracking_number)
                                            <div class="text-[11px] font-mono text-gray-500 mt-1 flex items-center gap-1">
                                                <span class="text-gray-400 font-sans">Resi:</span>
                                                <span class="font-bold text-gray-800 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">{{ $order->tracking_number }}</span>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- UBAH STATUS / AKSI --}}
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        @php
                                            $nextOptions = $allowedTransitions[$order->status] ?? [];
                                            $isFinal = empty($nextOptions);
                                        @endphp
                                        <div class="space-y-2">
                                            @if ($isFinal)
                                                {{-- Status final: tidak bisa diubah --}}
                                                <div class="text-xs text-gray-400 italic px-1 py-1">
                                                    @if (in_array($order->status, ['Selesai']))
                                                        <span class="inline-flex items-center gap-1 text-emerald-600">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            Pesanan selesai
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 text-rose-500">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                            Pesanan dibatalkan
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                {{-- Tampilkan dropdown hanya opsi yang diizinkan --}}
                                                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST"
                                                      onsubmit="return handleStatusSubmit(event, this, '{{ route('admin.orders.update-status', $order) }}', '{{ $order->code }}', '{{ addslashes($order->customer_name) }}')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="flex items-center gap-1">
                                                        <select name="status" required
                                                                class="text-xs py-1 px-2 pr-7 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-700 cursor-pointer">
                                                            {{-- Placeholder status (wajib dipilih) --}}
                                                            <option value="" disabled selected class="text-gray-400">
                                                                -- Pilih Status --
                                                            </option>
                                                            {{-- Opsi yang boleh dipilih --}}
                                                            @foreach ($nextOptions as $nextStatus)
                                                                <option value="{{ $nextStatus }}">
                                                                    → {{ $nextStatus }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit"
                                                                class="text-xs px-2 py-1 bg-gray-800 hover:bg-black text-white rounded-md transition font-medium">
                                                            Ubah
                                                        </button>
                                                    </div>
                                                </form>
                                            @endif

                                            <div>
                                                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 hover:underline inline-flex items-center gap-1">
                                                    <span>Lihat Detail</span>
                                                    <span>&rarr;</span>
                                                </a>
                                            </div>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            <p class="font-medium text-gray-900 text-sm">Tidak ada data pesanan</p>
                                            <p class="text-xs text-gray-500 mt-1">Coba sesuaikan kata kunci pencarian atau tab status pesanan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($orders->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-white">
                        {{ $orders->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Modal Input Nomor Resi untuk Status Dikirim --}}
    <div id="resiModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 border border-gray-100">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 flex items-center justify-center text-base font-bold">🚚</span>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Input Nomor Resi Pengiriman</h3>
                        <p class="text-[11px] text-gray-400">Nomor resi wajib diisi untuk mengubah status ke <strong>Dikirim</strong></p>
                    </div>
                </div>
                <button type="button" onclick="closeResiModal()" class="text-gray-400 hover:text-gray-600 text-lg font-bold leading-none">&times;</button>
            </div>

            <div class="bg-gray-50 rounded-xl p-3 text-xs space-y-1">
                <div class="flex justify-between text-gray-500">
                    <span>No. Pesanan:</span>
                    <span id="modalOrderCode" class="font-mono font-bold text-gray-900">#ORD</span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>Pelanggan:</span>
                    <span id="modalCustomerName" class="font-medium text-gray-900">-</span>
                </div>
            </div>

            <form id="resiModalForm" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="Dikirim">

                <div>
                    <label for="modalTrackingNumber" class="block text-xs font-bold text-gray-700 mb-1">
                        NOMOR RESI PENGIRIMAN <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           id="modalTrackingNumber"
                           name="tracking_number"
                           required
                           placeholder="Contoh: JNE8892103982 / J&T / SiCepat"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none">
                    <p class="text-[11px] text-gray-400 mt-1">Pastikan nomor resi valid agar pembeli dapat melacak paket.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeResiModal()"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                        <span>🚚</span>
                        <span>Simpan Resi & Ubah ke Dikirim</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function handleStatusSubmit(event, form, actionUrl, orderCode, customerName) {
            const select = form.querySelector('select[name="status"]');
            const chosenStatus = select ? select.value : '';

            // Error handling jika admin belum memilih status tujuan
            if (!chosenStatus || chosenStatus.trim() === '') {
                event.preventDefault();
                alert('⚠️ Harap pilih status tujuan terlebih dahulu sebelum menekan tombol Ubah untuk pesanan #' + orderCode + '!');
                if (select) {
                    select.focus();
                    select.classList.add('ring-2', 'ring-rose-500', 'border-rose-500');
                    setTimeout(() => {
                        select.classList.remove('ring-2', 'ring-rose-500', 'border-rose-500');
                    }, 2500);
                }
                return false;
            }

            if (chosenStatus === 'Dikirim') {
                event.preventDefault();
                openResiModal(actionUrl, orderCode, customerName);
                return false;
            }

            return confirm('Apakah Anda yakin ingin mengubah status pesanan #' + orderCode + ' menjadi "' + chosenStatus + '"?');
        }

        function openResiModal(actionUrl, orderCode, customerName) {
            const modal = document.getElementById('resiModal');
            const form = document.getElementById('resiModalForm');
            const codeEl = document.getElementById('modalOrderCode');
            const customerEl = document.getElementById('modalCustomerName');
            const input = document.getElementById('modalTrackingNumber');

            form.action = actionUrl;
            codeEl.textContent = '#' + orderCode;
            customerEl.textContent = customerName;
            input.value = '';
            modal.classList.remove('hidden');
            setTimeout(() => input.focus(), 100);
        }

        function closeResiModal() {
            document.getElementById('resiModal').classList.add('hidden');
        }

        // Tutup modal jika klik di luar area modal
        document.getElementById('resiModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeResiModal();
            }
        });
    </script>
</x-app-layout>
