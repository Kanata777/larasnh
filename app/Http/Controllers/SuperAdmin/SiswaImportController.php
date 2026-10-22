<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Support\SiswaData;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

/**
 * Import siswa dari Excel/CSV: unggah -> pemetaan kolom -> validasi & simpan.
 * Membutuhkan: composer require phpoffice/phpspreadsheet
 */
class SiswaImportController extends Controller
{
    private const MAX_ROWS = 3000;
    private const MAX_ERRORS_SHOWN = 300;
    private const DATE_FIELDS = ['tanggal_lahir', 'tanggal_masuk'];

    /** Isian yang tidak boleh kosong di database; baris tanpa isian ini dilewati. */
    private const WAJIB_DB = ['nama_lengkap', 'jenis_kelamin'];

    /**
     * Perilaku jika NIS kosong / kolom NIS tidak dipetakan:
     *  - false : NIS disimpan sebagai NULL (kolom `nis` di database harus nullable)
     *  - true  : NIS diisi otomatis (NISN bila ada, jika tidak "TMPyymmddNNNN")
     */
    private const NIS_OTOMATIS = false;

    /** Nama kolom lain yang dikenali saat pemetaan otomatis. */
    private const SYNONYMS = [
        'nis' => ['nomorinduk', 'noinduk', 'nis'],
        'nama_lengkap' => ['namalengkap', 'namasiswa'],
        'jenis_kelamin' => ['jeniskelamin', 'lp', 'gender'],
        'tempat_lahir' => ['tmplahir'],
        'tanggal_lahir' => ['tgllahir'],
        'status_siswa' => ['status'],
        'asal_sekolah' => ['sekolahasal', 'asalsekolah'],
        'nama_ayah' => ['ayah'],
        'nik_ayah' => ['nikbapak', 'nikayahkandung'],
        'nama_ibu' => ['ibu'],
        'nik_ibu' => ['nikibukandung'],
        'pendidikan_ayah' => ['pendidikanayah'],
        'pendidikan_ibu' => ['pendidikanibu'],
        'pendidikan_wali' => ['pendidikanwali'],
        'alamat' => ['alamatlengkap'],
        'desa_kelurahan' => ['desa', 'kelurahan', 'desakelurahan'],
        'kabupaten_kota' => ['kabupaten', 'kota'],
        'kode_pos' => ['pos'],
        'jarak_ke_sekolah' => ['jarak'],
        'telepon_rumah' => ['telepon', 'telp', 'teleponrumah'],
        'no_hp_siswa' => ['hp', 'hpsiswa'],
        'no_hp_ayah' => ['hpayah'],
        'no_hp_ibu' => ['hpibu'],
        'email_siswa' => ['email', 'emailsiswa'],
        'penerima_kps' => ['kps'],
        'no_kip' => ['nokip'],
        'no_kks' => ['nokks'],
        'no_rekening' => ['norekening', 'rekening'],
        'kelas' => ['namakelas', 'rombel', 'rombelsaatini'],
    ];

    /* ===================== 1. UNGGAH ===================== */

    public function index()
    {
        return view('super-admin.siswa.import', ['step' => 'upload']);
    }

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa');

