@extends('bk.layouts.app')
@section('title', 'Kasus & Konseling')
@section('subtitle', 'Laporan kasus dan layanan konseling per siswa.')
@section('content')

@include('bk.partials.pesan')

<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Laporan kasus</h3>
            <p>Klik <strong>Detail</strong> untuk membaca laporan lengkap.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('bk.kasus-konseling.pilih-siswa') }}">➕ Buat laporan kasus</a>
    </div>

    <form class="filter" method="get" action="{{ route('bk.kasus-konseling.index') }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau NIS siswa" aria-label="Cari siswa">
        <select name="bidang" aria-label="Filter bidang layanan">
            <option value="">Semua bidang</option>
            @foreach(\App\Models\KasusKonseling::BIDANG as $b)
                <option value="{{ $b }}" @selected(request('bidang') === $b)>{{ $b }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary" type="submit">Cari</button>
        @if(request()->hasAny(['q','bidang']))
            <a class="btn btn-light" href="{{ route('bk.kasus-konseling.index') }}">Reset</a>
        @endif
    </form>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas / Semester</th><th>Komponen</th><th>Bidang</th><th>Topik</th><th style="text-align:right">Aksi</th></tr></thead>
            <tbody>
            @forelse($daftarKasus as $k)
                <tr>
                    <td>{{ $k->tanggal->translatedFormat('j M Y') }}</td>
                    <td>{{ $k->siswa?->nama_lengkap ?? 'Siswa tidak ditemukan' }}</td>
                    <td>{{ $k->nama_kelas ?: '-' }} / {{ $k->semester }}</td>
                    <td>{{ $k->komponen_layanan }}</td>
                    <td><span class="badge badge-blue">{{ $k->bidang_layanan }}</span></td>
                    <td>{{ \Illuminate\Support\Str::limit($k->topik_layanan, 60) }}</td>
                    <td class="actions">
                        <a class="btn btn-light btn-sm" href="{{ route('bk.kasus-konseling.show', $k->id) }}">Detail</a>
                        <form class="inline-form" method="POST" action="{{ route('bk.kasus-konseling.destroy', $k->id) }}"
                              onsubmit="return confirm('Hapus laporan kasus ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Belum ada laporan kasus. Klik "Buat laporan kasus" untuk memulai.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($daftarKasus->total() > 0)
        <div class="pager">
            <span>Menampilkan {{ $daftarKasus->firstItem() }}–{{ $daftarKasus->lastItem() }} dari {{ $daftarKasus->total() }} laporan</span>
            {{ $daftarKasus->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
