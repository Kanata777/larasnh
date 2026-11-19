@extends('bk.layouts.app')
@section('title', 'Naik Kelas ' . $tingkat . ' → ' . ($tingkat + 1))
@section('subtitle', $perluGender
    ? 'Pilih laki-laki atau perempuan, lalu centang siswa yang naik ke kelas ' . ($tingkat + 1) . ' (huruf kelas tetap).'
    : 'Centang siswa yang naik ke kelas ' . ($tingkat + 1) . ' (huruf kelas tetap): semua sekaligus atau satu per satu.')

@push('styles')
<style>
    .nama-L{color:#1d4ed8;font-weight:600}
    .nama-P{color:#db2777;font-weight:600}
    tr.baris-L{background:#eff6ff}
    tr.baris-P{background:#fdf2f8}
    tr.nonaktif{opacity:.6}
    .badge-L{background:#dbeafe;color:#1d4ed8}
    .badge-P{background:#fce7f3;color:#be185d}
    .tab-jk{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
    .tab-jk .btn{padding:9px 18px}
    .btn-L.aktif{background:#1d4ed8;color:#fff}
    .btn-P.aktif{background:#db2777;color:#fff}
    .ringkas{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px}
    .ringkas .badge{font-size:12px;padding:5px 12px}
</style>
@endpush

@section('content')

@include('bk.naik-kelas._menu')

@php
    $qs = ['tingkat' => $tingkat, 'tahun_asal' => $tahunAsal, 'tahun_tujuan' => $tahunTujuan];
    $dipilihKelas = $kelasId ? $daftarKelas->firstWhere('id', $kelasId) : null;
@endphp

<div class="panel">
    <div class="panel-head"><div><h3>1. Tahun pelajaran dan kelas</h3></div></div>

    @if($perluGender)
        <div class="tab-jk">
            <a class="btn btn-light btn-L {{ $jk === 'L' ? 'aktif' : '' }}"
                href="{{ route('bk.naik-kelas.otomatis', $qs + ['jk' => 'L', 'kelas' => $kelasId ?: null]) }}">♂ Laki-laki ({{ $jumlahL }})</a>
            <a class="btn btn-light btn-P {{ $jk === 'P' ? 'aktif' : '' }}"
                href="{{ route('bk.naik-kelas.otomatis', $qs + ['jk' => 'P', 'kelas' => $kelasId ?: null]) }}">♀ Perempuan ({{ $jumlahP }})</a>
        </div>
    @endif

    <form class="filter" method="get" action="{{ route('bk.naik-kelas.otomatis') }}">
        <input type="hidden" name="tingkat" value="{{ $tingkat }}">
        @if($perluGender)<input type="hidden" name="jk" value="{{ $jk }}">@endif
        <select name="tahun_asal" aria-label="Tahun asal" onchange="this.form.kelas.value='';this.form.submit()">
            @foreach($daftarTahun as $t)
                <option value="{{ $t->id }}" @selected($t->id === $tahunAsal)>Asal: {{ $t->label }}</option>
            @endforeach
        </select>
        <select name="tahun_tujuan" aria-label="Tahun tujuan" onchange="this.form.submit()">
            @foreach($daftarTahun as $t)
                <option value="{{ $t->id }}" @selected($t->id === $tahunTujuan)>Tujuan: {{ $t->label }}</option>
            @endforeach
        </select>
        <select name="kelas" aria-label="Kelas asal" onchange="this.form.submit()">
            <option value="">Semua kelas {{ $tingkat }}</option>
            @foreach($daftarKelas as $k)
                <option value="{{ $k->id }}" @selected($kelasId === $k->id)>{{ $k->nama }}</option>
            @endforeach
        </select>
    </form>
</div>

<form method="POST" action="{{ route('bk.naik-kelas.otomatis.simpan') }}" id="form-naik">
    @csrf
    <input type="hidden" name="tingkat" value="{{ $tingkat }}">
    <input type="hidden" name="tahun_asal" value="{{ $tahunAsal }}">
    <input type="hidden" name="tahun_tujuan" value="{{ $tahunTujuan }}">
    @if($perluGender)<input type="hidden" name="jk" value="{{ $jk }}">@endif

    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>2. Pilih siswa
                    @if($perluGender)
                        ({{ $jk === 'L' ? 'laki-laki' : 'perempuan' }})
                    @endif
                </h3>
                <p>{{ $baris->count() }} siswa aktif{{ $dipilihKelas ? ' di ' . $dipilihKelas->nama : '' }}. Yang tidak dicentang tetap di kelasnya.</p>
            </div>
        </div>

        @if(! $perluGender)
            <div class="ringkas">
                <span class="badge badge-L">♂ Laki-laki: {{ $baris->where('jenis_kelamin', 'L')->count() }}</span>
                <span class="badge badge-P">♀ Perempuan: {{ $baris->where('jenis_kelamin', 'P')->count() }}</span>
            </div>
        @endif

        @if($baris->contains(fn ($b) => ! $b->tujuan_id))
            <div class="alert alert-error" style="margin-bottom:14px">
                Ada kelas {{ $tingkat + 1 }} yang belum tersedia di tahun pelajaran tujuan (tidak bisa dicentang). Buat kelasnya dulu di Master Data.
            </div>
        @endif

        <div class="ringkas">
            <button type="button" class="btn btn-light btn-sm" id="btn-semua">Centang semua</button>
            <button type="button" class="btn btn-light btn-sm" id="btn-kosong">Hapus centang</button>
            <span class="muted">Dipilih: <strong id="jml-dipilih">0</strong> siswa</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px"><input type="checkbox" id="pilih-semua" aria-label="Pilih semua"></th>
                        <th>Nama</th><th>NIS</th><th>L/P</th><th>Kelas asal</th><th>Kelas tujuan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($baris as $b)
                    <tr class="baris-{{ $b->jenis_kelamin }} {{ $b->bisa ? '' : 'nonaktif' }}">
                        <td>
                            <input type="checkbox" class="cek-siswa" name="siswa_ids[]" value="{{ $b->siswa_id }}"
                                aria-label="Pilih {{ $b->nama_lengkap }}"
                                @checked($b->bisa && is_array(old('siswa_ids')) && in_array($b->siswa_id, old('siswa_ids')))
                                @disabled(! $b->bisa)>
                        </td>
                        <td class="nama-{{ $b->jenis_kelamin }}">{{ $b->nama_lengkap }}</td>
                        <td>{{ $b->nis ?: '-' }}</td>
                        <td>
                            <span class="badge badge-{{ $b->jenis_kelamin }}">
                                {{ $b->jenis_kelamin === 'L' ? '♂ Laki-laki' : '♀ Perempuan' }}
                            </span>
                        </td>
                        <td>{{ $b->asal_nama }}</td>
                        <td>
                            @if($b->tujuan_id)
                                → {{ $b->tujuan_nama }}
                            @else
                                <span class="badge badge-red">Kelas tujuan belum ada</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">Tidak ada siswa aktif untuk filter ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($baris->isNotEmpty())
    <div class="panel">
        <div class="panel-head"><div><h3>3. Proses</h3></div></div>
        <div class="form-grid">
            <div class="field">
                <label for="tanggal">Tanggal naik kelas <span class="req">*</span></label>
                <input type="date" id="tanggal" name="tanggal" max="{{ now()->toDateString() }}" value="{{ old('tanggal', now()->toDateString()) }}" required>
            </div>
        </div>
        <button type="button" class="btn btn-primary" id="btn-naik">Naikkan yang dipilih ke kelas {{ $tingkat + 1 }}</button>
    </div>
    @endif
</form>

<script>
(function () {
    var form = document.getElementById('form-naik');
    var semua = document.getElementById('pilih-semua');
    var jml = document.getElementById('jml-dipilih');
    // Hanya kotak yang aktif (siswa yang memang bisa naik) yang ikut dicentang massal
    var cek = function () { return Array.prototype.slice.call(document.querySelectorAll('.cek-siswa:not(:disabled)')); };
    var hitung = function () {
        var n = cek().filter(function (c) { return c.checked; }).length;
        jml.textContent = n;
        if (semua) semua.checked = n > 0 && n === cek().length;
    };
    var set = function (v) { cek().forEach(function (c) { c.checked = v; }); hitung(); };

    if (semua) semua.addEventListener('change', function () { set(semua.checked); });
    document.getElementById('btn-semua').addEventListener('click', function () { set(true); });
    document.getElementById('btn-kosong').addEventListener('click', function () { set(false); });
    cek().forEach(function (c) { c.addEventListener('change', hitung); });
    hitung();

    var btn = document.getElementById('btn-naik');
    if (btn) btn.addEventListener('click', function () {
        var n = cek().filter(function (c) { return c.checked; }).length;
        if (!n) { alert('Centang minimal satu siswa.'); return; }
        if (!confirm('Naikkan ' + n + ' siswa ke kelas {{ $tingkat + 1 }}? Siswa yang tidak dicentang tetap di kelasnya.')) return;
        form.submit();
    });
})();
</script>
@endsection