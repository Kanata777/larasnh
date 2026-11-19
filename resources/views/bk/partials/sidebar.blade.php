{{--
Sidebar modul BK (menu per role). Staff TU tidak memakai sidebar ini
(lihat bk/layouts/app.blade.php: Staff TU memakai partials.sidebar).
Item: [nama rute, label, role yang boleh melihat, pola rute aktif, parameter opsional]
--}}
@php
    $peran = auth()->user()->role->value;
    $ada = fn(string $nama) => \Illuminate\Support\Facades\Route::has($nama);

    $tampilkanSegera = true;
    $semuaLihat = ['guru_bk', 'kesiswaan', 'wali_kelas', 'kepala_tu', 'staff_tu', 'kepala_sekolah'];

    $grup = [
        'Menu utama' => [
            ['bk.dashboard', '🏠 Dashboard', ['guru_bk'], 'bk.dashboard'],
        ],
        'Data siswa' => [
            ['bk.siswa.index', '👥 Daftar Siswa', $semuaLihat, 'bk.siswa.*'],
        ],
        'Layanan BK' => [
            ['bk.laporan-konseling.index', '💬 Laporan Konseling', ['guru_bk', 'wali_kelas', 'kepala_sekolah'], 'bk.laporan-konseling.*'],
            ['bk.kasus-konseling.index', '🗂️ Kasus Konseling', ['guru_bk', 'wali_kelas', 'kepala_sekolah'], 'bk.kasus-konseling.*'],
            ['bk.observasi-potensi.index', '🔎 Observasi & Potensi', ['guru_bk', 'kesiswaan', 'wali_kelas', 'kepala_sekolah'], 'bk.observasi-potensi.*'],
            ['bk.rujukan-psikolog.index', '🩺 Rujukan Psikolog', ['guru_bk'], 'bk.rujukan-psikolog.*'],
        ],
        'Penempatan & Naik Kelas' => [
            ['bk.naik-kelas.penempatan', '🏷️ Penempatan Kelas 1A–1D', ['guru_bk'], 'bk.naik-kelas.penempatan'],
            ['bk.naik-kelas.otomatis', '🎓 Naik Kelas 3 → 4', ['guru_bk'], 'bk.naik-kelas.otomatis', ['tingkat' => 3]],
            ['bk.pindah-kelas.create', '🔀 Pindahkan Siswa', ['guru_bk'], 'bk.pindah-kelas.create'],
        ],
        'Riwayat' => [
            ['bk.pindah-kelas.index', '📜 Riwayat Pindah Kelas', ['guru_bk', 'wali_kelas', 'kepala_tu'], 'bk.pindah-kelas.index'],
        ],
        'Sistem' => [
            ['bk.laporan.index', '📊 Laporan Layanan', ['guru_bk', 'kepala_sekolah'], 'bk.laporan.*'],
        ],
    ];
@endphp

<aside class="sidebar">
    <div class="brand">
        🎓 SISTEM DATA SISWA
        <small>Administrasi Sekolah Terintegrasi</small>
    </div>

    @foreach($grup as $judul => $menu)
        @php
            $tampil = collect($menu)->filter(
                fn($m) => in_array($peran, $m[2])
                    && ($tampilkanSegera || $ada($m[0]))
            );
        @endphp

        @if($tampil->isNotEmpty())
            <div class="nav-title">{{ $judul }}</div>

            @foreach($tampil as $m)
                @php
                    $query = $m[4] ?? [];
                    $aktif = request()->routeIs($m[3])
                        && ($query === [] || (int) request('tingkat', 3) === (int) $query['tingkat']);
                @endphp
                @if($ada($m[0]))
                    <a class="nav-link {{ $aktif ? 'active' : '' }}"
                        href="{{ route($m[0], $query) }}">{{ $m[1] }}</a>
                @else
                    <span class="nav-link disabled" style="opacity:.5;cursor:not-allowed;pointer-events:none;">
                        {{ $m[1] }} <small>Segera</small>
                    </span>
                @endif
            @endforeach
        @endif
    @endforeach
</aside>