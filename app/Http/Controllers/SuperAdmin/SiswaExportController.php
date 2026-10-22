<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Support\SiswaData;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Export data SATU siswa (semua bagian, satu bagian, atau beberapa bagian) ke Excel atau PDF.
 * ?bagian=biodata                  -> satu bagian
 * ?bagian=biodata,ayah,alamat      -> beberapa bagian sekaligus (dipisah koma)
 *   kunci: biodata|fisik|ayah|ibu|wali|alamat|kontak|bantuan|riwayat  (kosong = semua)
 * ?format=pdf  (kosong = Excel)
 */
class SiswaExportController extends Controller
{
    private const KUNCI = ['biodata', 'fisik', 'ayah', 'ibu', 'wali', 'alamat', 'kontak', 'bantuan', 'riwayat'];

    public function siswa(Request $request, $id)
    {
        $d = SiswaData::load((int) $id);
        abort_if($d === null, 404);

        $pilih = $this->bacaBagian($request->query('bagian'));   // [] = semua bagian
        $pdf = $request->query('format') === 'pdf';

        $riwayat = DB::table('riwayat_kelas as rk')
            ->join('kelas as k', 'k.id', '=', 'rk.kelas_id')
            ->join('tahun_pelajaran as tp', 'tp.id', '=', 'k.tahun_pelajaran_id')
            ->where('rk.siswa_id', $d['id'])
            ->orderByDesc('tp.nama')
            ->get(['tp.nama as tahun_pelajaran', 'k.nama_kelas', 'rk.status', 'rk.catatan']);

        $bagian = $this->susunBagian($d, $pilih);
        $denganRiwayat = empty($pilih) || in_array('riwayat', $pilih, true);

        $identitas = [
            'nama' => (string) $d['nama_lengkap'],
            'baris2' => 'NIS: ' . ($d['nis'] ?? '-') . '   |   NISN: ' . ($d['nisn'] ?? '-')
                . '   |   Status: ' . ($d['status_siswa'] ?? '-'),
        ];

        // Judul halaman & akhiran nama file
        if (empty($pilih)) {
            $judulHalaman = 'Data Lengkap';
            $akhiran = '';
        } elseif (count($pilih) === 1) {
            $judulHalaman = $pilih[0] === 'riwayat' ? 'Riwayat Kelas' : ($bagian[$pilih[0]][0] ?? 'Data Siswa');
            $akhiran = '-' . $pilih[0];
        } else {
            $judulHalaman = 'Bagian Terpilih';
            $akhiran = '-pilihan';
        }

        $namaFile = 'data-siswa-' . Str::slug($d['nama_lengkap'] . '-' . ($d['nis'] ?? $d['id'])) . $akhiran;

        if ($pdf) {
            try {
                $pdfObj = Pdf::loadView('super-admin.siswa.export-pdf', [
                    'identitas'    => $identitas,
                    'bagian'       => $bagian,
                    'riwayat'      => $denganRiwayat ? $riwayat : null,
                    'judulHalaman' => $judulHalaman,
                    'diekspor'     => now()->format('d/m/Y H:i'),
                ])->setPaper('a4', 'portrait');

                $isi = $pdfObj->output();   // PDF benar-benar dirender di sini
            } catch (\Throwable $e) {
                report($e); // tercatat di storage/logs/laravel.log
                abort(500, 'Gagal membuat PDF: ' . $e->getMessage());
            }

            // Buang output liar (spasi, warning, dll.) supaya isi PDF tidak rusak
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            return response($isi, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $namaFile . '.pdf"',
                'Content-Length'      => strlen($isi),
            ]);
        }

