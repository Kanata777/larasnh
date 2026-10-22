@extends('super-admin.layout')

@php
    // Berlaku untuk semua role: link mengikuti prefix role, tombol mengikuti hak akses role
    $to = fn(string $nama, $param = []) => \App\Support\RoleRoute::to($nama, $param);
    $ada = fn(string $nama) => \App\Support\RoleRoute::has($nama);
    $ubahStatus = $ubahStatus ?? false;
@endphp

@section('title', 'Detail Siswa')
@section('subtitle', 'Data lengkap siswa')

@section('content')

    @php
        $badge = ['Aktif' => 'badge-green', 'Pindah' => 'badge-blue', 'Lulus' => 'badge-orange', 'Nonaktif' => 'badge-gray'];
        $tgl = fn($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '-';
        $v = fn($key) => ($d[$key] ?? null) !== null && $d[$key] !== '' ? $d[$key] : '-';

        // Nilai dengan satuan, mis. "30 kg"; tetap "-" bila kosong
        $unit = fn($key, $satuan) => ($d[$key] ?? null) !== null && $d[$key] !== ''
            ? rtrim(rtrim(number_format((float) $d[$key], 7, ',', '.'), '0'), ',') . ' ' . $satuan
            : '-';

        // Kolom Ya/Tidak (disimpan 1/0)
        $ya = fn($key) => ($d[$key] ?? null) === null ? '-' : ((int) $d[$key] === 1 ? 'Ya' : 'Tidak');

        // Apakah salah satu kolom pada kelompok ini terisi?
        $ada_ = fn(array $keys) => collect($keys)->contains(fn($k) => filled($d[$k] ?? null));

        $adaWali = $ada_(['nama_wali', 'tahun_lahir_wali', 'pendidikan_wali', 'nik_wali', 'pekerjaan_wali', 'penghasilan_wali']);
        $adaBantuan = $ada_([
            'penerima_kps',
            'no_kps',
            'penerima_kip',
            'no_kip',
            'nama_di_kip',
            'no_kks',
            'layak_pip',
            'alasan_layak_pip',
            'bank',
            'no_rekening',
            'rekening_atas_nama'
        ]);

        $rtrw = collect([$d['rt'] ?? null, $d['rw'] ?? null]);
        $rtrwTeks = $rtrw->filter()->isEmpty() ? '-' : 'RT ' . ($d['rt'] ?? '-') . ' / RW ' . ($d['rw'] ?? '-');
        $koordinat = filled($d['lintang'] ?? null) && filled($d['bujur'] ?? null)
            ? rtrim(rtrim((string) $d['lintang'], '0'), '.') . ', ' . rtrim(rtrim((string) $d['bujur'], '0'), '.')
            : '-';

        $urlExport = route('super-admin.siswa.export', $d['id']);

        // Tombol di kepala tiap bagian: Edit + Excel + PDF (khusus bagian itu saja)
        $tools = function (string $key, string $nama, bool $bisaEdit = true) use ($urlExport, $ada, $to, $d) {
            $html = '<div class="pane-tools">';
            if ($bisaEdit && $ada('siswa.edit')) {
                $html .= '<a class="btn btn-light" href="' . e($to('siswa.edit', $d['id'])) . '#' . $key . '" title="Ubah data ' . e($nama) . '">✏ Edit</a>';
            }
            $html .= '<a class="btn btn-light" href="' . e($urlExport) . '?bagian=' . $key . '" title="Unduh ' . e($nama) . ' sebagai Excel">⬇ Excel</a>';
            $html .= '<a class="btn btn-light" target="_blank" href="' . e($urlExport) . '?format=pdf&bagian=' . $key . '" title="Unduh ' . e($nama) . ' sebagai PDF">⬇ PDF</a>';
            return $html . '</div>';
        };
    @endphp

    <style>
        /* ===== Profil (ringkas) ===== */
        .panel.panel-profile {
            padding: 8px 12px !important;
            margin-bottom: 12px !important;
            min-height: 0 !important;
            overflow: visible;
        }

        .panel.panel-profile .panel-head.profile-head {
            flex-wrap: wrap !important;
            margin: 0 !important;
            padding: 0 !important;
            border-bottom: 0 !important;
            min-height: 0 !important;
            gap: 8px 12px;
            align-items: center;
        }

        .profile {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: nowrap;
        }

        .profile img,
        .profile .noimg {
            width: 72px;
            aspect-ratio: 3 / 4;
            height: auto;
            flex-shrink: 0;
            object-fit: cover;
            object-position: center top;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #eef3f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .profile h3 {
            font-size: 16px;
            line-height: 1.2;
            margin: 0 0 2px;
        }

        .profile p {
            font-size: 12px;
            line-height: 1.2;
            margin: 0 0 4px;
        }

        .profile .badge {
            font-size: 10px;
            padding: 1px 6px;
        }

        /* Tombol utama di header: hanya 3-4 tombol */
        .profile-head .btn-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-left: auto;
            align-items: center;
        }

        .profile-head .btn-row .btn,
        .export-menu > summary {
            font-size: 12.5px;
            padding: 6px 12px;
            line-height: 1.2;
        }

        /* ===== Menu Export (pilih bagian, sekaligus) ===== */
        .export-menu {
            position: relative;
        }

        .export-menu > summary {
            list-style: none;
            cursor: pointer;
            border: 1px solid var(--primary, #2563eb);
            background: var(--primary, #2563eb);
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
            user-select: none;
        }

        .export-menu > summary::-webkit-details-marker {
            display: none;
        }

        .export-menu[open] > summary {
            filter: brightness(.92);
        }

        .export-panel {
            position: absolute;
            right: 0;
            top: calc(100% + 6px);
            z-index: 50;
            width: 300px;
            max-width: 90vw;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(15, 30, 60, .15);
            padding: 14px;
        }

        .export-panel h4 {
            margin: 0 0 2px;
            font-size: 14px;
        }

        .export-panel .hint {
            margin: 0 0 10px;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.4;
        }

        .export-panel .export-checks {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 10px;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .export-panel .export-checks label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .export-panel .export-all {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            padding: 8px 0;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 600;
        }

        .export-panel .export-all label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .export-panel .export-count {
            font-weight: 400;
            color: var(--muted);
            font-size: 12px;
        }

        .export-panel .export-actions {
            display: flex;
            gap: 8px;
        }

        .export-panel .export-actions .btn {
            flex: 1;
            text-align: center;
            font-size: 12.5px;
            padding: 7px 10px;
        }

        .export-panel .export-actions .btn[aria-disabled="true"] {
            opacity: .4;
            pointer-events: none;
        }

        /* ===== Kartu tab horizontal + panah ===== */
        .tabs-wrap {
            display: flex;
            align-items: stretch;
            gap: 8px;
            margin-bottom: 16px;
        }

        .tabs {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-wrap: nowrap;
            gap: 10px;
            overflow-x: auto;
            scroll-behavior: smooth;
            scrollbar-width: none;
        }

        .tabs::-webkit-scrollbar {
            display: none;
        }

        .tabs .tab {
            flex: 0 0 170px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
            padding: 12px 14px;
            text-align: left;
            font: inherit;
            color: inherit;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }

        .tabs .tab:hover {
            border-color: #b6c4d8;
        }

        .tabs .tab .icon {
            font-size: 22px;
            line-height: 1;
            margin-bottom: 4px;
        }

        .tabs .tab strong {
            font-size: 14px;
        }

        .tabs .tab .desc {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.3;
        }

        .tabs .tab.active {
            border-color: var(--primary, #2563eb);
            background: #eff5ff;
            box-shadow: 0 0 0 1px var(--primary, #2563eb);
        }

        .tabs-arrow {
            flex: 0 0 34px;
            border: 1px solid var(--line);
            background: #fff;
            border-radius: 12px;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
            color: inherit;
        }

        .tabs-arrow:hover:not([disabled]) {
            border-color: #b6c4d8;
            background: #f5f8fc;
        }

        .tabs-arrow[disabled] {
            opacity: .35;
            cursor: default;
        }

        .tabs-wrap.no-overflow .tabs-arrow {
            display: none;
        }

        /* ===== Isi halaman ===== */
        .tab-pane[hidden] {
            display: none !important;
        }

        .tab-pane {
            padding: 24px 32px 28px;
            min-height: 420px;
        }

        .tab-pane .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px 16px;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--line);
        }

        .tab-pane .panel-head h3 {
            font-size: 22px;
            margin: 0;
        }

        /* Tombol per bagian */
        .pane-tools {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .pane-tools .btn {
            font-size: 12.5px;
            padding: 6px 12px;
            line-height: 1.2;
        }

        dl.info {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 16px 24px;
            font-size: 15px;
            line-height: 1.5;
        }

        dl.info dt {
            color: var(--muted);
        }

        dl.info dd {
            word-break: break-word;
            font-weight: 500;
        }

        .tab-pane .table-wrap table {
            font-size: 15px;
        }

        .tab-pane .table-wrap th,
        .tab-pane .table-wrap td {
            padding: 14px 16px;
        }

        /* Layar lebar: isi ditampilkan 2 kolom */
        @media (min-width: 1200px) {
            dl.info {
                grid-template-columns: 200px 1fr 200px 1fr;
            }
        }

        /* ===== HP ===== */
        @media (max-width: 600px) {
            .tab-pane {
                padding: 18px 16px;
                min-height: 0;
            }

            dl.info {
                grid-template-columns: 1fr;
                gap: 2px 0;
            }

            dl.info dd {
                margin-bottom: 12px;
            }

            .tabs .tab {
                flex-basis: 130px;
                padding: 10px 12px;
            }

            .tabs .tab .desc {
                display: none;
            }

            .tabs-arrow {
                flex-basis: 30px;
            }

            .profile {
                flex-wrap: wrap;
            }

            .export-panel {
                position: fixed;
                left: 12px;
                right: 12px;
                top: auto;
                bottom: 12px;
                width: auto;
                max-width: none;
            }
        }
    </style>

    {{-- ===== Header: profil + 3-4 tombol utama ===== --}}
    <div class="panel panel-profile">
        <div class="panel-head profile-head">
            <div class="profile">
                @if($fotoUrl)
                    <img src="{{ $fotoUrl }}" alt="Foto {{ $d['nama_lengkap'] }}">
                @else
                    <div class="noimg" aria-hidden="true">🎓</div>
                @endif
                <div>
                    <h3>{{ $d['nama_lengkap'] }}</h3>
                    <p class="muted">NIPD {{ $v('nis') }} &nbsp;|&nbsp; NISN {{ $v('nisn') }}</p>
                    <span class="badge {{ $badge[$d['status_siswa']] ?? 'badge-gray' }}">{{ $d['status_siswa'] }}</span>
                </div>
            </div>

            <div class="btn-row">
                {{-- Export banyak bagian sekaligus --}}
                <details class="export-menu" id="export-menu">
                    <summary title="Unduh beberapa bagian atau semua data sekaligus">⬇ Export</summary>
                    <div class="export-panel">
                        <h4>Export data siswa</h4>
                        <p class="hint">Pilih bagian yang ingin diunduh, lalu pilih format. Semua bagian terpilih
                            akan digabung dalam satu file.</p>

                        <div class="export-all">
                            <label><input type="checkbox" id="exp-all" checked> Pilih semua</label>
                            <span class="export-count" id="exp-count"></span>
                        </div>

                        <div class="export-checks" id="exp-checks"></div>

                        <div class="export-actions">
                            <a class="btn btn-light" id="exp-xlsx" href="{{ $urlExport }}">⬇ Excel</a>
                            <a class="btn btn-light" id="exp-pdf" target="_blank"
                                href="{{ $urlExport }}?format=pdf">⬇ PDF</a>
                        </div>
                    </div>
                </details>

                @if($ubahStatus)
                    @if($d['status_siswa'] === 'Aktif')
                        <button type="button" class="btn btn-danger" data-nonaktif data-nama="{{ $d['nama_lengkap'] }}"
                            data-action="{{ $to('siswa.status', $d['id']) }}">Nonaktifkan</button>
                    @else
                        <form class="inline-form" method="POST" action="{{ $to('siswa.status', $d['id']) }}"
                            onsubmit="return confirm('Aktifkan kembali siswa ini?')">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status_siswa" value="Aktif">
                            <button type="submit" class="btn btn-light">Aktifkan kembali</button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="tabs-wrap" id="tabs-wrap">
        <button type="button" class="tabs-arrow" id="tabs-left" aria-label="Geser ke kiri">&lsaquo;</button>
        <div class="tabs" id="pager-nav" role="tablist"></div>
        <button type="button" class="tabs-arrow" id="tabs-right" aria-label="Geser ke kanan">&rsaquo;</button>
    </div>

    <div id="pager-body">

        <div class="panel tab-pane" data-key="biodata" data-icon="👤" data-title="Biodata"
            data-desc="Identitas dan data diri">
            <div class="panel-head">
                <h3>Biodata</h3>
                {!! $tools('biodata', 'Biodata') !!}
            </div>
            <dl class="info">
                <dt>Jenis kelamin</dt>
                <dd>{{ ($d['jenis_kelamin'] ?? '') === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                <dt>NIK</dt>
                <dd>{{ $v('nik') }}</dd>
                <dt>No. KK</dt>
                <dd>{{ $v('no_kk') }}</dd>
                <dt>Tempat lahir</dt>
                <dd>{{ $v('tempat_lahir') }}</dd>
                <dt>Tanggal lahir</dt>
                <dd>{{ $tgl($d['tanggal_lahir'] ?? null) }}</dd>
                <dt>Agama</dt>
                <dd>{{ $v('agama') }}</dd>
                <dt>No. registrasi akta lahir</dt>
                <dd>{{ $v('no_akta_lahir') }}</dd>
                <dt>Anak ke-</dt>
                <dd>{{ $v('anak_ke') }}</dd>
                <dt>Jumlah saudara kandung</dt>
                <dd>{{ $v('jumlah_saudara') }}</dd>
                <dt>Kebutuhan khusus</dt>
                <dd>{{ $v('kebutuhan_khusus') }}</dd>
                <dt>Asal sekolah</dt>
                <dd>{{ $v('asal_sekolah') }}</dd>
                <dt>Tanggal masuk</dt>
                <dd>{{ $tgl($d['tanggal_masuk'] ?? null) }}</dd>
            </dl>
        </div>

        <div class="panel tab-pane" data-key="fisik" data-icon="📏" data-title="Fisik &amp; Dokumen"
            data-desc="Berat, tinggi, ijazah">
            <div class="panel-head">
                <h3>Data Fisik &amp; Dokumen</h3>
                {!! $tools('fisik', 'Data Fisik & Dokumen') !!}
            </div>
            <dl class="info">
                <dt>Berat badan</dt>
                <dd>{{ $unit('berat_badan', 'kg') }}</dd>
                <dt>Tinggi badan</dt>
                <dd>{{ $unit('tinggi_badan', 'cm') }}</dd>
                <dt>Lingkar kepala</dt>
                <dd>{{ $unit('lingkar_kepala', 'cm') }}</dd>
                <dt>No. peserta ujian nasional</dt>
                <dd>{{ $v('no_peserta_un') }}</dd>
                <dt>No. seri ijazah</dt>
                <dd>{{ $v('no_seri_ijazah') }}</dd>
                <dt>SKHUN</dt>
                <dd>{{ $v('skhun') }}</dd>
            </dl>
        </div>

        <div class="panel tab-pane" data-key="ayah" data-icon="👨" data-title="Ayah" data-desc="Data ayah siswa">
            <div class="panel-head">
                <h3>Ayah</h3>
                {!! $tools('ayah', 'data Ayah') !!}
            </div>
            <dl class="info">
                <dt>Nama</dt>
                <dd>{{ $v('nama_ayah') }}</dd>
                <dt>NIK</dt>
                <dd>{{ $v('nik_ayah') }}</dd>
                <dt>Tahun lahir</dt>
                <dd>{{ $v('tahun_lahir_ayah') }}</dd>
                <dt>Pendidikan</dt>
                <dd>{{ $v('pendidikan_ayah') }}</dd>
                <dt>Pekerjaan</dt>
                <dd>{{ $v('pekerjaan_ayah') }}</dd>
                <dt>Penghasilan</dt>
                <dd>{{ $v('penghasilan_ayah') }}</dd>
            </dl>
        </div>

        <div class="panel tab-pane" data-key="ibu" data-icon="👩" data-title="Ibu" data-desc="Data ibu siswa">
            <div class="panel-head">
                <h3>Ibu</h3>
                {!! $tools('ibu', 'data Ibu') !!}
            </div>
            <dl class="info">
                <dt>Nama</dt>
                <dd>{{ $v('nama_ibu') }}</dd>
                <dt>NIK</dt>
                <dd>{{ $v('nik_ibu') }}</dd>
                <dt>Tahun lahir</dt>
                <dd>{{ $v('tahun_lahir_ibu') }}</dd>
                <dt>Pendidikan</dt>
                <dd>{{ $v('pendidikan_ibu') }}</dd>
                <dt>Pekerjaan</dt>
                <dd>{{ $v('pekerjaan_ibu') }}</dd>
                <dt>Penghasilan</dt>
                <dd>{{ $v('penghasilan_ibu') }}</dd>
            </dl>
        </div>

        @if($adaWali)
            <div class="panel tab-pane" data-key="wali" data-icon="🧑‍🤝‍🧑" data-title="Wali" data-desc="Data wali siswa">
                <div class="panel-head">
                    <h3>Wali</h3>
                    {!! $tools('wali', 'data Wali') !!}
                </div>
                <dl class="info">
                    <dt>Nama</dt>
                    <dd>{{ $v('nama_wali') }}</dd>
                    <dt>NIK</dt>
                    <dd>{{ $v('nik_wali') }}</dd>
                    <dt>Tahun lahir</dt>
                    <dd>{{ $v('tahun_lahir_wali') }}</dd>
                    <dt>Pendidikan</dt>
                    <dd>{{ $v('pendidikan_wali') }}</dd>
                    <dt>Pekerjaan</dt>
                    <dd>{{ $v('pekerjaan_wali') }}</dd>
                    <dt>Penghasilan</dt>
                    <dd>{{ $v('penghasilan_wali') }}</dd>
                </dl>
            </div>
        @endif

        <div class="panel tab-pane" data-key="alamat" data-icon="🏠" data-title="Alamat" data-desc="Alamat dan lokasi">
            <div class="panel-head">
                <h3>Alamat</h3>
                {!! $tools('alamat', 'Alamat') !!}
            </div>
            <dl class="info">
                <dt>Alamat lengkap</dt>
                <dd>{{ $v('alamat') }}</dd>
                <dt>RT / RW</dt>
                <dd>{{ $rtrwTeks }}</dd>
                <dt>Dusun</dt>
                <dd>{{ $v('dusun') }}</dd>
                <dt>Desa / kelurahan</dt>
                <dd>{{ $v('desa_kelurahan') }}</dd>
                <dt>Kecamatan</dt>
                <dd>{{ $v('kecamatan') }}</dd>
                <dt>Kabupaten / kota</dt>
                <dd>{{ $v('kabupaten_kota') }}</dd>
                <dt>Provinsi</dt>
                <dd>{{ $v('provinsi') }}</dd>
                <dt>Kode pos</dt>
                <dd>{{ $v('kode_pos') }}</dd>
                <dt>Jenis tinggal</dt>
                <dd>{{ $v('jenis_tinggal') }}</dd>
                <dt>Alat transportasi</dt>
                <dd>{{ $v('alat_transportasi') }}</dd>
                <dt>Jarak ke sekolah</dt>
                <dd>{{ $unit('jarak_ke_sekolah', 'km') }}</dd>
                <dt>Koordinat (lintang, bujur)</dt>
                <dd>{{ $koordinat }}</dd>
            </dl>
        </div>

        <div class="panel tab-pane" data-key="kontak" data-icon="📞" data-title="Kontak" data-desc="Telepon dan email">
            <div class="panel-head">
                <h3>Kontak</h3>
                {!! $tools('kontak', 'Kontak') !!}
            </div>
            <dl class="info">
                <dt>Telepon rumah</dt>
                <dd>{{ $v('telepon_rumah') }}</dd>
                <dt>No. HP siswa</dt>
                <dd>{{ $v('no_hp_siswa') }}</dd>
                <dt>Email siswa</dt>
                <dd>{{ $v('email_siswa') }}</dd>
            </dl>
        </div>

        @if($adaBantuan)
            <div class="panel tab-pane" data-key="bantuan" data-icon="💳" data-title="Bantuan &amp; Rekening"
                data-desc="KPS, KIP, PIP, rekening">
                <div class="panel-head">
                    <h3>Bantuan &amp; Rekening</h3>
                    {!! $tools('bantuan', 'Bantuan & Rekening') !!}
                </div>
                <dl class="info">
                    <dt>Penerima KPS</dt>
                    <dd>{{ $ya('penerima_kps') }}</dd>
                    <dt>No. KPS</dt>
                    <dd>{{ $v('no_kps') }}</dd>
                    <dt>Penerima KIP</dt>
                    <dd>{{ $ya('penerima_kip') }}</dd>
                    <dt>Nomor KIP</dt>
                    <dd>{{ $v('no_kip') }}</dd>
                    <dt>Nama di KIP</dt>
                    <dd>{{ $v('nama_di_kip') }}</dd>
                    <dt>Nomor KKS</dt>
                    <dd>{{ $v('no_kks') }}</dd>
                    <dt>Layak PIP</dt>
                    <dd>{{ $ya('layak_pip') }}</dd>
                    <dt>Alasan layak PIP</dt>
                    <dd>{{ $v('alasan_layak_pip') }}</dd>
                    <dt>Bank</dt>
                    <dd>{{ $v('bank') }}</dd>
                    <dt>Nomor rekening</dt>
                    <dd>{{ $v('no_rekening') }}</dd>
                    <dt>Rekening atas nama</dt>
                    <dd>{{ $v('rekening_atas_nama') }}</dd>
                </dl>
            </div>
        @endif

        <div class="panel tab-pane" data-key="riwayat" data-icon="🏫" data-title="Riwayat Kelas"
            data-desc="Kelas tiap tahun pelajaran">
            <div class="panel-head">
                <h3>Riwayat Kelas</h3>
                {{-- Riwayat kelas tidak diedit dari form siswa, jadi tanpa tombol Edit --}}
                {!! $tools('riwayat', 'Riwayat Kelas', false) !!}
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tahun Pelajaran</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $r)
                            <tr>
                                <td>{{ $r->tahun_pelajaran }}</td>
                                <td><strong>{{ $r->nama_kelas }}</strong></td>
                                <td><span class="badge {{ $badge[$r->status] ?? 'badge-gray' }}">{{ $r->status }}</span></td>
                                <td>{{ $r->catatan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty">Siswa belum ditempatkan di kelas mana pun.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        (function () {
            const panes = Array.from(document.querySelectorAll('#pager-body .tab-pane'));
            const nav = document.getElementById('pager-nav');
            const wrap = document.getElementById('tabs-wrap');
            const btnL = document.getElementById('tabs-left');
            const btnR = document.getElementById('tabs-right');
            let cur = 0;

            /* ---------- Tab ---------- */
            panes.forEach(function (p, i) {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'tab';
                b.setAttribute('role', 'tab');

                const icon = document.createElement('span');
                icon.className = 'icon';
                icon.textContent = p.dataset.icon || '📄';

                const title = document.createElement('strong');
                title.textContent = p.dataset.title;

                const desc = document.createElement('span');
                desc.className = 'desc';
                desc.textContent = p.dataset.desc || '';

                b.append(icon, title, desc);
                b.addEventListener('click', function () { show(i); });
                nav.appendChild(b);
            });

            function updateArrows() {
                btnL.disabled = nav.scrollLeft <= 2;
                btnR.disabled = nav.scrollLeft + nav.clientWidth >= nav.scrollWidth - 2;
                wrap.classList.toggle('no-overflow', nav.scrollWidth <= nav.clientWidth + 2);
            }

            btnL.addEventListener('click', function () { nav.scrollBy({ left: -nav.clientWidth * 0.7 }); });
            btnR.addEventListener('click', function () { nav.scrollBy({ left: nav.clientWidth * 0.7 }); });
            nav.addEventListener('scroll', updateArrows);
            window.addEventListener('resize', updateArrows);

            function show(i) {
                cur = Math.max(0, Math.min(i, panes.length - 1));
                const key = panes[cur].dataset.key;

                panes.forEach(function (p, j) { p.hidden = j !== cur; });
                Array.from(nav.children).forEach(function (b, j) {
                    b.classList.toggle('active', j === cur);
                    b.setAttribute('aria-selected', j === cur);
                });

                history.replaceState(null, '', '#' + key);
                nav.children[cur].scrollIntoView({ block: 'nearest', inline: 'center' });
                updateArrows();
            }

            const start = panes.findIndex(function (p) { return '#' + p.dataset.key === location.hash; });
            show(start >= 0 ? start : 0);

            /* ---------- Menu Export (banyak bagian sekaligus) ---------- */
            const menu = document.getElementById('export-menu');
            const checks = document.getElementById('exp-checks');
            const all = document.getElementById('exp-all');
            const count = document.getElementById('exp-count');
            const aXlsx = document.getElementById('exp-xlsx');
            const aPdf = document.getElementById('exp-pdf');
            const base = @json($urlExport);

            panes.forEach(function (p) {
                const label = document.createElement('label');
                const cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.value = p.dataset.key;
                cb.checked = true;
                cb.addEventListener('change', refresh);
                label.append(cb, document.createTextNode(' ' + p.dataset.title));
                checks.appendChild(label);
            });

            function selected() {
                return Array.from(checks.querySelectorAll('input:checked')).map(function (c) { return c.value; });
            }

            function refresh() {
                const sel = selected();
                const total = panes.length;

                all.checked = sel.length === total;
                all.indeterminate = sel.length > 0 && sel.length < total;
                count.textContent = sel.length + ' dari ' + total + ' bagian';

                // Semua terpilih -> tanpa parameter "bagian" (export lengkap)
                const bagian = sel.length === total ? '' : '&bagian=' + sel.join(',');
                aXlsx.href = sel.length === total ? base : base + '?' + bagian.substring(1);
                aPdf.href = base + '?format=pdf' + bagian;

                [aXlsx, aPdf].forEach(function (a) {
                    a.setAttribute('aria-disabled', sel.length === 0 ? 'true' : 'false');
                });
            }

            all.addEventListener('change', function () {
                checks.querySelectorAll('input').forEach(function (c) { c.checked = all.checked; });
                refresh();
            });

            // Tutup menu setelah mengunduh atau klik di luar
            [aXlsx, aPdf].forEach(function (a) {
                a.addEventListener('click', function () { setTimeout(function () { menu.open = false; }, 100); });
            });
            document.addEventListener('click', function (e) {
                if (menu.open && !menu.contains(e.target)) menu.open = false;
            });

            refresh();
        })();
    </script>

    @if($ubahStatus)
        @include('super-admin.siswa._nonaktif_modal')
    @endif

@endsection