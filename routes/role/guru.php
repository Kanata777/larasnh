<?php

/*
|--------------------------------------------------------------------------
| GURU — wali kelas (hanya lihat, terbatas kelasnya)
| URL /guru/…  |  nama route "guru."
| PENTING: route di bawah hanya membuka pintu. Pembatasan "hanya siswa di
| kelas yang diampu" harus dilakukan di controller (filter berdasarkan
| kelas.wali_user_id = user yang login), bukan di file route ini.
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\SuperAdmin\LaporanController;
use App\Http\Controllers\SuperAdmin\SiswaController;
use Illuminate\Support\Facades\Route;

/* ===== Dashboard ===== */
Route::get('/dashboard', fn () => view('super-admin.dashboard'))->name('dashboard');

/* ===== Data Siswa (hanya lihat) ===== */
Route::prefix('siswa')->name('siswa.')->where(['id' => '[0-9]+'])->group(function () {
    Route::get('/', [SiswaController::class, 'index'])->name('index');
    Route::get('/{id}', [SiswaController::class, 'show'])->name('show');
});

/* ===== Laporan (hanya lihat) ===== */
Route::prefix('laporan')->name('laporan.')->controller(LaporanController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/siswa', 'siswa')->name('siswa');
    Route::get('/per-kelas', 'perKelas')->name('per-kelas');
    Route::get('/jenis-kelamin', 'jenisKelamin')->name('jenis-kelamin');
    Route::get('/siswa-baru', 'siswaBaru')->name('siswa-baru');
});