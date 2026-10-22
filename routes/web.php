<?php

use App\Enums\Role;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\RoleMiddleware;
use App\Support\RoleRoute;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PETA ROUTE
|--------------------------------------------------------------------------
| Setiap role punya satu file di routes/role/ dengan prefix URL dan awalan
| nama route sendiri:
|
|   Role                   URL                 Nama route         File
|   ---------------------  ------------------  -----------------  -------------------------
|   Super Admin            /super-admin/…      super-admin.…      role/super-admin.php
|   Kepala TU              /kepala-tu/…        kepala-tu.…        role/kepala-tu.php
|   Staff TU               /staff-tu/…         staff-tu.…         role/staff-tu.php
|   Operator               /operator/…         operator.…         role/operator.php
|   Kepala Sekolah         /kepala-sekolah/…   kepala-sekolah.…   role/kepala-sekolah.php
|   Guru                   /guru/…             guru.…             role/guru.php
|
| Mengubah hak akses suatu role = edit file role tersebut saja.
| Nama route di belakang prefix sengaja sama antar role (siswa.show, dst.),
| supaya bisa dipanggil dengan RoleRoute::to('siswa.show', $id).
*/

/* ---------- Publik ---------- */
Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.process');
});

/* ---------- Wajib login ---------- */
Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Arahkan user ke dashboard sesuai role-nya
    Route::get('/dashboard', fn () => redirect()->route(RoleRoute::prefix() . '.dashboard'))
        ->name('dashboard');

    // Kompatibilitas: kode lama yang masih memanggil route('tu.dashboard')
    // (mis. LoginController atau sidebar) diarahkan ke dashboard sesuai role.
    // Hapus setelah semua pemanggilan diganti ke route('dashboard').
    Route::get('/tu/dashboard', fn () => redirect()->route('dashboard'))->name('tu.dashboard');

    // ----- Super Admin -----
    Route::prefix('super-admin')->name('super-admin.')
        ->middleware(RoleMiddleware::untuk(Role::SuperAdmin))
        ->group(base_path('routes/role/super-admin.php'));

    // ----- Kepala TU -----
    Route::prefix('kepala-tu')->name('kepala-tu.')
        ->middleware(RoleMiddleware::untuk(Role::KepalaTU))
        ->group(base_path('routes/role/kepala-tu.php'));

    // ----- Staff TU -----
    Route::prefix('staff-tu')->name('staff-tu.')
        ->middleware(RoleMiddleware::untuk(Role::StaffTU))
        ->group(base_path('routes/role/staff-tu.php'));

    // ----- Operator -----
    Route::prefix('operator')->name('operator.')
        ->middleware(RoleMiddleware::untuk(Role::Operator))
        ->group(base_path('routes/role/operator.php'));

    // ----- Kepala Sekolah -----
    Route::prefix('kepala-sekolah')->name('kepala-sekolah.')
        ->middleware(RoleMiddleware::untuk(Role::KepalaSekolah))
        ->group(base_path('routes/role/kepala-sekolah.php'));

    // ----- Guru -----
    Route::prefix('guru')->name('guru.')
        ->middleware(RoleMiddleware::untuk(Role::Guru))
        ->group(base_path('routes/role/guru.php'));
});