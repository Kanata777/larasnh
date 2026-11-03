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

    /**
     * Atur wali kelas: 1 utama + maksimal 2 asisten.
     * Form mengirim: wali_utama_id, asisten_ids[] (maks 2), dan pengganti_id
     * (id asisten yang menggantikan utama untuk sementara, boleh kosong).
     * Wali utama TETAP tercatat sebagai utama selama penggantian berlangsung.
     */
    public function wali(Request $request, int $id): RedirectResponse
    {
        abort_unless(DB::table('kelas')->where('id', $id)->exists(), 404);

        $data = $request->validate(
            [
                'wali_utama_id' => ['nullable', 'integer', 'exists:wali_kelas,id'],
                'asisten_ids'   => ['nullable', 'array', 'max:2'],
                'asisten_ids.*' => ['nullable', 'integer', 'exists:wali_kelas,id'],
                'pengganti_id'  => ['nullable', 'integer'],
            ],
            $this->pesan() + [
                'asisten_ids.max' => 'Asisten wali kelas maksimal 2 orang.',
            ],
            [
                'wali_utama_id' => 'Wali kelas utama',
                'asisten_ids'   => 'Asisten wali kelas',
                'asisten_ids.*' => 'Asisten wali kelas',
                'pengganti_id'  => 'Wali kelas pengganti',
            ]
        );

        $utama   = !empty($data['wali_utama_id']) ? (int) $data['wali_utama_id'] : null;
        $asisten = collect($data['asisten_ids'] ?? [])->filter()->map(fn ($v) => (int) $v)->values();

        // satu orang tidak boleh punya dua peran di kelas yang sama
        $semua = $asisten->concat($utama ? [$utama] : []);
        if ($semua->count() !== $semua->unique()->count()) {
            return back()
                ->withErrors(['wali_utama_id' => 'Satu orang tidak boleh memiliki dua peran dalam satu kelas.'])
                ->withInput();
        }

        // pengganti harus salah satu asisten, dan wali utama harus ada
        $pengganti = !empty($data['pengganti_id']) ? (int) $data['pengganti_id'] : null;
        if ($pengganti && (!$utama || !$asisten->contains($pengganti))) {
            $pengganti = null;
        }

        // peta wali_kelas_id => [peran, menggantikan]
        $peran = [];
        if ($utama) {
            $peran[$utama] = ['peran' => 'utama', 'menggantikan' => 0];
        }
        foreach ($asisten as $waliId) {
            $peran[$waliId] = ['peran' => 'asisten', 'menggantikan' => $pengganti === $waliId ? 1 : 0];
        }

        DB::transaction(function () use ($id, $utama, $peran) {
            // hapus wali yang tidak dipilih lagi
            DB::table('kelas_wali_kelas')
                ->where('kelas_id', $id)
                ->whereNotIn('wali_kelas_id', array_keys($peran))
                ->delete();

            $sudahAda = DB::table('kelas_wali_kelas')
                ->where('kelas_id', $id)
                ->pluck('wali_kelas_id')
                ->map(fn ($v) => (int) $v)
                ->all();

            foreach ($peran as $waliId => $atribut) {
                if (in_array($waliId, $sudahAda, true)) {
                    // sudah terdaftar: cukup perbarui peran dan status penggantiannya
                    DB::table('kelas_wali_kelas')
                        ->where('kelas_id', $id)
                        ->where('wali_kelas_id', $waliId)
                        ->update($atribut + ['updated_at' => now()]);
                } else {
                    DB::table('kelas_wali_kelas')->insert($atribut + [
                        'kelas_id'      => $id,
                        'wali_kelas_id' => $waliId,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }

            // kolom lama diisi wali utama agar tampilan lama tidak rusak
            DB::table('kelas')->where('id', $id)->update([
                'wali_kelas_id' => $utama,
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