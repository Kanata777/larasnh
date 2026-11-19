@extends('bk.layouts.app')
@section('title', 'Ubah Laporan Konseling')
@section('subtitle', 'Perbarui laporan layanan untuk ' . $siswa->nama_lengkap)
@section('content')

<p style="margin-bottom:18px"><a class="btn btn-light btn-sm" href="{{ route('bk.laporan-konseling.show', $laporan) }}">← Kembali ke detail laporan</a></p>

@include('bk.laporan-konseling._formulir-laporan', [
    'aksi'   => route('bk.laporan-konseling.update', $laporan),
    'metode' => 'PUT',
])
@endsection