        return $this->excel($identitas, $bagian, $denganRiwayat ? $riwayat : null, $namaFile . '.xlsx');
    }

    /** "biodata,ayah" -> ['biodata','ayah'] (hanya kunci yang valid, tanpa duplikat) */
    private function bacaBagian($nilai): array
    {
        if (!is_string($nilai) || trim($nilai) === '') {
            return [];
        }

        return collect(explode(',', $nilai))
            ->map(fn($k) => trim($k))
            ->filter(fn($k) => in_array($k, self::KUNCI, true))
            ->unique()
            ->values()
            ->all();
    }

    /** Susun bagian data: [key => [judul, [[label, nilai], ...]]] */
    private function susunBagian(array $d, array $pilih): array
    {
        $semua = empty($pilih);

        $tgl = fn($key) => ($d[$key] ?? null) ? Carbon::parse($d[$key])->format('d/m/Y') : '-';
        $v = fn($key) => filled($d[$key] ?? null) ? (string) $d[$key] : '-';
        $unit = fn($key, $satuan) => filled($d[$key] ?? null)
            ? rtrim(rtrim(number_format((float) $d[$key], 7, ',', '.'), '0'), ',') . ' ' . $satuan
            : '-';
        $ya = fn($key) => ($d[$key] ?? null) === null ? '-' : ((int) $d[$key] === 1 ? 'Ya' : 'Tidak');
        $ada = fn(array $keys) => collect($keys)->contains(fn($k) => filled($d[$k] ?? null));

        $rtrw = (filled($d['rt'] ?? null) || filled($d['rw'] ?? null))
            ? 'RT ' . ($d['rt'] ?? '-') . ' / RW ' . ($d['rw'] ?? '-') : '-';
        $koordinat = filled($d['lintang'] ?? null) && filled($d['bujur'] ?? null)
            ? rtrim(rtrim((string) $d['lintang'], '0'), '.') . ', ' . rtrim(rtrim((string) $d['bujur'], '0'), '.')
            : '-';

        $bagian = [];

        $bagian['biodata'] = [
            'Biodata',
            [
                ['Jenis kelamin', ($d['jenis_kelamin'] ?? '') === 'L' ? 'Laki-laki' : 'Perempuan'],
                ['NIK', $v('nik')],
                ['No. KK', $v('no_kk')],
                ['Tempat lahir', $v('tempat_lahir')],
                ['Tanggal lahir', $tgl('tanggal_lahir')],
                ['Agama', $v('agama')],
                ['No. registrasi akta lahir', $v('no_akta_lahir')],
                ['Anak ke-', $v('anak_ke')],
                ['Jumlah saudara kandung', $v('jumlah_saudara')],
                ['Kebutuhan khusus', $v('kebutuhan_khusus')],
                ['Asal sekolah', $v('asal_sekolah')],
                ['Tanggal masuk', $tgl('tanggal_masuk')],
            ]
        ];

        $bagian['fisik'] = [
            'Data Fisik & Dokumen',
            [
                ['Berat badan', $unit('berat_badan', 'kg')],
                ['Tinggi badan', $unit('tinggi_badan', 'cm')],
                ['Lingkar kepala', $unit('lingkar_kepala', 'cm')],
                ['No. peserta ujian nasional', $v('no_peserta_un')],
                ['No. seri ijazah', $v('no_seri_ijazah')],
                ['SKHUN', $v('skhun')],
            ]
        ];

        foreach (['Ayah' => 'ayah', 'Ibu' => 'ibu'] as $judul => $s) {
            $bagian[$s] = [
                $judul,
                [
                    ['Nama', $v("nama_{$s}")],
                    ['NIK', $v("nik_{$s}")],
                    ['Tahun lahir', $v("tahun_lahir_{$s}")],
                    ['Pendidikan', $v("pendidikan_{$s}")],
                    ['Pekerjaan', $v("pekerjaan_{$s}")],
                    ['Penghasilan', $v("penghasilan_{$s}")],
                ]
            ];
        }

        if (in_array('wali', $pilih, true) || ($semua && $ada(['nama_wali', 'tahun_lahir_wali', 'pendidikan_wali', 'nik_wali', 'pekerjaan_wali', 'penghasilan_wali']))) {
            $bagian['wali'] = [
                'Wali',
                [
                    ['Nama', $v('nama_wali')],
                    ['NIK', $v('nik_wali')],
                    ['Tahun lahir', $v('tahun_lahir_wali')],
                    ['Pendidikan', $v('pendidikan_wali')],
                    ['Pekerjaan', $v('pekerjaan_wali')],
                    ['Penghasilan', $v('penghasilan_wali')],
                ]
            ];
        }

        $bagian['alamat'] = [
            'Alamat',
            [
                ['Alamat lengkap', $v('alamat')],
                ['RT / RW', $rtrw],
                ['Dusun', $v('dusun')],
                ['Desa / kelurahan', $v('desa_kelurahan')],
                ['Kecamatan', $v('kecamatan')],
                ['Kabupaten / kota', $v('kabupaten_kota')],
                ['Provinsi', $v('provinsi')],
                ['Kode pos', $v('kode_pos')],
                ['Jenis tinggal', $v('jenis_tinggal')],
                ['Alat transportasi', $v('alat_transportasi')],
                ['Jarak ke sekolah', $unit('jarak_ke_sekolah', 'km')],
                ['Koordinat (lintang, bujur)', $koordinat],
            ]
        ];

        $bagian['kontak'] = [
            'Kontak',
            [
                ['Telepon rumah', $v('telepon_rumah')],
                ['No. HP siswa', $v('no_hp_siswa')],
                ['Email siswa', $v('email_siswa')],
            ]
        ];

        if (
            in_array('bantuan', $pilih, true) || ($semua && $ada([
                'penerima_kps',
                'no_kps',
                'penerima_kip',
                'no_kip',
                'nama_di_kip',
                'no_kks',
                'layak_pip',
                'alasan_layak_pip',
                'bank',
                'no_rekening',
                'rekening_atas_nama'
            ]))
        ) {
            $bagian['bantuan'] = [
                'Bantuan & Rekening',
                [
                    ['Penerima KPS', $ya('penerima_kps')],
                    ['No. KPS', $v('no_kps')],
                    ['Penerima KIP', $ya('penerima_kip')],
                    ['Nomor KIP', $v('no_kip')],
                    ['Nama di KIP', $v('nama_di_kip')],
                    ['Nomor KKS', $v('no_kks')],
                    ['Layak PIP', $ya('layak_pip')],
                    ['Alasan layak PIP', $v('alasan_layak_pip')],
                    ['Bank', $v('bank')],
                    ['Nomor rekening', $v('no_rekening')],
                    ['Rekening atas nama', $v('rekening_atas_nama')],
                ]
            ];
        }

        // Hanya bagian yang dipilih, urutan tetap mengikuti urutan di atas.
        // ('riwayat' bukan bagian kolom, ditangani terpisah lewat $riwayat)
        return $semua ? $bagian : array_intersect_key($bagian, array_flip($pilih));
    }

    private function excel(array $identitas, array $bagian, $riwayat, string $namaFile)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa');

        foreach (['A' => 32, 'B' => 46, 'C' => 20, 'D' => 32] as $kolom => $lebar) {
            $sheet->getColumnDimension($kolom)->setWidth($lebar);
        }

        $teks = fn(string $cell, string $isi) => $sheet->setCellValueExplicit($cell, $isi, DataType::TYPE_STRING);
        $garis = fn(string $rentang) => $sheet->getStyle($rentang)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $header = function (string $rentang) use ($sheet) {
            $sheet->getStyle($rentang)->getFont()->setBold(true);
            $sheet->getStyle($rentang)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');
        };

        $r = 1;
        $teks("A{$r}", $identitas['nama']);
        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(14);
        $r++;
        $teks("A{$r}", $identitas['baris2']);
        $r++;
        $teks("A{$r}", 'Diekspor: ' . now()->format('d/m/Y H:i'));
        $sheet->getStyle("A{$r}")->getFont()->getColor()->setRGB('667085');
        $r += 2;

        foreach ($bagian as [$judul, $baris]) {
            $teks("A{$r}", $judul);
            $header("A{$r}:D{$r}");
            $sheet->mergeCells("A{$r}:D{$r}");
            $r++;

            foreach ($baris as [$label, $isi]) {
                $teks("A{$r}", $label);
                $teks("B{$r}", (string) $isi);
                $sheet->mergeCells("B{$r}:D{$r}");
                $sheet->getStyle("A{$r}")->getFont()->getColor()->setRGB('475467');
                $sheet->getStyle("B{$r}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("A{$r}:D{$r}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $garis("A{$r}:D{$r}");
                $r++;
            }
            $r++;
        }

        if ($riwayat !== null) {
            $teks("A{$r}", 'Riwayat Kelas');
            $header("A{$r}:D{$r}");
            $sheet->mergeCells("A{$r}:D{$r}");
            $r++;

            foreach (['Tahun Pelajaran', 'Kelas', 'Status', 'Catatan'] as $i => $judul) {
                $teks(chr(65 + $i) . $r, $judul);
            }
            $sheet->getStyle("A{$r}:D{$r}")->getFont()->setBold(true);
            $garis("A{$r}:D{$r}");
            $r++;

            if ($riwayat->isEmpty()) {
                $teks("A{$r}", 'Siswa belum ditempatkan di kelas mana pun.');
                $sheet->mergeCells("A{$r}:D{$r}");
                $garis("A{$r}:D{$r}");
            } else {
                foreach ($riwayat as $rk) {
                    $teks("A{$r}", (string) $rk->tahun_pelajaran);
                    $teks("B{$r}", (string) $rk->nama_kelas);
                    $teks("C{$r}", (string) $rk->status);
                    $teks("D{$r}", (string) ($rk->catatan ?: '-'));
                    $garis("A{$r}:D{$r}");
                    $r++;
                }
            }
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $namaFile, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}