        $contoh = [
            'nis' => '2026001', 'nisn' => '0012345678', 'nik' => '3372010101100001', 'no_kk' => '3372010101080001',
            'nama_lengkap' => 'Contoh Siswa', 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Surakarta',
            'tanggal_lahir' => '2010-05-17', 'agama' => 'Islam', 'asal_sekolah' => 'TK Contoh',
            'tanggal_masuk' => '2026-07-13', 'status_siswa' => 'Aktif',
            'no_akta_lahir' => 'AL-123456', 'kebutuhan_khusus' => 'Tidak ada', 'anak_ke' => '1', 'jumlah_saudara' => '2',
            'no_peserta_un' => '', 'no_seri_ijazah' => '', 'skhun' => '',
            'berat_badan' => '30', 'tinggi_badan' => '130', 'lingkar_kepala' => '50',
            'nama_ayah' => 'Nama Ayah', 'tahun_lahir_ayah' => '1980', 'pendidikan_ayah' => 'S1',
            'nik_ayah' => '3372010101800001', 'pekerjaan_ayah' => 'Wiraswasta', 'penghasilan_ayah' => 'Rp. 2,000,000 - Rp. 4,999,999',
            'nama_ibu' => 'Nama Ibu', 'tahun_lahir_ibu' => '1982', 'pendidikan_ibu' => 'SMA / sederajat',
            'nik_ibu' => '3372014202820002', 'pekerjaan_ibu' => 'Mengurus rumah tangga', 'penghasilan_ibu' => '',
            'nama_wali' => '', 'tahun_lahir_wali' => '', 'pendidikan_wali' => '', 'nik_wali' => '',
            'pekerjaan_wali' => '', 'penghasilan_wali' => '',
            'alamat' => 'Jl. Contoh No. 1', 'rt' => '01', 'rw' => '02', 'dusun' => '', 'desa_kelurahan' => 'Contoh',
            'kecamatan' => 'Contoh', 'kabupaten_kota' => 'Surakarta', 'provinsi' => 'Jawa Tengah', 'kode_pos' => '57100',
            'jenis_tinggal' => 'Bersama orang tua', 'alat_transportasi' => 'Sepeda motor',
            'lintang' => '-7.5561', 'bujur' => '110.8315', 'jarak_ke_sekolah' => '2',
            'telepon_rumah' => '0271123456', 'no_hp_siswa' => '', 'no_hp_ayah' => '081234567890',
            'no_hp_ibu' => '081234567891', 'email_siswa' => 'contoh@email.com',
            'penerima_kps' => 'Tidak', 'no_kps' => '', 'penerima_kip' => 'Tidak', 'no_kip' => '', 'nama_di_kip' => '',
            'no_kks' => '', 'layak_pip' => 'Tidak', 'alasan_layak_pip' => '', 'bank' => '', 'no_rekening' => '',
            'rekening_atas_nama' => '',
            'kelas' => '1A',
        ];

        // Kolom yang harus berformat teks agar angka 0 di depan (dan angka 16 digit NIK) tidak berubah
        $teks = [
            'nis', 'nisn', 'nik', 'no_kk', 'nik_ayah', 'nik_ibu', 'nik_wali', 'no_akta_lahir', 'no_peserta_un',
            'no_seri_ijazah', 'skhun', 'rt', 'rw', 'kode_pos', 'telepon_rumah', 'no_hp_siswa', 'no_hp_ayah',
            'no_hp_ibu', 'no_kps', 'no_kip', 'no_kks', 'no_rekening',
        ];

        $i = 1;
        foreach (SiswaData::IMPORT as $field => [$label]) {
            $col = Coordinate::stringFromColumnIndex($i++);
            $sheet->setCellValue("{$col}1", $label);
            $sheet->getStyle("{$col}1")->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);

            if (in_array($field, $teks, true)) {
                $sheet->getStyle("{$col}2:{$col}3000")->getNumberFormat()->setFormatCode('@');
            }

            $sheet->setCellValueExplicit("{$col}2", (string) ($contoh[$field] ?? ''), DataType::TYPE_STRING);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'template-import-siswa.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /* ===================== 2. PEMETAAN ===================== */

    public function preview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes' => 'File harus berformat .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $token = basename($request->file('file')->store('import-siswa'));

        try {
            $rows = $this->readRows($token);
        } catch (Throwable $e) {
            Storage::delete("import-siswa/{$token}");

            return back()->withErrors(['file' => 'File tidak bisa dibaca. Pastikan formatnya benar.']);
        }

        if (count($rows) < 1) {
            Storage::delete("import-siswa/{$token}");

            return back()->withErrors(['file' => 'File kosong.']);
        }

        // Beberapa file (mis. export Dapodik) punya beberapa baris judul/info
        // sebelum baris header kolom yang sebenarnya, jadi baris pertama di
        // file belum tentu baris header. detectHeaderRow() mencari baris mana
        // yang benar-benar berisi nama-nama kolom yang dikenali aplikasi.
        $headerRow = $this->detectHeaderRow($rows);

