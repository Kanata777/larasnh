<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WaliKelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimPegawaiService
{
    public function sync(): array
    {
        try {
            $response = Http::withOptions([
                    'force_ip_resolve' => 'v4',
                    'verify'           => (bool) config('services.sim_pegawai.verify_ssl', true),
                ])
                ->withToken((string) config('services.sim_pegawai.key'))
                ->timeout(20)
                ->retry(2, 500, throw: false)
                ->get((string) config('services.sim_pegawai.url'), ['role' => 'guru']);
        } catch (\Throwable $e) {
            Log::warning('SimPegawai: API tidak terjangkau: ' . $e->getMessage());
            return $this->gagal('API SIM Pegawai tidak terjangkau: ' . $e->getMessage());
        }

        if (! $response->successful() || $response->json('status') !== 'success') {
            $pesan = $response->json('message') ?? ('HTTP ' . $response->status());
            return $this->gagal('API SIM Pegawai error: ' . $pesan);
        }

        $items = collect($response->json('data') ?? []);

        if (config('services.sim_pegawai.hanya_wali_kelas')) {
            $items = $items->filter(
                fn ($i) => strcasecmp(trim((string) ($i['tugas_tambahan']['nama'] ?? '')), 'Wali Kelas') === 0
            );
        }

        if ($items->isEmpty()) {
            return $this->gagal('Tidak ada data guru dari SIM Pegawai. Data lokal tidak diubah.');
        }

        $baru = $diperbarui = $dilewati = 0;
        $idDariSim = [];

        DB::transaction(function () use ($items, &$baru, &$diperbarui, &$dilewati, &$idDariSim) {
            foreach ($items as $item) {
                $idSim = (int) ($item['id_sim'] ?? 0);
                if ($idSim === 0) {
                    continue;
                }

                $nip = ! empty($item['nip_yayasan']) ? trim((string) $item['nip_yayasan']) : null;

                $guru = WaliKelas::where('sim_pegawai_id', $idSim)->first();

                if (! $guru && $nip) {
                    $pemilikNip = WaliKelas::where('nip', $nip)->first();

                    if ($pemilikNip && $pemilikNip->sim_pegawai_id && (int) $pemilikNip->sim_pegawai_id !== $idSim) {
                        Log::warning("SimPegawai: NIP {$nip} ganda (id_sim {$idSim} dan {$pemilikNip->sim_pegawai_id}), dilewati.");
                        $dilewati++;
                        continue;
                    }

                    $guru = $pemilikNip;
                }

                $guru ??= new WaliKelas();
                $idDariSim[] = $idSim;

                $email = $item['email'] ?? null;
                if ($email && WaliKelas::where('email', $email)->where('id', '!=', $guru->id ?? 0)->exists()) {
                    $email = $guru->email;
                }

                $aktif = filter_var($item['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN);
                $tugas = $item['tugas_tambahan']['nama'] ?? null;

                $guru->fill([
                    'sim_pegawai_id' => $idSim,
                    'nip'            => $nip,
                    'nama'           => trim((string) ($item['nama_lengkap'] ?? 'Tanpa Nama')),
                    'jenis_kelamin'  => $this->jenisKelamin($item['jenis_kelamin'] ?? null),
                    'email'          => $email,
                    'no_hp'          => $item['nomor_hp'] ?? null,
                    'status'         => $aktif ? 'aktif' : 'nonaktif',
                    'tugas_tambahan' => $tugas === 'Tanpa Tugas Tambahan' ? null : $tugas,
                    'disinkron_pada' => now(),
                ]);

                $guru->exists ? $diperbarui++ : $baru++;
                $guru->save();
            }

            WaliKelas::whereNotNull('sim_pegawai_id')
                ->whereNotIn('sim_pegawai_id', $idDariSim)
                ->update(['status' => 'nonaktif']);
        });

        return [
            'success' => true,
            'total'   => $items->count(),
            'created' => $baru,
            'updated' => $diperbarui,
            'message' => "Sinkronisasi selesai: {$items->count()} data ({$baru} baru, {$diperbarui} diperbarui, {$dilewati} dilewati karena NIP ganda).",
        ];
    }

    private function jenisKelamin(?string $v): ?string
    {
        $v = strtoupper(trim((string) $v));

        return match (true) {
            str_starts_with($v, 'L') => 'L',
            str_starts_with($v, 'P') => 'P',
            default                  => null,
        };
    }

    private function gagal(string $pesan): array
    {
        return ['success' => false, 'total' => 0, 'created' => 0, 'updated' => 0, 'message' => $pesan];
    }
}