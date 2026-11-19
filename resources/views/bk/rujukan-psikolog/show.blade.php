@extends('bk.layouts.app')
@section('title', 'Detail Rujukan')
@section('subtitle', ($rujukan->siswa?->nama_lengkap ?? 'Siswa tidak ditemukan') . ' · ' . $rujukan->status)
@section('content')

@include('bk.partials.pesan')

<div class="btn-row" style="margin-bottom:18px">
    <a class="btn btn-light btn-sm" href="{{ route('bk.rujukan-psikolog.index') }}#riwayat">← Kembali ke daftar</a>
    @if($rujukan->siswa)
        <a class="btn btn-light btn-sm" href="{{ route('bk.siswa.show', $rujukan->siswa_id) }}">Detail siswa</a>
    @endif
    <a class="btn btn-primary btn-sm" href="{{ route('bk.rujukan-psikolog.surat', $rujukan->id) }}" target="_blank" rel="noopener">🖨 Cetak surat rujukan</a>
</div>

<div class="grid2" style="margin-bottom:18px">
    <section class="panel" style="margin-bottom:0">
        <div class="panel-head"><h3>Data rujukan</h3></div>
        <dl>
            <dt>Siswa</dt><dd>{{ $rujukan->siswa?->nama_lengkap ?? 'Siswa tidak ditemukan' }}@if($rujukan->siswa) <span class="muted">(Kelas {{ $rujukan->siswa->riwayatAktif?->kelas?->nama_kelas ?? '-' }})</span>@endif</dd>
            <dt>Tanggal rujukan</dt><dd>{{ $rujukan->tanggal_rujukan->translatedFormat('j F Y') }}</dd>
            <dt>Psikolog / lembaga</dt><dd>{{ $rujukan->psikolog?->nama_lengkap ?? '-' }}</dd>
            <dt>Kontak</dt><dd>{{ $rujukan->psikolog?->telepon ?: '-' }}@if($rujukan->psikolog?->email) · {{ $rujukan->psikolog->email }}@endif</dd>
            <dt>Status</dt><dd><span class="badge {{ $rujukan->badge }}">{{ $rujukan->status }}</span></dd>
            @if($rujukan->tanggal_selesai)
                <dt>Tanggal selesai</dt><dd>{{ $rujukan->tanggal_selesai->translatedFormat('j F Y') }}</dd>
            @endif
        </dl>
    </section>

    <section class="panel" style="margin-bottom:0">
        <div class="panel-head"><h3>Alasan dan kasus</h3></div>
        <dl>
            <dt>Alasan rujukan</dt><dd>{!! nl2br(e($rujukan->alasan)) !!}</dd>
            <dt>Ringkasan kasus</dt><dd>{!! nl2br(e($rujukan->ringkasan_kasus)) !!}</dd>
            @if($rujukan->catatan)
                <dt>Catatan guru BK</dt><dd>{!! nl2br(e($rujukan->catatan)) !!}</dd>
            @endif
            @if($rujukan->kasus)
                <dt>Laporan kasus</dt><dd><a href="{{ route('bk.kasus-konseling.show', $rujukan->kasus->id) }}">{{ $rujukan->kasus->tanggal->translatedFormat('j M Y') }} · {{ \Illuminate\Support\Str::limit($rujukan->kasus->topik_layanan, 50) }}</a></dd>
            @endif
        </dl>
    </section>
</div>

<section class="panel">
    <div class="panel-head">
        <div>
            <h3>Perkembangan rujukan</h3>
            <p>Perbarui status dan catat hasil atau saran dari psikolog.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('bk.rujukan-psikolog.status', $rujukan->id) }}">
        @csrf @method('PATCH')
        <div class="form-grid">
            <div class="field">
                <label for="status">Status <span class="req">*</span></label>
                <select id="status" name="status" required>
                    @foreach($daftarStatus as $st)
                        <option value="{{ $st }}" @selected(old('status', $rujukan->status) === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="tanggal_selesai">Tanggal selesai</label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $rujukan->tanggal_selesai?->toDateString()) }}">
                <small class="muted">Kosongkan jika status Selesai, akan terisi tanggal hari ini.</small>
            </div>
            <div class="field full">
                <label for="hasil_catatan">Hasil / saran dari psikolog</label>
                <textarea id="hasil_catatan" name="hasil_catatan" rows="4">{{ old('hasil_catatan', $rujukan->hasil_catatan) }}</textarea>
            </div>
            <div class="field full">
                <label for="tindak_lanjut">Tindak lanjut di sekolah</label>
                <textarea id="tindak_lanjut" name="tindak_lanjut" rows="3">{{ old('tindak_lanjut', $rujukan->tindak_lanjut) }}</textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan perkembangan</button>
    </form>
</section>

<form method="POST" action="{{ route('bk.rujukan-psikolog.destroy', $rujukan->id) }}"
      onsubmit="return confirm('Hapus rujukan ini? Tindakan ini tidak bisa dibatalkan.');">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">Hapus rujukan</button>
</form>
@endsection
