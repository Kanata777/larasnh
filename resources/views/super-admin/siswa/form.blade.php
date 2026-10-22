@extends('super-admin.layout')

@php
    $edit = !empty($siswaId);
    $statusList = array_combine(\App\Support\SiswaData::STATUS, \App\Support\SiswaData::STATUS);

    $kelasTerpilih = old('kelas_id', $data['kelas_id'] ?? null);
    $tahunTerpilih = optional($kelasList->firstWhere('id', $kelasTerpilih))->tahun_pelajaran_id
        ?? $tahunAktifId;

    /* Opsi pilihan mengikuti isian standar Dapodik */
    $opt = fn(array $a) => array_combine($a, $a);
    $agamaList = $opt(['Islam', 'Kristen', 'Katholik', 'Hindu', 'Budha', 'Konghucu', 'Kepercayaan kpd Tuhan YME', 'Lainnya']);
    $jenisTinggalList = $opt(['Bersama orang tua', 'Wali', 'Kos', 'Asrama', 'Panti Asuhan', 'Pesantren', 'Lainnya']);
    $transportList = $opt(['Jalan kaki', 'Sepeda', 'Sepeda motor', 'Mobil pribadi', 'Angkutan umum/bus/pete-pete', 'Ojek', 'Andong/bendi/sado/dokar/delman/becak', 'Perahu penyeberangan/rakit/getek', 'Kereta api', 'Lainnya']);
    $jenjangList = $opt(['Tidak sekolah', 'Putus SD', 'SD / sederajat', 'SMP / sederajat', 'SMA / sederajat', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3']);
    $yaTidak = ['Ya' => 'Ya', 'Tidak' => 'Tidak'];
@endphp

@section('title', $edit ? 'Edit Data Siswa' : 'Tambah Siswa')
@section('subtitle', $edit ? 'Ubah data siswa, lalu simpan' : 'Isi biodata, orang tua, alamat, kontak, dan kelas siswa')

@section('content')

    <style>
        .tab-pane[hidden] {
            display: none !important;
        }

        .tab-pane {
            min-height: 360px;
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
            flex: 0 0 38px;
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

        .pager-foot {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 8px;
        }

        .pager-foot .right {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        @media (max-width: 600px) {
            .tab-pane {
                min-height: 0;
            }

            .tabs .tab {
                flex-basis: 130px;
                padding: 10px 12px;
            }

            .tabs .tab .desc {
                display: none;
            }

            .tabs-arrow {
                flex-basis: 32px;
            }
        }
    </style>

    <form id="form-siswa" method="POST" enctype="multipart/form-data"
        action="{{ $edit ? route('super-admin.siswa.update', $siswaId) : route('super-admin.siswa.store') }}">
        @csrf
        @if($edit) @method('PUT') @endif

        <input type="hidden" name="bagian" id="bagian-aktif" value="">

        <div class="tabs-wrap" id="tabs-wrap">
            <button type="button" class="tabs-arrow" id="tabs-left" aria-label="Geser ke kiri">&lsaquo;</button>
            <div class="tabs" id="pager-nav" role="tablist"></div>
            <button type="button" class="tabs-arrow" id="tabs-right" aria-label="Geser ke kanan">&rsaquo;</button>
        </div>

        <div id="pager-body">

            {{-- ===== 1. Biodata ===== --}}
            <div class="panel tab-pane" data-key="biodata" data-icon="👤" data-title="Biodata"
                data-desc="Identitas dan data diri">
                <div class="panel-head">
                    <div>
                        <h3>Biodata Siswa</h3>
                        <p>Kolom bertanda <span class="req">*</span> wajib diisi.</p>
                    </div>
                </div>

                <div class="form-grid">
                    @include('super-admin.siswa._field', ['name' => 'nis', 'label' => 'NIS', 'required' => true, 'max' => 20, 'inputmode' => 'numeric'])
                    @include('super-admin.siswa._field', ['name' => 'nisn', 'label' => 'NISN', 'max' => 10, 'inputmode' => 'numeric', 'placeholder' => '10 digit angka'])
                    @include('super-admin.siswa._field', ['name' => 'nama_lengkap', 'label' => 'Nama lengkap', 'required' => true, 'max' => 150, 'full' => true])
                    @include('super-admin.siswa._field', ['name' => 'jenis_kelamin', 'label' => 'Jenis kelamin', 'type' => 'select', 'required' => true, 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']])
                    @include('super-admin.siswa._field', ['name' => 'status_siswa', 'label' => 'Status siswa', 'type' => 'select', 'required' => true, 'options' => $statusList])
                    @include('super-admin.siswa._field', ['name' => 'tempat_lahir', 'label' => 'Tempat lahir', 'max' => 100])
                    @include('super-admin.siswa._field', ['name' => 'tanggal_lahir', 'label' => 'Tanggal lahir', 'type' => 'date'])
                    @include('super-admin.siswa._field', ['name' => 'nik', 'label' => 'NIK', 'max' => 16, 'inputmode' => 'numeric', 'placeholder' => '16 digit angka'])
                    @include('super-admin.siswa._field', ['name' => 'no_kk', 'label' => 'No KK', 'max' => 16, 'inputmode' => 'numeric', 'placeholder' => '16 digit angka'])
                    @include('super-admin.siswa._field', ['name' => 'agama', 'label' => 'Agama', 'type' => 'select', 'options' => $agamaList])
                    @include('super-admin.siswa._field', ['name' => 'no_registrasi_akta_lahir', 'label' => 'No Registrasi Akta Lahir', 'max' => 50])
                    @include('super-admin.siswa._field', ['name' => 'anak_ke', 'label' => 'Anak ke-berapa', 'type' => 'number', 'inputmode' => 'numeric'])
                    @include('super-admin.siswa._field', ['name' => 'jumlah_saudara_kandung', 'label' => 'Jml. saudara kandung', 'type' => 'number', 'inputmode' => 'numeric'])
                    @include('super-admin.siswa._field', ['name' => 'kebutuhan_khusus', 'label' => 'Kebutuhan khusus', 'max' => 100, 'placeholder' => 'Tidak ada'])
                    @include('super-admin.siswa._field', ['name' => 'asal_sekolah', 'label' => 'Asal sekolah', 'max' => 150, 'list' => 'list-asal-sekolah'])
                    @include('super-admin.siswa._field', ['name' => 'tanggal_masuk', 'label' => 'Tanggal masuk', 'type' => 'date'])

                    {{-- Kelas --}}
                    <div class="field">
                        <label for="f-tahun">Tahun pelajaran</label>
                        <select id="f-tahun">
                            @foreach($tahunOptions as $tid => $tnama)
                                <option value="{{ $tid }}" {{ (string) $tahunTerpilih === (string) $tid ? 'selected' : '' }}>
                                    {{ $tnama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="f-kelas_id">Kelas</label>
                        <select id="f-kelas_id" name="kelas_id" class="{{ $errors->has('kelas_id') ? 'invalid' : '' }}">
                            <option value="">Belum ditempatkan di kelas</option>
                            @foreach($kelasList as $k)
                                <option value="{{ $k->id }}" data-tahun="{{ $k->tahun_pelajaran_id }}"
                                    {{ (string) $kelasTerpilih === (string) $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id') <small class="err">{{ $message }}</small> @enderror
                    </div>

                    @include('super-admin.siswa._field', ['name' => 'catatan', 'label' => 'Catatan kelas', 'max' => 255, 'full' => true])

                    <div class="field full">
                        <label for="f-foto">Foto siswa</label>
                        <div style="display:flex;align-items:center;gap:16px;">
                            <img id="foto-preview" alt="Pratinjau foto" src="{{ $fotoUrl ?? '' }}"
                                style="width:84px;aspect-ratio:3/4;object-fit:cover;object-position:center top;border-radius:8px;border:1px solid var(--line);background:#eef3f9;{{ empty($fotoUrl) ? 'display:none;' : '' }}">
                            <div style="flex:1;">
                                <input type="file" id="f-foto" name="foto" accept="image/*">
                                <p class="muted">Format JPG atau PNG, maksimal 2
                                    MB.{{ $edit ? ' Kosongkan jika tidak ingin mengganti foto.' : '' }}</p>
                            </div>
                        </div>
                        @error('foto') <small class="err">{{ $message }}</small> @enderror
                    </div>
                </div>
            </div>

            {{-- ===== 2. Data fisik & dokumen ===== --}}
            <div class="panel tab-pane" data-key="fisik" data-icon="📏" data-title="Fisik &amp; Dokumen"
                data-desc="Berat, tinggi, ijazah">
                <div class="panel-head">
                    <div>
                        <h3>Data Fisik &amp; Dokumen</h3>
                    </div>
                </div>

                <div class="form-grid">
                    @include('super-admin.siswa._field', ['name' => 'berat_badan', 'label' => 'Berat badan (kg)', 'type' => 'number', 'step' => '0.1', 'inputmode' => 'decimal'])
                    @include('super-admin.siswa._field', ['name' => 'tinggi_badan', 'label' => 'Tinggi badan (cm)', 'type' => 'number', 'step' => '0.1', 'inputmode' => 'decimal'])
                    @include('super-admin.siswa._field', ['name' => 'lingkar_kepala', 'label' => 'Lingkar kepala (cm)', 'type' => 'number', 'step' => '0.1', 'inputmode' => 'decimal'])
                    @include('super-admin.siswa._field', ['name' => 'no_peserta_un', 'label' => 'No Peserta Ujian Nasional', 'max' => 50])
                    @include('super-admin.siswa._field', ['name' => 'no_seri_ijazah', 'label' => 'No Seri Ijazah', 'max' => 50])
                    @include('super-admin.siswa._field', ['name' => 'skhun', 'label' => 'SKHUN', 'max' => 50])
                </div>
            </div>

            {{-- ===== 3. Ayah ===== --}}
            <div class="panel tab-pane" data-key="ayah" data-icon="👨" data-title="Ayah" data-desc="Data ayah siswa">
                <div class="panel-head">
                    <div>
                        <h3>Data Ayah</h3>
                    </div>
                </div>

                <div class="form-grid">
                    @include('super-admin.siswa._field', ['name' => 'nama_ayah', 'label' => 'Nama ayah', 'max' => 150])
                    @include('super-admin.siswa._field', ['name' => 'ayah_nik', 'label' => 'NIK ayah', 'max' => 16, 'inputmode' => 'numeric'])
                    @include('super-admin.siswa._field', ['name' => 'ayah_tahun_lahir', 'label' => 'Tahun lahir ayah', 'max' => 4, 'inputmode' => 'numeric'])
                    @include('super-admin.siswa._field', ['name' => 'ayah_jenjang_pendidikan', 'label' => 'Jenjang pendidikan ayah', 'type' => 'select', 'options' => $jenjangList])
                    @include('super-admin.siswa._field', ['name' => 'pekerjaan_ayah', 'label' => 'Pekerjaan ayah', 'max' => 100, 'list' => 'list-pekerjaan'])
                    @include('super-admin.siswa._field', ['name' => 'penghasilan_ayah', 'label' => 'Penghasilan ayah', 'max' => 100, 'list' => 'list-penghasilan'])
                </div>
            </div>

            {{-- ===== 4. Ibu ===== --}}
            <div class="panel tab-pane" data-key="ibu" data-icon="👩" data-title="Ibu" data-desc="Data ibu siswa">
                <div class="panel-head">
                    <div>
                        <h3>Data Ibu</h3>
                    </div>
                </div>

                <div class="form-grid">
                    @include('super-admin.siswa._field', ['name' => 'nama_ibu', 'label' => 'Nama ibu', 'max' => 150])
                    @include('super-admin.siswa._field', ['name' => 'ibu_nik', 'label' => 'NIK ibu', 'max' => 16, 'inputmode' => 'numeric'])
                    @include('super-admin.siswa._field', ['name' => 'ibu_tahun_lahir', 'label' => 'Tahun lahir ibu', 'max' => 4, 'inputmode' => 'numeric'])
                    @include('super-admin.siswa._field', ['name' => 'ibu_jenjang_pendidikan', 'label' => 'Jenjang pendidikan ibu', 'type' => 'select', 'options' => $jenjangList])
                    @include('super-admin.siswa._field', ['name' => 'pekerjaan_ibu', 'label' => 'Pekerjaan ibu', 'max' => 100, 'list' => 'list-pekerjaan'])
                    @include('super-admin.siswa._field', ['name' => 'penghasilan_ibu', 'label' => 'Penghasilan ibu', 'max' => 100, 'list' => 'list-penghasilan'])
                </div>
            </div>

            {{-- ===== 5. Wali ===== --}}
            <div class="panel tab-pane" data-key="wali" data-icon="🧑‍🤝‍🧑" data-title="Wali" data-desc="Data wali siswa">
                <div class="panel-head">
                    <div>
                        <h3>Data Wali</h3>
                        <p>Isi jika siswa diasuh oleh wali.</p>
                    </div>
                </div>

                <div class="form-grid">
                    @include('super-admin.siswa._field', ['name' => 'wali_nama', 'label' => 'Nama wali', 'max' => 150])
                    @include('super-admin.siswa._field', ['name' => 'wali_nik', 'label' => 'NIK wali', 'max' => 16, 'inputmode' => 'numeric'])
                    @include('super-admin.siswa._field', ['name' => 'wali_tahun_lahir', 'label' => 'Tahun lahir wali', 'max' => 4, 'inputmode' => 'numeric'])
                    @include('super-admin.siswa._field', ['name' => 'wali_jenjang_pendidikan', 'label' => 'Jenjang pendidikan wali', 'type' => 'select', 'options' => $jenjangList])
                    @include('super-admin.siswa._field', ['name' => 'wali_pekerjaan', 'label' => 'Pekerjaan wali', 'max' => 100, 'list' => 'list-pekerjaan'])
                    @include('super-admin.siswa._field', ['name' => 'wali_penghasilan', 'label' => 'Penghasilan wali', 'max' => 100, 'list' => 'list-penghasilan'])
                </div>
            </div>

            {{-- ===== 6. Alamat ===== --}}
            <div class="panel tab-pane" data-key="alamat" data-icon="🏠" data-title="Alamat" data-desc="Alamat dan lokasi">
                <div class="panel-head">
                    <div>
                        <h3>Alamat</h3>
                    </div>
                </div>

                <div class="form-grid">
                    @include('super-admin.siswa._field', ['name' => 'alamat', 'label' => 'Alamat lengkap', 'type' => 'textarea', 'full' => true])
                    @include('super-admin.siswa._field', ['name' => 'rt', 'label' => 'RT', 'max' => 5, 'inputmode' => 'numeric'])
                    @include('super-admin.siswa._field', ['name' => 'rw', 'label' => 'RW', 'max' => 5, 'inputmode' => 'numeric'])
                    @include('super-admin.siswa._field', ['name' => 'dusun', 'label' => 'Dusun', 'max' => 100])
                    @include('super-admin.siswa._field', ['name' => 'desa_kelurahan', 'label' => 'Desa / kelurahan', 'max' => 100])
                    @include('super-admin.siswa._field', ['name' => 'kecamatan', 'label' => 'Kecamatan', 'max' => 100])
                    @include('super-admin.siswa._field', ['name' => 'kabupaten_kota', 'label' => 'Kabupaten / kota', 'max' => 100])
                    @include('super-admin.siswa._field', ['name' => 'provinsi', 'label' => 'Provinsi', 'max' => 100])
                    @include('super-admin.siswa._field', ['name' => 'kode_pos', 'label' => 'Kode pos', 'max' => 5, 'inputmode' => 'numeric'])
                    @include('super-admin.siswa._field', ['name' => 'jenis_tinggal', 'label' => 'Jenis tinggal', 'type' => 'select', 'options' => $jenisTinggalList])
                    @include('super-admin.siswa._field', ['name' => 'alat_transportasi', 'label' => 'Alat transportasi', 'type' => 'select', 'options' => $transportList])
                    @include('super-admin.siswa._field', ['name' => 'lintang', 'label' => 'Lintang', 'max' => 30, 'inputmode' => 'decimal'])
                    @include('super-admin.siswa._field', ['name' => 'bujur', 'label' => 'Bujur', 'max' => 30, 'inputmode' => 'decimal'])
                    @include('super-admin.siswa._field', ['name' => 'jarak_rumah_ke_sekolah', 'label' => 'Jarak rumah ke sekolah (KM)', 'type' => 'number', 'step' => '0.01', 'inputmode' => 'decimal'])
                </div>
            </div>

            {{-- ===== 7. Kontak ===== --}}
            <div class="panel tab-pane" data-key="kontak" data-icon="📞" data-title="Kontak" data-desc="Telepon dan email">
                <div class="panel-head">
                    <div>
                        <h3>Kontak</h3>
                        <p>Informasi kontak siswa dan kedua orang tua.</p>
                    </div>
                </div>

                <div class="form-grid">
                    @include('super-admin.siswa._field', ['name' => 'telepon_rumah', 'label' => 'Telepon rumah', 'max' => 20, 'inputmode' => 'tel', 'placeholder' => '0271123456'])
                    @include('super-admin.siswa._field', ['name' => 'hp', 'label' => 'HP siswa', 'max' => 20, 'inputmode' => 'tel', 'placeholder' => '081234567890'])
                    @include('super-admin.siswa._field', ['name' => 'email_siswa', 'label' => 'Email siswa', 'type' => 'email', 'max' => 100])

                    @include('super-admin.siswa._field', ['name' => 'no_hp_ayah', 'label' => 'No. HP ayah', 'max' => 20, 'inputmode' => 'tel', 'placeholder' => '081234567890'])
                    @include('super-admin.siswa._field', ['name' => 'email_ayah', 'label' => 'Email ayah', 'type' => 'email', 'max' => 100])

                    @include('super-admin.siswa._field', ['name' => 'no_hp_ibu', 'label' => 'No. HP ibu', 'max' => 20, 'inputmode' => 'tel', 'placeholder' => '081234567890'])
                    @include('super-admin.siswa._field', ['name' => 'email_ibu', 'label' => 'Email ibu', 'type' => 'email', 'max' => 100])
                </div>
            </div>

            {{-- ===== 8. Bantuan & rekening ===== --}}
            <div class="panel tab-pane" data-key="bantuan" data-icon="💳" data-title="Bantuan &amp; Rekening"
                data-desc="KPS, KIP, PIP, rekening">
                <div class="panel-head">
                    <div>
                        <h3>Bantuan &amp; Rekening</h3>
                    </div>
                </div>

                <div class="form-grid">
                    @include('super-admin.siswa._field', ['name' => 'penerima_kps', 'label' => 'Penerima KPS', 'type' => 'select', 'options' => $yaTidak])
                    @include('super-admin.siswa._field', ['name' => 'no_kps', 'label' => 'No. KPS', 'max' => 50])
                    @include('super-admin.siswa._field', ['name' => 'penerima_kip', 'label' => 'Penerima KIP', 'type' => 'select', 'options' => $yaTidak])
                    @include('super-admin.siswa._field', ['name' => 'nomor_kip', 'label' => 'Nomor KIP', 'max' => 50])
                    @include('super-admin.siswa._field', ['name' => 'nama_di_kip', 'label' => 'Nama di KIP', 'max' => 150])
                    @include('super-admin.siswa._field', ['name' => 'nomor_kks', 'label' => 'Nomor KKS', 'max' => 50])
                    @include('super-admin.siswa._field', ['name' => 'layak_pip', 'label' => 'Layak PIP (usulan dari sekolah)', 'type' => 'select', 'options' => $yaTidak])
                    @include('super-admin.siswa._field', ['name' => 'alasan_layak_pip', 'label' => 'Alasan Layak PIP', 'max' => 255])
                    @include('super-admin.siswa._field', ['name' => 'bank', 'label' => 'Bank', 'max' => 100])
                    @include('super-admin.siswa._field', ['name' => 'nomor_rekening_bank', 'label' => 'Nomor rekening bank', 'max' => 30, 'inputmode' => 'numeric'])
                    @include('super-admin.siswa._field', ['name' => 'rekening_atas_nama', 'label' => 'Rekening atas nama', 'max' => 150])
                </div>
            </div>

        </div>{{-- /#pager-body --}}

        <div class="pager-foot">
            <div class="right">
                <a class="btn btn-light"
                    href="{{ $edit ? route('super-admin.siswa.show', $siswaId) : route('super-admin.siswa.index') }}">Batal</a>
                <button type="submit" class="btn btn-primary">{{ $edit ? 'Simpan perubahan' : 'Simpan siswa' }}</button>
            </div>
        </div>
    </form>

    <datalist id="list-pekerjaan">@foreach($ref['pekerjaan'] as $r)
    <option value="{{ $r }}">@endforeach
    </datalist>
    <datalist id="list-penghasilan">@foreach($ref['penghasilan'] as $r)
    <option value="{{ $r }}">@endforeach
    </datalist>
    <datalist id="list-asal-sekolah">@foreach($ref['asal_sekolah'] as $r)
    <option value="{{ $r }}">@endforeach
    </datalist>

@endsection

@push('scripts')
    <script>
        /* ---------- Pratinjau foto ---------- */
        const fotoInput = document.getElementById('f-foto');
        const fotoPreview = document.getElementById('foto-preview');

        fotoInput.addEventListener('change', () => {
            const file = fotoInput.files[0];
            if (!file) return;
            fotoPreview.src = URL.createObjectURL(file);
            fotoPreview.style.display = 'block';
        });

        /* ---------- Filter kelas berdasarkan tahun pelajaran ---------- */
        const tahunSelect = document.getElementById('f-tahun');
        const kelasSelect = document.getElementById('f-kelas_id');

        function filterKelas(resetPilihan) {
            const tahun = tahunSelect.value;

            Array.from(kelasSelect.options).forEach(opt => {
                if (!opt.value) return;
                const cocok = opt.dataset.tahun === tahun;
                opt.hidden = !cocok;
                opt.disabled = !cocok;
            });

            const terpilih = kelasSelect.selectedOptions[0];
            if (resetPilihan || (terpilih && terpilih.value && terpilih.disabled)) {
                kelasSelect.value = '';
            }
        }

        tahunSelect.addEventListener('change', () => filterKelas(true));
        filterKelas(false);

        /* ---------- Halaman per tab (kartu horizontal) ---------- */
        (function () {
            const form = document.getElementById('form-siswa');
            const panes = Array.from(document.querySelectorAll('#pager-body .tab-pane'));
            const nav = document.getElementById('pager-nav');
            const wrap = document.getElementById('tabs-wrap');
            const btnL = document.getElementById('tabs-left');
            const btnR = document.getElementById('tabs-right');
            const bagianInput = document.getElementById('bagian-aktif');
            let cur = 0;

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
                bagianInput.value = key;
                history.replaceState(null, '', '#' + key);
                nav.children[cur].scrollIntoView({ block: 'nearest', inline: 'center' });
                updateArrows();
            }

            // Field wajib kosong di tab yang sedang tersembunyi: pindah ke tab itu
            form.addEventListener('invalid', function (e) {
                const idx = panes.findIndex(function (p) { return p.contains(e.target); });
                if (idx >= 0) show(idx);
            }, true);

            // Tab awal: dari hash (#ayah), atau tab pertama yang berisi error validasi server
            let start = panes.findIndex(function (p) { return '#' + p.dataset.key === location.hash; });
            const adaError = panes.findIndex(function (p) { return p.querySelector('.invalid, .err'); });
            if (adaError >= 0) start = adaError;
            show(start >= 0 ? start : 0);
        })();
    </script>
@endpush