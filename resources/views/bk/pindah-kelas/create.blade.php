@extends('bk.layouts.app')
@section('title', 'Pindahkan Siswa')
@section('subtitle', 'Pilih kelas asal, lalu pindahkan siswa satu per satu atau seluruh kelas.')
@section('content')

@include('bk.partials.pesan')

@if($errors->any())
    <div class="alert alert-error" style="margin-bottom:14px">
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
@endif

<div class="panel">
    <div class="panel-head">
        <div><h3>1. Pilih kelas asal</h3></div>
    </div>
    <form class="filter" method="get" action="{{ route('bk.pindah-kelas.create') }}">
        <select name="kelas" aria-label="Kelas asal" onchange="this.form.submit()">
            <option value="">-- pilih kelas asal --</option>
            @foreach($daftarKelas as $k)
                <option value="{{ $k->id }}" @selected($kelasAsal && (int) $kelasAsal->id === (int) $k->id)>{{ $k->nama_kelas }}</option>
            @endforeach
        </select>
    </form>
</div>

@if($kelasAsal)
<form method="POST" action="{{ route('bk.pindah-kelas.store') }}" id="form-pindah">
    @csrf
    <input type="hidden" name="kelas_asal_id" value="{{ $kelasAsal->id }}">
    <input type="hidden" name="mode" id="mode" value="terpilih">

    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>2. Siswa kelas {{ $kelasAsal->nama_kelas }}</h3>
                <p>{{ $daftarSiswa->count() }} siswa aktif. Centang siswa yang akan dipindahkan.</p>
            </div>
        </div>

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
                    <tr>
                        <td>
                            <input type="checkbox" class="cek-siswa" name="siswa_ids[]" value="{{ $s->id }}"
                                @checked(is_array(old('siswa_ids')) ? in_array($s->id, old('siswa_ids')) : $siswaDipilih === $s->id)>
                        </td>
                        <td>{{ $s->nama_lengkap }}</td>
                        <td>{{ $s->nis ?: '-' }}</td>
                        <td>{{ $s->jenis_kelamin }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">Tidak ada siswa aktif di kelas ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($daftarSiswa->isNotEmpty())
    <div class="panel">
        <div class="panel-head"><div><h3>3. Kelas tujuan</h3></div></div>
        <div class="form-grid">
            <div class="field">
                <label for="kelas_tujuan_id">Pindah ke kelas <span class="req">*</span></label>
                <select id="kelas_tujuan_id" name="kelas_tujuan_id" required>
                    <option value="">-- pilih --</option>
                    @foreach($daftarKelas as $k)
                        @continue((int) $k->id === (int) $kelasAsal->id)
                        <option value="{{ $k->id }}" @selected((int) old('kelas_tujuan_id') === (int) $k->id)>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="tanggal">Tanggal pindah <span class="req">*</span></label>
                <input type="date" id="tanggal" name="tanggal" max="{{ now()->toDateString() }}" value="{{ old('tanggal', now()->toDateString()) }}" required>
            </div>
            <div class="field full">
                <label for="alasan">Alasan (opsional)</label>
                <input type="text" id="alasan" name="alasan" maxlength="500" value="{{ old('alasan') }}" placeholder="Contoh: penyesuaian rombel, kenaikan kelas, permintaan orang tua">
            </div>
        </div>

        <button type="button" class="btn btn-primary" id="btn-terpilih">Pindahkan yang dipilih</button>
        <button type="button" class="btn btn-danger" id="btn-semua">Pindahkan seluruh kelas ({{ $daftarSiswa->count() }} siswa)</button>
        <a class="btn btn-light" href="{{ route('bk.pindah-kelas.create') }}">Batal</a>
    </div>
    @endif
</form>

<script>
(function () {
    var form = document.getElementById('form-pindah');
    if (!form) return;
    var mode = document.getElementById('mode');
    var semua = document.getElementById('pilih-semua');
    var cek = function () { return Array.prototype.slice.call(document.querySelectorAll('.cek-siswa')); };
    var tujuan = function () {
        var s = document.getElementById('kelas_tujuan_id');
        return s.value ? s.options[s.selectedIndex].text : '';
    };

    if (semua) semua.addEventListener('change', function () {
        cek().forEach(function (c) { c.checked = semua.checked; });
    });

    var btnTerpilih = document.getElementById('btn-terpilih');
    var btnSemua = document.getElementById('btn-semua');

    if (btnTerpilih) btnTerpilih.addEventListener('click', function () {
        var n = cek().filter(function (c) { return c.checked; }).length;
        if (!n) { alert('Centang minimal satu siswa.'); return; }
        if (!tujuan()) { alert('Pilih kelas tujuan dulu.'); return; }
        if (!confirm('Pindahkan ' + n + ' siswa ke kelas ' + tujuan() + '?')) return;
        mode.value = 'terpilih';
        form.submit();
    });

    if (btnSemua) btnSemua.addEventListener('click', function () {
        if (!tujuan()) { alert('Pilih kelas tujuan dulu.'); return; }
        if (!confirm('Pindahkan SELURUH siswa kelas @json($kelasAsal->nama_kelas) ke kelas ' + tujuan() + '?')) return;
        mode.value = 'semua';
        form.submit();
    });
})();
</script>
@endif
@endsection