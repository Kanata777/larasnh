<?php

/*
|--------------------------------------------------------------------------
| KEPALA TU  —  penanggung jawab data siswa
|--------------------------------------------------------------------------
| URL /kepala-tu/…  |  nama route "kepala-tu."  (diatur di routes/web.php)
|
| Boleh : semua yang boleh Staff TU, ditambah bagian "TAMBAHAN KEPALA TU"
|         di bawah (tahun pelajaran, wali kelas, ubah referensi, hapus
|         orang tua).
| Tidak : hapus siswa & kelola user (khusus Super Admin).
*/

use App\Http\Controllers\SuperAdmin\LaporanController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\IndexController as MasterIndexController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\KelasController as MasterKelasController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\KelasImportController as MasterKelasImportController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\ReferensiController as MasterReferensiController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\TahunPelajaranController as MasterTahunController;
use App\Http\Controllers\SuperAdmin\SiswaController;
use App\Http\Controllers\SuperAdmin\SiswaImportController;
use Illuminate\Support\Facades\Route;

/* ===== Dashboard ===== */
Route::get('/dashboard', fn () => view('super-admin.dashboard'))->name('dashboard');

/* ===== Data Siswa ===== */
Route::prefix('siswa')->name('siswa.')->where(['id' => '[0-9]+'])->group(function () {
    Route::get('/', [SiswaController::class, 'index'])->name('index');
    Route::get('/create', [SiswaController::class, 'create'])->name('create');
    Route::post('/', [SiswaController::class, 'store'])->name('store');

    // Import Excel
    Route::get('/import', [SiswaImportController::class, 'index'])->name('import');
    Route::get('/import/template', [SiswaImportController::class, 'template'])->name('import.template');
    Route::post('/import/preview', [SiswaImportController::class, 'preview'])->name('import.preview');
    Route::get('/import/preview', fn () => redirect()->route('kepala-tu.siswa.import')
        ->with('info', 'Silakan unggah ulang file untuk melanjutkan.'));
    Route::post('/import/process', [SiswaImportController::class, 'process'])->name('import.process');

    // Detail, ubah, status (mutasi/lulus)
    Route::get('/{id}', [SiswaController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [SiswaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [SiswaController::class, 'update'])->name('update');
    Route::patch('/{id}/status', [SiswaController::class, 'status'])->name('status');
});

/* ===== Master Pendukung ===== */
Route::prefix('master-pendukung')->name('master-pendukung.')->group(function () {
    Route::get('/', MasterIndexController::class)->name('index');

    // Kelas / rombel (tambah, ubah, import) — wali kelas khusus Kepala TU
    Route::controller(MasterKelasController::class)->prefix('kelas')->name('kelas.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('{id}', 'update')->name('update');
    });
    Route::controller(MasterKelasImportController::class)->prefix('kelas/import')->name('kelas.import.')->group(function () {
        Route::post('preview', 'preview')->name('preview');
        Route::post('process', 'process')->name('process');
        Route::post('cancel', 'cancel')->name('cancel');
    });

    // Data orang tua (tambah, ubah) — hapus khusus Kepala TU
    Route::controller(MasterReferensiController::class)->group(function () {
        Route::post('orang-tua', 'storeOrangTua')->name('orang-tua.store');
        Route::put('orang-tua/{orangTua}', 'updateOrangTua')->name('orang-tua.update');
    });
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

/*
|--------------------------------------------------------------------------
| TAMBAHAN KEPALA TU  (tidak dimiliki Staff TU)
|--------------------------------------------------------------------------
*/
Route::prefix('master-pendukung')->name('master-pendukung.')->group(function () {

    // Tahun pelajaran
    Route::controller(MasterTahunController::class)->prefix('tahun-pelajaran')->name('tahun-pelajaran.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('{id}', 'update')->name('update');
        Route::patch('{id}/aktifkan', 'activate')->name('activate');
    });

    // Wali kelas
    Route::patch('kelas/{id}/wali', [MasterKelasController::class, 'wali'])->name('kelas.wali');

    // Referensi (ubah) & hapus orang tua
    Route::controller(MasterReferensiController::class)->group(function () {
        Route::post('referensi', 'store')->name('referensi.store');
        Route::put('referensi/{id}', 'update')->name('referensi.update');
        Route::delete('orang-tua/{orangTua}', 'destroyOrangTua')->name('orang-tua.destroy');
    });
});