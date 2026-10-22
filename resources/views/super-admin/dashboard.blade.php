@extends('super-admin.layout')

@php
    // Beranda = halaman tanpa ?halaman=… yang valid. Dipakai untuk gaya "satu layar penuh" di bawah.
    $isBeranda = !in_array(request('halaman'), ['kelas', 'tahun', 'gender', 'baru', 'sebulan'], true);
@endphp

@section('title', 'Dashboard')
@section('subtitle', \App\Support\RoleRoute::has('siswa.create') ? 'Ringkasan dan pengelolaan data siswa' : 'Ringkasan data siswa')

@push('styles')
    <style>
        /* Gaya khusus dashboard. Sidebar, topbar, panel, tabel, badge, alert, dan footer sudah ada di layout. */
        :root {
            --purple: #8b5cf6;
            --pink: #ec4899;
            --jk-l: #2563eb;
            --jk-p: #ec4899;
        }

        /* ===== Kartu menu halaman (posisi tetap di semua halaman, sama seperti Master Data) ===== */
        .dash .dcards { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; margin-bottom: 18px; }
        .dash .dcard { position: relative; display: block; text-decoration: none; color: var(--text); background: #fff; border: 1px solid var(--line); border-top: 3px solid var(--line); border-radius: 12px; padding: 14px 18px; transition: border-color .15s, box-shadow .15s, transform .15s; }
        .dash .dcard:hover { border-color: #bcd3ee; border-top-color: #bcd3ee; box-shadow: 0 4px 14px #0f172a0d; transform: translateY(-1px); }
        .dash .dcard.active { background: #f5f9ff; border-color: var(--blue); border-top-color: var(--blue); box-shadow: 0 4px 14px #1675ce1f; }
        .dash .dcard .ico { font-size: 24px; line-height: 1; margin-bottom: 8px; }
        .dash .dcard h4 { font-size: 14px; margin-bottom: 4px; color: var(--navy); }
        .dash .dcard p { font-size: 12px; color: var(--muted); line-height: 1.5; }
        .dash .dcard .jml { position: absolute; top: 14px; right: 14px; padding: 2px 9px; border-radius: 999px; background: #eaf0f7; color: var(--navy); font-size: 11px; font-weight: 700; }
        .dash .dcard.active .jml { background: var(--blue); color: #fff; }

        /* ===== Selamat datang ===== */
        .dash .hero { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; background: linear-gradient(120deg, #0755a5, #1686ce); color: #fff; padding: 26px 30px; border-radius: 14px; margin-bottom: 18px; position: relative; overflow: hidden; }
        .dash .hero::after { content: ""; position: absolute; right: -60px; top: -70px; width: 240px; height: 240px; border-radius: 50%; background: rgba(255, 255, 255, .08); }
        .dash .hero::before { content: ""; position: absolute; right: 120px; bottom: -90px; width: 180px; height: 180px; border-radius: 50%; background: rgba(255, 255, 255, .06); }
        .dash .hero > * { position: relative; z-index: 1; }
        .dash .hero .sapa { font-size: 12px; color: #cfe5ff; margin-bottom: 6px; letter-spacing: .02em; }
        .dash .hero h1 { font-size: 26px; margin: 0 0 8px; }
        .dash .hero p { margin: 0; font-size: 13px; color: #e1efff; line-height: 1.7; max-width: 560px; }
        .dash .hero .meta { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
        .dash .year-pill { display: inline-flex; align-items: center; gap: 6px; padding: 8px 15px; border-radius: 999px; background: rgba(255, 255, 255, .16); border: 1px solid rgba(255, 255, 255, .35); font-size: 13px; }
        .dash .year-pill strong { font-size: 15px; }
        .dash .year-pill.warn { background: #fffbeb; border-color: #fde68a; color: #92400e; }
        .dash .hero .tgl { font-size: 12px; color: #cfe5ff; }

        .dash.beranda .hero { padding: 18px 26px; }
        .dash.beranda .hero h1 { font-size: 22px; margin-bottom: 6px; }
        .dash.beranda .hero .sapa { margin-bottom: 4px; }

        /* ===== Kartu angka ===== */
        .dash .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; margin-bottom: 18px; }
        .dash .stat { background: #fff; border: 1px solid var(--line); border-top: 4px solid var(--blue); border-radius: 12px; padding: 14px 18px; }
        .dash .stat .top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
        .dash .stat .lbl { font-size: 12px; color: var(--muted); }
        .dash .stat .ico { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; background: #eef5fd; }
        .dash .stat strong { display: block; font-size: 28px; line-height: 1.15; color: var(--navy); }
        .dash .stat small { display: block; font-size: 11px; color: var(--muted); margin-top: 6px; }
        .dash .stat .mini-bar { height: 6px; background: #eaf0f7; border-radius: 10px; overflow: hidden; margin-top: 10px; }
        .dash .stat .mini-bar span { display: block; height: 100%; border-radius: 10px; background: var(--green); }
        .dash .s-blue { border-top-color: var(--blue); }
        .dash .s-green { border-top-color: var(--green); }
        .dash .s-green .ico { background: #e7f8f1; }
        .dash .s-orange { border-top-color: var(--orange); }
        .dash .s-orange .ico { background: #fef4e0; }
        .dash .s-pink { border-top-color: var(--pink); }
        .dash .s-pink .ico { background: #fdeaf4; }
        .dash .s-purple { border-top-color: var(--purple); }
        .dash .s-purple .ico { background: #f1ebfe; }

        /* ===== Isi beranda ===== */
        .dash .fill-row { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .dash .fill-row > .panel, .dash .fill-row .col > .panel { margin-bottom: 0; }
        .dash .col { display: flex; flex-direction: column; gap: 14px; min-width: 0; }
        .dash .panel h3 { font-size: 15px; }
        .dash .panel .panel-head { margin-bottom: 12px; }
        .dash .panel .panel-head a { font-size: 12px; color: var(--blue); text-decoration: none; font-weight: 600; white-space: nowrap; }

        /* ===== Bar ===== */
        .dash .bar { height: 9px; background: #eaf0f7; border-radius: 10px; overflow: hidden; }
        .dash .bar.lg { height: 10px; }
        .dash .bar-fill { height: 100%; background: var(--blue); border-radius: 10px; }
        .dash .bar .bar-fill.fill-male { background: var(--jk-l); }
        .dash .bar .bar-fill.fill-female { background: var(--jk-p); }
        .dash .bar-fill.bar-split { display: flex; background: transparent; overflow: hidden; }
        .dash .bar-fill .seg { height: 100%; }
        .dash .bar-fill .seg-l { background: var(--jk-l); }
        .dash .bar-fill .seg-p { background: var(--jk-p); }

        .dash .class-row { display: grid; grid-template-columns: 90px 1fr 44px; align-items: center; gap: 12px; padding: 8px 0; font-size: 12px; }
        .dash .class-row strong { text-align: right; color: var(--navy); }

        .dash .jk-ket { display: flex; gap: 14px; margin-top: 5px; font-size: 11px; font-weight: 600; }
        .dash .jk-ket .ket-l { color: #1d4ed8; }
        .dash .jk-ket .ket-p { color: #be185d; }

        .dash .legend { display: flex; gap: 14px; font-size: 12px; color: var(--muted); margin: 0 0 12px; }
        .dash .legend i { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 5px; }

        /* ===== Laki-laki / Perempuan ===== */
        .dash .gender-box { max-width: 560px; }
        .dash .gender-item { margin-bottom: 20px; }
        .dash .gender-item:last-of-type { margin-bottom: 0; }
        .dash .gender-head { display: flex; justify-content: space-between; align-items: baseline; font-size: 13px; margin-bottom: 8px; }
        .dash .gender-head strong { font-size: 20px; color: var(--navy); }
        .dash .gender-head em { font-style: normal; color: var(--muted); font-size: 12px; margin-left: 6px; }
        .dash .gender-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 14px; border-top: 1px solid #edf0f5; font-size: 13px; }
        .dash .gender-total strong { color: var(--navy); }

        /* ===== Daftar ringkas ===== */
        .dash .recent { list-style: none; }
        .dash .recent li { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f4f8; font-size: 13px; }
        .dash .recent li:last-child { border-bottom: none; }
        .dash .recent .avatar { flex: 0 0 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
        .dash .recent .av-L { background: #dbeafe; color: #1d4ed8; }
        .dash .recent .av-P { background: #fce7f3; color: #be185d; }
        .dash .recent .av- { background: #e2e8f0; color: #475569; }
        .dash .recent .who { flex: 1; min-width: 0; }
        .dash .recent .who strong { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dash .recent .who span { font-size: 11px; color: var(--muted); }
        .dash .recent .when { font-size: 11px; color: var(--muted); white-space: nowrap; }

        /* ===== Pengingat ===== */
        .dash .notice { display: flex; gap: 10px; background: #fffbeb; border: 1px solid #fde68a; padding: 11px 14px; border-radius: 8px; color: #92400e; font-size: 12px; line-height: 1.6; margin-bottom: 10px; }
        .dash .notice:last-child { margin-bottom: 0; }
        .dash .notice.ok { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }

        /* ===== Tabel ===== */
        .dash .dash-table td { white-space: nowrap; }
        .dash .table-note { margin-top: 12px; font-size: 12px; color: var(--muted); }
        .dash .table-note a { color: var(--blue); }
        .dash .dash-table tr.row-L td:first-child { box-shadow: inset 4px 0 0 var(--jk-l); }
        .dash .dash-table tr.row-P td:first-child { box-shadow: inset 4px 0 0 var(--jk-p); }

        .dash .jk { display: inline-block; min-width: 24px; text-align: center; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .dash .jk-L { background: #dbeafe; color: #1d4ed8; }
        .dash .jk-P { background: #fce7f3; color: #be185d; }

        @media (max-width: 1100px) {
            .dash .fill-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 700px) {
            .dash .hero { padding: 20px; }
            .dash .hero h1 { font-size: 20px; }
            .dash .hero .meta { align-items: flex-start; }
        }
    </style>
@endpush

@push('styles')
    @if($isBeranda)
        <style>
            /* Beranda: seluruh isi pas dalam satu layar, tanpa scroll halaman.
               Pada layar kecil/pendek, halaman kembali normal dan bisa di-scroll. */
            @media (min-width: 1101px) and (min-height: 760px) {
                html, body { height: 100%; overflow: hidden; }
                .main { height: 100vh; min-height: 0; display: flex; flex-direction: column; }
                .content { flex: 1; min-height: 0; display: flex; flex-direction: column; padding: 18px 28px 20px; }
                .content > .footer { display: none; }

                .dash.beranda { flex: 1; min-height: 0; display: flex; flex-direction: column; }
                .dash.beranda .dcards,
                .dash.beranda .hero,
                .dash.beranda .stats { flex: 0 0 auto; margin-bottom: 14px; }

                .dash.beranda .fill-row { flex: 1; min-height: 0; }
                .dash.beranda .fill-row > .panel,
                .dash.beranda .col { min-height: 0; }
                .dash.beranda .panel { display: flex; flex-direction: column; overflow: hidden; padding: 16px 20px; }
                .dash.beranda .col > .panel { flex: 0 0 auto; }
                .dash.beranda .col > .panel.grow { flex: 1; min-height: 0; }

                /* isi panel dibagi rata supaya terlihat penuh */
                .dash.beranda .fill-list { flex: 1; min-height: 0; display: flex; flex-direction: column; justify-content: space-around; }
                .dash.beranda .fill-list .class-row { padding: 0; }
                .dash.beranda .recent { flex: 1; min-height: 0; display: flex; flex-direction: column; justify-content: space-around; }
                .dash.beranda .recent li { padding: 0; border-bottom: none; }
                .dash.beranda .grow .notice { margin-bottom: 8px; }
            }
        </style>
    @endif
@endpush

@section('content')

    @php
        $badge = ['Aktif' => 'badge-green', 'Pindah' => 'badge-blue', 'Lulus' => 'badge-orange', 'Nonaktif' => 'badge-gray'];

        // Tautan hanya dibuat jika role yang sedang login memang punya route-nya
        $prefix = \App\Support\RoleRoute::prefix();
        $rute = fn(string $nama, ...$param) => \Illuminate\Support\Facades\Route::has("{$prefix}.{$nama}")
            ? route("{$prefix}.{$nama}", $param)
            : null;

        $sejak = now()->subMonth();
        $daftarSebulan = $siswaSebulanList ?? [];
        $jumlahSebulan = $siswaSebulan ?? 0;

        $lk = $siswaLakiLaki ?? 0;
        $pr = $siswaPerempuan ?? 0;
        $total = $lk + $pr;
        $persenLk = $total > 0 ? round($lk / $total * 100, 1) : 0;
        $persenPr = $total > 0 ? round($pr / $total * 100, 1) : 0;

        $jumlahSiswa = $totalSiswa ?? 0;
        $jumlahAktif = $siswaAktif ?? 0;
        $jumlahKelas = $totalKelas ?? 0;
        $persenAktif = $jumlahSiswa > 0 ? round($jumlahAktif / $jumlahSiswa * 100) : 0;
        $rataKelas = $jumlahKelas > 0 ? round($jumlahSiswa / $jumlahKelas) : 0;

        $jam = now()->hour;
        $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));

        // Menu halaman: [judul, ikon, keterangan singkat, jumlah (opsional)]
        $menu = [
            'beranda' => ['Beranda', '🏠', 'Ringkasan umum data siswa', null],
            'kelas'   => ['Siswa per Kelas', '📊', 'Sebaran siswa tiap rombel', $jumlahKelas],
            'tahun'   => ['Tahun Pelajaran', '📅', 'Tahun aktif dan riwayatnya', !empty($daftarTahun) ? count($daftarTahun) : null],
            'gender'  => ['Laki-laki / Perempuan', '👥', 'Komposisi jenis kelamin', null],
            'sebulan' => ['Baru Ditambahkan', '🕒', 'Data 1 bulan terakhir', $jumlahSebulan],
        ];
        $halaman = request('halaman', 'beranda');
        if (!array_key_exists($halaman, $menu)) {
            $halaman = 'beranda';
        }
        $tautan = fn(string $kunci) => $kunci === 'beranda'
            ? request()->url()
            : request()->url() . '?halaman=' . $kunci;

        // Jumlah L dan P per kelas pada tahun pelajaran aktif (hanya dihitung di halaman kelas).
        $jkPerKelas = collect();
        if ($halaman === 'kelas') {
            $jkPerKelas = \Illuminate\Support\Facades\DB::table('riwayat_kelas as rk')
                ->join('kelas', 'kelas.id', '=', 'rk.kelas_id')
                ->join('siswa', 'siswa.id', '=', 'rk.siswa_id')
                ->when(!empty($tahunAktif), fn($q) => $q->where('rk.tahun_pelajaran_id', $tahunAktif->id))
                ->groupBy('kelas.nama_kelas')
                ->selectRaw("kelas.nama_kelas,
                    SUM(UPPER(LEFT(siswa.jenis_kelamin, 1)) = 'L') as laki_laki,
                    SUM(UPPER(LEFT(siswa.jenis_kelamin, 1)) = 'P') as perempuan")
                ->get()
                ->keyBy('nama_kelas');
        }

        // Pengingat otomatis dari data yang sudah ada, ditambah $notifikasi dari controller
        $pengingat = collect($notifikasi ?? []);
        if (empty($tahunAktif)) {
            $pengingat->push('Belum ada tahun pelajaran yang diaktifkan.');
        } elseif (($siswaBaru ?? 0) === 0) {
            $pengingat->push('Belum ada siswa baru pada tahun pelajaran ' . $tahunAktif->nama . '.');
        }
        if ($jumlahSiswa > $jumlahAktif) {
            $pengingat->push(number_format($jumlahSiswa - $jumlahAktif, 0, ',', '.') . ' siswa berstatus tidak aktif (pindah, lulus, atau nonaktif).');
        }

    @endphp

    <div class="dash {{ $halaman === 'beranda' ? 'beranda' : '' }}">

        @if($errors->any())
            <div class="alert alert-error">
                Terjadi kesalahan. Periksa informasi berikut:
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ================= KARTU MENU HALAMAN (posisi tetap di setiap halaman) ================= --}}
        <nav class="dcards" aria-label="Halaman dashboard">
            @foreach($menu as $kunci => [$judul, $ikon, $ket, $jml])
                <a href="{{ $tautan($kunci) }}" class="dcard {{ $halaman === $kunci ? 'active' : '' }}"
                    @if($halaman === $kunci) aria-current="page" @endif>
                    @if($jml !== null)
                        <span class="jml">{{ number_format($jml, 0, ',', '.') }}</span>
                    @endif
                    <div class="ico">{{ $ikon }}</div>
                    <h4>{{ $judul }}</h4>
                    <p>{{ $ket }}</p>
                </a>
            @endforeach
        </nav>

        {{-- ================= BERANDA (satu layar penuh) ================= --}}
        @if($halaman === 'beranda')

            <div class="hero">
                <div>
                    <div class="sapa">{{ $sapaan }}</div>
                    <h1>Selamat datang, {{ auth()->user()->name }}!</h1>
                    <p>Ringkasan data siswa, sebaran kelas, komposisi jenis kelamin, siswa baru, dan pengingat penting
                        dalam satu halaman.</p>
                </div>

                <div class="meta">
                    @if(!empty($tahunAktif))
                        <span class="year-pill">📅 Tahun pelajaran aktif <strong>{{ $tahunAktif->nama }}</strong></span>
                    @else
                        <span class="year-pill warn">⚠️ Belum ada tahun pelajaran yang diaktifkan</span>
                    @endif
                    <span class="tgl">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</span>
                </div>
            </div>

            <div class="stats">
                <div class="stat s-blue">
                    <div class="top"><span class="lbl">Total Siswa</span><span class="ico">🎓</span></div>
                    <strong>{{ number_format($jumlahSiswa, 0, ',', '.') }}</strong>
                    <small>Seluruh data siswa</small>
                </div>

                <div class="stat s-green">
                    <div class="top"><span class="lbl">Siswa Aktif</span><span class="ico">✅</span></div>
                    <strong>{{ number_format($jumlahAktif, 0, ',', '.') }}</strong>
                    <small>{{ $persenAktif }}% dari total siswa</small>
                </div>

                <div class="stat s-orange">
                    <strong>{{ number_format($siswaBaru ?? 0, 0, ',', '.') }}</strong>
                    <small>{{ !empty($tahunAktif) ? 'Tahun pelajaran ' . $tahunAktif->nama : 'Tahun berjalan' }}</small>
                </div>

                <div class="stat s-pink">
                    <div class="top"><span class="lbl">Baru Ditambahkan</span><span class="ico">🕒</span></div>
                    <strong>{{ number_format($jumlahSebulan, 0, ',', '.') }}</strong>
                    <small>Sejak {{ $sejak->format('d/m/Y') }}</small>
                </div>

                <div class="stat s-purple">
                    <div class="top"><span class="lbl">Total Kelas</span><span class="ico">🏫</span></div>
                    <strong>{{ number_format($jumlahKelas, 0, ',', '.') }}</strong>
                    <small>Rata-rata {{ number_format($rataKelas, 0, ',', '.') }} siswa per kelas</small>
                </div>
            </div>

            <div class="fill-row">

                {{-- Kolom 1: Kelas terbanyak --}}
                <div class="panel">
                    <div class="panel-head">
                        <h3>📊 Kelas Terbanyak</h3>
                        <a href="{{ $tautan('kelas') }}">Lihat semua &raquo;</a>
                    </div>

                    @php $teratas = collect($jumlahPerKelas ?? [])->sortByDesc('total')->take(6); @endphp
                    <div class="fill-list">
                        @forelse($teratas as $item)
                            <div class="class-row">
                                <span>{{ $item->nama_kelas }}</span>
                                <div class="bar">
                                    <div class="bar-fill"
                                        style="width: {{ ($maksimumSiswaKelas ?? 0) > 0 ? min(100, ($item->total / $maksimumSiswaKelas) * 100) : 0 }}%">
                                    </div>
                                </div>
                                <strong>{{ $item->total }}</strong>
                            </div>
                        @empty
                            <p class="empty">Belum ada data siswa per kelas.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Kolom 2: Komposisi jenis kelamin + pengingat --}}
                <div class="col">
                    <div class="panel">
                        <div class="panel-head">
                            <h3>👥 Laki-laki / Perempuan</h3>
                            <a href="{{ $tautan('gender') }}">Detail &raquo;</a>
                        </div>

                        <div class="gender-item">
                            <div class="gender-head">
                                <span>👨 Laki-laki</span>
                                <span><strong>{{ number_format($lk, 0, ',', '.') }}</strong><em>{{ $persenLk }}%</em></span>
                            </div>
                            <div class="bar lg"><div class="bar-fill fill-male" style="width: {{ $persenLk }}%"></div></div>
                        </div>

                        <div class="gender-item">
                            <div class="gender-head">
                                <span>👩 Perempuan</span>
                                <span><strong>{{ number_format($pr, 0, ',', '.') }}</strong><em>{{ $persenPr }}%</em></span>
                            </div>
                            <div class="bar lg"><div class="bar-fill fill-female" style="width: {{ $persenPr }}%"></div></div>
                        </div>

                        <div class="gender-total">
                            <span>Total</span>
                            <strong>{{ number_format($total, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="panel grow">
                        <div class="panel-head">
                            <h3>🔔 Notifikasi &amp; Pengingat</h3>
                        </div>

                        @forelse($pengingat->take(3) as $pesan)
                            <div class="notice"><span>⚠️</span><span>{{ $pesan }}</span></div>
                        @empty
                            <div class="notice ok"><span>✅</span><span>Tidak ada pengingat. Semua data dalam kondisi baik.</span></div>
                        @endforelse
                    </div>
                </div>

                {{-- Kolom 3: Baru ditambahkan --}}
                <div class="panel">
                    <div class="panel-head">
                        <h3>🕒 Baru Ditambahkan</h3>
                        <a href="{{ $tautan('sebulan') }}">Selengkapnya &raquo;</a>
                    </div>

                    @php $terbaru = collect($daftarSebulan)->take(5); @endphp
                    @if($terbaru->count() > 0)
                        <ul class="recent">
                            @foreach($terbaru as $s)
                                @php
                                    $jk = strtoupper(substr($s->jenis_kelamin ?? '', 0, 1));
                                    $inisial = strtoupper(mb_substr($s->nama ?? '?', 0, 1));
                                    $linkDetail = $rute('siswa.show', $s->id);
                                @endphp
                                <li>
                                    <span class="avatar av-{{ $jk }}">{{ $inisial }}</span>
                                    <div class="who">
                                        @if($linkDetail)
                                            <a href="{{ $linkDetail }}" style="color:inherit;text-decoration:none;"><strong>{{ $s->nama ?? '-' }}</strong></a>
                                        @else
                                            <strong>{{ $s->nama ?? '-' }}</strong>
                                        @endif
                                        <span>{{ $s->nama_kelas ?? 'Belum ada kelas' }}</span>
                                    </div>
                                    <span class="when">{{ !empty($s->created_at) ? \Carbon\Carbon::parse($s->created_at)->format('d/m/Y') : '-' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="empty">Belum ada siswa yang ditambahkan sejak {{ $sejak->format('d/m/Y') }}.</p>
                    @endif
                </div>

            </div>
        @endif

        {{-- ================= HALAMAN: Siswa per Kelas ================= --}}
        @if($halaman === 'kelas')
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3>Jumlah Siswa per Kelas</h3>
                        <p>Sebaran siswa laki-laki dan perempuan pada tiap kelas{{ !empty($tahunAktif) ? ' (tahun pelajaran ' . $tahunAktif->nama . ')' : '' }}.</p>
                    </div>
                </div>

                <div class="legend">
                    <span><i style="background: var(--jk-l)"></i>Laki-laki</span>
                    <span><i style="background: var(--jk-p)"></i>Perempuan</span>
                </div>

                @forelse(($jumlahPerKelas ?? []) as $item)
                    @php
                        $jkKelas = $jkPerKelas[$item->nama_kelas] ?? null;
                        $l = (int) ($item->laki_laki ?? $jkKelas->laki_laki ?? 0);
                        $p = (int) ($item->perempuan ?? $jkKelas->perempuan ?? 0);
                        $sub = max($l + $p, 1);
                        $lebar = ($maksimumSiswaKelas ?? 0) > 0
                            ? min(100, ($item->total / $maksimumSiswaKelas) * 100)
                            : 0;
                    @endphp
                    <div class="class-row">
                        <span>{{ $item->nama_kelas }}</span>

                        <div>
                            <div class="bar">
                                <div class="bar-fill bar-split" style="width: {{ $lebar }}%">
                                    <span class="seg seg-l" style="width: {{ $l / $sub * 100 }}%" title="Laki-laki: {{ $l }}"></span>
                                    <span class="seg seg-p" style="width: {{ $p / $sub * 100 }}%" title="Perempuan: {{ $p }}"></span>
                                </div>
                            </div>
                            <div class="jk-ket">
                                <span class="ket-l">Laki-laki: {{ $l }} orang</span>
                                <span class="ket-p">Perempuan: {{ $p }} orang</span>
                            </div>
                        </div>

                        <strong>{{ $item->total }}</strong>
                    </div>
                @empty
                    <p class="empty">Belum ada data siswa per kelas.</p>
                @endforelse
            </div>
        @endif

        {{-- ================= HALAMAN: Tahun Pelajaran ================= --}}
        @if($halaman === 'tahun')
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3>Tahun Pelajaran</h3>
                        <p>Hanya satu tahun pelajaran yang aktif dalam satu waktu.</p>
                    </div>
                </div>

                @if(!empty($daftarTahun) && count($daftarTahun) > 0)
                    <div class="table-wrap">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tahun Pelajaran</th>
                                    <th>Status</th>
                                    <th>Jumlah Kelas</th>
                                    <th>Siswa Aktif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($daftarTahun as $i => $t)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><strong>{{ $t->nama }}</strong></td>
                                        <td>
                                            <span class="badge {{ $t->status === 'aktif' ? 'badge-green' : 'badge-gray' }}">
                                                {{ ucfirst($t->status) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($t->jumlah_kelas, 0, ',', '.') }}</td>
                                        <td>{{ number_format($t->jumlah_siswa, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="empty">Belum ada data tahun pelajaran.</p>
                @endif
            </div>
        @endif

        {{-- ================= HALAMAN: Laki-laki / Perempuan ================= --}}
        @if($halaman === 'gender')
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3>Laki-laki / Perempuan</h3>
                        <p>Komposisi jenis kelamin seluruh siswa.</p>
                    </div>
                </div>

                <div class="gender-box">
                    <div class="gender-item">
                        <div class="gender-head">
                            <span>👨 Laki-laki</span>
                            <span><strong>{{ number_format($lk, 0, ',', '.') }}</strong><em>{{ $persenLk }}%</em></span>
                        </div>
                        <div class="bar lg"><div class="bar-fill fill-male" style="width: {{ $persenLk }}%"></div></div>
                    </div>

                    <div class="gender-item">
                        <div class="gender-head">
                            <span>👩 Perempuan</span>
                            <span><strong>{{ number_format($pr, 0, ',', '.') }}</strong><em>{{ $persenPr }}%</em></span>
                        </div>
                        <div class="bar lg"><div class="bar-fill fill-female" style="width: {{ $persenPr }}%"></div></div>
                    </div>

                    <div class="gender-total">
                        <span>Total</span>
                        <strong>{{ number_format($total, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        @endif

        {{-- ================= HALAMAN: Siswa Baru (tahun pelajaran berjalan) ================= --}}
        @if($halaman === 'baru')
            <div class="panel">
                <div class="panel-head">
                </div>

                @if(!empty($siswaBaruList) && count($siswaBaruList) > 0)
                    <div class="table-wrap">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIPD</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>L/P</th>
                                    <th>Tahun Pelajaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($siswaBaruList as $i => $s)
                                    @php $jk = strtoupper(substr($s->jenis_kelamin ?? '', 0, 1)); @endphp
                                    <tr class="row-{{ $jk }}">
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $s->nis ?? '-' }}</td>
                                        <td>{{ $s->nama ?? '-' }}</td>
                                        <td>{{ $s->nama_kelas ?? '-' }}</td>
                                        <td>
                                            @if($jk)
                                                <span class="jk jk-{{ $jk }}">{{ $jk }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $s->tahun_pelajaran ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="empty">Belum ada siswa baru pada tahun pelajaran berjalan.</p>
                @endif
            </div>
        @endif

        {{-- ================= HALAMAN: Baru Ditambahkan (1 bulan terakhir) ================= --}}
        @if($halaman === 'sebulan')
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3>Baru Ditambahkan</h3>
                        <p>Siswa yang ditambahkan ke sistem sejak {{ $sejak->format('d/m/Y') }}.</p>
                    </div>
                </div>

                @if(count($daftarSebulan) > 0)
                    <div class="table-wrap">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIPD</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>L/P</th>
                                    <th>Status</th>
                                    <th>Ditambahkan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($daftarSebulan as $i => $s)
                                    @php
                                        $linkDetail = $rute('siswa.show', $s->id);
                                        $jk = strtoupper(substr($s->jenis_kelamin ?? '', 0, 1));
                                    @endphp
                                    <tr class="row-{{ $jk }}">
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $s->nis ?: '-' }}</td>
                                        <td>
                                            @if($linkDetail)
                                                <a href="{{ $linkDetail }}"><strong>{{ $s->nama ?? '-' }}</strong></a>
                                            @else
                                                <strong>{{ $s->nama ?? '-' }}</strong>
                                            @endif
                                        </td>
                                        <td>{{ $s->nama_kelas ?? '-' }}</td>
                                        <td>
                                            @if($jk)
                                                <span class="jk jk-{{ $jk }}">{{ $jk }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $badge[$s->status_siswa ?? ''] ?? 'badge-gray' }}">{{ $s->status_siswa ?? '-' }}</span>
                                        </td>
                                        <td>{{ !empty($s->created_at) ? \Carbon\Carbon::parse($s->created_at)->format('d/m/Y H:i') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($jumlahSebulan > count($daftarSebulan))
                        @php $linkDaftar = $rute('siswa.index'); @endphp
                        <p class="table-note">
                            Menampilkan {{ count($daftarSebulan) }} dari {{ number_format($jumlahSebulan, 0, ',', '.') }} siswa terbaru.
                            @if($linkDaftar)
                                <a href="{{ $linkDaftar }}">Lihat daftar siswa lengkap &raquo;</a>
                            @endif
                        </p>
                    @endif
                @else
                    <p class="empty">Belum ada siswa yang ditambahkan sejak {{ $sejak->format('d/m/Y') }}.</p>
                @endif
            </div>
        @endif

    </div>

@endsection