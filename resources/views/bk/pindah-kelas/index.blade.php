@extends('bk.layouts.app')
@section('title', 'Riwayat Pindah Kelas')
@section('subtitle', 'Riwayat perpindahan siswa antar kelas.')
@section('content')

@include('bk.partials.pesan')

<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Riwayat pindah kelas</h3>
            <p>Semua perpindahan tercatat: siapa, dari kelas mana, ke kelas mana.</p>
        </div>
    </div>

    <form class="filter" method="get" action="{{ route('bk.pindah-kelas.index') }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau NIS siswa" aria-label="Cari siswa">
        <button class="btn btn-primary" type="submit">Cari</button>
        @if(request('q'))
            <a class="btn btn-light" href="{{ route('bk.pindah-kelas.index') }}">Reset</a>
        @endif
    </form>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Tanggal</th><th>Siswa</th><th>Dari</th><th>Ke</th><th>Jenis</th><th>Alasan</th><th>Oleh</th></tr></thead>
            <tbody>
            @forelse($riwayat as $r)
                <tr>
                    <td>{{ $r->tanggal->translatedFormat('j M Y') }}</td>
                    <td>{{ $r->siswa?->nama_lengkap ?? 'Siswa tidak ditemukan' }}</td>
                    <td><span class="badge badge-blue">{{ $r->kelasAsal?->nama_kelas ?? '-' }}</span></td>
                    <td><span class="badge badge-blue">{{ $r->kelasTujuan?->nama_kelas ?? '-' }}</span></td>
                    <td>{{ $r->mode === 'massal' ? 'Satu kelas' : 'Satuan' }}</td>
                    <td>{{ $r->alasan ?: '-' }}</td>
                    <td>{{ $r->pelaksana?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Belum ada riwayat perpindahan kelas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($riwayat->total() > 0)
        <div class="pager">
            <span>Menampilkan {{ $riwayat->firstItem() }}–{{ $riwayat->lastItem() }} dari {{ $riwayat->total() }} catatan</span>
            {{ $riwayat->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection