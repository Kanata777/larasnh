@extends('bk.layouts.app')
@section('title', 'Rujukan Psikolog')
@section('subtitle', 'Rujuk siswa yang punya atau pernah punya kasus konseling ke psikolog, dan pantau perkembangannya.')
@section('content')

<style>
    .tabs { display:flex; gap:4px; flex-wrap:wrap; border-bottom:1px solid #e3e7ec; margin-bottom:18px; }
    .tab-btn { background:none; border:0; border-bottom:3px solid transparent; padding:10px 16px; margin-bottom:-1px;
        font:inherit; font-weight:600; color:#6b7280; cursor:pointer; }
    .tab-btn:hover { color:#111827; }
    .tab-btn.aktif { color:#1d4ed8; border-bottom-color:#1d4ed8; }
    .tab-btn small { background:#e5e7eb; color:#374151; border-radius:10px; padding:1px 7px; margin-left:4px; font-weight:600; }
    .tab-wrap.js .tab-panel { display:none; }
    .tab-wrap.js .tab-panel.aktif { display:block; }
</style>

@include('bk.partials.pesan')

<div class="tab-wrap" id="tab-wrap">
    <div class="tabs" role="tablist">
        <button type="button" class="tab-btn aktif" role="tab" data-tab="siswa">Pilih Siswa <small>{{ $daftarSiswa->total() }}</small></button>
        <button type="button" class="tab-btn" role="tab" data-tab="riwayat">Riwayat Rujukan <small>{{ $jumlahRiwayat }}</small></button>
        <button type="button" class="tab-btn" role="tab" data-tab="psikolog">Daftar Psikolog <small>{{ $daftarPsikolog->count() }}</small></button>
    </div>

    {{-- ===== TAB 1: PILIH SISWA (hanya yang punya / pernah punya kasus) ===== --}}
    <div class="tab-panel" id="tab-siswa" role="tabpanel">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3>Siswa dengan kasus konseling</h3>
                    <p>Hanya siswa aktif yang punya atau pernah punya laporan kasus. Klik <strong>Rujuk</strong> pada siswa yang dipilih.</p>
                </div>
            </div>

            <form class="filter" method="get" action="{{ route('bk.rujukan-psikolog.index') }}#siswa">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIS, atau NISN" aria-label="Cari siswa">
                <select name="kelas" aria-label="Filter kelas">
                    <option value="">Semua kelas</option>
                    @foreach($daftarKelas as $k)
                        <option value="{{ $k->id }}" @selected(request('kelas') == $k->id)>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary" type="submit">Cari</button>
                @if(request()->hasAny(['q','kelas']))
                    <a class="btn btn-light" href="{{ route('bk.rujukan-psikolog.index') }}#siswa">Reset</a>
                @endif
            </form>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Nama</th><th>Kelas</th><th>Jumlah kasus</th><th>Kasus terakhir</th><th>Rujukan</th><th style="text-align:right">Aksi</th></tr></thead>
                    <tbody>
                    @forelse($daftarSiswa as $s)
                        @php
                            $r = $ringkasKasus[$s->id] ?? null;
                            $aktif = $rujukanAktif[$s->id] ?? null;
                        @endphp
                        <tr>
                            <td>{{ $s->nama_lengkap }}</td>
                            <td><span class="badge badge-blue">{{ $s->riwayatAktif?->kelas?->nama_kelas ?? 'Belum ada kelas' }}</span></td>
                            <td>{{ $r->jumlah ?? 0 }} kasus</td>
                            <td>{{ $r ? \Carbon\Carbon::parse($r->terakhir)->translatedFormat('j M Y') : '-' }}</td>
                            <td>
                                @if($aktif)
                                    <span class="badge {{ $aktif->badge }}">{{ $aktif->status }}</span>
                                @elseif(($totalRujukan[$s->id] ?? 0) > 0)
                                    <span class="muted">Pernah dirujuk ({{ $totalRujukan[$s->id] }}×)</span>
                                @else
                                    <span class="muted">Belum pernah</span>
                                @endif
                            </td>
                            <td class="actions">
                                <a class="btn btn-light btn-sm" href="{{ route('bk.siswa.show', $s->id) }}">Detail</a>
                                @if($aktif)
                                    <a class="btn btn-light btn-sm" href="{{ route('bk.rujukan-psikolog.show', $aktif->id) }}">Lihat rujukan</a>
                                @else
                                    <a class="btn btn-primary btn-sm" href="{{ route('bk.rujukan-psikolog.create', $s->id) }}">Rujuk</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Belum ada siswa dengan kasus konseling yang cocok. Buat laporan kasus dulu di menu Kasus &amp; Konseling.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($daftarSiswa->total() > 0)
                <div class="pager">
                    <span>Menampilkan {{ $daftarSiswa->firstItem() }}–{{ $daftarSiswa->lastItem() }} dari {{ $daftarSiswa->total() }} siswa</span>
                    {{ $daftarSiswa->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>

    {{-- ===== TAB 2: RIWAYAT RUJUKAN ===== --}}
    <div class="tab-panel" id="tab-riwayat" role="tabpanel">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3>Riwayat rujukan</h3>
                    <p>Semua rujukan yang pernah dibuat. Klik <strong>Detail</strong> untuk memperbarui status atau mencetak surat.</p>
                </div>
            </div>

            <form class="filter" method="get" action="{{ route('bk.rujukan-psikolog.index') }}#riwayat">
                <select name="status" aria-label="Filter status">
                    <option value="">Semua status</option>
                    @foreach($daftarStatus as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary" type="submit">Terapkan</button>
                @if(request()->filled('status'))
                    <a class="btn btn-light" href="{{ route('bk.rujukan-psikolog.index') }}#riwayat">Reset</a>
                @endif
            </form>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Tanggal</th><th>Siswa</th><th>Psikolog</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead>
                    <tbody>
                    @forelse($riwayat as $r)
                        <tr>
                            <td>{{ $r->tanggal_rujukan->translatedFormat('j M Y') }}</td>
                            <td>{{ $r->siswa?->nama_lengkap ?? 'Siswa tidak ditemukan' }}</td>
                            <td>{{ $r->psikolog?->nama_lengkap ?? '-' }}</td>
                            <td><span class="badge {{ $r->badge }}">{{ $r->status }}</span></td>
                            <td class="actions">
                                <a class="btn btn-light btn-sm" href="{{ route('bk.rujukan-psikolog.show', $r->id) }}">Detail</a>
                                <a class="btn btn-light btn-sm" href="{{ route('bk.rujukan-psikolog.surat', $r->id) }}" target="_blank" rel="noopener">Surat</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">Belum ada rujukan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($riwayat->total() > 0)
                <div class="pager">
                    <span>Menampilkan {{ $riwayat->firstItem() }}–{{ $riwayat->lastItem() }} dari {{ $riwayat->total() }} rujukan</span>
                    {{ $riwayat->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>

    {{-- ===== TAB 3: DAFTAR PSIKOLOG ===== --}}
    <div class="tab-panel" id="tab-psikolog" role="tabpanel">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3>Daftar psikolog</h3>
                    <p>Psikolog atau lembaga tujuan rujukan. Yang dinonaktifkan tidak muncul di form rujukan.</p>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Nama</th><th>Lembaga</th><th>Kontak</th><th>Rujukan</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead>
                    <tbody>
                    @forelse($daftarPsikolog as $p)
                        <tr>
                            <td>{{ $p->nama }}</td>
                            <td>{{ $p->lembaga ?: '-' }}</td>
                            <td>{{ $p->telepon ?: '-' }}@if($p->email)<br><span class="muted">{{ $p->email }}</span>@endif</td>
                            <td>{{ $p->rujukan_count }}</td>
                            <td><span class="badge {{ $p->aktif ? 'badge-green' : 'badge-gray' }}">{{ $p->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="actions">
                                <form class="inline-form" method="POST" action="{{ route('bk.rujukan-psikolog.psikolog.toggle', $p->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-light btn-sm">{{ $p->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                                @if($p->rujukan_count === 0)
                                    <form class="inline-form" method="POST" action="{{ route('bk.rujukan-psikolog.psikolog.destroy', $p->id) }}"
                                          onsubmit="return confirm('Hapus psikolog ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Belum ada psikolog. Tambahkan lewat form di bawah.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <details class="tambah" @if($errors->has('nama') || $errors->has('email')) open @endif>
                <summary>➕ Tambah psikolog</summary>
                <form method="POST" action="{{ route('bk.rujukan-psikolog.psikolog.store') }}" style="margin-top:14px">
                    @csrf
                    <div class="form-grid">
                        <div class="field">
                            <label for="nama">Nama psikolog <span class="req">*</span></label>
                            <input type="text" id="nama" name="nama" maxlength="150" value="{{ old('nama') }}" required>
                        </div>
                        <div class="field">
                            <label for="lembaga">Lembaga / klinik</label>
                            <input type="text" id="lembaga" name="lembaga" maxlength="150" value="{{ old('lembaga') }}">
                        </div>
                        <div class="field">
                            <label for="telepon">Telepon</label>
                            <input type="text" id="telepon" name="telepon" maxlength="40" value="{{ old('telepon') }}">
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" maxlength="150" value="{{ old('email') }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan psikolog</button>
                </form>
            </details>
        </div>
    </div>
</div>

<script>
(function () {
    var wrap = document.getElementById('tab-wrap');
    var tombol = wrap.querySelectorAll('.tab-btn');
    var panel = wrap.querySelectorAll('.tab-panel');
    wrap.classList.add('js'); // tanpa JavaScript, semua bagian tetap tampil berurutan

    function buka(nama) {
        if (!document.getElementById('tab-' + nama)) nama = 'siswa';
        tombol.forEach(function (b) { b.classList.toggle('aktif', b.dataset.tab === nama); });
        panel.forEach(function (p) { p.classList.toggle('aktif', p.id === 'tab-' + nama); });
    }

    tombol.forEach(function (b) {
        b.addEventListener('click', function () {
            buka(b.dataset.tab);
            history.replaceState(null, '', '#' + b.dataset.tab);
        });
    });

    buka(location.hash.replace('#', '') || 'siswa');
})();
</script>
@endsection
