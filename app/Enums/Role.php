<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case KepalaTU = 'kepala_tu';
    case StaffTU = 'staff_tu';
    case Operator = 'operator';
    case KepalaSekolah = 'kepala_sekolah';
    case Guru = 'guru';

    /**
     * Nama role untuk ditampilkan di halaman login.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrator',
            self::KepalaTU => 'Kepala Tata Usaha',
            self::StaffTU => 'Staff Tata Usaha',
            self::Operator => 'Operator Sekolah',
            self::KepalaSekolah => 'Kepala Sekolah',
            self::Guru => 'Guru',
        };
    }
}
