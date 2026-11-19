<?php

/*
|--------------------------------------------------------------------------
| STAFF TU  —  pengelola data harian
|--------------------------------------------------------------------------
| URL /staff-tu/…  |  nama route "staff-tu."  (diatur di routes/web.php)
|
| Sama dengan Super Admin, KECUALI:
|   - Tahun pelajaran            : tidak ada
|   - Wali kelas (atur wali)     : tidak ada
|   - Ubah referensi             : tidak ada
|   - Hapus orang tua            : tidak ada (khusus Kepala TU)
|   - Hapus / pulihkan siswa     : tidak ada (khusus Super Admin)
|   - Kelola user & role         : tidak ada (khusus Super Admin)
|
| Tambahan dari modul BK (diatur di routes/bk.php, URL /bk/...):
|   - Naik Kelas  (bk.naik-kelas.*)
|   - Pindah Kelas (bk.pindah-kelas.*)
*/

use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\LaporanController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\IndexController as MasterIndexController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\KelasController as MasterKelasController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\KelasImportController as MasterKelasImportController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\OrangTuaController as MasterOrangTuaController;
use App\Http\Controllers\SuperAdmin\SiswaController;
use App\Http\Controllers\SuperAdmin\SiswaExportController;
use App\Http\Controllers\SuperAdmin\SiswaImportController;
use Illuminate\Support\Facades\Route;

/* ===== Dashboard ===== */
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

/* ===== Data Siswa ===== */
Route::prefix('siswa')->name('siswa.')->where(['id' => '[0-9]+'])->group(function () {
    Route::get('/', [SiswaController::class, 'index'])->name('index');
    Route::get('/create', [SiswaController::class, 'create'])->name('create');
    Route::post('/', [SiswaController::class, 'store'])->name('store');

    // Import Excel
    Route::get('/import', [SiswaImportController::class, 'index'])->name('import');
    Route::get('/import/template', [SiswaImportController::class, 'template'])->name('import.template');
    Route::post('/import/preview', [SiswaImportController::class, 'preview'])->name('import.preview');
    Route::get('/import/preview', fn () => redirect()->route('staff-tu.siswa.import')
        ->with('info', 'Silakan unggah ulang file untuk melanjutkan.'));
    Route::post('/import/process', [SiswaImportController::class, 'process'])->name('import.process');

    // Export Excel
    Route::get('/{id}/export', [SiswaExportController::class, 'siswa'])->name('export');

    // Detail, ubah, status (mutasi/lulus)
    Route::get('/{id}', [SiswaController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [SiswaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [SiswaController::class, 'update'])->name('update');
    Route::patch('/{id}/status', [SiswaController::class, 'status'])->name('status');

    // TIDAK ADA: terhapus, pulihkan, hapus siswa (khusus Super Admin)
});

/* ===== Master Pendukung ===== */
Route::prefix('master-pendukung')->name('master-pendukung.')->group(function () {
    Route::get('/', MasterIndexController::class)->name('index');

    // Kelas / rombel (tambah, ubah, import). Atur wali kelas TIDAK ada.
    Route::controller(MasterKelasController::class)->prefix('kelas')->name('kelas.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('{id}', 'update')->name('update');
    });
    Route::controller(MasterKelasImportController::class)->prefix('kelas/import')->name('kelas.import.')->group(function () {
        Route::post('preview', 'preview')->name('preview');
        Route::post('process', 'process')->name('process');
        Route::post('cancel', 'cancel')->name('cancel');
    });

    // TIDAK ADA: tahun pelajaran, referensi
});

/* ===== Data Orang Tua (tambah, ubah; hapus khusus Kepala TU) ===== */
Route::prefix('orang-tua')->name('orang-tua.')->controller(MasterOrangTuaController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::put('{orangTua}', 'update')->name('update');
});

/* ===== Laporan ===== */
Route::prefix('laporan')->name('laporan.')->controller(LaporanController::class)->group(function () {
    Route::get('/', 'index')->name('index');

    Route::get('/siswa', 'siswa')->name('siswa');
    Route::get('/per-kelas', 'perKelas')->name('per-kelas');
    Route::get('/jenis-kelamin', 'jenisKelamin')->name('jenis-kelamin');
    Route::get('/siswa-baru', 'siswaBaru')->name('siswa-baru');
    Route::get('/orang-tua', 'orangTua')->name('orang-tua');

    Route::get('/dokumen', 'dokumen')->name('dokumen');
    Route::get('/dokumen/hadir', 'dokumenHadir')->name('dokumen.hadir');
    Route::get('/dokumen/label', 'dokumenLabel')->name('dokumen.label');
    Route::get('/dokumen/surat/{id}', 'dokumenSurat')->whereNumber('id')->name('dokumen.surat');

    Route::get('/export', 'export')->name('export');
    Route::get('/export/{jenis}', 'unduhExport')->name('export.unduh');
});