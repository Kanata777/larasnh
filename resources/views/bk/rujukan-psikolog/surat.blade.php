<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Surat Rujukan - {{ $rujukan->siswa?->nama_lengkap ?? 'Siswa' }}</title>
<style>
    body { font-family: "Times New Roman", Times, serif; font-size: 14px; line-height: 1.6; color: #000; margin: 0; background: #f3f4f6; }
    .kertas { width: 210mm; max-width: 100%; margin: 20px auto; background: #fff; padding: 25mm 22mm; box-sizing: border-box; }
    h1 { font-size: 18px; text-align: center; margin: 0 0 4px; text-decoration: underline; letter-spacing: 1px; }
    .tengah { text-align: center; margin-bottom: 22px; }
    table.data { border-collapse: collapse; margin: 8px 0 14px 18px; }
    table.data td { padding: 2px 10px 2px 0; vertical-align: top; }
    .blok { margin: 6px 0 14px 18px; white-space: pre-line; }
    .ttd { margin-top: 40px; margin-left: 55%; }
    .ttd .ruang { height: 70px; }
    .alat { text-align: center; margin: 14px; }
    .alat button, .alat a { font: inherit; padding: 8px 16px; margin: 0 4px; border: 1px solid #9ca3af; border-radius: 4px; background: #fff; cursor: pointer; text-decoration: none; color: #111; }
    @media print {
        body { background: #fff; }
        .kertas { margin: 0; padding: 0; width: auto; }
        .alat { display: none; }
        @page { size: A4; margin: 22mm; }
    }
</style>
</head>
<body>

<div class="alat">
    <button type="button" onclick="window.print()">🖨 Cetak</button>
    <a href="{{ route('bk.rujukan-psikolog.show', $rujukan->id) }}">← Kembali</a>
</div>

<div class="kertas">
    <div class="tengah">
        <h1>SURAT RUJUKAN KONSELING</h1>
        <div>{{ config('app.name') }}</div>
    </div>

    <p>
        Kepada Yth.<br>
        <strong>{{ $rujukan->psikolog?->nama ?? '-' }}</strong><br>
        @if($rujukan->psikolog?->lembaga){{ $rujukan->psikolog->lembaga }}<br>@endif
        di tempat
    </p>

    <p>Dengan hormat,</p>
    <p>Bersama surat ini, kami dari layanan Bimbingan dan Konseling sekolah merujuk siswa berikut untuk mendapatkan penanganan lebih lanjut:</p>

    <table class="data">
        <tr><td>Nama</td><td>:</td><td>{{ $rujukan->siswa?->nama_lengkap ?? '-' }}</td></tr>
        <tr><td>NIS / NISN</td><td>:</td><td>{{ $rujukan->siswa?->nis ?: '-' }} / {{ $rujukan->siswa?->nisn ?: '-' }}</td></tr>
        <tr><td>Jenis kelamin</td><td>:</td><td>{{ ($rujukan->siswa?->jenis_kelamin ?? '') === 'L' ? 'Laki-laki' : (($rujukan->siswa?->jenis_kelamin ?? '') === 'P' ? 'Perempuan' : '-') }}</td></tr>
        <tr><td>Tempat, tanggal lahir</td><td>:</td><td>{{ $rujukan->siswa?->tempat_lahir ?: '-' }}, {{ $rujukan->siswa?->tanggal_lahir?->translatedFormat('j F Y') ?? '-' }}</td></tr>
        <tr><td>Kelas</td><td>:</td><td>{{ $rujukan->siswa?->riwayatAktif?->kelas?->nama_kelas ?? '-' }}</td></tr>
    </table>

    <p><strong>Alasan rujukan:</strong></p>
    <div class="blok">{{ $rujukan->alasan }}</div>

    <p><strong>Ringkasan kasus:</strong></p>
    <div class="blok">{{ $rujukan->ringkasan_kasus }}</div>

    @if($rujukan->catatan)
        <p><strong>Catatan:</strong></p>
        <div class="blok">{{ $rujukan->catatan }}</div>
    @endif

    <p>Demikian surat rujukan ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.</p>

    <div class="ttd">
        {{ $rujukan->tanggal_rujukan->translatedFormat('j F Y') }}<br>
        Guru BK,
        <div class="ruang"></div>
        <strong>{{ auth()->user()->name }}</strong>
    </div>
</div>

</body>
</html>
