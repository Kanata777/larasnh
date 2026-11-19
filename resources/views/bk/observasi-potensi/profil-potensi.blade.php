@extends('bk.layouts.app')
@section('title', $siswa->nama_lengkap)
@section('subtitle', 'Profil potensi · Kelas ' . ($siswa->riwayatAktif?->kelas?->nama_kelas ?? '-'))
@section('content')

@php
    $peran = auth()->user()->role->value;
    $namaKelas = $siswa->riwayatAktif?->kelas?->nama_kelas ?? 'Belum ada kelas';
    $inisial = mb_strtoupper(mb_substr($siswa->nama_lengkap, 0, 1));
    // Kalau simpan prestasi gagal validasi, halaman kembali langsung ke tab Prestasi
    $tabAwal = ($errors->has('nama_prestasi') || $errors->has('tingkat') || $errors->has('tanggal')) ? 'prestasi' : 'ringkasan';
@endphp

<style>
    .profil-head { display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
    .profil-avatar { width:64px; height:64px; border-radius:50%; background:#dbeafe; color:#1d4ed8;
        display:flex; align-items:center; justify-content:center; font-size:26px; font-weight:700; flex-shrink:0; }
    .profil-info { flex:1; min-width:200px; }
    .profil-info h2 { margin:0 0 6px; font-size:20px; }
    .tabs { display:flex; gap:4px; flex-wrap:wrap; border-bottom:1px solid #e3e7ec; margin-bottom:18px; }
    .tab-btn { background:none; border:0; border-bottom:3px solid transparent; padding:10px 16px; margin-bottom:-1px;
        font:inherit; font-weight:600; color:#6b7280; cursor:pointer; }
    .tab-btn:hover { color:#111827; }
    .tab-btn.aktif { color:#1d4ed8; border-bottom-color:#1d4ed8; }
    .tab-btn small { background:#e5e7eb; color:#374151; border-radius:10px; padding:1px 7px; margin-left:4px; font-weight:600; }
    .tab-wrap.js .tab-panel { display:none; }
    .tab-wrap.js .tab-panel.aktif { display:block; }
</style>

<p style="margin-bottom:18px"><a class="btn btn-light btn-sm" href="{{ route('bk.observasi-potensi.index') }}">← Kembali ke daftar</a></p>

{{-- Kepala profil: selalu tampil di atas semua tab --}}
<section class="panel">
    <div class="profil-head">
        <div class="profil-avatar" aria-hidden="true">{{ $inisial }}</div>
        <div class="profil-info">
            <h2>{{ $siswa->nama_lengkap }}</h2>
            <span class="badge badge-blue">{{ $namaKelas }}</span>
            <span class="muted">NIS {{ $siswa->nis ?: '-' }}</span>
        </div>
        @if($peran === 'guru_bk')
            <a class="btn btn-primary" href="{{ route('bk.observasi-potensi.create', $siswa->id) }}">➕ Tambah observasi</a>
        @endif
    </div>
</section>

<div class="tab-wrap" id="tab-wrap" data-awal="{{ $tabAwal }}">
    <div class="tabs" role="tablist">
        <button type="button" class="tab-btn" role="tab" data-tab="ringkasan">Ringkasan</button>
        <button type="button" class="tab-btn" role="tab" data-tab="prestasi">Prestasi <small>{{ $daftarPrestasi->count() }}</small></button>
        <button type="button" class="tab-btn" role="tab" data-tab="observasi">Riwayat Observasi <small>{{ $catatan->count() }}</small></button>
    </div>

    {{-- TAB 1: RINGKASAN (bakat/minat + potensi per bidang) --}}
    <div class="tab-panel" id="tab-ringkasan" role="tabpanel">
        <div class="panel">
            <div class="panel-head"><h3>Bakat dan minat</h3></div>
            <dl style="margin-bottom:14px">
                <dt>Cita-cita</dt><dd>{{ $profilBk?->cita_cita ?: '-' }}</dd>
                <dt>Hobi</dt><dd>{{ $profilBk?->hobi ?: '-' }}</dd>
            </dl>
            @if($bakat->isNotEmpty())
                <div class="chips">
                    @foreach($bakat as $nama => $jumlah)
                        <span class="chip">{{ \Illuminate\Support\Str::ucfirst($nama) }}@if($jumlah > 1)<small>× {{ $jumlah }}</small>@endif</span>
                    @endforeach
                </div>
                <p class="muted">Dari catatan observasi. Angka menunjukkan berapa kali bakat itu muncul.</p>
            @else
                <p class="empty">Belum ada bakat atau minat yang tercatat.</p>
            @endif
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3>Potensi per bidang</h3>
                    <p>Tingkat diambil dari catatan observasi terbaru pada tiap bidang.</p>
                </div>
            </div>
            <div class="potensi-grid">
                @foreach($potensiPerBidang as $p)
                    @php $kt = $p['kategori']; $o = $p['terbaru']; @endphp
                    <div class="potensi-kartu {{ $o ? '' : 'potensi-kosong' }}">
                        <h4>{{ $kt->ikon() }} {{ $kt->label() }}</h4>
                        @if($o)
                            <span class="badge {{ $o->tingkat_potensi->badge() }}">{{ $o->tingkat_potensi->label() }}</span>
                            <span class="muted">Diamati {{ $o->tanggal->translatedFormat('j M Y') }}</span>
                            @if($o->bakat_minat)<span class="muted">{{ $o->bakat_minat }}</span>@endif
                        @else
                            <span class="badge badge-gray">Belum diamati</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- TAB 2: PRESTASI (diinput kesiswaan) --}}
    <div class="tab-panel" id="tab-prestasi" role="tabpanel">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3>Prestasi</h3>
                    <p>Dicatat oleh kesiswaan.</p>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Tanggal</th><th>Prestasi</th><th>Bidang</th><th>Tingkat</th><th>Peringkat</th>@if($peran === 'kesiswaan')<th></th>@endif</tr></thead>
                    <tbody>
                    @forelse($daftarPrestasi as $pr)
                        <tr>
                            <td>{{ $pr->tanggal->translatedFormat('j M Y') }}</td>
                            <td>{{ $pr->nama_prestasi }}</td>
                            <td>{{ $pr->kategori ? $pr->kategori->ikon() . ' ' . $pr->kategori->label() : '-' }}</td>
                            <td><span class="badge badge-blue">{{ $pr->tingkat->label() }}</span></td>
                            <td>{{ $pr->peringkat ?: '-' }}</td>
                            @if($peran === 'kesiswaan')
                                <td class="actions">
                                    <form class="inline-form" method="POST" action="{{ route('bk.observasi-potensi.prestasi.destroy', $pr->id) }}"
                                        onsubmit="return confirm('Hapus prestasi ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $peran === 'kesiswaan' ? 6 : 5 }}" class="empty">Belum ada prestasi yang tercatat.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($peran === 'kesiswaan')
                <details class="tambah" @if($errors->has('nama_prestasi') || $errors->has('tingkat') || $errors->has('tanggal')) open @endif>
                    <summary>➕ Tambah prestasi</summary>
                    <form method="POST" action="{{ route('bk.observasi-potensi.prestasi.store', $siswa->id) }}" style="margin-top:14px">
                        @csrf
                        <div class="form-grid">
                            <div class="field full">
                                <label for="nama_prestasi">Nama prestasi <span class="req">*</span></label>
                                <input type="text" id="nama_prestasi" name="nama_prestasi" maxlength="200" value="{{ old('nama_prestasi') }}" required>
                            </div>
                            <div class="field">
                                <label for="kategori_prestasi">Bidang</label>
                                <select id="kategori_prestasi" name="kategori">
                                    <option value="">Tidak ditentukan</option>
                                    @foreach($daftarKategori as $kt)
                                        <option value="{{ $kt->value }}" @selected(old('kategori') === $kt->value)>{{ $kt->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label for="tingkat">Tingkat <span class="req">*</span></label>
                                <select id="tingkat" name="tingkat" required>
                                    @foreach($daftarTingkatPrestasi as $tp)
                                        <option value="{{ $tp->value }}" @selected(old('tingkat') === $tp->value)>{{ $tp->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label for="peringkat">Peringkat</label>
                                <input type="text" id="peringkat" name="peringkat" maxlength="50" placeholder="Contoh: Juara 1" value="{{ old('peringkat') }}">
                            </div>
                            <div class="field">
                                <label for="tanggal_prestasi">Tanggal <span class="req">*</span></label>
                                <input type="date" id="tanggal_prestasi" name="tanggal" max="{{ now()->toDateString() }}" value="{{ old('tanggal', now()->toDateString()) }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan prestasi</button>
                    </form>
                </details>
            @endif
        </div>
    </div>

    {{-- TAB 3: RIWAYAT OBSERVASI --}}
    <div class="tab-panel" id="tab-observasi" role="tabpanel">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3>Riwayat observasi</h3>
                    <p>Klik ringkasan temuan untuk membaca selengkapnya.</p>
                </div>
                @if($peran === 'guru_bk')
                    <a class="btn btn-primary btn-sm" href="{{ route('bk.observasi-potensi.create', $siswa->id) }}">➕ Tambah observasi</a>
                @endif
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Tanggal</th><th>Bidang</th><th>Tingkat</th><th>Temuan</th><th>Pengamat</th>@if($peran === 'guru_bk')<th></th>@endif</tr></thead>
                    <tbody>
                    @forelse($catatan as $o)
                        <tr>
                            <td>{{ $o->tanggal->translatedFormat('j M Y') }}</td>
                            <td>{{ $o->kategori->ikon() }} {{ $o->kategori->label() }}</td>
                            <td><span class="badge {{ $o->tingkat_potensi->badge() }}">{{ $o->tingkat_potensi->label() }}</span></td>
                            <td>
                                <details>
                                    <summary>{{ \Illuminate\Support\Str::limit($o->temuan, 70) }}</summary>
                                    <div class="isi-laporan" style="margin-top:8px">{{ $o->temuan }}</div>
                                    @if($o->bakat_minat)<div class="isi-laporan"><strong>Bakat/minat:</strong> {{ $o->bakat_minat }}</div>@endif
                                    @if($o->rekomendasi)<div class="isi-laporan"><strong>Rekomendasi:</strong> {{ $o->rekomendasi }}</div>@endif
                                </details>
                            </td>
                            <td>{{ $o->pengamat }}</td>
                            @if($peran === 'guru_bk')
                                <td class="actions">
                                    <a class="btn btn-light btn-sm" href="{{ route('bk.observasi-potensi.edit', $o->id) }}">Ubah</a>
                                    <form class="inline-form" method="POST" action="{{ route('bk.observasi-potensi.destroy', $o->id) }}"
                                        onsubmit="return confirm('Hapus catatan observasi ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $peran === 'guru_bk' ? 6 : 5 }}" class="empty">Belum ada catatan observasi.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
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
        if (!document.getElementById('tab-' + nama)) nama = 'ringkasan';
        tombol.forEach(function (b) { b.classList.toggle('aktif', b.dataset.tab === nama); });
        panel.forEach(function (p) { p.classList.toggle('aktif', p.id === 'tab-' + nama); });
    }

    tombol.forEach(function (b) {
        b.addEventListener('click', function () {
            buka(b.dataset.tab);
            history.replaceState(null, '', '#' + b.dataset.tab);
        });
    });

    // Urutan: alamat (#prestasi) lebih dulu, kalau tidak ada pakai tab awal dari server
    buka(location.hash.replace('#', '') || wrap.dataset.awal);
})();
</script>
@endsection