<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Support\SiswaData;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanController extends Controller
{
    /**
     * "label kolom" => "relasi.nama_kolom" pada model Siswa (relasi orangTua dan alamat).
     */
    private const KOLOM_ORTU = [
        'Nama Ayah'          => 'orangTua.nama_ayah',
        'Tahun Lahir Ayah'   => 'orangTua.tahun_lahir_ayah',
        'Pendidikan Ayah'    => 'orangTua.pendidikan_ayah',
        'NIK Ayah'           => 'orangTua.nik_ayah',
        'Pekerjaan Ayah'     => 'orangTua.pekerjaan_ayah',
        'Penghasilan Ayah'   => 'orangTua.penghasilan_ayah',
        'Nama Ibu'           => 'orangTua.nama_ibu',
        'Tahun Lahir Ibu'    => 'orangTua.tahun_lahir_ibu',
        'Pendidikan Ibu'     => 'orangTua.pendidikan_ibu',
        'NIK Ibu'            => 'orangTua.nik_ibu',
        'Pekerjaan Ibu'      => 'orangTua.pekerjaan_ibu',
        'Penghasilan Ibu'    => 'orangTua.penghasilan_ibu',
        'Alamat'             => 'alamat.alamat',
        'RT'                 => 'alamat.rt',
        'RW'                 => 'alamat.rw',
    ];

    /**
     * Kolom export "lengkap": [judul kolom, sumber, jenis nilai].
     * Urutan dan judul mengikuti export Dapodik (supaya file hasil export bisa diimpor ulang),
     * diikuti kolom tambahan milik aplikasi. Jenis: tgl, ya (Ya/Tidak), dec (desimal), kosong = apa adanya.
     * Sumber "alias.kolom": ot = orang_tua, w = wali_siswa, al = alamat_siswa, ko = kontak_siswa, b = bantuan_siswa.
     */
    private const EXPORT_LENGKAP = [
        ['Rombel Saat Ini', 'kelas.nama_kelas'],
        ['Nama', 'siswa.nama_lengkap'],
        ['NIPD', 'siswa.nis'],
        ['JK', 'siswa.jenis_kelamin'],
        ['NISN', 'siswa.nisn'],
        ['Tempat Lahir', 'siswa.tempat_lahir'],
        ['Tanggal Lahir', 'siswa.tanggal_lahir', 'tgl'],
        ['NIK', 'siswa.nik'],
        ['Agama', 'siswa.agama'],
        ['Alamat', 'al.alamat'],
        ['RT', 'al.rt'],
        ['RW', 'al.rw'],
        ['Dusun', 'al.dusun'],
        ['Kelurahan', 'al.desa_kelurahan'],
        ['Kecamatan', 'al.kecamatan'],
        ['Kabupaten/Kota', 'al.kabupaten_kota'],
        ['Provinsi', 'al.provinsi'],
        ['Kode Pos', 'al.kode_pos'],
        ['Jenis Tinggal', 'al.jenis_tinggal'],
        ['Alat Transportasi', 'al.alat_transportasi'],
        ['Telepon', 'ko.telepon_rumah'],
        ['HP', 'ko.no_hp_siswa'],
        ['E-Mail', 'ko.email_siswa'],
        ['SKHUN', 'siswa.skhun'],
        ['Penerima KPS', 'b.penerima_kps', 'ya'],
        ['No. KPS', 'b.no_kps'],
        ['Nama Ayah', 'ot.nama_ayah'],
        ['Tahun Lahir Ayah', 'ot.tahun_lahir_ayah'],
        ['Jenjang Pendidikan Ayah', 'ot.pendidikan_ayah'],
        ['Pekerjaan Ayah', 'ot.pekerjaan_ayah'],
        ['Penghasilan Ayah', 'ot.penghasilan_ayah'],
        ['NIK Ayah', 'ot.nik_ayah'],
        ['Nama Ibu', 'ot.nama_ibu'],
        ['Tahun Lahir Ibu', 'ot.tahun_lahir_ibu'],
        ['Jenjang Pendidikan Ibu', 'ot.pendidikan_ibu'],
        ['Pekerjaan Ibu', 'ot.pekerjaan_ibu'],
        ['Penghasilan Ibu', 'ot.penghasilan_ibu'],
        ['NIK Ibu', 'ot.nik_ibu'],
        ['Nama Wali', 'w.nama_wali'],
        ['Tahun Lahir Wali', 'w.tahun_lahir_wali'],
        ['Jenjang Pendidikan Wali', 'w.pendidikan_wali'],
        ['Pekerjaan Wali', 'w.pekerjaan_wali'],
        ['Penghasilan Wali', 'w.penghasilan_wali'],
        ['NIK Wali', 'w.nik_wali'],
        ['No Peserta Ujian Nasional', 'siswa.no_peserta_un'],
        ['No Seri Ijazah', 'siswa.no_seri_ijazah'],
        ['Penerima KIP', 'b.penerima_kip', 'ya'],
        ['Nomor KIP', 'b.no_kip'],
        ['Nama di KIP', 'b.nama_di_kip'],
        ['Nomor KKS', 'b.no_kks'],
        ['No Registrasi Akta Lahir', 'siswa.no_akta_lahir'],
        ['Bank', 'b.bank'],
        ['Nomor Rekening Bank', 'b.no_rekening'],
        ['Rekening Atas Nama', 'b.rekening_atas_nama'],
        ['Layak PIP (usulan dari sekolah)', 'b.layak_pip', 'ya'],
        ['Alasan Layak PIP', 'b.alasan_layak_pip'],
        ['Kebutuhan Khusus', 'siswa.kebutuhan_khusus'],
        ['Sekolah Asal', 'siswa.asal_sekolah'],
        ['Anak ke-berapa', 'siswa.anak_ke'],
        ['Lintang', 'al.lintang', 'dec'],
        ['Bujur', 'al.bujur', 'dec'],
        ['No KK', 'siswa.no_kk'],
        ['Berat Badan', 'siswa.berat_badan', 'dec'],
        ['Tinggi Badan', 'siswa.tinggi_badan', 'dec'],
        ['Lingkar Kepala', 'siswa.lingkar_kepala', 'dec'],
        ['Jml. Saudara Kandung', 'siswa.jumlah_saudara'],
        ['Jarak Rumah ke Sekolah (KM)', 'al.jarak_ke_sekolah', 'dec'],
        // Tambahan di luar template Dapodik
        ['Tanggal Masuk', 'siswa.tanggal_masuk', 'tgl'],
        ['Status Siswa', 'siswa.status_siswa'],
    ];

    /* ===================== HALAMAN UTAMA ===================== */

    public function index()
    {
        return view('super-admin.laporan.index');
    }

    /* ===================== 1. DAFTAR SISWA LENGKAP ===================== */

    public function siswa(Request $r)
    {
        $tahunId = $this->tahunId($r);

        $rows = $this->siswaQuery($r, $tahunId)->get()->values()
            ->map(fn ($s, $i) => [
                $i + 1, $s->nis, $s->nisn, $s->nik, $s->no_kk, $s->nama_lengkap, $s->jenis_kelamin, $s->nama_kelas ?? '-',
                $s->tempat_lahir, $this->tgl($s->tanggal_lahir), $s->agama, $s->asal_sekolah,
                $this->tgl($s->tanggal_masuk), $s->status_siswa,
            ])->all();

        return $this->tampil($r, [
            'judul'   => 'Daftar Siswa Lengkap',
            'slug'    => 'daftar-siswa',
            'tahunId' => $tahunId,
            'fields'  => ['q', 'tahun', 'kelas', 'status', 'jk'],
            'head'    => ['No', 'NIS', 'NISN', 'NIK', 'No KK', 'Nama Lengkap', 'L/P', 'Kelas', 'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Asal Sekolah', 'Tanggal Masuk', 'Status'],
            'rows'    => $rows,
        ]);
    }

    /* ===================== 2. LAPORAN PER KELAS ===================== */

    public function perKelas(Request $r)
    {
        $tahunId = $this->tahunId($r);
        $rekap = $this->rekapKelas($tahunId);

        $rows = [];
        foreach ($rekap as $i => $k) {
            $rows[] = [$i + 1, $k->nama_kelas, (int) $k->l, (int) $k->p, (int) $k->l + (int) $k->p, (int) $k->nonaktif];
        }
        $l = (int) $rekap->sum('l');
        $p = (int) $rekap->sum('p');
        $rows[] = ['', 'TOTAL', $l, $p, $l + $p, (int) $rekap->sum('nonaktif')];

        return $this->tampil($r, [
            'judul'      => 'Laporan Jumlah Siswa per Kelas',
            'slug'       => 'siswa-per-kelas',
            'tahunId'    => $tahunId,
            'fields'     => ['tahun'],
            'head'       => ['No', 'Kelas / Rombel', 'Laki-laki', 'Perempuan', 'Jumlah Aktif', 'Tidak Aktif'],
            'rows'       => $rows,
            'total'      => true,
            'ringkasan'  => ['Jumlah kelas' => $rekap->count(), 'Siswa aktif' => $l + $p],
        ]);
    }

    /* ===================== 3. LAPORAN LAKI-LAKI / PEREMPUAN ===================== */

    public function jenisKelamin(Request $r)
    {
        $tahunId = $this->tahunId($r);
        $rekap = $this->rekapKelas($tahunId);

        $rows = [];
        $tl = $tp = 0;
        foreach ($rekap->groupBy('tingkat') as $tingkat => $g) {
            $l = (int) $g->sum('l');
            $p = (int) $g->sum('p');
            $rows[] = ['Kelas ' . $tingkat, $l, $this->pct($l, $l + $p), $p, $this->pct($p, $l + $p), $l + $p];
            $tl += $l;
            $tp += $p;
        }
        $tt = $tl + $tp;
        $rows[] = ['TOTAL', $tl, $this->pct($tl, $tt), $tp, $this->pct($tp, $tt), $tt];

        return $this->tampil($r, [
            'judul'   => 'Laporan Siswa Laki-laki / Perempuan',
            'slug'    => 'siswa-jenis-kelamin',
            'tahunId' => $tahunId,
            'fields'  => ['tahun'],
            'head'    => ['Tingkat', 'Laki-laki', '%', 'Perempuan', '%', 'Jumlah'],
            'rows'    => $rows,
            'total'   => true,
            'bar'     => [
                ['label' => 'Laki-laki', 'n' => $tl, 'pct' => $tt ? round($tl / $tt * 100, 1) : 0],
                ['label' => 'Perempuan', 'n' => $tp, 'pct' => $tt ? round($tp / $tt * 100, 1) : 0],
            ],
        ]);
    }

    /* ===================== 4. LAPORAN SISWA BARU ===================== */

    /**
     * Siswa baru = punya riwayat kelas pada tahun pelajaran terpilih
     * dan belum pernah punya riwayat kelas pada tahun pelajaran sebelumnya (id lebih kecil).
     */
    public function siswaBaru(Request $r)
    {
        $tahunId = $this->tahunId($r);

        $siswa = Siswa::query()
            ->join('riwayat_kelas as rk', fn ($j) => $j->on('rk.siswa_id', '=', 'siswa.id')
                ->where('rk.tahun_pelajaran_id', '=', $tahunId ?? 0))
            ->leftJoin('kelas', 'kelas.id', '=', 'rk.kelas_id')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('riwayat_kelas as lama')
                ->whereColumn('lama.siswa_id', 'siswa.id')
                ->where('lama.tahun_pelajaran_id', '<', $tahunId ?? 0))
            ->select('siswa.*', 'kelas.nama_kelas')
            ->orderBy('kelas.tingkat')->orderBy('kelas.nama_kelas')->orderBy('siswa.nama_lengkap')
            ->get()->values();

        $rows = $siswa->map(fn ($s, $i) => [
            $i + 1, $s->nis, $s->nama_lengkap, $s->jenis_kelamin, $s->nama_kelas ?? '-',
            $s->asal_sekolah, $this->tgl($s->tanggal_masuk),
        ])->all();

        return $this->tampil($r, [
            'judul'     => 'Laporan Siswa Baru',
            'slug'      => 'siswa-baru',
            'tahunId'   => $tahunId,
            'fields'    => ['tahun'],
            'head'      => ['No', 'NIS', 'Nama Lengkap', 'L/P', 'Kelas', 'Asal Sekolah', 'Tanggal Masuk'],
            'rows'      => $rows,
            'ringkasan' => ['Siswa baru' => count($rows)],
        ]);
    }

    /* ===================== 5. LAPORAN DATA ORANG TUA ===================== */

    public function orangTua(Request $r)
    {
        $tahunId = $this->tahunId($r);

        $rows = $this->siswaQuery($r, $tahunId)->with(['orangTua', 'alamat'])->get()->values()
            ->map(fn ($s, $i) => array_merge(
                [$i + 1, $s->nis, $s->nama_lengkap, $s->nama_kelas ?? '-'],
                array_map(fn ($path) => data_get($s, $path), array_values(self::KOLOM_ORTU))
            ))->all();

        return $this->tampil($r, [
            'judul'   => 'Laporan Data Orang Tua',
            'slug'    => 'data-orang-tua',
            'tahunId' => $tahunId,
            'fields'  => ['q', 'tahun', 'kelas', 'status'],
            'head'    => array_merge(['No', 'NIS', 'Nama Siswa', 'Kelas'], array_keys(self::KOLOM_ORTU)),
            'rows'    => $rows,
        ]);
    }

    /* ===================== 6. CETAK DOKUMEN ===================== */

    public function dokumen(Request $r)
    {
        $tahunId = $this->tahunId($r);

        $siswa = DB::table('riwayat_kelas as rk')
            ->join('siswa', 'siswa.id', '=', 'rk.siswa_id')
            ->where('rk.tahun_pelajaran_id', $tahunId ?? 0)
            ->where('siswa.status_siswa', 'Aktif')
            ->orderBy('siswa.nama_lengkap')
            ->get(['siswa.id', 'siswa.nama_lengkap as nama', 'rk.kelas_id']);

        return view('super-admin.laporan.dokumen', [
            'kelasOptions' => $this->kelasOptions($tahunId),
            'siswa'        => $siswa,
            'tahunNama'    => $this->tahunNama($tahunId),
        ]);
    }

    public function dokumenHadir(Request $r)
    {
        [$kelas, $siswa] = $this->siswaDalamKelas($r);

        try {
            $bulan = Carbon::parse(($r->query('bulan') ?: now()->format('Y-m')) . '-01')->startOfMonth();
        } catch (\Throwable) {
            $bulan = now()->startOfMonth();
        }

        return view('super-admin.laporan.dokumen-hadir', [
            'kelas'   => $kelas,
            'siswa'   => $siswa,
            'bulan'   => $bulan->copy()->locale('id')->translatedFormat('F Y'),
            'jumlahHari' => $bulan->daysInMonth,
            'sekolah' => $r->query('sekolah', config('app.name')),
        ]);
    }

    public function dokumenLabel(Request $r)
    {
        [$kelas, $siswa] = $this->siswaDalamKelas($r);

        return view('super-admin.laporan.dokumen-label', [
            'kelas'   => $kelas,
            'siswa'   => $siswa,
            'sekolah' => $r->query('sekolah', config('app.name')),
        ]);
    }

    public function dokumenSurat(Request $r, int $id)
    {
        $siswa = Siswa::with('orangTua')->findOrFail($id);
        $tahunId = $this->tahunId($r);

        $kelas = DB::table('riwayat_kelas as rk')
            ->join('kelas', 'kelas.id', '=', 'rk.kelas_id')
            ->where('rk.siswa_id', $id)
            ->where('rk.tahun_pelajaran_id', $tahunId ?? 0)
            ->value('kelas.nama_kelas');

        return view('super-admin.laporan.dokumen-surat', [
            'd'         => $siswa,
            'kelas'     => $kelas,
            'tahunNama' => $this->tahunNama($tahunId),
            'sekolah'   => $r->query('sekolah', config('app.name')),
            'nomor'     => $r->query('nomor', '......./......./' . now()->year),
            'kepala'    => $r->query('kepala', '..............................'),
            'nip'       => $r->query('nip', '..............................'),
            'kota'      => $r->query('kota', '..............'),
            'tanggal'   => now()->locale('id')->translatedFormat('d F Y'),
        ]);
    }

    /* ===================== 7. EXPORT DATA ===================== */

    public function export(Request $r)
    {
        $tahunId = $this->tahunId($r);

        return view('super-admin.laporan.export', [
            'tahunId'       => $tahunId,
            'tahunOptions'  => $this->tahunOptions(),
            'kelasOptions'  => $this->kelasOptions($tahunId),
            'statusOptions' => SiswaData::STATUS,
        ]);
    }

    public function unduhExport(Request $r, string $jenis)
    {
        abort_unless(in_array($jenis, ['lengkap', 'dapodik'], true), 404);

        $tahunId = $this->tahunId($r);
        if ($jenis === 'dapodik') {
            $siswa = $this->siswaQuery($r, $tahunId)->with(['orangTua', 'alamat'])->get();

            // Kolom dasar mengikuti urutan umum template Dapodik; cocokkan dengan template resmi sebelum diunggah.
            $head = ['Nama', 'NIPD', 'JK', 'NISN', 'Tempat Lahir', 'Tanggal Lahir', 'Nama Ayah', 'Nama Ibu', 'Rombel Saat Ini', 'Alamat'];
            $rows = $siswa->map(fn ($s) => [
                $s->nama_lengkap, $s->nis, $s->jenis_kelamin, $s->nisn, $s->tempat_lahir,
                $s->tanggal_lahir ? Carbon::parse($s->tanggal_lahir)->format('d/m/Y') : '',
                data_get($s, 'orangTua.nama_ayah'), data_get($s, 'orangTua.nama_ibu'),
                $s->nama_kelas, data_get($s, 'alamat.alamat'),
            ])->all();
        } else {
            // Semua data: siswa + orang tua + wali + alamat + kontak + bantuan + kelas
            $query = $this->siswaQuery($r, $tahunId)
                ->leftJoin('orang_tua as ot', 'ot.siswa_id', '=', 'siswa.id')
                ->leftJoin('wali_siswa as w', 'w.siswa_id', '=', 'siswa.id')
                ->leftJoin('alamat_siswa as al', 'al.siswa_id', '=', 'siswa.id')
                ->leftJoin('kontak_siswa as ko', 'ko.siswa_id', '=', 'siswa.id')
                ->leftJoin('bantuan_siswa as b', 'b.siswa_id', '=', 'siswa.id');

            // siswa.* dan kelas.nama_kelas sudah ada di siswaQuery; sisanya ditambahkan di sini
            $tambahan = [];
            foreach (self::EXPORT_LENGKAP as [, $sumber]) {
                if (!str_starts_with($sumber, 'siswa.') && !str_starts_with($sumber, 'kelas.')) {
                    $tambahan[] = $sumber;
                }
            }
            $siswa = $query->addSelect($tambahan)->get();

            $head = array_column(self::EXPORT_LENGKAP, 0);
            $rows = $siswa->map(fn ($s) => array_map(
                fn ($kolom) => $this->nilaiExport($s, $kolom),
                self::EXPORT_LENGKAP
            ))->all();
        }

        return $this->unduh("export-{$jenis}-" . now()->format('Ymd'), $head, $rows);
    }

    /* ===================== HELPER ===================== */

    /** Satu tempat untuk menampilkan, mencetak, atau mengunduh laporan. */
    private function tampil(Request $r, array $d)
    {
        if ($r->boolean('export')) {
            return $this->unduh($d['slug'] . '-' . now()->format('Ymd'), $d['head'], $d['rows']);
        }

        $d['tahunNama'] = $this->tahunNama($d['tahunId']);

        if ($r->boolean('cetak')) {
            return view('super-admin.laporan.print', $d);
        }

        return view('super-admin.laporan.page', $d + [
            'tahunOptions'  => $this->tahunOptions(),
            'kelasOptions'  => $this->kelasOptions($d['tahunId']),
            'statusOptions' => SiswaData::STATUS,
        ]);
    }

    /** Excel (.xlsx) bila PhpSpreadsheet terpasang, jika tidak CSV (pemisah titik koma, cocok untuk Excel Indonesia). */
    private function unduh(string $nama, array $head, iterable $rows)
    {
        if (class_exists(Spreadsheet::class)) {
            $ss = new Spreadsheet();
            $ws = $ss->getActiveSheet();
            foreach (array_values($head) as $c => $h) {
                $ws->setCellValueExplicit([$c + 1, 1], (string) $h, DataType::TYPE_STRING);
            }
            $ws->getStyle('A1:' . Coordinate::stringFromColumnIndex(count($head)) . '1')->getFont()->setBold(true);

            $r = 2;
            foreach ($rows as $row) {
                foreach (array_values($row) as $c => $v) {
                    // NIS/NISN dan nomor telepon disimpan sebagai teks agar angka 0 di depan tidak hilang.
                    if (is_int($v) || is_float($v)) {
                        $ws->setCellValue([$c + 1, $r], $v);
                    } else {
                        $ws->setCellValueExplicit([$c + 1, $r], (string) $v, DataType::TYPE_STRING);
                    }
                }
                $r++;
            }
            foreach (range(1, count($head)) as $c) {
                $ws->getColumnDimensionByColumn($c)->setAutoSize(true);
            }

            return response()->streamDownload(
                fn () => (new Xlsx($ss))->save('php://output'),
                "{$nama}.xlsx",
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );
        }

        return response()->streamDownload(function () use ($head, $rows) {
            $o = fopen('php://output', 'w');
            fwrite($o, "\xEF\xBB\xBF");
            fputcsv($o, $head, ';');
            foreach ($rows as $row) {
                fputcsv($o, $row, ';');
            }
            fclose($o);
        }, "{$nama}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Siswa + kelas pada tahun pelajaran terpilih, lengkap dengan filter dari query string. */
    private function siswaQuery(Request $r, ?int $tahunId)
    {
        return Siswa::query()
            ->leftJoin('riwayat_kelas as rk', fn ($j) => $j->on('rk.siswa_id', '=', 'siswa.id')
                ->where('rk.tahun_pelajaran_id', '=', $tahunId ?? 0))
            ->leftJoin('kelas', 'kelas.id', '=', 'rk.kelas_id')
            ->select('siswa.*', 'kelas.nama_kelas')
            ->when(trim((string) $r->query('q')), fn ($q, $c) => $q->where(fn ($w) => $w
                ->where('siswa.nama_lengkap', 'like', "%{$c}%")
                ->orWhere('siswa.nis', 'like', "%{$c}%")
                ->orWhere('siswa.nisn', 'like', "%{$c}%")))
            ->when($r->query('kelas_id'), fn ($q, $v) => $q->where('rk.kelas_id', $v))
            ->when($r->query('status'), fn ($q, $v) => $q->where('siswa.status_siswa', $v))
            ->when($r->query('jk'), fn ($q, $v) => $q->where('siswa.jenis_kelamin', $v))
            ->orderBy('kelas.tingkat')->orderBy('kelas.nama_kelas')->orderBy('siswa.nama_lengkap');
    }

    /** Jumlah siswa aktif L/P dan non-aktif per kelas. */
    private function rekapKelas(?int $tahunId)
    {
        return DB::table('kelas')
            ->leftJoin('riwayat_kelas as rk', fn ($j) => $j->on('rk.kelas_id', '=', 'kelas.id')
                ->where('rk.tahun_pelajaran_id', '=', $tahunId ?? 0))
            ->leftJoin('siswa', 'siswa.id', '=', 'rk.siswa_id')
            ->where('kelas.tahun_pelajaran_id', $tahunId ?? 0)
            ->groupBy('kelas.id', 'kelas.nama_kelas', 'kelas.tingkat')
            ->orderBy('kelas.tingkat')->orderBy('kelas.nama_kelas')
            ->selectRaw("kelas.nama_kelas, kelas.tingkat,
                COALESCE(SUM(siswa.status_siswa = 'Aktif' AND siswa.jenis_kelamin = 'L'), 0) AS l,
                COALESCE(SUM(siswa.status_siswa = 'Aktif' AND siswa.jenis_kelamin = 'P'), 0) AS p,
                COALESCE(SUM(siswa.id IS NOT NULL AND siswa.status_siswa <> 'Aktif'), 0) AS nonaktif")
            ->get();
    }

    private function siswaDalamKelas(Request $r): array
    {
        $kelas = DB::table('kelas')->where('id', $r->query('kelas_id'))->first();
        abort_unless($kelas, 404, 'Pilih kelas terlebih dahulu.');

        $siswa = DB::table('riwayat_kelas as rk')
            ->join('siswa', 'siswa.id', '=', 'rk.siswa_id')
            ->where('rk.kelas_id', $kelas->id)
            ->where('siswa.status_siswa', 'Aktif')
            ->orderBy('siswa.nama_lengkap')
            ->get(['siswa.nis', 'siswa.nisn', 'siswa.nama_lengkap', 'siswa.jenis_kelamin']);

        return [$kelas, $siswa];
    }

    private function tahunId(Request $r): ?int
    {
        if ($id = $r->query('tahun_id')) {
            return (int) $id;
        }

        return DB::table('tahun_pelajaran')->where('status', 'aktif')->orderByDesc('id')->value('id');
    }

    private function tahunNama(?int $tahunId): ?string
    {
        return $tahunId ? DB::table('tahun_pelajaran')->where('id', $tahunId)->value('nama') : null;
    }

    private function tahunOptions()
    {
        return DB::table('tahun_pelajaran')->orderByDesc('id')->get(['id', 'nama']);
    }

    private function kelasOptions(?int $tahunId)
    {
        return DB::table('kelas')
            ->when($tahunId, fn ($q) => $q->where('tahun_pelajaran_id', $tahunId))
            ->orderBy('tingkat')->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas']);
    }

    /** Nilai satu sel export lengkap sesuai jenisnya (tgl / ya / dec / apa adanya). */
    private function nilaiExport($s, array $kolom)
    {
        $nama = substr($kolom[1], strpos($kolom[1], '.') + 1);
        $v = $s->{$nama} ?? null;

        if ($v === null || $v === '') {
            return '';
        }

        return match ($kolom[2] ?? null) {
            'tgl' => $this->tgl($v),
            'ya'  => (int) $v === 1 ? 'Ya' : 'Tidak',
            'dec' => str_contains((string) $v, '.') ? rtrim(rtrim((string) $v, '0'), '.') : (string) $v,
            default => $v,
        };
    }

    private function tgl($v): string
    {
        return $v ? Carbon::parse($v)->format('d-m-Y') : '';
    }

    private function pct(int $a, int $total): string
    {
        return $total ? number_format($a / $total * 100, 1, ',', '') . '%' : '0%';
    }
}