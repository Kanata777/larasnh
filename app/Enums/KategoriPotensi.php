<?php

namespace App\Enums;

/** Bidang potensi/bakat siswa (dipakai pada observasi dan prestasi). */
enum KategoriPotensi: string
{
    case Akademik = 'akademik';
    case BahasaKomunikasi = 'bahasa_komunikasi';
    case SainsTeknologi = 'sains_teknologi';
    case Seni = 'seni';
    case Olahraga = 'olahraga';
    case Kepemimpinan = 'kepemimpinan';
    case SosialEmosional = 'sosial_emosional';

    public function label(): string
    {
        return match ($this) {
            self::Akademik => 'Akademik',
            self::BahasaKomunikasi => 'Bahasa dan Komunikasi',
            self::SainsTeknologi => 'Sains dan Teknologi',
            self::Seni => 'Seni',
            self::Olahraga => 'Olahraga',
            self::Kepemimpinan => 'Kepemimpinan',
            self::SosialEmosional => 'Sosial dan Emosional',
        };
    }

    public function ikon(): string
    {
        return match ($this) {
            self::Akademik => '📚',
            self::BahasaKomunikasi => '🗣️',
            self::SainsTeknologi => '🔬',
            self::Seni => '🎨',
            self::Olahraga => '⚽',
            self::Kepemimpinan => '🧭',
            self::SosialEmosional => '🤝',
        };
    }
}
