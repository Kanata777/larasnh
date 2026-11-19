@extends('bk.layouts.app')
@section('title', 'Kelulusan Kelas 6')
@section('subtitle', 'Siswa kelas 6 yang dipilih berstatus Lulus dan menjadi alumni.')
@section('content')

@include('bk.naik-kelas._menu')

<div class="panel">
    <div class="panel-head"><div><h3>Tahun pelajaran</h3></div></div>
    <form class="filter" method="get" action="{{ route('bk.naik-kelas.kelulusan') }}">
        <select name="tahun" aria-label="Tahun pelajaran" onchange="this.form.submit()">
            @foreach($daftarTahun as $t)
                <option value="{{ $t->id }}" @selected($t->id === $tahun)>{{ $t->label }}</option>
            @endforeach
        </select>
    </form>
</div>

<form method="POST" action="{{ route('bk.naik-kelas.kelulusan.simpan') }}" id="form-lulus">
    @csrf
    <input type="hidden" name="tahun" value="{{ $tahun }}">

    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Siswa kelas 6</h3>
                <p>{{ $daftarSiswa->count() }} siswa aktif. Centang siswa yang dinyatakan lulus.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px"><input type="checkbox" id="pilih-semua" aria-label="Pilih semua"></th>
                        <th>Nama</th><th>NIS</th><th>L/P</th><th>Kelas</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($daftarSiswa as $s)
                    <tr>
                        <td><input type="checkbox" class="cek-siswa" name="siswa_ids[]" value="{{ $s->id }}"
                            @checked(is_array(old('siswa_ids')) && in_array($s->id, old('siswa_ids')))></td>
                        <td>{{ $s->nama_lengkap }}</td>
                        <td>{{ $s->nis ?: '-' }}</td>
                        <td>{{ $s->jenis_kelamin }}</td>
                        <td>{{ $s->nama_kelas }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Tidak ada siswa aktif kelas 6 pada tahun pelajaran ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($daftarSiswa->isNotEmpty())
    <div class="panel">
        <div class="panel-head"><div><h3>Proses</h3></div></div>
        <div class="form-grid">
            <div class="field">
                <label for="tanggal">Tanggal kelulusan <span class="req">*</span></label>
                <input type="date" id="tanggal" name="tanggal" max="{{ now()->toDateString() }}" value="{{ old('tanggal', now()->toDateString()) }}" required>
            </div>
        </div>
        <button type="button" class="btn btn-danger" id="btn-lulus">Luluskan yang dipilih</button>
    </div>
    @endif
</form>

<script>
(function () {
    var form = document.getElementById('form-lulus');
    var semua = document.getElementById('pilih-semua');
    var cek = function () { return Array.prototype.slice.call(document.querySelectorAll('.cek-siswa')); };
    if (semua) semua.addEventListener('change', function () {
        cek().forEach(function (c) { c.checked = semua.checked; });
    });
    var btn = document.getElementById('btn-lulus');
    if (btn) btn.addEventListener('click', function () {
        var n = cek().filter(function (c) { return c.checked; }).length;
        if (!n) { alert('Centang minimal satu siswa.'); return; }
        if (!confirm('Luluskan ' + n + ' siswa? Status mereka menjadi Lulus (alumni).')) return;
        form.submit();
    });
})();
</script>
@endsection