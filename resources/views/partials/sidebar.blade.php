{{--
    Sidebar bersama untuk semua role.
    Menu tampil/hilang otomatis mengikuti route yang dimiliki role tersebut
    (lihat routes/web.php dan routes/role/*.php), jadi tidak perlu if per role di sini.
--}}
@php
    $p = \App\Support\RoleRoute::prefix();                         // super-admin | kepala-sekolah | ...
    $url = fn (string $nama) => \App\Support\RoleRoute::to($nama);
    $ada = fn (string $nama) => \App\Support\RoleRoute::has($nama);

    $adaMaster = $ada('master-pendukung.index');
    $urlMaster = $adaMaster ? $url('master-pendukung.index') : '#';
    $onMaster = request()->routeIs($p . '.master-pendukung.*');

    // Tab master: Status Siswa tidak untuk Operator; Referensi hanya untuk role yang boleh mengubahnya
    $tabStatus = $adaMaster && !auth()->user()->hasRole(\App\Enums\Role::Operator);
    $tabReferensi = $ada('master-pendukung.referensi.store');
@endphp

<aside class="sidebar">
    <div class="brand">
        🎓 SISTEM DATA SISWA
        <small>Administrasi Sekolah Terintegrasi</small>
    </div>

    <div class="nav-title">Menu utama</div>
    <a class="nav-link {{ request()->routeIs($p . '.dashboard') ? 'active' : '' }}"
        href="{{ $url('dashboard') }}">🏠 Dashboard</a>

    @if($adaMaster)
        <div class="nav-title">Master Data</div>
        {{-- Menu yang aktif mengikuti tab yang sedang terbuka (diatur oleh skrip di layout) --}}
        <a class="nav-link {{ $onMaster ? 'active' : '' }}" data-master-tab="tahun-pelajaran"
            href="{{ $urlMaster }}#tahun-pelajaran">📅 Tahun Pelajaran</a>
        <a class="nav-link" data-master-tab="kelas" href="{{ $urlMaster }}#kelas">🏫 Kelas / Rombel</a>
        @if($tabStatus)
            <a class="nav-link" data-master-tab="status-siswa" href="{{ $urlMaster }}#status-siswa">📋 Status Siswa</a>
        @endif
        <a class="nav-link" data-master-tab="orang-tua" href="{{ $urlMaster }}#orang-tua">👨‍👩‍👧 Data Orang Tua Siswa</a>
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

    @if($ada('laporan.index'))
        <div class="nav-title">Sistem</div>
        <a class="nav-link {{ request()->routeIs($p . '.laporan.*') ? 'active' : '' }}"
            href="{{ $url('laporan.index') }}">📊 Output &amp; Laporan</a>
    @endif

    {{-- Khusus Super Admin: aktifkan setelah menu Kelola User dibuat
    @if($ada('users.index'))
        <div class="nav-title">Pengaturan</div>
        <a class="nav-link {{ request()->routeIs($p . '.users.*') ? 'active' : '' }}"
            href="{{ $url('users.index') }}">🔐 Kelola User &amp; Role</a>
    @endif
    --}}
</aside>