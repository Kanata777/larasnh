<?php

namespace App\Support;

use App\Enums\Role;
use Illuminate\Support\Facades\Auth;

/**
 * Membuat URL/route sesuai role user yang sedang login.
 *
 * Semua role memakai nama route yang sama di belakang prefix-nya:
 *   super-admin.siswa.show | kepala-tu.siswa.show | staff-tu.siswa.show | operator.siswa.show | ...
 *
 * Pemakaian:
 *   Controller : return redirect(RoleRoute::to('siswa.show', $id));
 *   Blade      : {{ \App\Support\RoleRoute::to('siswa.index') }}
 *   Blade (aman, untuk tombol/form yang mungkin tidak dimiliki role): {{ \App\Support\RoleRoute::safe('siswa.store') }}
 */
class RoleRoute
{
    public static function prefix(?Role $role = null): string
    {
        return match ($role ?? Auth::user()->role) {
            Role::SuperAdmin => 'super-admin',
            Role::KepalaTU => 'kepala-tu',
            Role::StaffTU => 'staff-tu',
            Role::Operator => 'operator',
            Role::KepalaSekolah => 'kepala-sekolah',
            Role::Guru => 'guru',
            Role::GuruBk => 'guru-bk',
            Role::Kesiswaan => 'kesiswaan',
            Role::WaliKelas => 'wali-kelas',
            Role::GuruMapel => 'guru-mapel',
            Role::OrangTua => 'orang-tua',
        };
    }

    public static function to(string $nama, mixed $parameter = [], bool $absolute = true): string
    {
        return route(self::prefix() . '.' . $nama, $parameter, $absolute);
    }

    /** True jika route tersebut ada untuk role yang sedang login (untuk menyembunyikan menu/tombol). */
    public static function has(string $nama): bool
    {
        return \Illuminate\Support\Facades\Route::has(self::prefix() . '.' . $nama);
    }

    /**
     * Seperti to(), tetapi mengembalikan '#' jika role ini tidak punya route tersebut.
     * Dipakai di view bersama: panel yang disembunyikan untuk suatu role tidak membuat halaman error.
     */
    public static function safe(string $nama, mixed $parameter = [], bool $absolute = true): string
    {
        return self::has($nama) ? self::to($nama, $parameter, $absolute) : '#';
    }
}