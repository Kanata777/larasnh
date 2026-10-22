<?php

namespace App\Support;

use App\Enums\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Satu tempat untuk aturan "role ini boleh/tidak boleh apa" di level DATA
 * (route hanya membuka pintu; aturan di bawah memilah isinya).
 */
class RoleAccess
{
    /** Kolom sensitif (nama field mengikuti App\Support\SiswaData) yang disembunyikan dari Operator & Guru. */
    public const KOLOM_SENSITIF = [
        'nik', 'no_kk', 'nik_ayah', 'nik_ibu', 'nik_wali',
        'skhun', 'no_peserta_un', 'no_seri_ijazah',
        'penerima_kps', 'no_kps', 'penerima_kip', 'no_kip', 'nama_di_kip', 'no_kks',
        'layak_pip', 'alasan_layak_pip',
        'bank', 'no_rekening', 'rekening_atas_nama',
    ];

    private static function adalah(Role ...$roles): bool
    {
        return (bool) Auth::user()?->hasRole(...$roles);
    }

    /** NIK, KIP/KPS, rekening, dll. */
    public static function lihatDataSensitif(): bool
    {
        return self::adalah(Role::SuperAdmin, Role::KepalaTU, Role::StaffTU);
    }

    /** Mengubah status siswa (aktif, pindah, lulus, dst.). */
    public static function ubahStatusSiswa(): bool
    {
        return self::adalah(Role::SuperAdmin, Role::KepalaTU, Role::StaffTU);
    }

    /** Guru hanya boleh melihat siswa di kelas yang diampunya. */
    public static function hanyaKelasSendiri(): bool
    {
        return self::adalah(Role::Guru);
    }

    /**
     * ID kelas yang diampu user Guru yang sedang login (semua tahun pelajaran).
     * kelas.wali_kelas_id menunjuk ke tabel guru, jadi akun user harus
     * dihubungkan ke baris guru lewat kolom guru.user_id.
     * Jika hubungan itu tidak ada, hasilnya KOSONG (Guru tidak melihat apa pun), bukan semua.
     */
    public static function kelasDiampu(): array
    {
        if (!Schema::hasTable('guru') || !Schema::hasColumn('guru', 'user_id')) {
            return [];
        }

        $guruId = DB::table('guru')->where('user_id', Auth::id())->value('id');
        if (!$guruId) {
            return [];
        }

        return DB::table('kelas')->where('wali_kelas_id', $guruId)->pluck('id')->all();
    }

    /** Mengosongkan (null) kolom sensitif untuk TAMPILAN jika role tidak berhak. */
    public static function saring(array $data): array
    {
        if (self::lihatDataSensitif()) {
            return $data;
        }
        foreach (self::KOLOM_SENSITIF as $kolom) {
            if (array_key_exists($kolom, $data)) {
                $data[$kolom] = null;
            }
        }

        return $data;
    }

    /** Membuang kolom sensitif dari data yang akan DISIMPAN jika role tidak berhak. */
    public static function buang(array $data): array
    {
        return self::lihatDataSensitif() ? $data : array_diff_key($data, array_flip(self::KOLOM_SENSITIF));
    }
}