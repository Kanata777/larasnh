<?php

namespace App\Http\Controllers\Bk\Concerns;

use App\Models\Kelas;
use Illuminate\Http\Request;

// Dipakai bersama oleh controller yang menampilkan daftar siswa dengan pencarian + filter kelas.
trait MencariSiswa
{
    protected function terapkanFilterSiswa($query, Request $request)
    {
        $q = trim((string) $request->input('q'));
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nama_lengkap', 'like', "%{$q}%")
                  ->orWhere('nis', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%");
            });
        }

        if ($kelas = $request->input('kelas')) {
            $query->whereHas('riwayatAktif', fn ($r) => $r->where('kelas_id', $kelas));
        }

        return $query;
    }

    protected function daftarKelas()
    {
        return Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get(['id', 'nama_kelas']);
    }
}
