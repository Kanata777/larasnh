<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Daftar Hadir {{ $kelas->nama_kelas }} - {{ $bulan }}</title>
<style>
    @page { size: A4 landscape; margin: 10mm; }
    body { font-family: Arial, sans-serif; font-size: 10px; color: #000; }
    h1 { font-size: 14px; margin: 0; text-align: center; text-transform: uppercase; }
    p.sub { text-align: center; margin: 2px 0 10px; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 2px 3px; height: 17px; }
    th { background: #eee; }
    td.nama { text-align: left; white-space: nowrap; }
    td.c, th.c { text-align: center; width: 17px; padding: 0; }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }
    .ttd { margin-top: 14px; width: 100%; font-size: 11px; }
    .ttd td { border: 0; height: auto; }
</style>
</head>
<body onload="window.print()">
    <h1>Daftar Hadir Siswa</h1>
    <p class="sub">{{ $sekolah }} · Kelas {{ $kelas->nama_kelas }} · {{ $bulan }}</p>
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width:24px">No</th>
                <th rowspan="2">Nama Siswa</th>
                <th rowspan="2" style="width:22px">L/P</th>
                <th colspan="{{ $jumlahHari }}">Tanggal</th>
                <th colspan="3">Jumlah</th>
            </tr>
            <tr>
                @for ($d = 1; $d <= $jumlahHari; $d++)<th class="c">{{ $d }}</th>@endfor
                <th class="c">S</th><th class="c">I</th><th class="c">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($siswa as $i => $s)
                <tr>
                    <td class="c" style="width:24px">{{ $i + 1 }}</td>
                    <td class="nama">{{ $s->nama_lengkap }}</td>
                    <td class="c" style="width:22px">{{ $s->jenis_kelamin }}</td>
                    @for ($d = 1; $d <= $jumlahHari + 3; $d++)<td class="c"></td>@endfor
                </tr>
            @endforeach
        </tbody>
    </table>
    <table class="ttd">
        <tr>
            <td style="width:60%"></td>
            <td>Wali Kelas,<br><br><br><br>(............................................)</td>
        </tr>
    </table>
</body>
</html>
