<?php

namespace App\Http\Controllers\SuperAdmin\MasterPendukung;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WaliKelasController extends BaseController
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        DB::table('wali_kelas')->insert($data + ['created_at' => now(), 'updated_at' => now()]);

        return $this->kembali('wali-kelas', 'Wali kelas berhasil ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless(DB::table('wali_kelas')->where('id', $id)->exists(), 404);

        $data = $this->validasi($request, $id);

        DB::transaction(function () use ($id, $data) {
            DB::table('wali_kelas')->where('id', $id)->update($data + ['updated_at' => now()]);

            // wali yang dinonaktifkan dilepas dari semua kelas, asisten naik jadi utama
            if ($data['status'] === 'nonaktif') {
                $this->lepasDariKelas($id);
            }
        });

        return $this->kembali('wali-kelas', 'Wali kelas berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(DB::table('wali_kelas')->where('id', $id)->exists(), 404);

        DB::transaction(function () use ($id) {
            // kalau dia wali utama, asisten naik dulu sebelum datanya dihapus
            $this->lepasDariKelas($id);

            // pivot ikut terhapus (cascade)
            DB::table('wali_kelas')->where('id', $id)->delete();
        });

        return $this->kembali('wali-kelas', 'Wali kelas berhasil dihapus.');
    }

    /**
     * Lepas seorang wali dari semua kelas.
     * Di kelas yang dia pegang sebagai UTAMA, asisten aktif pertama otomatis naik jadi utama.
     * Kalau kelas itu tidak punya asisten aktif, kelas menjadi tanpa wali utama.
     */
    private function lepasDariKelas(int $waliId): void
    {
        $kelasIds = DB::table('kelas_wali_kelas')
            ->where('wali_kelas_id', $waliId)
            ->where('peran', 'utama')
            ->pluck('kelas_id');

        foreach ($kelasIds as $kelasId) {
            $penggantiId = DB::table('kelas_wali_kelas as p')
                ->join('wali_kelas as w', 'w.id', '=', 'p.wali_kelas_id')
                ->where('p.kelas_id', $kelasId)
                ->where('p.peran', 'asisten')
                ->where('w.status', 'aktif')
                ->where('w.id', '!=', $waliId)
                ->orderByDesc('p.menggantikan')   // yang sedang menggantikan diutamakan
                ->orderBy('p.wali_kelas_id')
                ->value('p.wali_kelas_id');

            if ($penggantiId) {
                DB::table('kelas_wali_kelas')
                    ->where('kelas_id', $kelasId)
                    ->where('wali_kelas_id', $penggantiId)
                    ->update(['peran' => 'utama', 'menggantikan' => 0]);
            }

            // jaga kolom kelas.wali_kelas_id tetap sama dengan wali utama (NULL jika tidak ada)
            DB::table('kelas')->where('id', $kelasId)->update(['wali_kelas_id' => $penggantiId]);
        }

        DB::table('kelas_wali_kelas')->where('wali_kelas_id', $waliId)->delete();
    }

    private function validasi(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'nip'           => ['nullable', 'string', 'max:30', Rule::unique('wali_kelas', 'nip')->ignore($id)],
            'nama'          => ['required', 'string', 'max:100'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'email'         => ['nullable', 'email', 'max:100', Rule::unique('wali_kelas', 'email')->ignore($id)],
            'no_hp'         => ['nullable', 'string', 'max:20'],
            'status'        => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], $this->pesan() + [
            'nip.unique'   => 'NIP sudah terdaftar.',
            'email.unique' => 'Email sudah terdaftar.',
        ], [
            'nip'           => 'NIP',
            'nama'          => 'Nama',
            'jenis_kelamin' => 'Jenis kelamin',
            'email'         => 'Email',
            'no_hp'         => 'No. HP',
            'status'        => 'Status',
        ]);
    }
}