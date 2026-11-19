<?php

namespace App\Enums;

/** Tingkat potensi hasil observasi. */
enum TingkatPotensi: string
{
    case Menonjol = 'menonjol';
    case Berkembang = 'berkembang';
    case PerluStimulasi = 'perlu_stimulasi';

    public function label(): string
    {
        return match ($this) {
            self::Menonjol => 'Menonjol',
            self::Berkembang => 'Berkembang',
            self::PerluStimulasi => 'Perlu stimulasi',
        };
    }

    /** Kelas warna badge di layout. */
    public function badge(): string
    {
        return match ($this) {
            self::Menonjol => 'badge-green',
            self::Berkembang => 'badge-blue',
            self::PerluStimulasi => 'badge-orange',
        };
    }
}
