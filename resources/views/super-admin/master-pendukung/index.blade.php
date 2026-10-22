@extends('super-admin.layout')

@section('title', 'Master Data')
@section('subtitle', 'Kelola data acuan yang dipakai pada data siswa')

@section('content')

    <link rel="stylesheet" href="{{ asset('css/master-pendukung.css') }}">

    <div class="mp">

        @if($errors->any())
            <div class="alert alert-error">
                Data belum bisa disimpan. Periksa isian berikut:
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('super-admin.master-pendukung._tabs')

        {{-- Panel per modul --}}
        @include('super-admin.master-pendukung.tahun-pelajaran._panel')
        @include('super-admin.master-pendukung.kelas._panel')
        @include('super-admin.master-pendukung.status-siswa._panel')
        @include('super-admin.master-pendukung.orang-tua.orang-tua')

        {{-- Modal per modul --}}
        @include('super-admin.master-pendukung.tahun-pelajaran._modal')
        @include('super-admin.master-pendukung.kelas._modal')
        @include('super-admin.master-pendukung.kelas._modal-wali')
        @include('super-admin.master-pendukung.kelas._modal-import')

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/master-pendukung.js') }}"></script>
@endpush