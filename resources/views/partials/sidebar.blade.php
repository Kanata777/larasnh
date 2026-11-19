{{--
Sidebar bersama untuk semua role.
Menu tampil/hilang otomatis mengikuti route yang dimiliki role tersebut
(lihat routes/web.php dan routes/role/*.php), jadi tidak perlu if per role di sini.
--}}
@php
    $p = \App\Support\RoleRoute::prefix();                         // super-admin | kepala-sekolah | ...
    $url = fn(string $nama) => \App\Support\RoleRoute::to($nama);
    $ada = fn(string $nama) => \App\Support\RoleRoute::has($nama);

    $adaMaster = $ada('master-pendukung.index');
    $urlMaster = $adaMaster ? $url('master-pendukung.index') : '#';
    $onMaster = request()->routeIs($p . '.master-pendukung.*');

    // Tab master: Status Siswa tidak untuk Operator; Referensi hanya untuk role yang boleh mengubahnya
    $tabStatus = $adaMaster && !auth()->user()->hasRole(\App\Enums\Role::Operator);
    $tabReferensi = $ada('master-pendukung.referensi.store');

    // Menu kenaikan kelas untuk Staff TU (route bk.*, diatur di routes/bk.php)
    $menuKelasBk = auth()->user()->hasRole(\App\Enums\Role::StaffTU)
        && \Illuminate\Support\Facades\Route::has('bk.naik-kelas.otomatis');
    $onOtomatis = request()->routeIs('bk.naik-kelas.otomatis');
@endphp

<aside class="sidebar">
    <div class="brand">
        🎓 SISTEM DATA SISWA
        <small>Administrasi Sekolah Terintegrasi</small>
    </div>

    <div class="nav-title">Menu utama</div>
    <a class="nav-link {{ request()->routeIs($p . '.dashboard') ? 'active' : '' }}" href="{{ $url('dashboard') }}">🏠
        Dashboard</a>

    @if($adaMaster)
        <div class="nav-title">Master Data</div>
        {{-- Menu yang aktif mengikuti tab yang sedang terbuka (diatur oleh skrip di layout) --}}
        @if($ada('master-pendukung.tahun-pelajaran.store'))
            <a class="nav-link {{ $onMaster ? 'active' : '' }}" data-master-tab="tahun-pelajaran"
                href="{{ $urlMaster }}#tahun-pelajaran">📅 Tahun Pelajaran</a>
        @endif
        <a class="nav-link" data-master-tab="kelas" href="{{ $urlMaster }}#kelas">🏫 Kelas / Rombel</a>
        @if($tabStatus)
            <a class="nav-link" data-master-tab="status-siswa" href="{{ $urlMaster }}#status-siswa">📋 Status Siswa</a>
        @endif
    @endif

    @if($ada('siswa.index'))
        <div class="nav-title">Data siswa</div>
        <a class="nav-link {{ request()->routeIs($p . '.siswa.index', $p . '.siswa.show', $p . '.siswa.edit') ? 'active' : '' }}"
            href="{{ $url('siswa.index') }}">👥 Daftar Siswa</a>
        @if($ada('siswa.create'))
            <a class="nav-link {{ request()->routeIs($p . '.siswa.create') ? 'active' : '' }}"
                href="{{ $url('siswa.create') }}">➕ Tambah Siswa</a>
        @endif
        @if($ada('siswa.import'))
            <a class="nav-link {{ request()->routeIs($p . '.siswa.import*') ? 'active' : '' }}"
                href="{{ $url('siswa.import') }}">📥 Import Excel</a>
        @endif
    @endif
    {{-- Khusus Staff TU: kenaikan kelas & kelulusan (modul BK) --}}
    @if($menuKelasBk)
        <div class="nav-title">Kenaikan &amp; Kelulusan</div>
        {{-- Satu tombol: pilihan tingkat (1→2, 2→3, 4→5, 5→6) ada di halaman Naik Kelas. Penempatan kelas 1 dan naik 3→4 milik Guru BK --}}
        <a class="nav-link {{ $onOtomatis ? 'active' : '' }}"
            href="{{ route('bk.naik-kelas.otomatis', ['tingkat' => 1]) }}">🎓 Naik Kelas</a>
        <a class="nav-link {{ request()->routeIs('bk.naik-kelas.kelulusan') ? 'active' : '' }}"
            href="{{ route('bk.naik-kelas.kelulusan') }}">🎖️ Kelulusan (Alumni)</a>
        <a class="nav-link {{ request()->routeIs('bk.pindah-kelas.index') ? 'active' : '' }}"
            href="{{ route('bk.pindah-kelas.index') }}">📜 Riwayat Perpindahan</a>
    @endif
    @if($ada('orang-tua.index'))
        <div class="nav-title">Data orang tua</div>
        <a class="nav-link {{ request()->routeIs($p . '.orang-tua.*') ? 'active' : '' }}"
            href="{{ $url('orang-tua.index') }}">👨‍👩‍👧 Data Orang Tua Siswa</a>
    @endif

    @if($ada('laporan.index'))
        <div class="nav-title">Sistem</div>
        <a class="nav-link {{ request()->routeIs($p . '.laporan.*') ? 'active' : '' }}"
            href="{{ $url('laporan.index') }}">📊 Output &amp; Laporan</a>
    @endif

    {{-- Khusus Super Admin: aktifkan setelah menu Kelola User dibuat
    @if($ada('users.index'))
    <div class="nav-title">Pengaturan</div>
    <a class="nav-link {{ request()->routeIs($p . '.users.*') ? 'active' : '' }}" href="{{ $url('users.index') }}">🔐
        Kelola User &amp; Role</a>
    @endif
    --}}
</aside>