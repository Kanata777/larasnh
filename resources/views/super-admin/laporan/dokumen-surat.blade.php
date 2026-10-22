<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Surat Keterangan - {{ $d->nama_lengkap }}</title>
<style>
    @page { size: A4 portrait; margin: 20mm; }
    body { font-family: "Times New Roman", serif; font-size: 13px; line-height: 1.6; color: #000; }
    .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 20px; }
    .kop b { font-size: 18px; text-transform: uppercase; }
    h2 { text-align: center; text-decoration: underline; margin: 0; font-size: 15px; }
    .no { text-align: center; margin-bottom: 18px; }
    table.data td { padding: 1px 6px 1px 0; vertical-align: top; }
    .ttd { margin-top: 36px; margin-left: 55%; }
</style>
</head>
<body onload="window.print()">
    <div class="kop"><b>{{ $sekolah }}</b></div>

    <h2>SURAT KETERANGAN SISWA</h2>
    <div class="no">Nomor: {{ $nomor }}</div>

    <p>Yang bertanda tangan di bawah ini, Kepala {{ $sekolah }}, menerangkan bahwa:</p>

    <table class="data" style="margin-left:24px">
        <tr><td>Nama</td><td>:</td><td><b>{{ $d->nama_lengkap }}</b></td></tr>
        <tr><td>NIS / NISN</td><td>:</td><td>{{ $d->nis }} / {{ $d->nisn }}</td></tr>
        <tr><td>Tempat, tanggal lahir</td><td>:</td><td>{{ $d->tempat_lahir }}{{ $d->tanggal_lahir ? ', ' . $d->tanggal_lahir->format('d-m-Y') : '' }}</td></tr>
        <tr><td>Jenis kelamin</td><td>:</td><td>{{ $d->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
        <tr><td>Nama orang tua</td><td>:</td><td>{{ data_get($d, 'orangTua.nama_ayah') ?: data_get($d, 'orangTua.nama_ibu') }}</td></tr>
        <tr><td>Kelas</td><td>:</td><td>{{ $kelas ?? '-' }}</td></tr>
        <tr><td>Tahun pelajaran</td><td>:</td><td>{{ $tahunNama ?? '-' }}</td></tr>
    </table>

    <p>adalah benar terdaftar sebagai siswa di {{ $sekolah }} dengan status <b>{{ $d->status_siswa }}</b> pada tahun pelajaran tersebut.</p>
    <p>Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>

    <div class="ttd">
        {{ $kota }}, {{ $tanggal }}<br>
        Kepala Sekolah,<br><br><br><br>
        <b><u>{{ $kepala }}</u></b><br>
        NIP. {{ $nip }}
    </div>
</body>
</html>
