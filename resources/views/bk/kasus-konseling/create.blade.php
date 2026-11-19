@extends('bk.layouts.app')
@section('title', 'Buat Laporan Kasus')
@section('subtitle', 'Langkah 2 dari 2: isi laporan untuk ' . $siswa->nama_lengkap . '.')
@section('content')

@include('bk.partials.pesan')

@php $kelasSiswaId = $siswa->riwayatAktif?->kelas_id; @endphp

<div class="panel">
    <div class="panel-head">
        <div>
            <h3>{{ $siswa->nama_lengkap }}</h3>
            <p>NIS {{ $siswa->nis ?: '-' }} · Kelas {{ $siswa->riwayatAktif?->kelas?->nama_kelas ?? '-' }}</p>
        </div>
        <a class="btn btn-light" href="{{ route('bk.kasus-konseling.pilih-siswa') }}">← Ganti siswa</a>
    </div>

    <form method="POST" action="{{ route('bk.kasus-konseling.store', $siswa->id) }}">
        @csrf
        <div class="form-grid">
            <div class="field">
                <label for="komponen_layanan">Komponen layanan <span class="req">*</span></label>
                <select id="komponen_layanan" name="komponen_layanan" required>
                    <option value="">-- pilih --</option>
                    @foreach(\App\Models\KasusKonseling::KOMPONEN as $x)
                        <option value="{{ $x }}" @selected(old('komponen_layanan') === $x)>{{ $x }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="bidang_layanan">Bidang layanan <span class="req">*</span></label>
                <select id="bidang_layanan" name="bidang_layanan" required>
                    <option value="">-- pilih --</option>
                    @foreach(\App\Models\KasusKonseling::BIDANG as $x)
                        <option value="{{ $x }}" @selected(old('bidang_layanan') === $x)>{{ $x }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="kelas_id">Kelas <span class="req">*</span></label>
                <select id="kelas_id" name="kelas_id" required>
                    @foreach($daftarKelas as $k)
                        <option value="{{ $k->id }}" @selected((int) old('kelas_id', $kelasSiswaId) === (int) $k->id)>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="semester">Semester <span class="req">*</span></label>
                <select id="semester" name="semester" required>
                    @foreach(\App\Models\KasusKonseling::SEMESTER as $x)
                        <option value="{{ $x }}" @selected(old('semester', $semesterSekarang) === $x)>{{ $x }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="tanggal">Hari / tanggal <span class="req">*</span></label>
                <input type="date" id="tanggal" name="tanggal" max="{{ now()->toDateString() }}" value="{{ old('tanggal', now()->toDateString()) }}" required>
            </div>
            <div class="field">
                <label for="durasi_menit">Durasi pertemuan (menit) <span class="req">*</span></label>
                <input type="number" id="durasi_menit" name="durasi_menit" min="1" max="600" value="{{ old('durasi_menit', 30) }}" required>
            </div>
            <div class="field full">
                <label for="topik_layanan">Topik layanan <span class="req">*</span></label>
                <input type="text" id="topik_layanan" name="topik_layanan" maxlength="200" value="{{ old('topik_layanan') }}" required>
            </div>
            <div class="field full">
                <label for="tujuan_layanan">Tujuan layanan <span class="req">*</span></label>
                <textarea id="tujuan_layanan" name="tujuan_layanan" rows="3" required>{{ old('tujuan_layanan') }}</textarea>
            </div>
            <div class="field full">
                <label for="materi">Materi <span class="req">*</span></label>
                <textarea id="materi" name="materi" rows="4" required>{{ old('materi') }}</textarea>
            </div>
            <div class="field full">
                <label for="tindak_lanjut">Tindak lanjut <span class="req">*</span></label>
                <textarea id="tindak_lanjut" name="tindak_lanjut" rows="3" required>{{ old('tindak_lanjut') }}</textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan laporan</button>
        <a class="btn btn-light" href="{{ route('bk.kasus-konseling.pilih-siswa') }}">Batal</a>
    </form>
</div>
@endsection
