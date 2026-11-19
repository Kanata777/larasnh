<?php

namespace App\Support;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Menentukan kelas mana yang boleh dilihat sebuah akun.
 * - null            : boleh semua kelas (guru BK, kepala sekolah, dll.)
 * - Collection ID   : hanya kelas-kelas itu (wali kelas: kelas utama + asisten/pengganti)
 */
class BatasAksesKelas
{
    public static function idKelasBoleh(User $pengguna): ?Collection
    {
        if ($pengguna->role?->value !== 'wali_kelas') {
            return null;
        }

        $idWali = $pengguna->wali_kelas_sekolah_id;
        if (! $idWali) {
            return collect();   // akun wali kelas belum dihubungkan ke data guru: tidak boleh melihat apa pun
        }

        return Kelas::where('wali_kelas_id', $idWali)->pluck('id')
            ->merge(DB::table('kelas_wali_kelas')
                ->where('wali_kelas_id', $idWali)->pluck('kelas_id'))
            ->unique()->values();
    }

    /**
     * Daftar kelas untuk pilihan filter: kelas aktif yang sudah berisi siswa
     * dan boleh dilihat akun ini.
     */
    public static function kelasUntukFilter(User $pengguna)
    {
        $terisi = DB::table('riwayat_kelas as r')
            ->join('siswa as s', 's.id', '=', 'r.siswa_id')
            ->where('r.status', 'Aktif')
            ->whereNull('s.deleted_at')
            ->select('r.kelas_id');

        $boleh = self::idKelasBoleh($pengguna);

        return Kelas::where('status', 'aktif')
            ->whereIn('id', $terisi)
            ->when($boleh !== null, fn ($k) => $k->whereIn('id', $boleh))
            ->orderBy('tingkat')->orderBy('nama_kelas')->get();
    }
}
