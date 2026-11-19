@extends('super-admin.layout')

@section('title', 'Data Orang Tua')
@section('subtitle', 'Kelola data ayah dan ibu siswa')

@php
    $fTahun = (int) request('tahun_id', 0);
    $fKelas = (int) request('kelas_id', 0);
    $adaFilter = request('q') || $fTahun || $fKelas;

    // Pilihan pendidikan (kolom pendidikan_ayah / pendidikan_ibu)
    $pendidikan = [
        'Tidak sekolah', 'SD / sederajat', 'SMP / sederajat', 'SMA / sederajat',
        'D1', 'D2', 'D3', 'D4 / S1', 'S2', 'S3',
    ];

    // Kolom yang dikirim ke modal saat Edit
    $kolomForm = [
        'siswa_id',
        'nama_ayah', 'nik_ayah', 'tahun_lahir_ayah', 'pendidikan_ayah', 'pekerjaan_ayah', 'penghasilan_ayah',
        'nama_ibu', 'nik_ibu', 'tahun_lahir_ibu', 'pendidikan_ibu', 'pekerjaan_ibu', 'penghasilan_ibu',
    ];
@endphp

@section('content')

    <link rel="stylesheet" href="{{ asset('css/master-pendukung.css') }}">

    <style>
        /* ---------- Filter ---------- */
        .ot-filter {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 10px;
            margin-bottom: 18px;
        }

        /* ---------- Modal ---------- */
        .ot-modal {
            position: fixed; inset: 0; background: rgba(16, 24, 40, .55);
            display: flex; align-items: center; justify-content: center;
            z-index: 1000; padding: 16px;
        }
        .ot-modal[hidden] { display: none; }
        .ot-modal-box {
            background: #fff; border-radius: 12px; padding: 22px; width: 100%;
            max-width: 760px; max-height: 92vh; overflow-y: auto;
        }
        .ot-modal-box h3 { margin: 0 0 14px; }
        .ot-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 6px; }
        .ot-grid fieldset { border: 1px solid #e4e7ec; border-radius: 10px; padding: 12px 14px; margin: 0; }
        .ot-grid legend { font-weight: 600; padding: 0 6px; }
        .ot-field { margin-bottom: 10px; display: flex; flex-direction: column; gap: 4px; }
        .ot-field label { font-size: 13px; font-weight: 500; }
        .ot-field input, .ot-field select {
            padding: 8px 10px; border: 1px solid #d0d5dd; border-radius: 8px;
            width: 100%; box-sizing: border-box;
        }
        .ot-field .req { color: #d92d20; }
        .ot-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; }

        /* ---------- Pencarian siswa ---------- */
        .ot-combo { position: relative; }
        .ot-combo-list {
            position: absolute; left: 0; right: 0; top: calc(100% + 4px);
            margin: 0; padding: 4px; list-style: none;
            background: #fff; border: 1px solid #d0d5dd; border-radius: 8px;
            box-shadow: 0 8px 24px rgba(16, 24, 40, .15);
            max-height: 220px; overflow-y: auto; z-index: 10;
        }
        .ot-combo-list[hidden] { display: none; }
        .ot-combo-list li { padding: 8px 10px; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .ot-combo-list li:hover { background: #eef2ff; }
        .ot-combo-list li.kosong { color: #98a2b3; cursor: default; }
        .ot-combo-list li.kosong:hover { background: transparent; }

        @media (max-width: 900px) { .ot-filter { grid-template-columns: 1fr; } }
        @media (max-width: 720px) { .ot-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="mp">

        @if($errors->any())
            <div class="alert alert-error">
                Data belum bisa disimpan. Periksa isian berikut:
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ============ PANEL DATA ORANG TUA ============ --}}
        <div class="tab-panel active" id="tab-orang-tua">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3>Data Orang Tua</h3>
                        <p>{{ number_format($orangTua->total(), 0, ',', '.') }} data orang tua ditemukan.</p>
                    </div>
                    <div class="btn-row">
                    </div>
                </div>

                <form method="GET" action="{{ url()->current() }}" class="ot-filter">
                    <input type="search" name="q" value="{{ request('q') }}"
                           placeholder="Kata kunci pencarian" aria-label="Cari orang tua">

                    <select name="tahun_id" id="ot-f-tahun" aria-label="Filter tahun pelajaran">
                        <option value="">Semua tahun pelajaran</option>
                        @foreach($tahunPelajaran as $tp)
                            <option value="{{ $tp->id }}" {{ $fTahun === (int) $tp->id ? 'selected' : '' }}>
                                {{ $tp->nama }}{{ $tp->status === 'aktif' ? ' (aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>

                    <select name="kelas_id" id="ot-f-kelas" aria-label="Filter kelas">
                        <option value="">Semua kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}"
                                    data-tahun="{{ $k->tahun_pelajaran_id }}"
                                    data-nama="{{ $k->nama_kelas }}"
                                    data-tahun-nama="{{ $k->tahun_pelajaran_nama }}"
                                    {{ $fKelas === (int) $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>

                    <div class="btn-row">
                        <button type="submit" class="btn btn-primary">Cari</button>
                        @if($adaFilter)
                            <a href="{{ url()->current() }}" class="btn btn-light">Reset</a>
                        @endif
                    </div>
                </form>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Ayah</th>
                                <th>Ibu</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orangTua as $i => $o)
                                <tr>
                                    <td>{{ $orangTua->firstItem() + $i }}</td>
                                    <td>
                                        <strong>{{ $o->siswa_nama ?? ('ID: ' . $o->siswa_id) }}</strong><br>
                                        <span class="muted">{{ $o->siswa_nis ?: '-' }}</span>
                                    </td>
                                    <td>{{ $o->kelas_nama ?: '-' }}</td>
                                    <td>
                                        <strong>{{ $o->nama_ayah ?: '-' }}</strong><br>
                                        <span class="muted">NIK: {{ $o->nik_ayah ?: '-' }}</span><br>
                                        <span class="muted">{{ collect([$o->tahun_lahir_ayah ? 'Lahir ' . $o->tahun_lahir_ayah : null, $o->pendidikan_ayah])->filter()->implode(' • ') ?: '-' }}</span><br>
                                        <span class="muted">{{ collect([$o->pekerjaan_ayah, $o->penghasilan_ayah])->filter()->implode(' • ') ?: '-' }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $o->nama_ibu ?: '-' }}</strong><br>
                                        <span class="muted">NIK: {{ $o->nik_ibu ?: '-' }}</span><br>
                                        <span class="muted">{{ collect([$o->tahun_lahir_ibu ? 'Lahir ' . $o->tahun_lahir_ibu : null, $o->pendidikan_ibu])->filter()->implode(' • ') ?: '-' }}</span><br>
                                        <span class="muted">{{ collect([$o->pekerjaan_ibu, $o->penghasilan_ibu])->filter()->implode(' • ') ?: '-' }}</span>
                                    </td>
                                    <td class="actions">
                                        <button type="button" class="btn btn-light btn-sm" data-ot-open
                                            data-title="Edit Orang Tua"
                                            data-action="{{ route('super-admin.orang-tua.update', $o->id) }}"
                                            data-method="PUT"
                                            data-values="{{ json_encode($o->only($kolomForm)) }}">
                                            Edit
                                        </button>
                                        <form class="inline-form" method="POST"
                                              action="{{ route('super-admin.orang-tua.destroy', $o->id) }}"
                                              onsubmit="return confirm('Hapus data orang tua {{ addslashes($o->siswa_nama ?? '') }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="empty">
                                        {{ $adaFilter
                                            ? 'Tidak ada data orang tua yang cocok dengan pencarian atau filter.'
                                            : 'Belum ada data orang tua. Gunakan "Tambah Data" untuk menambahkan.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($orangTua->hasPages())
                    <div class="pager">
                        <span>Halaman {{ $orangTua->currentPage() }} dari {{ $orangTua->lastPage() }}</span>
                        <span class="btn-row">
                            @if($orangTua->previousPageUrl())
                                <a class="btn btn-light btn-sm" href="{{ $orangTua->previousPageUrl() }}">&laquo; Sebelumnya</a>
                            @endif
                            @if($orangTua->nextPageUrl())
                                <a class="btn btn-light btn-sm" href="{{ $orangTua->nextPageUrl() }}">Berikutnya &raquo;</a>
                            @endif
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ MODAL TAMBAH / EDIT ============ --}}
        <div class="ot-modal" id="modal-orang-tua" hidden>
            <div class="ot-modal-box" role="dialog" aria-modal="true" aria-labelledby="ot-modal-title">
                <h3 id="ot-modal-title">Orang Tua</h3>

                <form method="POST" id="ot-form">
                    @csrf
                    <input type="hidden" name="_method" id="ot-method" value="POST">

                    {{-- Siswa (bisa dicari) --}}
                    <div class="ot-field">
                        <label for="ot-siswa-cari">Siswa <span class="req">*</span></label>
                        <div class="ot-combo">
                            <input type="hidden" name="siswa_id" id="ot-siswa">
                            <input type="text" id="ot-siswa-cari" autocomplete="off"
                                   placeholder="Ketik nama siswa untuk mencari...">
                            <ul class="ot-combo-list" id="ot-siswa-list" hidden></ul>
                        </div>
                    </div>

                    <div class="ot-grid">
                        @foreach(['ayah' => 'Ayah', 'ibu' => 'Ibu'] as $kode => $label)
                            <fieldset>
                                <legend>Data {{ $label }}</legend>

                                <div class="ot-field">
                                    <label>Nama {{ $label }}</label>
                                    <input type="text" name="nama_{{ $kode }}" maxlength="150">
                                </div>
                                <div class="ot-field">
                                    <label>NIK {{ $label }}</label>
                                    <input type="text" name="nik_{{ $kode }}" maxlength="20" inputmode="numeric">
                                </div>
                                <div class="ot-field">
                                    <label>Tahun Lahir {{ $label }}</label>
                                    <input type="number" name="tahun_lahir_{{ $kode }}"
                                           min="1900" max="{{ date('Y') }}" placeholder="Contoh: 1980">
                                </div>
                                <div class="ot-field">
                                    <label>Pendidikan {{ $label }}</label>
                                    <select name="pendidikan_{{ $kode }}">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pendidikan as $p)
                                            <option value="{{ $p }}">{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ot-field">
                                    <label>Pekerjaan {{ $label }}</label>
                                    <input type="text" name="pekerjaan_{{ $kode }}" maxlength="100"
                                        placeholder="Contoh: Petani, Wiraswasta, PNS">
                                </div>
                                <div class="ot-field">
                                    <label>Penghasilan {{ $label }}</label>
                                    <select name="penghasilan_{{ $kode }}">
                                        <option value="">-- Pilih --</option>
                                        @foreach(($referensi['penghasilan'] ?? []) as $r)
                                            <option value="{{ $r->nama }}">{{ $r->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </fieldset>
                        @endforeach
                    </div>

                    <div class="ot-actions">
                        <button type="button" class="btn btn-light" data-ot-close>Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection