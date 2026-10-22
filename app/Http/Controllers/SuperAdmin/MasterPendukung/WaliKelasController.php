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

        DB::table('wali_kelas')->where('id', $id)->update($data + ['updated_at' => now()]);

        return $this->kembali('wali-kelas', 'Wali kelas berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(DB::table('wali_kelas')->where('id', $id)->exists(), 404);

        // pivot ikut terhapus (cascade), kelas.wali_kelas_id otomatis jadi NULL
        DB::table('wali_kelas')->where('id', $id)->delete();

        return $this->kembali('wali-kelas', 'Wali kelas berhasil dihapus.');
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