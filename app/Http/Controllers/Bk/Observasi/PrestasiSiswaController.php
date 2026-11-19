<?php

namespace App\Http\Controllers\Bk\Observasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Observasi\SimpanPrestasiRequest;
use App\Models\Observasi\Prestasi;
use App\Models\Siswa;

/** Catat dan hapus prestasi siswa. Hanya role kesiswaan (dibatasi di routes/web.php). */
class PrestasiSiswaController extends Controller
{
    public function store(SimpanPrestasiRequest $request, Siswa $siswa)
    {
        Prestasi::create($request->validated() + [
            'siswa_id'     => $siswa->id,
            'dicatat_oleh' => $request->user()->id,
        ]);

        return redirect()->route('bk.observasi-potensi.profil', $siswa)
            ->with('success', 'Prestasi berhasil dicatat.');
    }

    public function destroy(Prestasi $prestasi)
    {
        $idSiswa = $prestasi->siswa_id;
        $prestasi->delete();   // soft delete

        return redirect()->route('bk.observasi-potensi.profil', $idSiswa)
            ->with('success', 'Prestasi dihapus.');
    }
}
