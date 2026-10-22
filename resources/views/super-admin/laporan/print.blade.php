<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>{{ $judul }}</title>
<style>
    @page { size: A4 landscape; margin: 12mm; }
    body { font-family: Arial, sans-serif; font-size: 11px; color: #000; }
    h1 { font-size: 16px; margin: 0; text-align: center; text-transform: uppercase; }
    p.sub { text-align: center; margin: 2px 0 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 3px 5px; text-align: left; }
    th { background: #eee; }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }
    .total td { font-weight: bold; background: #f3f3f3; }
    .foot { margin-top: 10px; font-size: 10px; }
</style>
</head>
<body onload="window.print()">
    <h1>{{ config('app.name') }}</h1>
    <p class="sub"><b>{{ $judul }}</b>{{ $tahunNama ? ' · Tahun Pelajaran ' . $tahunNama : '' }}</p>
    <table>
        <thead><tr>@foreach ($head as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr class="{{ ! empty($total) && $loop->last ? 'total' : '' }}">
                    @foreach ($row as $c)<td>{{ $c }}</td>@endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="foot">Dicetak {{ now()->format('d-m-Y H:i') }}</p>
</body>
</html>
