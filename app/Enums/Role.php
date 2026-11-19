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

    // Role dari modul Bimbingan Konseling (larasbk)
    case GuruBk = 'guru_bk';
    case Kesiswaan = 'kesiswaan';
    case WaliKelas = 'wali_kelas';
    case GuruMapel = 'guru_mapel';
    case OrangTua = 'orang_tua';

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
            self::GuruBk => 'Guru BK',
            self::Kesiswaan => 'Kesiswaan',
            self::WaliKelas => 'Wali Kelas',
            self::GuruMapel => 'Guru Mapel',
            self::OrangTua => 'Orang Tua',
        };
    }
}
