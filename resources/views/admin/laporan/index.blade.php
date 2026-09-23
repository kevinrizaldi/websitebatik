<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Laporan Penjualan') }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Lihat dan cetak laporan penjualan berdasarkan rentang tanggal.
                </p>
            </div>
            <div class="inline-flex items-center gap-2 bg-white px-3.5 py-1.5 rounded-lg border border-gray-200 text-xs sm:text-sm text-gray-700 shadow-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span>Total Pesanan: <strong class="text-gray-900 font-bold">{{ $orders->count() }}</strong></span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Kartu Putih Utama --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                
                {{-- Toolbar Filter --}}
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-white flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <form action="{{ route('admin.laporan.index') }}" method="GET" class="flex-1 flex flex-col sm:flex-row gap-3 items-end">
                        <div class="flex-1 w-full">
                            <label for="start_date" class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Mulai (Opsional)</label>
                            <input type="date" name="start_date" id="start_date" value="{{ request('start_date', $startDate) }}"
                                   class="w-full text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        </div>
                        <div class="flex-1 w-full">
                            <label for="end_date" class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Akhir (Opsional)</label>
                            <input type="date" name="end_date" id="end_date" value="{{ request('end_date', $endDate) }}"
                                   class="w-full text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        </div>
                        <div class="flex-1 w-full">
                            <label for="status" class="block text-xs font-semibold text-gray-700 mb-1">Status Pesanan</label>
                            <select name="status" id="status" class="w-full text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                <option value="">Semua Status</option>
                                <option value="Belum Dibayar" {{ request('status', $status ?? '') === 'Belum Dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
                                <option value="Sudah Dibayar" {{ request('status', $status ?? '') === 'Sudah Dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
                                <option value="Diproses" {{ request('status', $status ?? '') === 'Diproses' ? 'selected' : '' }}>Diproses</option>
                                <option value="Dikirim" {{ request('status', $status ?? '') === 'Dikirim' ? 'selected' : '' }}>Dikirim</option>
                                <option value="Selesai" {{ request('status', $status ?? '') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="Batal" {{ request('status', $status ?? '') === 'Batal' ? 'selected' : '' }}>Batal</option>
                            </select>
                        </div>
                        <div class="flex gap-2 w-full sm:w-auto">
                            <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold uppercase tracking-wider rounded-md shadow-sm transition h-[42px]">
                                Filter
                            </button>
                            <a href="{{ route('admin.laporan.index') }}"
                               class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-md transition h-[42px]">
                                Reset
                            </a>
                        </div>
                    </form>

                    <div>
                        <a href="{{ route('admin.laporan.print', request()->all()) }}" target="_blank"
                           class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-md shadow-sm transition gap-2 w-full sm:w-auto h-[42px]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Cetak Laporan
                        </a>
                    </div>
                </div>

                {{-- Tabel Data --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No. Pesanan</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Pelanggan</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-gray-700">
                            @php $totalRevenue = 0; @endphp
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
                                    // Hitung total hanya jika statusnya berhasil (bukan batal)
                                    if(!in_array($order->status, ['Batal', 'Dibatalkan'])) {
                                        $totalRevenue += $order->total_price;
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        {{ $order->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-gray-900 font-mono text-xs hover:text-indigo-600">
                                            #{{ $order->code }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 text-sm">
                                        {{ $order->customer_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }} {{ $statusStyle['border'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $statusStyle['dot'] }}"></span>
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-900 text-sm">
                                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <p class="font-medium text-gray-900 text-sm">Tidak ada data pesanan pada periode ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($orders->count() > 0)
                            <tfoot class="bg-gray-50 border-t border-gray-200">
                                <tr>
                                    <th colspan="4" class="px-6 py-4 text-right text-sm font-bold text-gray-900 uppercase">
                                        Total Pendapatan (Non-Batal):
                                    </th>
                                    <th class="px-6 py-4 text-right text-base font-bold text-indigo-600 whitespace-nowrap">
                                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                                    </th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            if (startDateInput && endDateInput) {
                // Set initial min value if start_date has a value
                if (startDateInput.value) {
                    endDateInput.min = startDateInput.value;
                }

                startDateInput.addEventListener('change', function() {
                    endDateInput.min = this.value;
                    if (endDateInput.value && endDateInput.value < this.value) {
                        endDateInput.value = this.value;
                    }
                });
            }
        });
    </script>
</x-app-layout>
