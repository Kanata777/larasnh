@extends('super-admin.layout')

@php
    $to = fn (string $nama, $param = []) => \App\Support\RoleRoute::to($nama, $param);
@endphp

@section('title', 'Siswa Terhapus')
@section('subtitle', 'Pulihkan siswa yang terhapus tidak sengaja')

@section('content')
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Siswa Terhapus</h3>
            <p>{{ number_format($siswa->total(), 0, ',', '.') }} siswa terhapus.</p>
        </div>
        <div class="btn-row">
            <a href="{{ $to('siswa.index') }}" class="btn btn-light">&laquo; Kembali ke Daftar Siswa</a>
        </div>
    </div>

    <form method="GET" action="{{ $to('siswa.terhapus') }}" style="display:flex;gap:10px;margin-bottom:18px;">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIS, atau NISN"
               style="flex:1;" aria-label="Cari siswa terhapus">
        <button type="submit" class="btn btn-primary">Cari</button>
        @if(request('q'))
            <a href="{{ $to('siswa.terhapus') }}" class="btn btn-light">Reset</a>
        @endif
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS / NISN</th>
                    <th>Nama Lengkap</th>
                    <th>L/P</th>
                    <th>Dihapus Pada</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswa as $i => $s)
                    <tr>
                        <td>{{ $siswa->firstItem() + $i }}</td>
                        <td>{{ $s->nis }}<br><span class="muted">{{ $s->nisn ?: '-' }}</span></td>
                        <td><strong>{{ $s->nama_lengkap }}</strong></td>
                        <td>{{ $s->jenis_kelamin }}</td>
                        <td>{{ \Carbon\Carbon::parse($s->deleted_at)->format('d/m/Y H:i') }}</td>
                        <td class="actions">
                            <form class="inline-form" method="POST" action="{{ $to('siswa.restore', $s->id) }}"
                                  onsubmit="return confirm('Pulihkan {{ addslashes($s->nama_lengkap) }}?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-primary btn-sm">Pulihkan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">Tidak ada siswa terhapus.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($siswa->hasPages())
        <div class="pager">
            <span>Halaman {{ $siswa->currentPage() }} dari {{ $siswa->lastPage() }}</span>
            <span class="btn-row">
                @if($siswa->previousPageUrl())
                    <a class="btn btn-light btn-sm" href="{{ $siswa->previousPageUrl() }}">&laquo; Sebelumnya</a>
                @endif
                @if($siswa->nextPageUrl())
                    <a class="btn btn-light btn-sm" href="{{ $siswa->nextPageUrl() }}">Berikutnya &raquo;</a>
                @endif
            </span>
        </div>
    @endif
</div>
@endsection