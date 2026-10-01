<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
</head>
<body>
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
                        @if (is_string($value) && preg_match('/^[=+@\-\t\r]/u', $value))
                            @php($value = "'".$value)
                        @endif
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}">Belum ada data untuk laporan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
