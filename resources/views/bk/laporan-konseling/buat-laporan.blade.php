@extends('bk.layouts.app')
@section('title', 'Buat Laporan Konseling')
@section('subtitle', 'Langkah 2 dari 2: isi laporan layanan untuk ' . $siswa->nama_lengkap)
@section('content')

<p style="margin-bottom:18px"><a class="btn btn-light btn-sm" href="{{ route('bk.laporan-konseling.pilih-siswa') }}">← Pilih siswa lain</a></p>

@include('bk.laporan-konseling._formulir-laporan', [
    'aksi'   => route('bk.laporan-konseling.store', $siswa->id),
    'metode' => 'POST',
])
@endsection
