@extends('super-admin.layout')

@php
    // Berlaku untuk semua role: link mengikuti prefix role, tombol mengikuti route yang dimiliki role
    $to = fn (string $nama, $param = []) => \App\Support\RoleRoute::to($nama, $param);
    $ada = fn (string $nama) => \App\Support\RoleRoute::has($nama);
    $ubahStatus = $ubahStatus ?? false;
@endphp

@section('title', 'Daftar Siswa')
@section('subtitle', $ada('siswa.edit') ? 'Cari, lihat, ubah, dan nonaktifkan data siswa' : 'Cari dan lihat data siswa')

@section('content')

<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Data Siswa</h3>
            <p>{{ number_format($siswa->total(), 0, ',', '.') }} siswa ditemukan.</p>
        </div>
        <div class="btn-row">
            @if($ada('siswa.import'))
                <a href="{{ $to('siswa.import') }}" class="btn btn-light">📥 Import Excel</a>
            @endif
            @if($ada('siswa.create'))
                <a href="{{ $to('siswa.create') }}" class="btn btn-primary">+ Tambah Siswa</a>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ $to('siswa.index') }}" id="form-filter"
          style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:10px;margin-bottom:18px;">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIPD, atau NISN" aria-label="Cari siswa">

        <select name="tahun_id" id="filter-tahun" aria-label="Filter tahun pelajaran">
            @foreach($tahunOptions as $t)
                <option value="{{ $t->id }}" {{ (int) $tahunTerpilih === (int) $t->id ? 'selected' : '' }}>
                    {{ $t->nama }}{{ $t->status === 'aktif' ? ' (aktif)' : '' }}
                </option>
            @endforeach
        </select>

        <select name="kelas_id" id="filter-kelas" aria-label="Filter kelas">
            <option value="">Semua kelas</option>
            @foreach($kelasOptions as $k)
                <option value="{{ $k->id }}" data-tahun="{{ $k->tahun_pelajaran_id }}"
                    {{ (string) $kelasTerpilih === (string) $k->id ? 'selected' : '' }}>
                    {{ $k->nama_kelas }}
                </option>
            @endforeach
        </select>

        <select name="status" aria-label="Filter status">
            <option value="">Semua status</option>
            @foreach($statusOptions as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>

        <div class="btn-row">
            <button type="submit" class="btn btn-primary">Cari</button>
            @if(request()->hasAny(['q', 'tahun_id', 'kelas_id', 'status']))
                <a href="{{ $to('siswa.index') }}" class="btn btn-light">Reset</a>
            @endif
        </div>
    </form>

    @php
        $badge = ['Aktif' => 'badge-green', 'Pindah' => 'badge-blue', 'Lulus' => 'badge-orange', 'Nonaktif' => 'badge-gray'];
    @endphp

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIPD / NISN</th>
                    <th>Nama Lengkap</th>
                    <th>L/P</th>
                    <th>Kelas</th>
                    <th>Asal Sekolah</th>
                    <th>Status</th>
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
                        <td>{{ $s->nama_kelas ?? '-' }}</td>
                        <td>{{ $s->asal_sekolah ?: '-' }}</td>
                        <td><span class="badge {{ $badge[$s->status_siswa] ?? 'badge-gray' }}">{{ $s->status_siswa }}</span></td>
                        <td class="actions">
                            <a class="btn btn-light btn-sm" href="{{ $to('siswa.show', $s->id) }}">Detail</a>

                            @if($ada('siswa.edit'))
                                <a class="btn btn-light btn-sm" href="{{ $to('siswa.edit', $s->id) }}">Edit</a>
                            @endif

                            @if($ubahStatus)
                                @if($s->status_siswa === 'Aktif')
                                    <button type="button" class="btn btn-danger btn-sm"
                                            data-nonaktif
                                            data-nama="{{ $s->nama_lengkap }}"
                                            data-action="{{ $to('siswa.status', $s->id) }}">
                                        Nonaktifkan
                                    </button>
                                @else
                                    <form class="inline-form" method="POST" action="{{ $to('siswa.status', $s->id) }}"
                                          onsubmit="return confirm('Aktifkan kembali {{ addslashes($s->nama_lengkap) }}?')">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status_siswa" value="Aktif">
                                        <button type="submit" class="btn btn-light btn-sm">Aktifkan</button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty">
                            Tidak ada siswa yang cocok.
                            @if($ada('siswa.create') || $ada('siswa.import'))
                                Gunakan "Tambah Siswa" atau "Import Excel" untuk menambah data.
                            @endif
                        </td>
                    </tr>
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

@if($ubahStatus)
    @include('super-admin.siswa._nonaktif_modal')
@endif

@endsection

@push('styles')
<style>
    @media (max-width: 900px) {
        #form-filter { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@push('scripts')
<script>
    /* Dropdown kelas hanya menampilkan kelas milik tahun pelajaran yang dipilih */
    const filterTahun = document.getElementById('filter-tahun');
    const filterKelas = document.getElementById('filter-kelas');

    function saringKelas(reset) {
        Array.from(filterKelas.options).forEach(opt => {
            if (!opt.value) return;
            const cocok = opt.dataset.tahun === filterTahun.value;
            opt.hidden = !cocok;
            opt.disabled = !cocok;
        });

        if (reset) filterKelas.value = '';
    }

    filterTahun.addEventListener('change', () => saringKelas(true));
    saringKelas(false);
</script>
@endpush