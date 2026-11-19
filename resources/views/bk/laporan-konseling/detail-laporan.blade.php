@extends('bk.layouts.app')
@section('title', 'Detail Laporan Konseling')
@section('subtitle', ($laporan->siswa?->nama_lengkap ?? '-') . ' · ' . $laporan->tanggal->translatedFormat('l, j F Y'))
@section('content')

<div class="btn-row" style="margin-bottom:18px">
    <a class="btn btn-light btn-sm" href="{{ route('bk.laporan-konseling.index') }}">← Kembali ke daftar laporan</a>
    @if(auth()->user()->role?->value === 'guru_bk')
        <a class="btn btn-primary btn-sm" href="{{ route('bk.laporan-konseling.edit', $laporan) }}">Ubah</a>
        <form class="inline-form" method="POST" action="{{ route('bk.laporan-konseling.destroy', $laporan) }}"
            onsubmit="return confirm('Hapus laporan ini?');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
        </form>
    @endif
</div>

<div class="grid2" style="margin-bottom:18px">
    <section class="panel" style="margin-bottom:0">
        <div class="panel-head"><h3>Data siswa</h3></div>
        <dl>
            <dt>Nama</dt><dd><strong>{{ $laporan->siswa?->nama_lengkap ?? '-' }}</strong></dd>
            <dt>NIS / NISN</dt><dd>{{ $laporan->siswa?->nis ?: '-' }} / {{ $laporan->siswa?->nisn ?: '-' }}</dd>
            <dt>Kelas saat ini</dt><dd>{{ $laporan->siswa?->riwayatAktif?->kelas?->nama_kelas ?? '-' }}</dd>
        </dl>
    </section>

    <section class="panel" style="margin-bottom:0">
        <div class="panel-head"><h3>Pelaksanaan layanan</h3></div>
        <dl>
            <dt>Komponen layanan</dt><dd>{{ $laporan->komponen_layanan->label() }}</dd>
            <dt>Bidang layanan</dt><dd><span class="badge badge-blue">{{ ucfirst($laporan->bidang?->nama ?? '-') }}</span></dd>
            <dt>Kelas / Semester</dt><dd>{{ $laporan->kelas?->nama_kelas ?? '-' }} · {{ $laporan->semester->label() }}</dd>
            <dt>Hari/tanggal</dt><dd>{{ $laporan->tanggal->translatedFormat('l, j F Y') }}</dd>
            <dt>Durasi pertemuan</dt><dd>{{ $laporan->durasi_menit }} menit</dd>
        </dl>
    </section>
</div>

<section class="panel">
    <div class="panel-head"><h3>Isi laporan</h3></div>
    <dl class="isi-laporan-daftar">
        <dt>Topik layanan</dt><dd>{{ $laporan->topik }}</dd>
        <dt>Tujuan layanan</dt><dd class="isi-laporan">{{ $laporan->tujuan }}</dd>
        <dt>Materi</dt><dd class="isi-laporan">{{ $laporan->materi }}</dd>
        <dt>Tindak lanjut</dt><dd class="isi-laporan">{{ $laporan->tindak_lanjut }}</dd>
    </dl>
    <p class="muted" style="margin-top:16px">
        Dicatat oleh {{ $laporan->pembuat?->name ?? '-' }} pada {{ $laporan->created_at?->translatedFormat('j F Y, H:i') }}
        @if($laporan->updated_at && $laporan->updated_at->ne($laporan->created_at))
            · diperbarui {{ $laporan->updated_at->translatedFormat('j F Y, H:i') }}
        @endif
    </p>
</section>
@endsection
