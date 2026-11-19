<?php

/*
| Rute modul Bimbingan Konseling (digabung dari larasbk).
| Semua URL diawali /bk dan semua nama rute diawali bk. supaya tidak bentrok dengan rute larasnh.
| Dimuat dari bootstrap/app.php (withRouting: web => [web.php, bk.php]).
| Pembatasan role memakai RoleMiddleware milik larasnh (nilai role = kolom users.role).
*/

use App\Http\Controllers\Bk\DashboardController;
use App\Http\Controllers\Bk\KasusKonselingController;
use App\Http\Controllers\Bk\Konseling\LaporanKonselingController;
use App\Http\Controllers\Bk\NaikKelasController;
use App\Http\Controllers\Bk\Observasi\ObservasiPotensiController;
use App\Http\Controllers\Bk\Observasi\PrestasiSiswaController;
use App\Http\Controllers\Bk\PindahKelasController;
use App\Http\Controllers\Bk\RujukanPsikologController;
use App\Http\Controllers\Bk\SiswaController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

$role = fn(string ...$roles) => RoleMiddleware::class . ':' . implode(',', $roles);

Route::middleware('auth')->prefix('bk')->name('bk.')->group(function () use ($role) {

    // Halaman awal /bk: arahkan menurut role
    Route::get('/', function () {
        $tujuan = [
            'guru_bk' => 'bk.dashboard',
            'kesiswaan' => 'bk.siswa.index',
            'wali_kelas' => 'bk.siswa.index',
            'kepala_tu' => 'bk.siswa.index',
            'staff_tu' => 'bk.siswa.index',
            'kepala_sekolah' => 'bk.siswa.index',
        ][auth()->user()->role->value] ?? null;

        abort_unless($tujuan && Route::has($tujuan), 403, 'Menu BK untuk peran Anda belum tersedia.');

        return redirect()->route($tujuan);
    })->name('home');

    // ------------------------------------------------------------ Dashboard
    Route::middleware($role('guru_bk'))->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    // ------------------------------------------------------------ Data siswa (read-only)
    Route::prefix('siswa')->name('siswa.')->group(function () use ($role) {
        Route::middleware($role('guru_bk', 'kesiswaan', 'wali_kelas', 'kepala_tu', 'staff_tu', 'kepala_sekolah'))->group(function () {
            Route::get('/', [SiswaController::class, 'index'])->name('index');
            Route::get('/{id}', [SiswaController::class, 'show'])->whereNumber('id')->name('show');
        });
    });

    // ------------------------------------------------------------ Kasus Konseling (versi lama, tabel kasus_konseling)
    Route::prefix('kasus-konseling')->name('kasus-konseling.')->group(function () use ($role) {
        Route::middleware($role('guru_bk'))->group(function () {
            Route::get('/pilih-siswa', [KasusKonselingController::class, 'pilihSiswa'])->name('pilih-siswa');
            Route::get('/siswa/{siswa}/buat', [KasusKonselingController::class, 'create'])->whereNumber('siswa')->name('create');
            Route::post('/siswa/{siswa}', [KasusKonselingController::class, 'store'])->whereNumber('siswa')->name('store');
            Route::delete('/{kasus}', [KasusKonselingController::class, 'destroy'])->whereNumber('kasus')->name('destroy');
        });
        Route::middleware($role('guru_bk', 'wali_kelas', 'kepala_sekolah'))->group(function () {
            Route::get('/', [KasusKonselingController::class, 'index'])->name('index');
            Route::get('/{kasus}', [KasusKonselingController::class, 'show'])->whereNumber('kasus')->name('show');
        });
    });

    // ------------------------------------------------------------ Laporan Konseling (versi baru, tabel laporan_konseling)
    Route::prefix('laporan-konseling')->name('laporan-konseling.')->group(function () use ($role) {
        Route::middleware($role('guru_bk'))->group(function () {
            Route::get('/pilih-siswa', [LaporanKonselingController::class, 'pilihSiswa'])->name('pilih-siswa');
            Route::get('/siswa/{siswa}/buat', [LaporanKonselingController::class, 'create'])->whereNumber('siswa')->name('create');
            Route::post('/siswa/{siswa}', [LaporanKonselingController::class, 'store'])->whereNumber('siswa')->name('store');
            Route::get('/{laporan}/ubah', [LaporanKonselingController::class, 'edit'])->whereNumber('laporan')->name('edit');
            Route::put('/{laporan}', [LaporanKonselingController::class, 'update'])->whereNumber('laporan')->name('update');
            Route::delete('/{laporan}', [LaporanKonselingController::class, 'destroy'])->whereNumber('laporan')->name('destroy');
        });
        Route::middleware($role('guru_bk', 'wali_kelas', 'kepala_sekolah'))->group(function () {
            Route::get('/', [LaporanKonselingController::class, 'index'])->name('index');
            Route::get('/{laporan}', [LaporanKonselingController::class, 'show'])->whereNumber('laporan')->name('show');
        });
    });

    // ------------------------------------------------------------ Observasi & Potensi
    Route::prefix('observasi-potensi')->name('observasi-potensi.')->group(function () use ($role) {
        Route::middleware($role('guru_bk'))->group(function () {
            Route::get('/siswa/{siswa}/buat', [ObservasiPotensiController::class, 'create'])->whereNumber('siswa')->name('create');
            Route::post('/siswa/{siswa}', [ObservasiPotensiController::class, 'store'])->whereNumber('siswa')->name('store');
            Route::get('/{observasi}/ubah', [ObservasiPotensiController::class, 'edit'])->whereNumber('observasi')->name('edit');
            Route::put('/{observasi}', [ObservasiPotensiController::class, 'update'])->whereNumber('observasi')->name('update');
            Route::delete('/{observasi}', [ObservasiPotensiController::class, 'destroy'])->whereNumber('observasi')->name('destroy');
        });
        Route::middleware($role('kesiswaan'))->group(function () {
            Route::post('/siswa/{siswa}/prestasi', [PrestasiSiswaController::class, 'store'])->whereNumber('siswa')->name('prestasi.store');
            Route::delete('/prestasi/{prestasi}', [PrestasiSiswaController::class, 'destroy'])->whereNumber('prestasi')->name('prestasi.destroy');
        });
        Route::middleware($role('guru_bk', 'kesiswaan', 'wali_kelas', 'kepala_sekolah'))->group(function () {
            Route::get('/', [ObservasiPotensiController::class, 'index'])->name('index');
            Route::get('/siswa/{siswa}', [ObservasiPotensiController::class, 'profil'])->whereNumber('siswa')->name('profil');
        });
    });

    // ------------------------------------------------------------ Rujukan Psikolog
    Route::prefix('rujukan')->name('rujukan-psikolog.')->group(function () use ($role) {
        Route::middleware($role('guru_bk'))->group(function () {
            Route::get('/', [RujukanPsikologController::class, 'index'])->name('index');
            Route::post('/psikolog', [RujukanPsikologController::class, 'psikologStore'])->name('psikolog.store');
            Route::patch('/psikolog/{psikolog}/toggle', [RujukanPsikologController::class, 'psikologToggle'])->whereNumber('psikolog')->name('psikolog.toggle');
            Route::delete('/psikolog/{psikolog}', [RujukanPsikologController::class, 'psikologDestroy'])->whereNumber('psikolog')->name('psikolog.destroy');
            Route::get('/siswa/{siswa}/buat', [RujukanPsikologController::class, 'create'])->whereNumber('siswa')->name('create');
            Route::post('/siswa/{siswa}', [RujukanPsikologController::class, 'store'])->whereNumber('siswa')->name('store');
            Route::get('/{rujukan}', [RujukanPsikologController::class, 'show'])->whereNumber('rujukan')->name('show');
            Route::put('/{rujukan}/status', [RujukanPsikologController::class, 'updateStatus'])->whereNumber('rujukan')->name('status');
            Route::get('/{rujukan}/surat', [RujukanPsikologController::class, 'surat'])->whereNumber('rujukan')->name('surat');
            Route::delete('/{rujukan}', [RujukanPsikologController::class, 'destroy'])->whereNumber('rujukan')->name('destroy');
        });
    });

    // ------------------------------------------------------------ Pindah Kelas
    // Memindahkan siswa (termasuk memisahkan L/P di kelas 3): guru BK saja.
    // Riwayat perpindahan: boleh dilihat guru BK, wali kelas, Kepala TU, Staff TU.
    Route::prefix('pindah-kelas')->name('pindah-kelas.')->group(function () use ($role) {
        Route::middleware($role('guru_bk'))->group(function () {
            Route::get('/buat', [PindahKelasController::class, 'create'])->name('create');
            Route::post('/', [PindahKelasController::class, 'store'])->name('store');
        });
        Route::middleware($role('guru_bk', 'wali_kelas', 'kepala_tu', 'staff_tu'))->group(function () {
            Route::get('/', [PindahKelasController::class, 'index'])->name('index');
        });
    });

    // ------------------------------------------------------------ Naik Kelas
    Route::prefix('naik-kelas')->name('naik-kelas.')->group(function () use ($role) {

        // Penempatan 1A-1D dan naik otomatis per tingkat (1->2 ... 5->6).
        // Guru BK dibatasi di controller: hanya tingkat 3 (3->4). Staff TU semua tingkat.
        Route::middleware($role('guru_bk', 'staff_tu'))->group(function () {
            Route::get('/penempatan', [NaikKelasController::class, 'penempatan'])->name('penempatan');
            Route::post('/penempatan', [NaikKelasController::class, 'simpanPenempatan'])->name('penempatan.simpan');
            Route::get('/otomatis', [NaikKelasController::class, 'otomatis'])->name('otomatis');
            Route::post('/otomatis', [NaikKelasController::class, 'simpanOtomatis'])->name('otomatis.simpan');
        });

        // Kelulusan kelas 6: Staff TU
        Route::middleware($role('staff_tu'))->group(function () {
            Route::get('/kelulusan', [NaikKelasController::class, 'kelulusan'])->name('kelulusan');
            Route::post('/kelulusan', [NaikKelasController::class, 'simpanKelulusan'])->name('kelulusan.simpan');
        });
    });
});