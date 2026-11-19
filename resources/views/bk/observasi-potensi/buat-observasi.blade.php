@extends('bk.layouts.app')
@section('title', 'Tambah Observasi')
@section('subtitle', 'Catat potensi dan bakat yang terlihat pada ' . $siswa->nama_lengkap)
@section('content')

<p style="margin-bottom:18px"><a class="btn btn-light btn-sm" href="{{ route('bk.observasi-potensi.profil', $siswa->id) }}">← Kembali ke profil</a></p>

@include('bk.observasi-potensi._formulir-observasi', [
    'aksi'   => route('bk.observasi-potensi.store', $siswa->id),
    'metode' => 'POST',
])
@endsection
