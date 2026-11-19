@extends('bk.layouts.app')
@section('title', 'Kasus & Konseling')
@section('subtitle', 'Laporan layanan bimbingan konseling per siswa.')
@section('content')

<div class="panel">
    <div class="panel-head">
        <h3>Daftar laporan</h3>
        @if(auth()->user()->role?->value === 'guru_bk')
            <a class="btn btn-primary" href="{{ route('bk.laporan-konseling.pilih-siswa') }}">➕ Buat laporan</a>
        @endif
    </div>

    <form class="filter" method="get" action="{{ route('bk.laporan-konseling.index') }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama siswa" aria-label="Cari nama siswa">
        <select name="bidang" aria-label="Filter bidang layanan">
            <option value="">Semua bidang</option>
            @foreach($daftarBidang as $b)
                <option value="{{ $b->id }}" @selected(request('bidang') == $b->id)>{{ ucfirst($b->nama) }}</option>
            @endforeach
        </select>
        <select name="komponen" aria-label="Filter komponen layanan">
            <option value="">Semua komponen</option>
            @foreach($daftarKomponen as $k)
                <option value="{{ $k->value }}" @selected(request('komponen') === $k->value)>{{ $k->label() }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary" type="submit">Cari</button>
        @if(request()->hasAny(['q','bidang','komponen']))
            <a class="btn btn-light" href="{{ route('bk.laporan-konseling.index') }}">Reset</a>
        @endif
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Tanggal</th><th>Siswa</th><th>Kelas / Semester</th><th>Komponen</th><th>Bidang</th><th>Topik</th><th>Durasi</th></tr>
            </thead>
            <tbody>
            @forelse($daftarLaporan as $l)
                <tr>
                    <td>{{ $l->tanggal->translatedFormat('j M Y') }}</td>
                    <td>{{ $l->siswa?->nama_lengkap ?? '-' }}</td>
                    <td>{{ $l->kelas?->nama_kelas ?? '-' }} · {{ $l->semester->label() }}</td>
                    <td>{{ $l->komponen_layanan->label() }}</td>
                    <td><span class="badge badge-blue">{{ ucfirst($l->bidang?->nama ?? '-') }}</span></td>
                    <td><a href="{{ route('bk.laporan-konseling.show', $l) }}">{{ \Illuminate\Support\Str::limit($l->topik, 50) }}</a></td>
                    <td>{{ $l->durasi_menit }} menit</td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Belum ada laporan konseling. @if(auth()->user()->role?->value === 'guru_bk')Klik "Buat laporan" untuk memulai.@endif</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($daftarLaporan->total() > 0)
        <div class="pager">
            <span>Menampilkan {{ $daftarLaporan->firstItem() }}–{{ $daftarLaporan->lastItem() }} dari {{ $daftarLaporan->total() }} laporan</span>
            {{ $daftarLaporan->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
