@extends('bk.layouts.app')
@section('title', 'Dashboard')
@section('subtitle', 'Gambaran siswa aktif. Data kasus dan konseling menyusul di tahap berikutnya.')
@section('content')

<style>
    /* ===== Grafik siswa per kelas: laki-laki (biru) kiri, perempuan (pink) kanan ===== */
    .grafik { display:flex; flex-direction:column; gap:10px; }

    /* Tiga kolom: nama kelas | garis | angka. Kolom garis memakai minmax(0,1fr)
       supaya tidak pernah mendorong angka keluar dari kotak panel. */
    .grafik-baris {
        display:grid;
        grid-template-columns:72px minmax(0, 1fr) 150px;
        align-items:center;
        column-gap:14px;
    }
    .grafik-nama { font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

    .grafik-garis {
        display:flex;
        height:14px;
        border-radius:999px;
        background:#eef1f6;
        overflow:hidden;
    }
    .grafik-garis i { display:block; flex:none; height:100%; }
    .grafik-garis .laki      { background:#3b82f6; }
    .grafik-garis .perempuan { background:#ec4899; }
    .grafik-garis .semua     { background:#94a3b8; }

    .grafik-angka {
        min-width:0;
        text-align:right;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        font-size:13px;
        color:#6b7280;
    }
    .grafik-angka b { font-size:15px; color:#111827; margin-right:6px; }

    .legenda { display:flex; flex-wrap:wrap; gap:16px; margin:0 0 14px; font-size:13px; color:#374151; }
    .legenda span::before {
        content:""; display:inline-block; width:12px; height:12px;
        border-radius:3px; margin-right:6px; vertical-align:-1px;
    }
    .legenda .l::before { background:#3b82f6; }
    .legenda .p::before { background:#ec4899; }

    /* Layar sempit: angka pindah ke baris bawah, tidak keluar kotak */
    @media (max-width: 640px) {
        .grafik-baris { grid-template-columns:56px minmax(0, 1fr); row-gap:4px; }
        .grafik-angka { grid-column:1 / -1; text-align:left; }
    }
</style>

<div class="stats">
    <div class="stat"><b>{{ $total }}</b><span>Siswa aktif</span></div>
    <div class="stat"><b>{{ $laki }}</b><span>Laki-laki</span></div>
    <div class="stat"><b>{{ $perempuan }}</b><span>Perempuan</span></div>
    <div class="stat"><b>{{ $perKelas->count() }}</b><span>Kelas berisi siswa</span></div>
</div>

<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Jumlah siswa per kelas</h3>
            <p>Klik nama kelas untuk melihat daftar siswanya.</p>
        </div>
    </div>

    <div class="legenda">
        <span class="l">Laki-laki</span>
        <span class="p">Perempuan</span>
    </div>

    @php $maks = max(1, $perKelas->max('jumlah') ?? 1); @endphp
    <div class="grafik">
        @forelse($perKelas as $k)
            @php
                $adaRincian = isset($k->laki) && isset($k->perempuan);
                $lk = (int) ($k->laki ?? 0);
                $pr = (int) ($k->perempuan ?? 0);
            @endphp
            <div class="grafik-baris">
                <a class="grafik-nama" href="{{ route('bk.siswa.index', ['kelas' => $k->id]) }}" title="{{ $k->nama_kelas }}">{{ $k->nama_kelas }}</a>

                <div class="grafik-garis" title="Laki-laki {{ $lk }}, Perempuan {{ $pr }}">
                    @if($adaRincian)
                        {{-- kiri: laki-laki (biru), kanan: perempuan (pink) --}}
                        <i class="laki" style="width: {{ round($lk / $maks * 100, 2) }}%"></i>
                        <i class="perempuan" style="width: {{ round($pr / $maks * 100, 2) }}%"></i>
                    @else
                        <i class="semua" style="width: {{ round($k->jumlah / $maks * 100, 2) }}%"></i>
                    @endif
                </div>

                <div class="grafik-angka">
                    <b>{{ $k->jumlah }}</b>@if($adaRincian){{ $lk }} L · {{ $pr }} P @endif
                </div>
            </div>
        @empty
            <p class="empty">Belum ada siswa di kelas aktif. Isi data kelas di website sekolah.</p>
        @endforelse
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h3>Siswa terbaru</h3>
        <a class="btn btn-light btn-sm" href="{{ route('bk.siswa.index') }}">Lihat semua</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>Kelas</th><th>Asal sekolah</th><th style="text-align:right">Aksi</th></tr></thead>
            <tbody>
            @forelse($terbaru as $s)
                <tr>
                    <td>{{ $s->nama_lengkap }}</td>
                    <td>{{ $s->riwayatAktif?->kelas?->nama_kelas ?? '-' }}</td>
                    <td>{{ $s->asal_sekolah ?: '-' }}</td>
                    <td class="actions"><a class="btn btn-light btn-sm" href="{{ route('bk.siswa.show', $s->id) }}">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Belum ada data siswa.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection