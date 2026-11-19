@extends('bk.layouts.app')
@section('title', 'Buat Laporan Kasus')
@section('subtitle', 'Langkah 1 dari 2: pilih siswa yang akan dibuatkan laporan.')
@section('content')

@include('bk.partials.pesan')

<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Pilih siswa</h3>
            <p>Cari berdasarkan nama, NIS, atau NISN, lalu klik <strong>Buat laporan</strong>.</p>
        </div>
        <a class="btn btn-light" href="{{ route('bk.kasus-konseling.index') }}">← Kembali</a>
    </div>

    <form class="filter" method="get" action="{{ route('bk.kasus-konseling.pilih-siswa') }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIS, atau NISN" aria-label="Cari siswa" autofocus>
        <select name="kelas" aria-label="Filter kelas">
            <option value="">Semua kelas</option>
            @foreach($daftarKelas as $k)
                <option value="{{ $k->id }}" @selected(request('kelas') == $k->id)>{{ $k->nama_kelas }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary" type="submit">Cari</button>
        @if(request()->hasAny(['q','kelas']))
            <a class="btn btn-light" href="{{ route('bk.kasus-konseling.pilih-siswa') }}">Reset</a>
        @endif
    </form>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>NIS</th><th>L/P</th><th>Kelas</th><th style="text-align:right">Aksi</th></tr></thead>
            <tbody>
            @forelse($daftarSiswa as $s)
                <tr>
                    <td>{{ $s->nama_lengkap }}</td>
                    <td>{{ $s->nis ?: '-' }}</td>
                    <td>{{ $s->jenis_kelamin }}</td>
                    <td><span class="badge badge-blue">{{ $s->riwayatAktif?->kelas?->nama_kelas ?? 'Belum ada kelas' }}</span></td>
                    <td class="actions">
                        <a class="btn btn-light btn-sm" href="{{ route('bk.siswa.show', $s->id) }}">Detail</a>
                        <a class="btn btn-primary btn-sm" href="{{ route('bk.kasus-konseling.create', $s->id) }}">➕ Buat laporan</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Tidak ada siswa yang cocok. Ubah kata kunci atau pilih kelas lain.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($daftarSiswa->total() > 0)
        <div class="pager">
            <span>Menampilkan {{ $daftarSiswa->firstItem() }}–{{ $daftarSiswa->lastItem() }} dari {{ $daftarSiswa->total() }} siswa</span>
            {{ $daftarSiswa->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
