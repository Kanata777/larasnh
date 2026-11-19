@extends('bk.layouts.app')
@section('title', $siswa->nama_lengkap)
@section('subtitle', 'Kelas ' . ($siswa->riwayatAktif?->kelas?->nama_kelas ?? '-') . ' · Status ' . $siswa->status_siswa)
@section('content')

@php
    $namaKelas = $siswa->riwayatAktif?->kelas?->nama_kelas ?? 'Belum ada kelas';
    $jk = $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    $inisial = mb_strtoupper(mb_substr($siswa->nama_lengkap, 0, 1));
@endphp

<style>
    .profil-head { display:flex; align-items:center; gap:16px; }
    .profil-avatar { width:64px; height:64px; border-radius:50%; background:#dbeafe; color:#1d4ed8;
        display:flex; align-items:center; justify-content:center; font-size:26px; font-weight:700; flex-shrink:0; }
    .profil-head h2 { margin:0 0 6px; font-size:20px; }
    .profil-head .badge { margin-right:4px; }
    .tabs { display:flex; gap:4px; flex-wrap:wrap; border-bottom:1px solid #e3e7ec; margin-bottom:18px; }
    .tab-btn { background:none; border:0; border-bottom:3px solid transparent; padding:10px 16px; margin-bottom:-1px;
        font:inherit; font-weight:600; color:#6b7280; cursor:pointer; }
    .tab-btn:hover { color:#111827; }
    .tab-btn.aktif { color:#1d4ed8; border-bottom-color:#1d4ed8; }
    .tab-wrap.js .tab-panel { display:none; }
    .tab-wrap.js .tab-panel.aktif { display:block; }
    .ringkas { display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:12px; }
    .ringkas > div { background:#f4f6f9; border-radius:6px; padding:12px 14px; }
    .ringkas small { display:block; color:#6b7280; margin-bottom:4px; }
    .ringkas strong { font-size:15px; }
</style>

<p style="margin-bottom:18px"><a class="btn btn-light btn-sm" href="{{ route('bk.siswa.index') }}">← Kembali ke Data Siswa</a></p>

{{-- Kepala profil: selalu tampil di atas semua tab --}}
<section class="panel">
    <div class="profil-head">
        <div class="profil-avatar" aria-hidden="true">{{ $inisial }}</div>
        <div>
            <h2>{{ $siswa->nama_lengkap }}</h2>
            <span class="badge badge-blue">{{ $namaKelas }}</span>
            <span class="badge badge-green">{{ $siswa->status_siswa }}</span>
            <span class="muted">NIS {{ $siswa->nis ?: '-' }} · NISN {{ $siswa->nisn ?: '-' }}</span>
        </div>
    </div>
</section>

<div class="tab-wrap" id="tab-wrap">
    <div class="tabs" role="tablist">
        <button type="button" class="tab-btn aktif" role="tab" data-tab="profil">Profil</button>
        <button type="button" class="tab-btn" role="tab" data-tab="biodata">Biodata</button>
        <button type="button" class="tab-btn" role="tab" data-tab="keluarga">Keluarga</button>
        <button type="button" class="tab-btn" role="tab" data-tab="konseling">Riwayat Konseling</button>
    </div>

    {{-- TAB 1: PROFIL (ringkasan singkat) --}}
    <section class="panel tab-panel aktif" id="tab-profil" role="tabpanel">
        <div class="panel-head"><h3>Profil singkat</h3></div>
        <div class="ringkas">
            <div><small>Kelas</small><strong>{{ $namaKelas }}</strong></div>
            <div><small>Status siswa</small><strong>{{ $siswa->status_siswa }}</strong></div>
            <div><small>Jenis kelamin</small><strong>{{ $jk }}</strong></div>
            <div><small>Tanggal lahir</small><strong>{{ $siswa->tanggal_lahir?->translatedFormat('j F Y') ?? '-' }}</strong></div>
            <div><small>Asal sekolah</small><strong>{{ $siswa->asal_sekolah ?: '-' }}</strong></div>
            <div><small>Tanggal masuk</small><strong>{{ $siswa->tanggal_masuk?->translatedFormat('j F Y') ?? '-' }}</strong></div>
        </div>
    </section>

    {{-- TAB 2: BIODATA --}}
    <section class="panel tab-panel" id="tab-biodata" role="tabpanel">
        <div class="panel-head"><h3>Biodata</h3></div>
        <dl>
            <dt>Nama lengkap</dt><dd>{{ $siswa->nama_lengkap }}</dd>
            <dt>NIS / NISN</dt><dd>{{ $siswa->nis ?: '-' }} / {{ $siswa->nisn ?: '-' }}</dd>
            <dt>Jenis kelamin</dt><dd>{{ $jk }}</dd>
            <dt>Tempat, tgl lahir</dt><dd>{{ $siswa->tempat_lahir ?: '-' }}, {{ $siswa->tanggal_lahir?->translatedFormat('j F Y') ?? '-' }}</dd>
            <dt>Agama</dt><dd>{{ $siswa->agama ?: '-' }}</dd>
            <dt>Asal sekolah</dt><dd>{{ $siswa->asal_sekolah ?: '-' }}</dd>
            <dt>Tanggal masuk</dt><dd>{{ $siswa->tanggal_masuk?->translatedFormat('j F Y') ?? '-' }}</dd>
            <dt>Anak ke</dt><dd>{{ $siswa->anak_ke ?? '-' }} dari {{ $siswa->jumlah_saudara ?? '-' }} bersaudara</dd>
            <dt>Kebutuhan khusus</dt><dd>{{ $siswa->kebutuhan_khusus ?: 'Tidak ada' }}</dd>
        </dl>
    </section>

    {{-- TAB 3: KELUARGA --}}
    <section class="panel tab-panel" id="tab-keluarga" role="tabpanel">
        <div class="panel-head"><h3>Keluarga</h3></div>
        <dl>
            <dt>Ayah</dt><dd>{{ $siswa->orangTua?->nama_ayah ?: '-' }}@if($siswa->orangTua?->pekerjaan_ayah) <span class="muted">({{ $siswa->orangTua->pekerjaan_ayah }})</span>@endif</dd>
            <dt>Ibu</dt><dd>{{ $siswa->orangTua?->nama_ibu ?: '-' }}@if($siswa->orangTua?->pekerjaan_ibu) <span class="muted">({{ $siswa->orangTua->pekerjaan_ibu }})</span>@endif</dd>
            @if($siswa->waliSiswa?->nama_wali)
                <dt>Wali</dt><dd>{{ $siswa->waliSiswa->nama_wali }}@if($siswa->waliSiswa->pekerjaan_wali) <span class="muted">({{ $siswa->waliSiswa->pekerjaan_wali }})</span>@endif</dd>
            @endif
        </dl>
    </section>

    {{-- TAB 4: RIWAYAT KONSELING --}}
    <section class="panel soon tab-panel" id="tab-konseling" role="tabpanel">
        <div class="panel-head">
            <div>
                <h3>Riwayat Konseling</h3>
                <p>Kasus, sesi konseling, prestasi, dan hasil tes siswa ini akan tampil di sini pada tahap berikutnya.</p>
            </div>
        </div>
    </section>
</div>

<script>
(function () {
    var wrap = document.getElementById('tab-wrap');
    var tombol = wrap.querySelectorAll('.tab-btn');
    var panel = wrap.querySelectorAll('.tab-panel');
    wrap.classList.add('js'); // tanpa JavaScript, semua bagian tetap tampil berurutan

    function buka(nama) {
        if (!document.getElementById('tab-' + nama)) nama = 'profil';
        tombol.forEach(function (b) { b.classList.toggle('aktif', b.dataset.tab === nama); });
        panel.forEach(function (p) { p.classList.toggle('aktif', p.id === 'tab-' + nama); });
    }

    tombol.forEach(function (b) {
        b.addEventListener('click', function () {
            buka(b.dataset.tab);
            history.replaceState(null, '', '#' + b.dataset.tab);
        });
    });

    buka(location.hash.replace('#', '') || 'profil');
})();
</script>
@endsection