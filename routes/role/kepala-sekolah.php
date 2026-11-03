<?php

/*
| KEPALA SEKOLAH — read only: lihat & cari siswa, laporan, cetak, export (unduh).
| Tidak ada route tambah / ubah / hapus / import.
*/

use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\LaporanController;
use App\Http\Controllers\SuperAdmin\SiswaController;
use App\Http\Controllers\SuperAdmin\SiswaExportController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

/* Data siswa: hanya daftar (termasuk pencarian) dan detail */
Route::prefix('siswa')->name('siswa.')->where(['id' => '[0-9]+'])->group(function () {
    Route::get('/', [SiswaController::class, 'index'])->name('index');
    Route::get('/{id}/export', [SiswaExportController::class, 'siswa'])->name('export');
    Route::get('/{id}', [SiswaController::class, 'show'])->name('show');
});

/* Laporan, cetak, export */
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

    Route::get('/export', 'export')->name('export');
    Route::get('/export/{jenis}', 'unduhExport')->name('export.unduh');
});