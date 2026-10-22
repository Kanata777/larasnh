<?php

namespace App\Http\Controllers\SuperAdmin\MasterPendukung;

use App\Http\Controllers\Controller;
use App\Models\OrangTua;
use App\Models\Referensi; // sesuaikan dengan model referensi Anda
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReferensiController extends Controller
{
    /* ==========================================================
     |  REFERENSI (pekerjaan, penghasilan, asal sekolah)
     |  >>> Jika Anda sudah punya isi asli method ini, PERTAHANKAN
     |      versi Anda dan hanya salin bagian ORANG TUA di bawah.
     ========================================================== */

    public function store(Request $request)
    {
        $data = $request->validate([
            'jenis' => ['required', Rule::in(['pekerjaan', 'penghasilan', 'asal_sekolah'])],
            'nama'  => ['required', 'string', 'max:100'],
        ]);

        Referensi::create($data);

        return back()->with('success', 'Referensi berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
        ]);

        Referensi::findOrFail($id)->update($data);

        return back()->with('success', 'Referensi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Referensi::findOrFail($id)->delete();

        return back()->with('success', 'Referensi berhasil dihapus.');
    }

    /* ==========================================================
     |  ORANG TUA
     ========================================================== */

    private function orangTuaRules(): array
    {
        return [
            'siswa_id'         => ['required', 'integer', 'exists:siswa,id'],
            'nama_ayah'        => ['nullable', 'string', 'max:150'],
            'nik_ayah'         => ['nullable', 'string', 'max:20'],
            'pekerjaan_ayah'   => ['nullable', 'string', 'max:100'],
            'penghasilan_ayah' => ['nullable', 'string', 'max:60'],
            'nama_ibu'         => ['nullable', 'string', 'max:150'],
            'nik_ibu'          => ['nullable', 'string', 'max:20'],
            'pekerjaan_ibu'    => ['nullable', 'string', 'max:100'],
            'penghasilan_ibu'  => ['nullable', 'string', 'max:60'],
        ];
    }

    public function storeOrangTua(Request $request)
    {
        OrangTua::create($request->validate($this->orangTuaRules()));

        return back()->with('success', 'Data orang tua berhasil ditambahkan.');
    }

    public function updateOrangTua(Request $request, OrangTua $orangTua)
    {
        $orangTua->update($request->validate($this->orangTuaRules()));

        return back()->with('success', 'Data orang tua berhasil diperbarui.');
    }

    public function destroyOrangTua(OrangTua $orangTua)
    {
        $orangTua->delete();

        return back()->with('success', 'Data orang tua berhasil dihapus.');
    }
}