<?php

namespace App\Enums;

/** Komponen layanan bimbingan dan konseling. */
enum KomponenLayanan: string
{
    case LayananDasar = 'layanan_dasar';
    case LayananResponsif = 'layanan_responsif';
    case PerencanaanIndividual = 'perencanaan_individual';
    case DukunganSistem = 'dukungan_sistem';

    public function label(): string
    {
        return match ($this) {
            self::LayananDasar => 'Layanan Dasar',
            self::LayananResponsif => 'Layanan Responsif',
            self::PerencanaanIndividual => 'Perencanaan Individual',
            self::DukunganSistem => 'Dukungan Sistem',
        };
    }
}
