@extends('super-admin.laporan.layout')

@php
    $to  = fn (string $n) => \App\Support\RoleRoute::to($n);
    $ada = fn (string $n) => \App\Support\RoleRoute::has($n);
@endphp

@section('laporan')
<div class="lap-head">
    <div>
        <h2>Output &amp; Laporan</h2>
        <p class="lap-muted">Laporan, cetak dokumen, dan export data siswa.</p>
    </div>
</div>

<div class="lap-grid">
    @if($ada('laporan.siswa'))
    <a class="lap-tile" href="{{ $to('laporan.siswa') }}"><span class="ic">📋</span><b>Daftar Siswa Lengkap</b><small>Filter, pencarian, cetak / export Excel</small></a>
    @endif
    @if($ada('laporan.per-kelas'))
    <a class="lap-tile" href="{{ $to('laporan.per-kelas') }}"><span class="ic">👥</span><b>Laporan per Kelas</b><small>Jumlah siswa per kelas / rombel</small></a>
    @endif
    @if($ada('laporan.jenis-kelamin'))
    <a class="lap-tile" href="{{ $to('laporan.jenis-kelamin') }}"><span class="ic">📊</span><b>Laporan Laki-laki / Perempuan</b><small>Jumlah dan persentase</small></a>
    @endif
    @if($ada('laporan.siswa-baru'))
    <a class="lap-tile" href="{{ $to('laporan.siswa-baru') }}"><span class="ic">🎓</span><b>Laporan Siswa Baru</b><small>Berdasarkan tahun pelajaran</small></a>
    @endif
    @if($ada('laporan.orang-tua'))
    <a class="lap-tile" href="{{ $to('laporan.orang-tua') }}"><span class="ic">👨‍👩‍👧</span><b>Laporan Data Orang Tua</b><small>Pekerjaan, penghasilan, kontak</small></a>
    @endif
    @if($ada('laporan.dokumen'))
    <a class="lap-tile" href="{{ $to('laporan.dokumen') }}"><span class="ic">🖨️</span><b>Cetak Dokumen</b><small>Daftar hadir, label, surat keterangan</small></a>
    @endif
    @if($ada('laporan.export'))
    <a class="lap-tile" href="{{ $to('laporan.export') }}"><span class="ic">📤</span><b>Export Data</b><small>Excel untuk kebutuhan lain (Dapodik, dll.)</small></a>
    @endif
</div>
@endsection