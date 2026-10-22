<?php

/*
|--------------------------------------------------------------------------
| KEPALA SEKOLAH — pemantau (hanya lihat & cetak)
| URL /kepala-sekolah/…  |  nama route "kepala-sekolah."
| Boleh : lihat siswa, lihat master, semua laporan, cetak dokumen.
| Tidak : tambah/ubah/hapus apa pun, import, export, surat.
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\LaporanController;
use App\Http\Controllers\SuperAdmin\MasterPendukung\IndexController as MasterIndexController;
use App\Http\Controllers\SuperAdmin\SiswaController;
use Illuminate\Support\Facades\Route;

/* ===== Dashboard ===== */
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

/* ===== Data Siswa (hanya lihat) ===== */
Route::prefix('siswa')->name('siswa.')->where(['id' => '[0-9]+'])->group(function () {
    Route::get('/', [SiswaController::class, 'index'])->name('index');
    Route::get('/{id}', [SiswaController::class, 'show'])->name('show');
});

/* ===== Master Pendukung (hanya lihat) ===== */
Route::get('/master-pendukung', MasterIndexController::class)->name('master-pendukung.index');

/* ===== Laporan & cetak ===== */
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
});