<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $identitas['nama'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1d2939; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .sub { color: #667085; margin: 0 0 2px; }
        .tag { color: #667085; font-size: 10px; margin-bottom: 14px; }
        h2 { font-size: 12px; background: #dce6f1; padding: 5px 8px; margin: 14px 0 0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #d0d5dd; padding: 5px 8px; vertical-align: top; }
        td.l { width: 35%; color: #475467; }
        th { background: #f2f4f7; text-align: left; }
    </style>
</head>
<body>
    <h1>{{ $identitas['nama'] }}</h1>
    <p class="sub">{{ $identitas['baris2'] }}</p>
    <p class="tag">{{ $judulHalaman }} &nbsp;|&nbsp; Diekspor: {{ $diekspor }}</p>

    @foreach($bagian as [$judul, $baris])
        <h2>{{ $judul }}</h2>
        <table>
            @foreach($baris as [$label, $isi])
                <tr>
                    <td class="l">{{ $label }}</td>
                    <td>{{ $isi }}</td>
                </tr>
            @endforeach
        </table>
    @endforeach

    @if($riwayat !== null)
        <h2>Riwayat Kelas</h2>
        <table>
            <tr>
                <th>Tahun Pelajaran</th>
                <th>Kelas</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
            @forelse($riwayat as $rk)
                <tr>
                    <td>{{ $rk->tahun_pelajaran }}</td>
                    <td>{{ $rk->nama_kelas }}</td>
                    <td>{{ $rk->status }}</td>
                    <td>{{ $rk->catatan ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Siswa belum ditempatkan di kelas mana pun.</td></tr>
            @endforelse
        </table>
    @endif
</body>
</html>