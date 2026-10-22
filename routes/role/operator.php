<?php

/*
|--------------------------------------------------------------------------
| OPERATOR — petugas input data
| URL /operator/…  |  nama route "operator."
| Boleh : lihat siswa, tambah & edit biodata, kelola data orang tua,
| laporan dasar.
| Tidak : ubah status siswa, import Excel, hapus, tahun pelajaran,
| kelas, referensi, export, cetak dokumen.
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\SuperAdmin\LaporanController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\IndexController as MasterIndexController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\ReferensiController as MasterReferensiController;
use App\Http\Controllers\SuperAdmin\SiswaController;
use Illuminate\Support\Facades\Route;

/* ===== Dashboard ===== */
Route::get('/dashboard', fn () => view('super-admin.dashboard'))->name('dashboard');

/* ===== Data Siswa ===== */
Route::prefix('siswa')->name('siswa.')->where(['id' => '[0-9]+'])->group(function () {
    Route::get('/', [SiswaController::class, 'index'])->name('index');
    Route::get('/create', [SiswaController::class, 'create'])->name('create');
    Route::post('/', [SiswaController::class, 'store'])->name('store');

    Route::get('/{id}', [SiswaController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [SiswaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [SiswaController::class, 'update'])->name('update');
});

/* ===== Master Pendukung (lihat, kecuali data orang tua boleh diubah) ===== */
Route::prefix('master-pendukung')->name('master-pendukung.')->group(function () {
    Route::get('/', MasterIndexController::class)->name('index');

    Route::controller(MasterReferensiController::class)->group(function () {
        Route::post('orang-tua', 'storeOrangTua')->name('orang-tua.store');
        Route::put('orang-tua/{orangTua}', 'updateOrangTua')->name('orang-tua.update');
    });
});

/* ===== Laporan (terbatas) ===== */
Route::prefix('laporan')->name('laporan.')->controller(LaporanController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/siswa', 'siswa')->name('siswa');
    Route::get('/per-kelas', 'perKelas')->name('per-kelas');
});