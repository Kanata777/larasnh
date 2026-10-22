@extends('super-admin.laporan.layout')

@section('laporan')
<div class="lap-head">
    <div>
        <h2>Output &amp; Laporan</h2>
        <p class="lap-muted">Laporan, cetak dokumen, dan export data siswa.</p>
    </div>
</div>

<div class="lap-grid">
    <a class="lap-tile" href="{{ route('super-admin.laporan.siswa') }}"><span class="ic">📋</span><b>Daftar Siswa Lengkap</b><small>Filter, pencarian, cetak / export Excel</small></a>
    <a class="lap-tile" href="{{ route('super-admin.laporan.per-kelas') }}"><span class="ic">👥</span><b>Laporan per Kelas</b><small>Jumlah siswa per kelas / rombel</small></a>
    <a class="lap-tile" href="{{ route('super-admin.laporan.jenis-kelamin') }}"><span class="ic">📊</span><b>Laporan Laki-laki / Perempuan</b><small>Jumlah dan persentase</small></a>
    <a class="lap-tile" href="{{ route('super-admin.laporan.siswa-baru') }}"><span class="ic">🎓</span><b>Laporan Siswa Baru</b><small>Berdasarkan tahun pelajaran</small></a>
    <a class="lap-tile" href="{{ route('super-admin.laporan.orang-tua') }}"><span class="ic">👨‍👩‍👧</span><b>Laporan Data Orang Tua</b><small>Pekerjaan, penghasilan, kontak</small></a>
    <a class="lap-tile" href="{{ route('super-admin.laporan.dokumen') }}"><span class="ic">🖨️</span><b>Cetak Dokumen</b><small>Daftar hadir, label, surat keterangan</small></a>
    <a class="lap-tile" href="{{ route('super-admin.laporan.export') }}"><span class="ic">📤</span><b>Export Data</b><small>Excel untuk kebutuhan lain (Dapodik, dll.)</small></a>
</div>
@endsection
