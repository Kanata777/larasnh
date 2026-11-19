@extends('super-admin.laporan.layout')

@section('laporan')
<div class="lap-head">
    <div>
        <h2>Export Data</h2>
        <p class="lap-muted">Unduh data siswa dalam format Excel untuk kebutuhan lain, misalnya Dapodik.</p>
    </div>
    <div class="lap-actions"><a class="lap-btn" href="{{ route('super-admin.laporan.index') }}">← Menu laporan</a></div>
</div>

<form method="GET" class="lap-card">
    <h3>Pilih data yang diexport</h3>
    <div class="lap-row">
        <select name="tahun_id">
            @foreach ($tahunOptions as $t)
                <option value="{{ $t->id }}" @selected($tahunId == $t->id)>{{ $t->nama }}</option>
            @endforeach
        </select>
        <select name="kelas_id">
            <option value="">Semua kelas</option>
            @foreach ($kelasOptions as $k)
                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
            @endforeach
        </select>
        <select name="status">
            <option value="">Semua status</option>
            @foreach ($statusOptions as $s)
                <option value="{{ $s }}" @selected($s === 'Aktif')>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="lap-row">
        <button class="lap-btn lap-btn-primary" formaction="{{ route('super-admin.laporan.export.unduh', 'lengkap') }}">Export data lengkap (semua kolom)</button>
        <button class="lap-btn" formaction="{{ route('super-admin.laporan.export.unduh', 'dapodik') }}">Export format Dapodik (kolom dasar)</button>
    </div>
    <p class="lap-muted" style="margin-top:12px">
        <b>Data lengkap</b> memuat identitas siswa, alamat, kontak, data ayah, ibu, dan wali, bantuan (KPS/KIP/PIP) dan rekening,
        data fisik, serta kelas pada tahun pelajaran terpilih. Urutan dan judul kolomnya mengikuti export Dapodik,
        jadi file ini bisa diimpor kembali lewat menu Import Excel.
    </p>
    <p class="lap-muted" style="margin-top:8px">
        Untuk PDF, buka salah satu laporan lalu klik <b>Cetak / PDF</b> dan pilih "Simpan sebagai PDF" di jendela cetak browser.
        Cocokkan urutan kolom format Dapodik dengan template resmi sebelum diunggah.
    </p>
</form>
@endsection