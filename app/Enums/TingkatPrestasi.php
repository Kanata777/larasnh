<?php

namespace App\Enums;

/** Tingkat lomba/prestasi (sesuai kolom tabel prestasi). */
enum TingkatPrestasi: string
{
    case Sekolah = 'sekolah';
    case Kecamatan = 'kecamatan';
    case Kabupaten = 'kabupaten';
    case Provinsi = 'provinsi';
    case Nasional = 'nasional';
    case Internasional = 'internasional';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
