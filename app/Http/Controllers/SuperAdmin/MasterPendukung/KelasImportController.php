<?php

namespace App\Http\Controllers\SuperAdmin\MasterPendukung;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Throwable;

/**
 * Import daftar kelas/rombel dari Excel.
 * Cocok untuk file "Daftar Peserta Didik" (Dapodik): kolom "Rombel Saat Ini"
 * dibaca, nama rombel yang sama digabung menjadi satu kelas.
 * Membutuhkan: composer require phpoffice/phpspreadsheet
 */
class KelasImportController extends BaseController
{
    private const SESSION_KEY = 'import_kelas';
    private const MAX_KELAS = 100;
    private const MAX_NAMA = 20;

    /** Judul kolom yang dikenali (sudah dinormalisasi: huruf kecil, tanpa spasi/simbol). */
    private const HEADERS = ['rombelsaatini', 'rombel', 'namarombel', 'rombonganbelajar', 'kelas', 'namakelas'];

    /* ===================== 1. UNGGAH & PRATINJAU ===================== */

    public function preview(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
            'tahun_pelajaran_id' => ['required', 'integer', 'exists:tahun_pelajaran,id'],
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes' => 'File harus berformat .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
            'tahun_pelajaran_id.required' => 'Pilih tahun pelajaran untuk kelas yang diimpor.',
        ]);

        try {
            $daftar = $this->bacaKelas($request->file('file')->getRealPath());
        } catch (RuntimeException $e) {
            return $this->bukaModal()->withErrors(['file' => $e->getMessage()]);
        } catch (Throwable $e) {
            return $this->bukaModal()
                ->withErrors(['file' => 'File tidak bisa dibaca. Pastikan formatnya Excel atau CSV yang benar.']);
        }

        $tahunId = (int) $request->input('tahun_pelajaran_id');

        $sudahAda = DB::table('kelas')
            ->where('tahun_pelajaran_id', $tahunId)
            ->pluck('nama_kelas')
            ->mapWithKeys(fn ($n) => [mb_strtolower($n) => true])
            ->all();

        $rows = [];
        foreach ($daftar as $k) {
            $status = 'baru';
            $pesan = null;

            if (mb_strlen($k['nama']) > self::MAX_NAMA) {
                $status = 'error';
                $pesan = 'Nama lebih dari ' . self::MAX_NAMA . ' karakter';
            } elseif (isset($sudahAda[mb_strtolower($k['nama'])])) {
                $status = 'ada';
                $pesan = 'Sudah ada di tahun pelajaran ini';
            }

            $rows[] = [
                'nama' => $k['nama'],
                'jumlah' => $k['jumlah'],
                'tingkat' => $this->tebakTingkat($k['nama']),
                'status' => $status,
                'pesan' => $pesan,
            ];
        }

        session()->put(self::SESSION_KEY, [
            'file' => $request->file('file')->getClientOriginalName(),
            'tahun_id' => $tahunId,
            'tahun_nama' => DB::table('tahun_pelajaran')->where('id', $tahunId)->value('nama'),
            'rows' => $rows,
        ]);

        return $this->bukaModal();
    }

    /* ===================== 2. SIMPAN ===================== */

    public function process(Request $request): RedirectResponse
    {
        $state = session(self::SESSION_KEY);

        if (! $state) {
            return $this->bukaModal()
                ->withErrors(['file' => 'Sesi import sudah berakhir. Unggah ulang filenya.']);
        }

        $input = $request->input('rows', []);
        $dipilih = [];
        $galat = [];

        foreach ($state['rows'] as $i => $row) {
            if ($row['status'] !== 'baru' || empty($input[$i]['pilih'])) {
                continue;
            }

            $tingkat = filter_var($input[$i]['tingkat'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 12],
            ]);

            if ($tingkat === false) {
                $galat[] = "Tingkat untuk \"{$row['nama']}\" harus berupa angka 1 sampai 12.";
                continue;
            }

            $dipilih[] = ['nama' => $row['nama'], 'tingkat' => $tingkat];
        }

        if (! $galat && ! $dipilih) {
            $galat[] = 'Pilih minimal satu kelas untuk diimpor.';
        }

        if ($galat) {
            return $this->bukaModal()->withErrors(['rows' => $galat]);
        }

        $dibuat = 0;
        $dilewati = 0;

        DB::transaction(function () use ($dipilih, $state, &$dibuat, &$dilewati) {
            foreach ($dipilih as $k) {
                $ada = DB::table('kelas')
                    ->where('tahun_pelajaran_id', $state['tahun_id'])
                    ->whereRaw('lower(nama_kelas) = ?', [mb_strtolower($k['nama'])])
                    ->exists();

                if ($ada) {
                    $dilewati++;
                    continue;
                }

                DB::table('kelas')->insert([
                    'tahun_pelajaran_id' => $state['tahun_id'],
                    'nama_kelas' => $k['nama'],
                    'tingkat' => $k['tingkat'],
                    'kapasitas' => null,
                    'wali_kelas_id' => null,
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $dibuat++;
            }
        });

        session()->forget(self::SESSION_KEY);

        $pesan = "{$dibuat} kelas berhasil ditambahkan ke tahun pelajaran {$state['tahun_nama']}.";
        if ($dilewati) {
            $pesan .= " {$dilewati} kelas dilewati karena sudah ada.";
        }
        $pesan .= ' Wali kelas dan kapasitas bisa diatur dari tabel kelas.';

        return $this->kembali('kelas', $pesan);
    }

    /* ===================== 3. BATAL ===================== */

    public function cancel(): RedirectResponse
    {
        session()->forget(self::SESSION_KEY);

        return $this->bukaModal();
    }

    /* ===================== HELPER ===================== */

    /**
     * Cari kolom rombel pada 25 baris pertama, lalu hitung berapa baris (siswa)
     * untuk tiap nama rombel. Mengembalikan [['nama' => ..., 'jumlah' => ...], ...].
     */
    private function bacaKelas(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $baris = $reader->load($path)->getActiveSheet()->toArray(null, true, false, false);

        $barisJudul = null;
        $kolom = null;

        foreach (array_slice($baris, 0, 25, true) as $r => $sel) {
            foreach ($sel as $c => $nilai) {
                if (! is_scalar($nilai)) {
                    continue;
                }
                $kunci = preg_replace('/[^a-z0-9]/', '', strtolower((string) $nilai));
                if (in_array($kunci, self::HEADERS, true)) {
                    $barisJudul = $r;
                    $kolom = $c;
                    break 2;
                }
            }
        }

        if ($barisJudul === null) {
            throw new RuntimeException('Kolom "Rombel Saat Ini" atau "Kelas" tidak ditemukan pada 25 baris pertama file.');
        }

        $hitung = [];
        foreach (array_slice($baris, $barisJudul + 1) as $sel) {
            $nilai = $sel[$kolom] ?? null;
            if (! is_scalar($nilai)) {
                continue; // sel kosong (termasuk sel hasil merge judul)
            }

            $nama = trim(preg_replace('/\s+/', ' ', (string) $nilai));
            if ($nama === '') {
                continue;
            }

            $kunci = mb_strtolower($nama);
            $hitung[$kunci] ??= ['nama' => $nama, 'jumlah' => 0];
            $hitung[$kunci]['jumlah']++;
        }

        if (! $hitung) {
            throw new RuntimeException('Tidak ada nama kelas di bawah kolom rombel.');
        }

        if (count($hitung) > self::MAX_KELAS) {
            throw new RuntimeException('Kolom yang terbaca punya lebih dari ' . self::MAX_KELAS . ' nilai berbeda, sepertinya bukan kolom kelas.');
        }

        $daftar = array_values($hitung);
        usort($daftar, fn ($a, $b) => strnatcasecmp($a['nama'], $b['nama']));

        return $daftar;
    }

    /** "Kelas 1A" -> 1, "X IPA 1" -> 10, "VII B" -> 7. Null jika tidak terbaca. */
    private function tebakTingkat(string $nama): ?int
    {
        if (preg_match('/^(?:kelas\s*)?(\d{1,2})/i', $nama, $m)) {
            $n = (int) $m[1];

            return $n >= 1 && $n <= 12 ? $n : null;
        }

        $romawi = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6,
            'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10, 'XI' => 11, 'XII' => 12];

        if (preg_match('/^(?:kelas\s*)?(XII|XI|X|IX|VIII|VII|VI|IV|V|III|II|I)(?![A-Za-z])/i', $nama, $m)) {
            return $romawi[strtoupper($m[1])] ?? null;
        }

        if (preg_match('/(\d{1,2})/', $nama, $m)) {
            $n = (int) $m[1];

            return $n >= 1 && $n <= 12 ? $n : null;
        }

        return null;
    }

    /** Kembali ke tab Kelas dan membuka lagi modal import (untuk galat, pratinjau, dan batal). */
    private function bukaModal(): RedirectResponse
    {
        return redirect()
            ->to(route('super-admin.master-pendukung.index') . '#kelas')
            ->with('open_import_kelas', true);
    }
}