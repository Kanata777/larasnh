<?php

namespace App\Http\Controllers\SuperAdmin\MasterPendukung;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TahunPelajaranController extends BaseController
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        // Tahun pertama langsung aktif; berikutnya nonaktif sampai diaktifkan manual.
        $pertama = ! DB::table('tahun_pelajaran')->exists();

        DB::table('tahun_pelajaran')->insert($data + [
            'status' => $pertama ? 'aktif' : 'nonaktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->kembali('tahun-pelajaran', 'Tahun pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless(DB::table('tahun_pelajaran')->where('id', $id)->exists(), 404);

        $data = $this->validasi($request, $id);

        DB::table('tahun_pelajaran')->where('id', $id)->update($data + ['updated_at' => now()]);

        return $this->kembali('tahun-pelajaran', 'Tahun pelajaran berhasil diperbarui.');
    }

    public function activate(int $id): RedirectResponse
    {
        abort_unless(DB::table('tahun_pelajaran')->where('id', $id)->exists(), 404);

        DB::transaction(function () use ($id) {
            DB::table('tahun_pelajaran')->update(['status' => 'nonaktif', 'updated_at' => now()]);
            DB::table('tahun_pelajaran')->where('id', $id)->update(['status' => 'aktif', 'updated_at' => now()]);
        });

        return $this->kembali('tahun-pelajaran', 'Tahun pelajaran berhasil diaktifkan.');
    }

    private function validasi(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:20', Rule::unique('tahun_pelajaran', 'nama')->ignore($id)],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], $this->pesan() + [
            'nama.unique' => 'Tahun pelajaran sudah ada.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ], [
            'nama' => 'Tahun pelajaran',
            'tanggal_mulai' => 'Tanggal mulai',
            'tanggal_selesai' => 'Tanggal selesai',
            'keterangan' => 'Keterangan',
        ]);
    }
}