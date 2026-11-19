@extends('bk.layouts.app')
@section('title', 'Data Siswa')
@section('subtitle', 'Data diambil langsung dari website sekolah dan hanya bisa dilihat. Klik tombol Detail untuk melihat profil dan biodata siswa.')
@section('content')

    @php $bolehPindah = auth()->user()->role?->value === 'guru_bk'; @endphp

    <div class="panel">
        <form class="filter" method="get" action="{{ route('bk.siswa.index') }}">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIS, atau NISN"
                aria-label="Cari siswa">
            <select name="kelas" aria-label="Filter kelas">
                <option value="">Semua kelas</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" @selected(request('kelas') == $k->id)>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary" type="submit">Cari</button>
            @if(request()->hasAny(['q', 'kelas']))
                <a class="btn btn-light" href="{{ route('bk.siswa.index') }}">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIS</th>
                        <th>L/P</th>
                        <th>Kelas</th>
                        <th>Asal sekolah</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswa as $s)
                        <tr>
                            <td>{{ $s->nama_lengkap }}</td>
                            <td>{{ $s->nis ?: '-' }}</td>
                            <td>{{ $s->jenis_kelamin }}</td>
                            <td><span
                                    class="badge badge-blue">{{ $s->riwayatAktif?->kelas?->nama_kelas ?? 'Belum ada kelas' }}</span>
                            </td>
                            <td>{{ $s->asal_sekolah ?: '-' }}</td>
                            <td class="actions">
                                <a class="btn btn-light btn-sm" href="{{ route('bk.siswa.show', $s->id) }}">Detail</a>
                                @if($bolehPindah && $s->riwayatAktif)
                                    <a class="btn btn-light btn-sm"
                                        href="{{ route('bk.pindah-kelas.create', ['siswa' => $s->id]) }}">🔀 Pindah kelas</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">Tidak ada siswa yang cocok. Ubah kata kunci atau pilih kelas lain.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($siswa->total() > 0)
            <div class="pager">
                <span>Menampilkan {{ $siswa->firstItem() }}–{{ $siswa->lastItem() }} dari {{ $siswa->total() }} siswa</span>
                {{ $siswa->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection