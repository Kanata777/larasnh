@extends('bk.layouts.app')
@section('title', 'Ubah Observasi')
@section('subtitle', 'Perbarui catatan observasi ' . $siswa->nama_lengkap)
@section('content')

<p style="margin-bottom:18px"><a class="btn btn-light btn-sm" href="{{ route('bk.observasi-potensi.profil', $siswa->id) }}">← Kembali ke profil</a></p>

@include('bk.observasi-potensi._formulir-observasi', [
    'aksi'   => route('bk.observasi-potensi.update', $observasi->id),
    'metode' => 'PUT',
])
@endsection
