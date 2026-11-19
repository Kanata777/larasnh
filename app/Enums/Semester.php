<?php

namespace App\Enums;

use Carbon\CarbonInterface;

enum Semester: string
{
    case Ganjil = 'ganjil';
    case Genap = 'genap';

    public function label(): string
    {
        return $this === self::Ganjil ? 'Ganjil' : 'Genap';
    }

    /** Tahun ajaran SD: Juli-Desember = ganjil, Januari-Juni = genap. */
    public static function dariTanggal(CarbonInterface $tanggal): self
    {
        return $tanggal->month >= 7 ? self::Ganjil : self::Genap;
    }
}
