<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Penjualan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { font-size: 12pt; }
            .no-print { display: none !important; }
            @page { margin: 2cm; }
        }
    </style>
</head>
<body class="bg-white text-gray-900 p-8 font-sans" onload="window.print()">
    
    <div class="max-w-4xl mx-auto">
        {{-- Header Laporan --}}
        <div class="text-center mb-8 border-b-2 border-gray-800 pb-4">
            <h1 class="text-2xl font-bold uppercase tracking-wider">Laporan Penjualan</h1>
            <p class="text-gray-600 mt-1">
                Periode: 
                @if($startDate && $endDate)
                    {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                @else
                    Semua Waktu
                @endif
                <br>
                Status: {{ $status ? $status : 'Semua Status' }}
            </p>
        </div>

        {{-- Tabel Data --}}
        <table class="w-full text-left border-collapse mb-8">
            <thead>
                <tr class="border-b border-gray-800">
                    <th class="py-2 px-1 font-semibold text-sm">No</th>
                    <th class="py-2 px-1 font-semibold text-sm">Tanggal</th>
                    <th class="py-2 px-1 font-semibold text-sm">No. Pesanan</th>
                    <th class="py-2 px-1 font-semibold text-sm">Pelanggan</th>
                    <th class="py-2 px-1 font-semibold text-sm">Status</th>
                    <th class="py-2 px-1 font-semibold text-sm text-right">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php $totalRevenue = 0; @endphp
                @forelse ($orders as $index => $order)
                    @php
                        if(!in_array($order->status, ['Batal', 'Dibatalkan'])) {
                            $totalRevenue += $order->total_price;
                        }
                    @endphp
                    <tr class="border-b border-gray-200 text-sm">
                        <td class="py-2 px-1">{{ $index + 1 }}</td>
                        <td class="py-2 px-1">{{ $order->created_at->format('d/m/Y') }}</td>
                        <td class="py-2 px-1">#{{ $order->code }}</td>
                        <td class="py-2 px-1">{{ $order->customer_name }}</td>
                        <td class="py-2 px-1">{{ $order->status }}</td>
                        <td class="py-2 px-1 text-right">{{ number_format($order->total_price, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-gray-500 italic">Tidak ada data pesanan pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($orders->count() > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-800">
                        <th colspan="5" class="py-3 px-1 text-right font-bold uppercase text-sm">Total Pendapatan (Non-Batal):</th>
                        <th class="py-3 px-1 text-right font-bold text-base">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            @endif
        </table>

        {{-- Footer TTD --}}
        <div class="mt-16 flex justify-end">
            <div class="text-center w-48">
                <p class="mb-16 text-sm">Mengetahui,</p>
                <p class="font-bold border-b border-gray-400 pb-1">{{ auth()->user()->name ?? 'Administrator' }}</p>
                <p class="text-xs text-gray-500 mt-1">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        {{-- Tombol Print (Sembunyi saat dicetak) --}}
        <div class="mt-8 text-center no-print">
            <button onclick="window.print()" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow transition">
                Cetak Sekarang
            </button>
            <a href="javascript:window.close();" class="ml-2 px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-lg shadow transition">
                Tutup
            </a>
        </div>
    </div>

</body>
</html>
