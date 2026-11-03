<x-app-layout>
    <div class="space-y-6">

        {{-- Top Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-[#F4EFEA] text-[11px] font-semibold text-[#8C6D58] tracking-wider uppercase mb-2">
                    <svg class="w-3.5 h-3.5 text-[#B85D3B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>ANALISIS OMZET &amp; PERPUTARAN WASTRA</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#1E1B18] tracking-tight">
                    Laporan Penjualan
                </h1>
                <p class="text-sm text-[#7A7269] mt-1">
                    Rekapitulasi transaksi, performa produk wastra, dan audit finansial berkala Studio Hamzah Style.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-[#ECE5D8] text-xs font-semibold text-[#1E1B18] shadow-xs">
                    <span>Total Baris:</span>
                    <strong class="font-bold text-[#B85D3B]">{{ $recordCount }}</strong>
                </span>
            </div>
        </div>

        {{-- Filter & Export Controls Card --}}
        <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 sm:p-6 shadow-xs space-y-4">
            <form action="{{ route('admin.laporan.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="start_date" class="block text-xs font-bold text-[#1E1B18] mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" max="{{ date('Y-m-d') }}"
                           class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-medium focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-bold text-[#1E1B18] mb-1.5">Tanggal Akhir</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" min="{{ $startDate }}" max="{{ date('Y-m-d') }}"
                           class="w-full py-2.5 px-3.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] font-medium focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#1E1B18] hover:bg-black text-xs font-semibold text-white transition shadow-xs w-full sm:w-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Tampilkan</span>
                    </button>
                    <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-[#D9D0C5] bg-[#FAF7F2] hover:bg-white text-xs font-semibold text-[#5A544E] transition">
                        Reset
                    </a>
                </div>
            </form>

            <div class="pt-3 border-t border-[#F3EDE6] flex flex-wrap items-center justify-between gap-3">
                <span class="text-[11px] font-bold text-[#8C827A] uppercase tracking-wider">Format Ekspor Berkas:</span>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.laporan.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 text-xs font-semibold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span>PDF / Simpan PDF</span>
                    </a>
                    <a href="{{ route('admin.laporan.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Excel</span>
                    </a>
                    <a href="{{ route('admin.laporan.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-sky-50 border border-sky-200 text-sky-700 hover:bg-sky-100 text-xs font-semibold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>CSV</span>
                    </a>
                    <a href="{{ route('admin.laporan.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg border border-[#D9D0C5] bg-[#FAF7F2] hover:bg-white text-xs font-semibold text-[#1E1B18] transition">
                        <svg class="w-3.5 h-3.5 text-[#7A7269]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Tabel Data Laporan --}}
        <div class="bg-white rounded-2xl border border-[#ECE5D8] shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#ECE5D8] bg-[#FAF7F2] text-[10px] font-bold uppercase tracking-wider text-[#8C827A]">
                            @foreach ($columns as $column)
                                <th scope="col" class="py-3.5 px-5 {{ ($column['type'] ?? '') === 'currency' ? 'text-right' : 'text-left' }}">
                                    {{ $column['label'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EDE6] text-[#1E1B18]">
                        @forelse ($rows as $row)
                            <tr class="hover:bg-[#FCFAF7] transition">
                                @foreach ($columns as $column)
                                    @php($value = $row[$column['key']] ?? '')
                                    <td class="py-3.5 px-5 {{ ($column['type'] ?? '') === 'currency' ? 'text-right font-bold' : 'text-left' }}">
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
                                <td colspan="{{ count($columns) }}" class="py-12 text-center text-[#7A7269]">
                                    Belum ada data untuk laporan penjualan periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($totalRevenue !== null && $recordCount > 0)
                        <tfoot class="border-t-2 border-[#ECE5D8] bg-[#FAF7F2]">
                            <tr>
                                <th colspan="{{ count($columns) - 1 }}" class="py-4 px-5 text-right text-xs font-bold text-[#1E1B18] uppercase tracking-wider">
                                    Total Pendapatan:
                                </th>
                                <th class="py-4 px-5 text-right text-base font-serif font-bold text-[#B85D3B] whitespace-nowrap">
                                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            if (!startDateInput || !endDateInput) {
                return;
            }

            if (startDateInput.value) {
                endDateInput.min = startDateInput.value;
            }

            startDateInput.addEventListener('change', function() {
                endDateInput.min = this.value;
                if (endDateInput.value && endDateInput.value < this.value) {
                    endDateInput.value = this.value;
                }
            });
        });
    </script>
</x-app-layout>
