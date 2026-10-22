<?php

namespace App\Http\Controllers\SuperAdmin\MasterPendukung;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KelasController extends BaseController
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        DB::table('kelas')->insert($data + ['created_at' => now(), 'updated_at' => now()]);

        return $this->kembali('kelas', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless(DB::table('kelas')->where('id', $id)->exists(), 404);

        $data = $this->validasi($request, $id);

        DB::table('kelas')->where('id', $id)->update($data + ['updated_at' => now()]);

        return $this->kembali('kelas', 'Kelas berhasil diperbarui.');
    }

    public function wali(Request $request, int $id): RedirectResponse
    {
        abort_unless(DB::table('kelas')->where('id', $id)->exists(), 404);

        $data = $request->validate(
            [
                'wali_kelas_ids'   => ['nullable', 'array'],
                'wali_kelas_ids.*' => ['integer', 'distinct', 'exists:wali_kelas,id'],
            ],
            $this->pesan(),
            ['wali_kelas_ids' => 'Wali kelas', 'wali_kelas_ids.*' => 'Wali kelas']
        );

        $ids = array_values(array_unique(array_map('intval', $data['wali_kelas_ids'] ?? [])));

        DB::transaction(function () use ($id, $ids) {
            // hapus wali yang tidak dipilih lagi
            DB::table('kelas_wali_kelas')
                ->where('kelas_id', $id)
                ->whereNotIn('wali_kelas_id', $ids)
                ->delete();

            // tambahkan wali baru saja
            $sudahAda = DB::table('kelas_wali_kelas')
                ->where('kelas_id', $id)
                ->pluck('wali_kelas_id')
                ->all();

            $baru = array_diff($ids, $sudahAda);

            if ($baru) {
                DB::table('kelas_wali_kelas')->insert(array_map(fn ($waliId) => [
                    'kelas_id'      => $id,
                    'wali_kelas_id' => $waliId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ], array_values($baru)));
            }

            // kolom lama diisi wali pertama agar tampilan lama tidak rusak
            DB::table('kelas')->where('id', $id)->update([
                'wali_kelas_id' => $ids[0] ?? null,
                'updated_at'    => now(),
            ]);
        });

        return $this->kembali('kelas', 'Wali kelas berhasil diatur.');
    }

    private function validasi(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'nama_kelas' => [
                'required', 'string', 'max:20',
                Rule::unique('kelas', 'nama_kelas')
                    ->where('tahun_pelajaran_id', $request->input('tahun_pelajaran_id'))
                    ->ignore($id),
            ],
            'tingkat'            => ['required', 'integer', 'between:1,6'],
            'tahun_pelajaran_id' => ['required', 'integer', 'exists:tahun_pelajaran,id'],
            'kapasitas'          => ['nullable', 'integer', 'between:1,200'],
            'status'             => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], $this->pesan() + [
            'nama_kelas.unique' => 'Nama kelas sudah dipakai pada tahun pelajaran tersebut.',
            'tingkat.between'   => 'Tingkat harus antara 1 dan 6.',
        ], [
            'nama_kelas'         => 'Nama kelas',
            'tingkat'            => 'Tingkat',
            'tahun_pelajaran_id' => 'Tahun pelajaran',
            'kapasitas'          => 'Kapasitas',
            'status'             => 'Status',
        ]);
    }
}