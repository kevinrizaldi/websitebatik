<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body { color: #111827; font: 12px Arial, sans-serif; margin: 32px; }
        h1 { font-size: 22px; margin-bottom: 6px; text-align: center; }
        .meta { color: #4b5563; margin-bottom: 24px; text-align: center; }
        table { border-collapse: collapse; margin-bottom: 28px; width: 100%; }
        th, td { border-bottom: 1px solid #d1d5db; padding: 9px 7px; text-align: left; }
        th { background: #f3f4f6; border-top: 1px solid #d1d5db; }
        .number { text-align: right; }
        .total { font-weight: bold; text-align: right; }
        .signature { margin: 48px 0 0 auto; text-align: center; width: 220px; }
        .signature-name { border-bottom: 1px solid #9ca3af; font-weight: bold; margin-top: 48px; padding-bottom: 5px; }
        .no-print { margin-top: 24px; text-align: center; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            @page { margin: 2cm; }
        }
    </style>
</head>
<body onload="window.print()">
    <h1>{{ $title }}</h1>
    <p class="meta">
        @if ($startDate || $endDate)
            Periode: {{ $startDate ?? 'Awal' }} sampai {{ $endDate ?? 'Hari ini' }}
        @else
            Semua periode
        @endif
        @if ($status)
            <br>Status pesanan: {{ $status }}
        @endif
        <br>Dibuat pada {{ now()->format('d/m/Y H:i') }}
    </p>

    <table>
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th>{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($columns as $column)
                        @php($value = $row[$column['key']] ?? '')
                        <td @class(['number' => ($column['type'] ?? '') === 'currency'])>
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
                    <td colspan="{{ count($columns) }}">Belum ada data untuk laporan ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($totalRevenue !== null && $recordCount > 0)
            <tfoot>
                <tr>
                    <td colspan="{{ count($columns) - 1 }}" class="total">Total Pendapatan</td>
                    <td class="number"><strong>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="signature">
        <p>Mengetahui,</p>
        <p class="signature-name">{{ auth()->user()->name ?? 'Administrator' }}</p>
        <small>Administrator</small>
    </div>

    <div class="no-print">
        <p>Pilih “Simpan sebagai PDF” pada dialog cetak untuk menyimpan laporan sebagai PDF.</p>
        <button onclick="window.print()">Cetak / Simpan PDF</button>
        <button onclick="window.close()">Tutup</button>
    </div>
</body>
</html>
