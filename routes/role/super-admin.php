<?php

/*
|--------------------------------------------------------------------------
| SUPER ADMIN — akses penuh
| URL /super-admin/…  |  nama route "super-admin."
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\LaporanController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\IndexController as MasterIndexController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\KelasController as MasterKelasController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\KelasImportController as MasterKelasImportController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\OrangTuaController as MasterOrangTuaController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\ReferensiController as MasterReferensiController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\TahunPelajaranController as MasterTahunController;
use App\Http\Controllers\SuperAdmin\SiswaController;
use App\Http\Controllers\SuperAdmin\SiswaImportController;
use App\Http\Controllers\SuperAdmin\SiswaExportController;
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
    Route::get('/import/preview', fn() => redirect()->route('super-admin.siswa.import')
        ->with('info', 'Silakan unggah ulang file untuk melanjutkan.'));
    Route::post('/import/process', [SiswaImportController::class, 'process'])->name('import.process');

    // Export Excel
    Route::get('/{id}/export', [SiswaExportController::class, 'siswa'])->name('export');

    // Detail, ubah, status
    Route::get('/terhapus', [SiswaController::class, 'terhapus'])->name('terhapus');
    Route::patch('/{id}/pulihkan', [SiswaController::class, 'restore'])->name('restore');
    Route::delete('/{id}', [SiswaController::class, 'destroy'])->name('destroy');
    Route::get('/{id}', [SiswaController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [SiswaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [SiswaController::class, 'update'])->name('update');
    Route::patch('/{id}/status', [SiswaController::class, 'status'])->name('status');

    // Hapus siswa — hanya Super Admin (aktifkan setelah method destroy dibuat)
    // Route::delete('/{id}', [SiswaController::class, 'destroy'])->name('destroy');
});

/* ===== Master Pendukung ===== */
Route::prefix('master-pendukung')->name('master-pendukung.')->group(function () {
    Route::get('/', MasterIndexController::class)->name('index');

    // Tahun pelajaran
    Route::controller(MasterTahunController::class)->prefix('tahun-pelajaran')->name('tahun-pelajaran.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('{id}', 'update')->name('update');
        Route::patch('{id}/aktifkan', 'activate')->name('activate');
    });

    // Kelas / rombel
    Route::controller(MasterKelasController::class)->prefix('kelas')->name('kelas.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('{id}', 'update')->name('update');
        Route::patch('{id}/wali', 'wali')->name('wali');
    });

    // Import kelas dari Excel (kolom "Rombel Saat Ini" pada file Dapodik)
    Route::controller(MasterKelasImportController::class)->prefix('kelas/import')->name('kelas.import.')->group(function () {
        Route::post('preview', 'preview')->name('preview');
        Route::post('process', 'process')->name('process');
        Route::post('cancel', 'cancel')->name('cancel');
    });

});
/* ===== Data Orang Tua ===== */
Route::prefix('orang-tua')->name('orang-tua.')->controller(MasterOrangTuaController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::put('{orangTua}', 'update')->name('update');
    Route::delete('{orangTua}', 'destroy')->name('destroy');
});
/* ===== Laporan ===== */
Route::prefix('laporan')->name('laporan.')->controller(LaporanController::class)->group(function () {
    Route::get('/', 'index')->name('index');

    // Laporan data
    Route::get('/siswa', 'siswa')->name('siswa');
    Route::get('/per-kelas', 'perKelas')->name('per-kelas');
    Route::get('/jenis-kelamin', 'jenisKelamin')->name('jenis-kelamin');
    Route::get('/siswa-baru', 'siswaBaru')->name('siswa-baru');
    Route::get('/orang-tua', 'orangTua')->name('orang-tua');

    // Cetak dokumen
    Route::get('/dokumen', 'dokumen')->name('dokumen');
    Route::get('/dokumen/hadir', 'dokumenHadir')->name('dokumen.hadir');
    Route::get('/dokumen/label', 'dokumenLabel')->name('dokumen.label');
    Route::get('/dokumen/surat/{id}', 'dokumenSurat')->whereNumber('id')->name('dokumen.surat');

    // Export
    Route::get('/export', 'export')->name('export');
    Route::get('/export/{jenis}', 'unduhExport')->name('export.unduh');
});

/* ===== Kelola User & Role (khusus Super Admin) — BELUM DIBUAT ===== */
// Route::resource('users', \App\Http\Controllers\SuperAdmin\UserController::class)->except('show');