@extends('bk.layouts.app')
@section('title', 'Rujuk ke Psikolog')
@section('subtitle', 'Buat rujukan untuk ' . $siswa->nama_lengkap . '.')
@section('content')

@include('bk.partials.pesan')

<div class="panel">
    <div class="panel-head">
        <div>
            <h3>{{ $siswa->nama_lengkap }}</h3>
            <p>NIS {{ $siswa->nis ?: '-' }} · Kelas {{ $siswa->riwayatAktif?->kelas?->nama_kelas ?? '-' }} · {{ $kasus->count() }} kasus tercatat</p>
        </div>
        <a class="btn btn-light" href="{{ route('bk.rujukan-psikolog.index') }}">← Kembali</a>
    </div>

    <form method="POST" action="{{ route('bk.rujukan-psikolog.store', $siswa->id) }}">
        @csrf
        <div class="form-grid">
            <div class="field full">
                <label for="kasus_konseling_id">Kasus yang menjadi dasar rujukan <span class="req">*</span></label>
                <select id="kasus_konseling_id" name="kasus_konseling_id" required>
                    @foreach($kasus as $k)
                        <option value="{{ $k->id }}"
                                data-ringkasan="{{ 'Topik: ' . $k->topik_layanan . "\n" . 'Tujuan: ' . $k->tujuan_layanan . "\n" . 'Tindak lanjut: ' . $k->tindak_lanjut }}"
                                @selected((int) old('kasus_konseling_id', $kasus->first()->id) === $k->id)>
                            {{ $k->tanggal->translatedFormat('j M Y') }} · {{ $k->bidang_layanan }} · {{ \Illuminate\Support\Str::limit($k->topik_layanan, 70) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="psikolog_id">Psikolog / lembaga tujuan <span class="req">*</span></label>
                <select id="psikolog_id" name="psikolog_id" required>
                    <option value="">-- pilih --</option>
                    @foreach($daftarPsikolog as $p)
                        <option value="{{ $p->id }}" @selected((int) old('psikolog_id') === $p->id)>{{ $p->nama_lengkap }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="tanggal_rujukan">Tanggal rujukan <span class="req">*</span></label>
                <input type="date" id="tanggal_rujukan" name="tanggal_rujukan" max="{{ now()->toDateString() }}" value="{{ old('tanggal_rujukan', now()->toDateString()) }}" required>
            </div>

            <div class="field full">
                <label for="alasan">Alasan rujukan <span class="req">*</span></label>
                <textarea id="alasan" name="alasan" rows="3" required>{{ old('alasan') }}</textarea>
            </div>
            <div class="field full">
                <label for="ringkasan_kasus">Ringkasan kasus <span class="req">*</span></label>
                <textarea id="ringkasan_kasus" name="ringkasan_kasus" rows="5" required>{{ old('ringkasan_kasus') }}</textarea>
                <small class="muted">Terisi otomatis dari kasus yang dipilih. Boleh diubah.</small>
            </div>
            <div class="field full">
                <label for="catatan">Catatan guru BK</label>
                <textarea id="catatan" name="catatan" rows="3">{{ old('catatan') }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan rujukan</button>
        <a class="btn btn-light" href="{{ route('bk.rujukan-psikolog.index') }}">Batal</a>
    </form>
</div>

<script>
(function () {
    var pilih = document.getElementById('kasus_konseling_id');
    var ringkasan = document.getElementById('ringkasan_kasus');
    var terakhirOtomatis = '';

    function isi() {
        var teks = pilih.options[pilih.selectedIndex].dataset.ringkasan || '';
        // Hanya menimpa kalau kolom masih kosong atau masih berisi isian otomatis sebelumnya
        if (ringkasan.value.trim() === '' || ringkasan.value === terakhirOtomatis) {
            ringkasan.value = teks;
            terakhirOtomatis = teks;
        }
    }

    pilih.addEventListener('change', isi);
    isi();
})();
</script>
@endsection
