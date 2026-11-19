@extends('bk.layouts.app')
@section('title', 'Penempatan Kelas 1')
@section('subtitle', 'Tempatkan siswa laki-laki dan perempuan hasil pendaftaran ke kelas 1A-1D: pilih semua atau satu per satu.')

@push('styles')
<style>
    .nama-L{color:#1d4ed8;font-weight:600}
    .nama-P{color:#db2777;font-weight:600}
    tr.baris-L{background:#eff6ff}
    tr.baris-P{background:#fdf2f8}
    .badge-L{background:#dbeafe;color:#1d4ed8}
    .badge-P{background:#fce7f3;color:#be185d}
    .btn-L{background:#dbeafe;color:#1d4ed8}
    .btn-L:hover{background:#bfdbfe}
    .btn-P{background:#fce7f3;color:#be185d}
    .btn-P:hover{background:#fbcfe8}
    .ringkas{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px}
    .ringkas .badge{font-size:12px;padding:5px 12px}
</style>
@endpush

@section('content')

@include('bk.naik-kelas._menu')

<div class="panel">
    <div class="panel-head"><div><h3>Tahun pelajaran tujuan</h3></div></div>
    <form class="filter" method="get" action="{{ route('bk.naik-kelas.penempatan') }}">
        <select name="tahun" aria-label="Tahun pelajaran" onchange="this.form.submit()">
            @foreach($daftarTahun as $t)
                <option value="{{ $t->id }}" @selected($t->id === $tahun)>{{ $t->label }}</option>
            @endforeach
        </select>
    </form>
</div>

<form method="POST" action="{{ route('bk.naik-kelas.penempatan.simpan') }}" id="form-tempat">
    @csrf
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Siswa belum ditempatkan</h3>
                <p>{{ $daftarSiswa->count() }} siswa. Centang semua, atau pilih satu per satu (laki-laki maupun perempuan).</p>
            </div>
        </div>

        @if($daftarSiswa->isNotEmpty())
            <div class="ringkas">
                <span class="badge badge-L">♂ Laki-laki: {{ $daftarSiswa->where('jenis_kelamin', 'L')->count() }}</span>
                <span class="badge badge-P">♀ Perempuan: {{ $daftarSiswa->where('jenis_kelamin', 'P')->count() }}</span>
            </div>

            <div class="ringkas">
                <button type="button" class="btn btn-light btn-sm" id="btn-semua">Centang semua</button>
                <button type="button" class="btn btn-L btn-sm" id="btn-semua-L">Centang semua laki-laki</button>
                <button type="button" class="btn btn-P btn-sm" id="btn-semua-P">Centang semua perempuan</button>
                <button type="button" class="btn btn-light btn-sm" id="btn-kosong">Hapus centang</button>
                <span class="muted">Dipilih: <strong id="jml-dipilih">0</strong> siswa</span>
            </div>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px"><input type="checkbox" id="pilih-semua" aria-label="Pilih semua"></th>
                        <th>Nama</th><th>NIS</th><th>L/P</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($daftarSiswa as $s)
                    <tr class="baris-{{ $s->jenis_kelamin }}">
                        <td><input type="checkbox" class="cek-siswa" name="siswa_ids[]" value="{{ $s->id }}"
                            data-jk="{{ $s->jenis_kelamin }}"
                            aria-label="Pilih {{ $s->nama_lengkap }}"
                            @checked(is_array(old('siswa_ids')) && in_array($s->id, old('siswa_ids')))></td>
                        <td class="nama-{{ $s->jenis_kelamin }}">{{ $s->nama_lengkap }}</td>
                        <td>{{ $s->nis ?: '-' }}</td>
                        <td>
                            <span class="badge badge-{{ $s->jenis_kelamin }}">
                                {{ $s->jenis_kelamin === 'L' ? '♂ Laki-laki' : '♀ Perempuan' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">Tidak ada siswa yang belum ditempatkan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($daftarSiswa->isNotEmpty())
    <div class="panel">
        <div class="panel-head"><div><h3>Kelas tujuan</h3></div></div>
        <div class="form-grid">
            <div class="field">
                <label for="kelas_tujuan_id">Masukkan ke kelas <span class="req">*</span></label>
                <select id="kelas_tujuan_id" name="kelas_tujuan_id" required>
                    <option value="">-- pilih --</option>
                    @foreach($kelasTujuan as $k)
                        <option value="{{ $k->id }}" @selected((int) old('kelas_tujuan_id') === (int) $k->id)>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
                @if($kelasTujuan->isEmpty())
                    <small>Belum ada kelas tingkat 1 di tahun pelajaran ini.</small>
                @endif
            </div>
            <div class="field">
                <label for="tanggal">Tanggal <span class="req">*</span></label>
                <input type="date" id="tanggal" name="tanggal" max="{{ now()->toDateString() }}" value="{{ old('tanggal', now()->toDateString()) }}" required>
            </div>
            <div class="field full">
                <label for="alasan">Catatan (opsional)</label>
                <input type="text" id="alasan" name="alasan" maxlength="500" value="{{ old('alasan') }}">
            </div>
        </div>
        <button type="button" class="btn btn-primary" id="btn-simpan">Tempatkan yang dipilih</button>
    </div>
    @endif
</form>

<script>
(function () {
    var form = document.getElementById('form-tempat');
    var semua = document.getElementById('pilih-semua');
    var jml = document.getElementById('jml-dipilih');
    var cek = function () { return Array.prototype.slice.call(document.querySelectorAll('.cek-siswa')); };
    var dipilih = function () { return cek().filter(function (c) { return c.checked; }); };

    var hitung = function () {
        var n = dipilih().length;
        if (jml) jml.textContent = n;
        if (semua) semua.checked = n > 0 && n === cek().length;
    };
    // Atur centang semua kotak; jk = 'L' / 'P' hanya menyalakan jenis itu (yang lain dimatikan)
    var set = function (v, jk) {
        cek().forEach(function (c) {
            c.checked = jk ? (v && c.getAttribute('data-jk') === jk) : v;
        });
        hitung();
    };
    var klik = function (id, fn) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('click', fn);
    };

    if (semua) semua.addEventListener('change', function () { set(semua.checked); });
    klik('btn-semua', function () { set(true); });
    klik('btn-semua-L', function () { set(true, 'L'); });
    klik('btn-semua-P', function () { set(true, 'P'); });
    klik('btn-kosong', function () { set(false); });
    cek().forEach(function (c) { c.addEventListener('change', hitung); });
    hitung();

    klik('btn-simpan', function () {
        var pilih = dipilih();
        var n = pilih.length;
        var l = pilih.filter(function (c) { return c.getAttribute('data-jk') === 'L'; }).length;
        var s = document.getElementById('kelas_tujuan_id');
        if (!n) { alert('Centang minimal satu siswa.'); return; }
        if (!s.value) { alert('Pilih kelas tujuan dulu.'); return; }
        if (!confirm('Tempatkan ' + n + ' siswa (' + l + ' laki-laki, ' + (n - l) + ' perempuan) ke ' + s.options[s.selectedIndex].text + '?')) return;
        form.submit();
    });
})();
</script>
@endsection