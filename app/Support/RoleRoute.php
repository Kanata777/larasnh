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
}