        // Gabungkan header 2 baris (grup + sub-kolom) bila ada.
        // $headerRow sesudah ini menunjuk baris header TERAKHIR.
        [$headers, $columns, $headerRow] = $this->resolveHeaders($rows, $headerRow);
        $dataRows = array_slice($rows, $headerRow + 1);

        if (count($dataRows) < 1) {
            Storage::delete("import-siswa/{$token}");

            return back()->withErrors(['file' => 'File kosong. Baris pertama harus berisi judul kolom, diikuti data siswa.']);
        }

        if (count($dataRows) > self::MAX_ROWS) {
            Storage::delete("import-siswa/{$token}");

            return back()->withErrors(['file' => 'Maksimal ' . self::MAX_ROWS . ' baris data per import.']);
        }

        return view('super-admin.siswa.import', [
            'step' => 'mapping',
            'token' => $token,
            'headerRow' => $headerRow,
            'headers' => $headers,   // nama untuk pencocokan otomatis
            'columns' => $columns,   // label dropdown: "[AG] Data Ibu - Nama"
            'sample' => array_slice($dataRows, 0, 5),
            'totalRows' => count($dataRows),
            'mapping' => $this->autoMap($headers),
            'tahunList' => DB::table('tahun_pelajaran')->orderByDesc('id')->get(),
            'tahunDefault' => DB::table('tahun_pelajaran')->where('status', 'aktif')->orderByDesc('id')->value('id'),
        ]);
    }

    /* ===================== 3. VALIDASI & SIMPAN ===================== */

    public function process(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'regex:/^[A-Za-z0-9._-]+$/'],
            'header_row' => ['required', 'integer', 'min:0'],
            'map' => ['required', 'array'],
            'tahun_pelajaran_id' => ['nullable', 'integer', 'exists:tahun_pelajaran,id'],
        ]);

        $token = $request->input('token');
        if (! Storage::exists("import-siswa/{$token}")) {
            return redirect()->route('super-admin.siswa.import')
                ->withErrors(['file' => 'Sesi import sudah berakhir. Unggah ulang filenya.']);
        }

        $map = $request->input('map');
        foreach (SiswaData::IMPORT as $field => [$label, $wajib]) {
            // NIS/NIPD tidak wajib dipetakan, boleh "Tidak diimpor"
            if ($field === 'nis') {
                continue;
            }

            if ($wajib && ($map[$field] ?? '') === '') {
                return back()->withErrors(['map' => "Kolom \"{$label}\" wajib dipetakan."]);
            }
        }

        $headerRow = (int) $request->input('header_row');
        $rows = $this->readRows($token);
        $rows = array_slice($rows, $headerRow + 1); // buang baris header + baris di atasnya

        $kelasMap = $this->kelasPerTahun($request->integer('tahun_pelajaran_id') ?: null);
        $rules = $this->importRules();
        $imported = 0;
        $errors = [];
        $notes = [];
        $seen = ['nis' => [], 'nisn' => []];

        foreach ($rows as $index => $row) {
            $baris = $index + $headerRow + 2; // nomor baris asli di Excel

            $record = [];
            $pesanNik = [];

            foreach (SiswaData::IMPORT as $field => $meta) {
                $col = $map[$field] ?? '';
                if ($col === '' || $col === null) {
                    continue;
                }

                $raw = $row[(int) $col] ?? null;

                // NIK 16 digit yang tersimpan sebagai ANGKA di Excel sudah dibulatkan Excel
                // (hanya 15 digit signifikan), jadi tidak bisa dipercaya. Tolak dan minta format Teks.
                if (in_array($field, SiswaData::NIK, true) && (is_int($raw) || is_float($raw)) && abs($raw) >= 1e15) {
                    $label = SiswaData::IMPORT[$field][0];
                    $pesanNik[] = "{$label} tersimpan sebagai angka di Excel sehingga digit terakhirnya bisa berubah. "
                        . 'Ubah kolom tersebut menjadi format Teks lalu ketik ulang nomornya.';
                    $record[$field] = null;
                    continue;
                }

                $record[$field] = $this->cell($raw, in_array($field, self::DATE_FIELDS, true));
            }

            if (! array_filter($record, fn ($v) => $v !== null && $v !== '')) {
                continue; // baris kosong
            }

            $namaKelas = SiswaData::clean($record['kelas'] ?? null);
            unset($record['kelas']);

            $data = SiswaData::normalize($record);
            $data['status_siswa'] = $data['status_siswa'] ?? 'Aktif';

            // NIS opsional: kosong -> NULL (atau diisi otomatis)
            if (! isset($data['nis']) || trim((string) $data['nis']) === '') {
                $data['nis'] = self::NIS_OTOMATIS ? $this->nisOtomatis($data, $baris) : null;
            }

            // Prinsip: semua baris tetap diimpor. Isian yang bermasalah dikosongkan/dipotong
            // dan dicatat (beserta nilai aslinya) di laporan. Hanya baris tanpa nama atau
            // tanpa jenis kelamin yang dilewati, karena keduanya wajib di database.
            $catatan = $pesanNik;

            if ($namaKelas !== null) {
                $kelasId = $kelasMap[$this->normalKelas($namaKelas)] ?? null;
                if ($kelasId) {
                    $data['kelas_id'] = $kelasId;
                } else {
                    $catatan[] = "Rombel \"{$namaKelas}\" tidak ditemukan pada tahun pelajaran yang dipilih; siswa diimpor tanpa kelas.";
                }
            }

            [$data, $catatanIsian, $fatal] = $this->bersihkan($data, $rules);
            $catatan = array_merge($catatan, $catatanIsian);
            $data['status_siswa'] = $data['status_siswa'] ?? 'Aktif';

            if ($fatal) {
                $errors[] = ['baris' => $baris, 'nis' => $data['nis'] ?? '-', 'nama' => $data['nama_lengkap'] ?? '-', 'pesan' => $fatal];
                continue;
            }

            // Duplikat di dalam file yang sama: baris pertama dipertahankan, berikutnya dikosongkan + dicatat
            foreach (['nis' => 'NIS', 'nisn' => 'NISN'] as $kolom => $label) {
                if (empty($data[$kolom])) {
                    continue;
                }
                if (isset($seen[$kolom][$data[$kolom]])) {
                    $catatan[] = "{$label} \"{$data[$kolom]}\" sama dengan baris {$seen[$kolom][$data[$kolom]]} pada file, dikosongkan.";
                    $data[$kolom] = null;
                } else {
                    $seen[$kolom][$data[$kolom]] = $baris;
                }
            }

            try {
                SiswaData::save($data);
                $imported++;

                if ($catatan) {
                    $notes[] = [
                        'baris' => $baris,
                        'nis' => $data['nis'] ?? '-',
                        'nama' => $data['nama_lengkap'] ?? '-',
                        'pesan' => array_values(array_unique($catatan)),
                    ];
                }
            } catch (Throwable $e) {
                Log::warning("Import siswa baris {$baris} gagal: " . $e->getMessage());

                $errors[] = [
                    'baris' => $baris,
                    'nis' => $data['nis'] ?? '-',
                    'nama' => $data['nama_lengkap'] ?? '-',
                    'pesan' => ['Gagal disimpan ke database: ' . mb_substr($e->getMessage(), 0, 300)],
                ];
            }
        }

        Storage::delete("import-siswa/{$token}");

        $report = [
            'imported' => $imported,
            'skipped' => count($errors),
            'errors' => array_slice($errors, 0, self::MAX_ERRORS_SHOWN),
            'hidden' => max(0, count($errors) - self::MAX_ERRORS_SHOWN),
            'noted' => count($notes),
            'notes' => array_slice($notes, 0, self::MAX_ERRORS_SHOWN),
            'notes_hidden' => max(0, count($notes) - self::MAX_ERRORS_SHOWN),
        ];

        $pesan = "{$imported} siswa berhasil diimpor";
        if ($notes) {
            $pesan .= ', ' . count($notes) . ' di antaranya ada catatan isian yang dikosongkan/dipotong';
        }
        $pesan .= $errors ? ', ' . count($errors) . ' baris dilewati.' : '.';

        return redirect()->route('super-admin.siswa.import')
            ->with('report', $report)
            ->with('success', $pesan);
    }

    /**
     * Validasi satu baris tanpa menolaknya. Isian yang gagal dipotong (jika terlalu panjang)
     * atau dikosongkan, dan setiap perubahan dicatat bersama nilai aslinya.
     * Mengembalikan [data, catatan, fatal]; $fatal hanya terisi untuk isian wajib di database.
     */
    private function bersihkan(array $data, array $rules): array
    {
        $catatan = [];
        $fatal = [];
        $attr = SiswaData::attributes();

        for ($pass = 0; $pass < 2; $pass++) {
            $v = Validator::make($data, $rules, SiswaData::messages(), $attr);
            if (! $v->fails()) {
                break;
            }

            $failed = $v->failed();
            foreach ($v->errors()->messages() as $field => $msgs) {
                if (in_array($field, self::WAJIB_DB, true)) {
                    $fatal[] = $msgs[0];
                    continue;
                }

                $nama = $attr[$field] ?? $field;
                $asli = $data[$field] ?? null;

                if ($pass === 0 && is_string($asli) && isset($failed[$field]['Max'])) {
                    $max = (int) $failed[$field]['Max'][0];
                    $data[$field] = mb_substr($asli, 0, $max);
                    $catatan[] = "{$nama} terlalu panjang (maks. {$max} karakter), nilai dipotong.";
                    continue;
                }

                $tampil = mb_substr((string) $asli, 0, 60);
                $data[$field] = null;
                $catatan[] = "{$nama} dikosongkan: {$msgs[0]} (nilai di file: \"{$tampil}\")";
            }
        }

        return [$data, array_values(array_unique($catatan)), array_values(array_unique($fatal))];
    }

    /* ===================== HELPER ===================== */

    private function readRows(string $token): array
    {
        $path = Storage::path("import-siswa/{$token}");

        return IOFactory::load($path)->getActiveSheet()->toArray(null, true, false, false);
    }

    /**
     * Aturan validasi import: sama dengan SiswaData::rules(), tetapi aturan
     * `nis` dibuat opsional (required dibuang, nullable ditambahkan), jadi
     * form tambah siswa manual tidak ikut berubah.
     */
    private function importRules(): array
    {
        $rules = SiswaData::rules();

        if (isset($rules['nis'])) {
            $nis = $rules['nis'];
            if (is_string($nis)) {
                $nis = explode('|', $nis);
            }

            $nis = array_values(array_filter($nis, fn ($r) => $r !== 'required'));
            if (! in_array('nullable', $nis, true)) {
                array_unshift($nis, 'nullable');
            }

            $rules['nis'] = $nis;
        }

        return $rules;
    }

    /** NIS sementara: NISN bila ada, jika tidak "TMP" + tanggal + nomor baris Excel. */
    private function nisOtomatis(array $data, int $baris): string
    {
        if (! empty($data['nisn'])) {
            return (string) $data['nisn'];
        }

        return 'TMP' . now()->format('ymd') . str_pad((string) $baris, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Deteksi baris header kolom yang sebenarnya. Beberapa file (mis. export
     * Dapodik) punya beberapa baris judul/informasi di atas baris header
     * asli, jadi baris pertama di file belum tentu baris header kolom.
     *
     * Caranya: cari di antara baris pertama (maksimal 20 baris) mana yang
     * isinya paling banyak cocok dengan nama kolom atau sinonim yang SUDAH
     * dikenali aplikasi (SiswaData::IMPORT & self::SYNONYMS). Baris dengan
     * kecocokan terbanyak (minimal 3) dianggap baris header; baris-baris di
     * atasnya diabaikan. Kalau tidak ada baris yang cukup meyakinkan, baris
     * pertama tetap dipakai seperti sebelumnya.
     */
    private function detectHeaderRow(array $rows): int
    {
        $normal = fn (string $s) => preg_replace('/[^a-z0-9]/', '', strtolower($s));

        $kandidat = [];
        foreach (SiswaData::IMPORT as $field => [$label]) {
            foreach (array_merge([$label, $field], self::SYNONYMS[$field] ?? []) as $k) {
                $kandidat[$normal((string) $k)] = true;
            }
        }

        $batas = min(count($rows), 20);
        $baikIndex = 0;
        $skorTerbaik = 0;

        for ($i = 0; $i < $batas; $i++) {
            $skor = 0;
            foreach ($rows[$i] as $cell) {
                $key = $normal((string) $cell);
                if ($key !== '' && isset($kandidat[$key])) {
                    $skor++;
                }
            }

            if ($skor > $skorTerbaik) {
                $skorTerbaik = $skor;
                $baikIndex = $i;
            }
        }

        return $skorTerbaik >= 3 ? $baikIndex : 0;
    }

    /**
     * Baca header (1 atau 2 baris). Mengembalikan:
     *  - $names   : nama untuk pencocokan otomatis ("Nama Ibu")
     *  - $columns : label tampilan dropdown ("[AG] Data Ibu - Nama")
     *  - $lastRow : indeks baris header terakhir (dipakai sebagai header_row)
     *
     * Header 2 baris (mis. "Data Ibu" di atas, "Nama" / "Tahun Lahir" di
     * bawahnya) terdeteksi jika ada kolom yang header atasnya kosong tetapi
     * bawahnya berisi teks, dan ada sel grup yang atas-bawahnya sama-sama
     * terisi. File biasa (semua header terisi di satu baris) tidak memenuhi
     * syarat ini, sehingga perilakunya tidak berubah.
     */
    private function resolveHeaders(array $rows, int $headerRow): array
    {
        $top = array_map(fn ($h) => trim((string) $h), $rows[$headerRow]);
        $next = array_map(fn ($h) => trim((string) $h), $rows[$headerRow + 1] ?? []);
        $total = max(count($top), count($next));
        $top = array_pad($top, $total, '');
        $next = array_pad($next, $total, '');

        $kosongAtas = 0;
        $adaGrup = false;
        for ($c = 0; $c < $total; $c++) {
            if ($top[$c] === '' && $next[$c] !== '' && ! is_numeric($next[$c])) {
                $kosongAtas++;
            }
            if ($top[$c] !== '' && $next[$c] !== '' && ! is_numeric($next[$c])) {
                $adaGrup = true;
            }
        }
        $dua = $kosongAtas >= 2 && $adaGrup;

        $names = [];
        $display = [];
        $group = '';

        for ($c = 0; $c < $total; $c++) {
            if (! $dua) {
                $names[$c] = $display[$c] = $top[$c];
                continue;
            }

            if ($top[$c] !== '' && $next[$c] !== '') {
                $group = $top[$c];   // awal grup baru
            } elseif ($top[$c] !== '') {
                $group = '';         // kolom biasa (merge vertikal), grup berakhir
            }

            if ($top[$c] !== '' && $next[$c] === '') {
                $names[$c] = $display[$c] = $top[$c];
            } elseif ($next[$c] !== '') {
                $sub = $next[$c];
                $display[$c] = $group !== '' ? "{$group} - {$sub}" : $sub;
                $short = trim(preg_replace('/^data\s+/i', '', $group)); // "Data Ibu" -> "Ibu"
                $names[$c] = trim($sub . ' ' . $short);                 // "Nama Ibu"
            } else {
                $names[$c] = $display[$c] = '';
            }
        }

        // Label dropdown: huruf kolom Excel + nama; fallback "Kolom N" bila benar-benar kosong
        $columns = [];
        foreach ($display as $c => $label) {
            $columns[$c] = '[' . Coordinate::stringFromColumnIndex($c + 1) . '] '
                . ($label !== '' ? $label : 'Kolom ' . ($c + 1));
        }

        return [$names, $columns, $dua ? $headerRow + 1 : $headerRow];
    }

    /**
     * Pemetaan otomatis kolom file -> isian siswa, dua tahap:
     *  1. Kecocokan PERSIS untuk semua isian. Kolom yang sudah cocok persis dicatat sebagai terpakai.
     *  2. Kecocokan SEBAGIAN (header mengandung kandidat, mis. "Rombel Saat Ini" mengandung "rombel")
     *     hanya untuk isian yang belum terpetakan, dan hanya pada kolom yang belum terpakai.
     *     Kandidat pendek (kurang dari 5 huruf, mis. "hp", "rt", "nik", "bank") tidak dipakai di tahap ini
     *     agar tidak salah menempel ke kolom lain.
     *     Ini mencegah, misalnya, kolom "NIK Ayah" terambil sebagai "Nama Ayah" karena sama-sama
     *     mengandung kata "ayah".
     */
    private function autoMap(array $headers): array
    {
        $normal = fn (string $s) => preg_replace('/[^a-z0-9]/', '', strtolower($s));
        $headerKeys = array_map($normal, $headers);
        $map = [];
        $kandidatPerField = [];
        $dipakai = [];

        // Tahap 1: kecocokan persis
        foreach (SiswaData::IMPORT as $field => [$label]) {
            $kandidat = array_merge([$normal($label), $normal($field)], self::SYNONYMS[$field] ?? []);
            $kandidatPerField[$field] = $kandidat;
            $map[$field] = null;

            foreach ($headerKeys as $i => $key) {
                if ($key !== '' && ! isset($dipakai[$i]) && in_array($key, $kandidat, true)) {
                    $map[$field] = $i;
                    $dipakai[$i] = true;
                    break;
                }
            }
        }

        // Tahap 2: kecocokan sebagian, hanya untuk yang belum terpetakan
        foreach (SiswaData::IMPORT as $field => $meta) {
            if ($map[$field] !== null) {
                continue;
            }

            foreach ($headerKeys as $i => $key) {
                if ($key === '' || isset($dipakai[$i])) {
                    continue;
                }

                foreach ($kandidatPerField[$field] as $k) {
                    if (strlen($k) >= 5 && str_contains($key, $k)) {
                        $map[$field] = $i;
                        $dipakai[$i] = true;
                        break 2;
                    }
                }
            }
        }

        return $map;
    }

    /** Ubah isi sel mentah (angka Excel, tanggal serial, dll.) menjadi teks bersih. */
    private function cell(mixed $raw, bool $isDate): ?string
    {
        if ($raw === null || is_bool($raw)) {
            return null;
        }

        if ($isDate) {
            if (is_numeric($raw) && (float) $raw > 20000) {
                return ExcelDate::excelToDateTimeObject((float) $raw)->format('Y-m-d');
            }

            return $this->parseDate(trim((string) $raw));
        }

        if (is_float($raw) && floor($raw) == $raw) {
            return (string) (int) $raw;
        }

        return trim((string) $raw);
    }

    private function parseDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'j/n/Y', 'j-n-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat('!' . $format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->format('Y-m-d');
                }
            } catch (Throwable $e) {
                // coba format berikutnya
            }
        }

        return $value; // dibiarkan agar ditolak validasi dengan pesan yang jelas
    }

    /**
     * nama kelas (ternormalisasi) => id, untuk tahun pelajaran yang dipilih.
     * Jika $tahunId kosong, dipakai tahun pelajaran yang berstatus aktif.
     */
    private function kelasPerTahun(?int $tahunId = null): array
    {
        $tahunId ??= DB::table('tahun_pelajaran')->where('status', 'aktif')->orderByDesc('id')->value('id');

        return DB::table('kelas')
            ->when($tahunId, fn ($q) => $q->where('tahun_pelajaran_id', $tahunId))
            ->pluck('id', 'nama_kelas')
            ->mapWithKeys(fn ($id, $nama) => [$this->normalKelas((string) $nama) => $id])
            ->all();
    }

    /**
     * Samakan penulisan nama kelas: huruf kecil, kata "kelas" dibuang, serta
     * spasi dan tanda baca dihilangkan. "Kelas 1A", "1A", dan "1 A" => "1a".
     */
    private function normalKelas(string $nama): string
    {
        $nama = mb_strtolower($nama);
        $nama = preg_replace('/\bkelas\b/u', '', $nama);

        return preg_replace('/[^a-z0-9]/', '', $nama);
    }
}