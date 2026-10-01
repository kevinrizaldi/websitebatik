<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Laporan Penjualan
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Ringkasan transaksi berdasarkan periode penjualan.
                </p>
            </div>
            <div class="inline-flex items-center gap-2 bg-white px-3.5 py-1.5 rounded-lg border border-gray-200 text-xs sm:text-sm text-gray-700 shadow-sm">
                <span>Total Data: <strong class="text-gray-900 font-bold">{{ $recordCount }}</strong></span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">

                {{-- Filter dan ekspor --}}
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-white space-y-4">
                    <form action="{{ route('admin.laporan.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3 items-end">
                        <div class="sm:col-span-2 xl:col-span-1">
                            <label for="report" class="block text-xs font-semibold text-gray-700 mb-1">Jenis Laporan</label>
                            <select name="report" id="report" class="w-full text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                                @foreach ($reportOptions as $group => $options)
                                    <optgroup label="{{ $group }}">
                                        @foreach ($options as $value => $label)
                                            <option value="{{ $value }}" @selected($reportType === $value)>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="start_date" class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" max="{{ date('Y-m-d') }}"
                                   class="w-full text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                        </div>
                        <div>
                            <label for="end_date" class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Akhir</label>
                            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" max="{{ date('Y-m-d') }}"
                                   class="w-full text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                        </div>
                        @if (str_starts_with($reportType, 'sales_'))
                            <div>
                                <label for="status" class="block text-xs font-semibold text-gray-700 mb-1">Status Pesanan</label>
                                <select name="status" id="status" class="w-full text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                                    <option value="">Semua Status</option>
                                    @foreach (['Belum Dibayar', 'Menunggu Konfirmasi', 'Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai', 'Batal', 'Dibatalkan'] as $orderStatus)
                                        <option value="{{ $orderStatus }}" @selected($status === $orderStatus)>{{ $orderStatus }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="flex gap-2">
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold rounded-md shadow-sm transition h-[42px]">Tampilkan</button>
                            <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-md transition h-[42px]">Reset</a>
                        </div>
                    </form>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.laporan.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" target="_blank" class="inline-flex items-center px-3.5 py-2 bg-rose-700 hover:bg-rose-800 text-white text-xs font-semibold rounded-md transition">PDF / Simpan PDF</a>
                        <a href="{{ route('admin.laporan.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="inline-flex items-center px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-md transition">Excel</a>
                        <a href="{{ route('admin.laporan.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="inline-flex items-center px-3.5 py-2 bg-sky-700 hover:bg-sky-800 text-white text-xs font-semibold rounded-md transition">CSV</a>
                        <a href="{{ route('admin.laporan.print', request()->query()) }}" target="_blank" class="inline-flex items-center px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-md transition">Cetak</a>
                    </div>
                </div>

                {{-- Tabel Data --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach ($columns as $column)
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider {{ ($column['type'] ?? '') === 'currency' ? 'text-right' : '' }}">
                                        {{ $column['label'] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-gray-700">
                            @forelse ($rows as $row)
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    @foreach ($columns as $column)
                                        @php($value = $row[$column['key']] ?? '')
                                        <td class="px-6 py-4 {{ ($column['type'] ?? '') === 'currency' ? 'text-right' : 'text-left' }} text-sm text-gray-700">
                                            @if (($column['type'] ?? '') === 'currency')
                                                Rp {{ number_format((float) $value, 0, ',', '.') }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($columns) }}" class="px-6 py-12 text-center text-gray-500">
                                        <p class="font-medium text-gray-900 text-sm">Belum ada data untuk laporan ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($totalRevenue !== null && $recordCount > 0)
                            <tfoot class="bg-gray-50 border-t border-gray-200">
                                <tr>
                                    <th colspan="{{ count($columns) - 1 }}" class="px-6 py-4 text-right text-sm font-bold text-gray-900 uppercase">
                                        Total Pendapatan:
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
            const reportInput = document.getElementById('report');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            if (!reportInput || !startDateInput || !endDateInput) {
                return;
            }

            const formatDate = date => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');

                return `${year}-${month}-${day}`;
            };

            const applyPeriodDates = () => {
                const today = new Date();
                const startDate = new Date(today);

                if (reportInput.value === 'sales_weekly') {
                    const daysSinceMonday = (today.getDay() + 6) % 7;
                    startDate.setDate(today.getDate() - daysSinceMonday);
                } else if (reportInput.value === 'sales_monthly') {
                    startDate.setDate(1);
                } else if (reportInput.value === 'sales_yearly') {
                    startDate.setMonth(0, 1);
                }

                startDateInput.value = formatDate(startDate);
                endDateInput.value = formatDate(today);
                endDateInput.min = startDateInput.value;
            };

            if (startDateInput.value) {
                endDateInput.min = startDateInput.value;
            }

            reportInput.addEventListener('change', applyPeriodDates);

            startDateInput.addEventListener('change', function() {
                endDateInput.min = this.value;
                if (endDateInput.value && endDateInput.value < this.value) {
                    endDateInput.value = this.value;
                }
            });
        });
    </script>
</x-app-layout>
