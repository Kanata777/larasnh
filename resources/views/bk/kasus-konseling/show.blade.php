@extends('bk.layouts.app')
@section('title', 'Detail Laporan Kasus')
@section('subtitle', ($kasus->siswa?->nama_lengkap ?? 'Siswa tidak ditemukan') . ' · ' . $kasus->tanggal->translatedFormat('j F Y'))
@section('content')

@include('bk.partials.pesan')

<div class="btn-row" style="margin-bottom:18px">
    <a class="btn btn-light btn-sm" href="{{ route('bk.kasus-konseling.index') }}">← Kembali ke daftar</a>
    @if($kasus->siswa)
        <a class="btn btn-light btn-sm" href="{{ route('bk.siswa.show', $kasus->siswa_id) }}">Detail siswa</a>
        <a class="btn btn-primary btn-sm" href="{{ route('bk.rujukan-psikolog.create', $kasus->siswa_id) }}">Rujuk ke psikolog</a>
    @endif
    <button type="button" class="btn btn-light btn-sm" onclick="window.print()">Cetak</button>
</div>

<div class="panel">
    <div class="panel-head"><h3>Laporan kasus</h3></div>
    <dl>
        <dt>Nama siswa</dt><dd>{{ $kasus->siswa?->nama_lengkap ?? 'Siswa tidak ditemukan' }}</dd>
        <dt>Komponen layanan</dt><dd>{{ $kasus->komponen_layanan }}</dd>
        <dt>Bidang layanan</dt><dd>{{ $kasus->bidang_layanan }}</dd>
        <dt>Kelas / semester</dt><dd>{{ $kasus->nama_kelas ?: '-' }} / {{ $kasus->semester }}</dd>
        <dt>Hari / tanggal</dt><dd>{{ $kasus->tanggal->translatedFormat('l, j F Y') }}</dd>
        <dt>Topik layanan</dt><dd>{{ $kasus->topik_layanan }}</dd>
        <dt>Tujuan layanan</dt><dd>{!! nl2br(e($kasus->tujuan_layanan)) !!}</dd>
        <dt>Durasi pertemuan</dt><dd>{{ $kasus->durasi_menit }} menit</dd>
        <dt>Materi</dt><dd>{!! nl2br(e($kasus->materi)) !!}</dd>
        <dt>Tindak lanjut</dt><dd>{!! nl2br(e($kasus->tindak_lanjut)) !!}</dd>
    </dl>
</div>
@endsection
