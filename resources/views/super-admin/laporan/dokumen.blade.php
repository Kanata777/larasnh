@extends('super-admin.laporan.layout')

@section('laporan')
<div class="lap-head">
    <div>
        <h2>Cetak Dokumen</h2>
        <p class="lap-muted">{{ $tahunNama ? 'Tahun pelajaran ' . $tahunNama . ' · ' : '' }}Dokumen dibuka di tab baru dan siap dicetak.</p>
    </div>
    <div class="lap-actions"><a class="lap-btn" href="{{ route('super-admin.laporan.index') }}">← Menu laporan</a></div>
</div>

<form method="GET" action="{{ route('super-admin.laporan.dokumen.hadir') }}" target="_blank" class="lap-card">
    <h3>Daftar hadir</h3>
    <p class="lap-muted">Daftar nama siswa aktif dengan kolom tanggal satu bulan.</p>
    <div class="lap-row">
        <select name="kelas_id" required>
            <option value="">Pilih kelas</option>
            @foreach ($kelasOptions as $k)<option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>@endforeach
        </select>
        <input type="month" name="bulan" value="{{ now()->format('Y-m') }}">
        <button class="lap-btn lap-btn-primary">Cetak daftar hadir</button>
    </div>
</form>

<form method="GET" action="{{ route('super-admin.laporan.dokumen.label') }}" target="_blank" class="lap-card">
    <h3>Label nama siswa</h3>
    <p class="lap-muted">Label berisi nama, NIS, dan kelas (3 kolom per baris).</p>
    <div class="lap-row">
        <select name="kelas_id" required>
            <option value="">Pilih kelas</option>
            @foreach ($kelasOptions as $k)<option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>@endforeach
        </select>
        <button class="lap-btn lap-btn-primary">Cetak label</button>
    </div>
</form>

<form id="fSurat" method="GET" target="_blank" class="lap-card">
    <h3>Surat keterangan siswa</h3>
    <p class="lap-muted">Isi data sekolah, lalu pilih siswa. Kolom kosong akan tercetak sebagai titik-titik.</p>
    <div class="lap-row">
        <select id="sKelas"><option value="">Pilih kelas</option>
            @foreach ($kelasOptions as $k)<option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>@endforeach
        </select>
        <select id="sSiswa" required><option value="">Pilih siswa</option></select>
    </div>
    <div class="lap-row">
        <input name="sekolah" placeholder="Nama sekolah" value="{{ config('app.name') }}">
        <input name="nomor" placeholder="Nomor surat">
        <input name="kepala" placeholder="Nama kepala sekolah">
        <input name="nip" placeholder="NIP kepala sekolah">
        <input name="kota" placeholder="Kota">
    </div>
    <div class="lap-row"><button class="lap-btn lap-btn-primary">Cetak surat</button></div>
</form>

<script>
    const siswa = @json($siswa);
    const base = @json(route('super-admin.laporan.dokumen.surat', '__ID__'));
    const sKelas = document.getElementById('sKelas');
    const sSiswa = document.getElementById('sSiswa');

    sKelas.addEventListener('change', () => {
        sSiswa.innerHTML = '<option value="">Pilih siswa</option>';
        siswa.filter(s => String(s.kelas_id) === sKelas.value).forEach(s => {
            const o = document.createElement('option');
            o.value = s.id; o.textContent = s.nama;
            sSiswa.appendChild(o);
        });
    });
    document.getElementById('fSurat').addEventListener('submit', e => {
        if (!sSiswa.value) { e.preventDefault(); return; }
        e.target.action = base.replace('__ID__', sSiswa.value);
    });
</script>
@endsection
