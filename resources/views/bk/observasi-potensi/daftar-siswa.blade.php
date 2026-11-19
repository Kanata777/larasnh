@extends('bk.layouts.app')
@section('title', 'Observasi & Potensi')
@section('subtitle', 'Potensi, bakat/minat, dan prestasi siswa. Klik tombol Detail untuk melihat profil lengkapnya.')
@section('content')

@php
    $isGuruBk  = auth()->user()->role?->value === 'guru_bk';
    // Mode tambah: hanya untuk guru BK, dipicu tombol "Tambah observasi" di atas
    $modeTambah = $isGuruBk && request('mode') === 'tambah';
@endphp

<div class="panel">
    <div class="panel-head">
        <div>
            <h3>{{ $modeTambah ? 'Tambah observasi: pilih siswa' : 'Daftar siswa' }}</h3>
            <p>
                @if($modeTambah)
                    Cari siswa berdasarkan nama, NIS, atau NISN, lalu klik <strong>Tambah observasi</strong> pada siswa yang dipilih.
                @else
                    Klik <strong>Detail</strong> untuk melihat profil, atau <strong>Tambah observasi</strong> untuk langsung mencatat.
                @endif
            </p>
        </div>
        @if($isGuruBk)
            @if($modeTambah)
                <a class="btn btn-light" href="{{ route('bk.observasi-potensi.index', request()->except(['mode', 'page'])) }}">Kembali ke daftar biasa</a>
            @else
                <a class="btn btn-primary" href="{{ route('bk.observasi-potensi.index', ['mode' => 'tambah']) }}">➕ Tambah observasi</a>
            @endif
        @endif
    </div>

    <form class="filter" method="get" action="{{ route('bk.observasi-potensi.index') }}">
        @if($modeTambah)
            <input type="hidden" name="mode" value="tambah">
        @endif
        <input type="search" id="cari-siswa" name="q" value="{{ request('q') }}"
               placeholder="Cari nama, NIS, atau NISN" aria-label="Cari siswa"
               @if($modeTambah) autofocus @endif>
        <select name="kelas" aria-label="Filter kelas">
            <option value="">Semua kelas</option>
            @foreach($daftarKelas as $k)
                <option value="{{ $k->id }}" @selected(request('kelas') == $k->id)>{{ $k->nama_kelas }}</option>
            @endforeach
        </select>
        <select name="kategori" aria-label="Filter potensi menonjol">
            <option value="">Semua bidang potensi</option>
            @foreach($daftarKategori as $kt)
                <option value="{{ $kt->value }}" @selected(request('kategori') === $kt->value)>Menonjol di {{ $kt->label() }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary" type="submit">Cari</button>
        @if(request()->hasAny(['q','kelas','kategori']))
            <a class="btn btn-light" href="{{ route('bk.observasi-potensi.index', $modeTambah ? ['mode' => 'tambah'] : []) }}">Reset</a>
        @endif
    </form>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>Kelas</th><th>Potensi menonjol</th><th>Observasi</th><th>Prestasi</th><th style="text-align:right">Aksi</th></tr></thead>
            <tbody>
            @forelse($daftarSiswa as $s)
                @php $r = $ringkasan[$s->id]; @endphp
                <tr>
                    <td>{{ $s->nama_lengkap }}</td>
                    <td><span class="badge badge-blue">{{ $s->riwayatAktif?->kelas?->nama_kelas ?? 'Belum ada kelas' }}</span></td>
                    <td>
                        @forelse($r['menonjol'] as $kt)
                            <span class="badge badge-green">{{ $kt->ikon() }} {{ $kt->label() }}</span>
                        @empty
                            <span class="muted">-</span>
                        @endforelse
                    </td>
                    <td>{{ $r['jumlah'] }} catatan</td>
                    <td>{{ $jumlahPrestasi[$s->id] ?? 0 }}</td>
                    <td class="actions">
                        {{-- Tombol Detail (profil) selalu ada untuk semua peran --}}
                        <a class="btn btn-light btn-sm" href="{{ route('bk.observasi-potensi.profil', $s->id) }}">Detail</a>
                        @if($isGuruBk)
                            <a class="btn btn-primary btn-sm" href="{{ route('bk.observasi-potensi.create', $s->id) }}">➕ Tambah observasi</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">Tidak ada siswa yang cocok. Ubah kata kunci atau filter.</td></tr>
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