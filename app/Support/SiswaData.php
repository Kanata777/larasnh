<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Satu tempat untuk aturan validasi, pembersihan, dan penyimpanan data siswa
 * (dipakai oleh form tambah/edit dan oleh import Excel).
 *
 * Label di IMPORT sengaja sama persis dengan judul kolom export Dapodik,
 * supaya pemetaan otomatis di halaman import langsung cocok.
 */
class SiswaData
{
    public const STATUS = ['Aktif', 'Pindah', 'Lulus', 'Nonaktif'];

    public const SISWA = [
        'nis',
        'nisn',
        'nik',
        'no_kk',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'asal_sekolah',
        'tanggal_masuk',
        'status_siswa',
        'no_akta_lahir',
        'kebutuhan_khusus',
        'anak_ke',
        'jumlah_saudara',
        'no_peserta_un',
        'no_seri_ijazah',
        'skhun',
        'berat_badan',
        'tinggi_badan',
        'lingkar_kepala',
    ];
    public const ORANG_TUA = [
        'nama_ayah',
        'tahun_lahir_ayah',
        'pendidikan_ayah',
        'nik_ayah',
        'pekerjaan_ayah',
        'penghasilan_ayah',
        'nama_ibu',
        'tahun_lahir_ibu',
        'pendidikan_ibu',
        'nik_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
    ];
    public const WALI = [
        'nama_wali',
        'tahun_lahir_wali',
        'pendidikan_wali',
        'nik_wali',
        'pekerjaan_wali',
        'penghasilan_wali',
    ];
    public const ALAMAT = [
        'alamat',
        'rt',
        'rw',
        'dusun',
        'desa_kelurahan',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'kode_pos',
        'jenis_tinggal',
        'alat_transportasi',
        'lintang',
        'bujur',
        'jarak_ke_sekolah',
    ];
    public const KONTAK = [
        'telepon_rumah',
        'no_hp_siswa',
        'no_hp_ayah',
        'no_hp_ibu',
        'email_siswa',
        'email_ayah',
        'email_ibu',
    ];
    public const BANTUAN = [
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
        'rekening_atas_nama',
    ];
    public const RIWAYAT = ['kelas_id', 'catatan'];

    /** Nomor 16 digit yang rawan rusak di Excel (dibulatkan jadi notasi ilmiah). */
    public const NIK = ['nik', 'no_kk', 'nik_ayah', 'nik_ibu', 'nik_wali'];

    /** Isian Ya/Tidak, disimpan sebagai 1/0. */
    public const BOOLEAN = ['penerima_kps', 'penerima_kip', 'layak_pip'];

    /** Isian desimal (koma dari Excel diubah jadi titik). */
    private const DECIMAL = ['lintang', 'bujur', 'jarak_ke_sekolah', 'berat_badan', 'tinggi_badan', 'lingkar_kepala'];

    /** Angka "0" yang sah, jangan dikosongkan oleh clean(). */
    private const NOL_SAH = ['jumlah_saudara', 'jarak_ke_sekolah', 'penerima_kps', 'penerima_kip', 'layak_pip'];

    private const PHONE = ['telepon_rumah', 'no_hp_siswa', 'no_hp_ayah', 'no_hp_ibu'];

    /** Kolom yang bisa diimpor: field => [label (judul Dapodik), wajib?] */
    public const IMPORT = [
        'nis' => ['NIPD', true],
        'nisn' => ['NISN', false],
        'nik' => ['NIK', false],
        'no_kk' => ['No KK', false],
        'nama_lengkap' => ['Nama', true],
        'jenis_kelamin' => ['JK', true],
        'tempat_lahir' => ['Tempat Lahir', false],
        'tanggal_lahir' => ['Tanggal Lahir', false],
        'agama' => ['Agama', false],
        'asal_sekolah' => ['Sekolah Asal', false],
        'tanggal_masuk' => ['Tanggal Masuk', false],
        'status_siswa' => ['Status Siswa', false],
        'no_akta_lahir' => ['No Registrasi Akta Lahir', false],
        'kebutuhan_khusus' => ['Kebutuhan Khusus', false],
        'anak_ke' => ['Anak ke-berapa', false],
        'jumlah_saudara' => ['Jml. Saudara Kandung', false],
        'no_peserta_un' => ['No Peserta Ujian Nasional', false],
        'no_seri_ijazah' => ['No Seri Ijazah', false],
        'skhun' => ['SKHUN', false],
        'berat_badan' => ['Berat Badan', false],
        'tinggi_badan' => ['Tinggi Badan', false],
        'lingkar_kepala' => ['Lingkar Kepala', false],

        'nama_ayah' => ['Nama Ayah', false],
        'tahun_lahir_ayah' => ['Tahun Lahir Ayah', false],
        'pendidikan_ayah' => ['Jenjang Pendidikan Ayah', false],
        'nik_ayah' => ['NIK Ayah', false],
        'pekerjaan_ayah' => ['Pekerjaan Ayah', false],
        'penghasilan_ayah' => ['Penghasilan Ayah', false],
        'nama_ibu' => ['Nama Ibu', false],
        'tahun_lahir_ibu' => ['Tahun Lahir Ibu', false],
        'pendidikan_ibu' => ['Jenjang Pendidikan Ibu', false],
        'nik_ibu' => ['NIK Ibu', false],
        'pekerjaan_ibu' => ['Pekerjaan Ibu', false],
        'penghasilan_ibu' => ['Penghasilan Ibu', false],
        'nama_wali' => ['Nama Wali', false],
        'tahun_lahir_wali' => ['Tahun Lahir Wali', false],
        'pendidikan_wali' => ['Jenjang Pendidikan Wali', false],
        'nik_wali' => ['NIK Wali', false],
        'pekerjaan_wali' => ['Pekerjaan Wali', false],
        'penghasilan_wali' => ['Penghasilan Wali', false],

        'alamat' => ['Alamat', false],
        'rt' => ['RT', false],
        'rw' => ['RW', false],
        'dusun' => ['Dusun', false],
        'desa_kelurahan' => ['Kelurahan', false],
        'kecamatan' => ['Kecamatan', false],
        'kabupaten_kota' => ['Kabupaten/Kota', false],
        'provinsi' => ['Provinsi', false],
        'kode_pos' => ['Kode Pos', false],
        'jenis_tinggal' => ['Jenis Tinggal', false],
        'alat_transportasi' => ['Alat Transportasi', false],
        'lintang' => ['Lintang', false],
        'bujur' => ['Bujur', false],
        'jarak_ke_sekolah' => ['Jarak Rumah ke Sekolah (KM)', false],

        'telepon_rumah' => ['Telepon', false],
        'no_hp_siswa' => ['HP', false],
        'no_hp_ayah' => ['No. HP Ayah', false],
        'no_hp_ibu' => ['No. HP Ibu', false],
        'email_siswa' => ['E-Mail', false],

        'penerima_kps' => ['Penerima KPS', false],
        'no_kps' => ['No. KPS', false],
        'penerima_kip' => ['Penerima KIP', false],
        'no_kip' => ['Nomor KIP', false],
        'nama_di_kip' => ['Nama di KIP', false],
        'no_kks' => ['Nomor KKS', false],
        'layak_pip' => ['Layak PIP (usulan dari sekolah)', false],
        'alasan_layak_pip' => ['Alasan Layak PIP', false],
        'bank' => ['Bank', false],
        'no_rekening' => ['Nomor Rekening Bank', false],
        'rekening_atas_nama' => ['Rekening Atas Nama', false],

        'kelas' => ['Rombel Saat Ini', false],
    ];

    public static function allFields(): array
    {
        return array_merge(
            self::SISWA,
            self::ORANG_TUA,
            self::WALI,
            self::ALAMAT,
            self::KONTAK,
            self::BANTUAN,
            self::RIWAYAT
        );
    }

    /* ---------------------------- Pembersihan ---------------------------- */

    /** Kosongkan nilai sampah: kosong, "-", "0", "null". */
    public static function clean(?string $value, bool $nolSah = false): ?string
    {
        if ($value === null) {
            return null;
        }

        $v = trim($value);
        $sampah = $nolSah ? ['', '-', '--', 'null'] : ['', '-', '--', '0', 'null'];

        return in_array(strtolower($v), $sampah, true) ? null : $v;
    }

    public static function normalize(array $input): array
    {
        $out = [];

        foreach (self::allFields() as $field) {
            if (!array_key_exists($field, $input)) {
                continue;
            }
            $value = $input[$field];
            $out[$field] = is_string($value) ? self::clean($value, in_array($field, self::NOL_SAH, true)) : $value;
        }

        foreach (self::PHONE as $field) {
            if (!empty($out[$field])) {
                $p = preg_replace('/[\s\-\.\(\)]/', '', $out[$field]);
                // Excel sering membuang angka 0 di depan nomor HP
                if (preg_match('/^8\d{8,12}$/', $p)) {
                    $p = '0' . $p;
                }
                $out[$field] = $p;
            }
        }

        // NIK / No KK: buang spasi, titik, strip, dan tanda kutip (Excel sering menambah ' di depan angka).
        // Nilai notasi ilmiah (mis. 3.3E+15) sengaja dibiarkan agar ditolak validasi.
        foreach (self::NIK as $field) {
            if (!empty($out[$field])) {
                $out[$field] = preg_replace('/[\s.\-\'’]/u', '', $out[$field]) ?? $out[$field];
            }
        }

        // Desimal: "5,5" -> "5.5"
        foreach (self::DECIMAL as $field) {
            if (isset($out[$field]) && is_string($out[$field])) {
                $out[$field] = str_replace(',', '.', preg_replace('/\s+/', '', $out[$field]));
            }
        }

        // Ya/Tidak -> 1/0 (nilai lain dibiarkan agar ditolak validasi)
        foreach (self::BOOLEAN as $field) {
            if (!isset($out[$field]) || !is_string($out[$field])) {
                continue;
            }
            $v = strtolower(trim($out[$field]));
            if (in_array($v, ['ya', 'y', 'iya', 'true', '1'], true)) {
                $out[$field] = 1;
            } elseif (in_array($v, ['tidak', 't', 'tdk', 'no', 'n', 'false', '0'], true)) {
                $out[$field] = 0;
            }
        }

        if (!empty($out['jenis_kelamin'])) {
            $jk = strtoupper(preg_replace('/[^A-Za-z]/', '', $out['jenis_kelamin']));
            if (in_array($jk, ['L', 'LAKILAKI', 'LAKI', 'PRIA'], true)) {
                $out['jenis_kelamin'] = 'L';
            } elseif (in_array($jk, ['P', 'PEREMPUAN', 'WANITA'], true)) {
                $out['jenis_kelamin'] = 'P';
            }
        }

        if (array_key_exists('status_siswa', $out)) {
            $out['status_siswa'] = $out['status_siswa'] ? ucfirst(strtolower($out['status_siswa'])) : 'Aktif';
        }

        return $out;
    }

    /* ---------------------------- Validasi ---------------------------- */

    public static function rules(?int $ignoreId = null): array
    {
        $hp = 'regex:/^(\+62|62|0)\d{7,13}$/';
        $nik = 'regex:/^\d{16}$/';
        $tahunLahir = ['nullable', 'integer', 'between:1900,' . date('Y')];

        return [
            'nis' => ['required', 'string', 'max:20', Rule::unique('siswa', 'nis')->ignore($ignoreId)],
            'nisn' => ['nullable', 'regex:/^\d{10}$/', Rule::unique('siswa', 'nisn')->ignore($ignoreId)],
            'nik' => ['nullable', $nik],
            'no_kk' => ['nullable', $nik],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'agama' => ['nullable', 'string', 'max:30'],
            'asal_sekolah' => ['nullable', 'string', 'max:150'],
            'tanggal_masuk' => ['nullable', 'date'],
            'status_siswa' => ['required', Rule::in(self::STATUS)],
            'no_akta_lahir' => ['nullable', 'string', 'max:50'],
            'kebutuhan_khusus' => ['nullable', 'string', 'max:100'],
            'anak_ke' => ['nullable', 'integer', 'between:1,30'],
            'jumlah_saudara' => ['nullable', 'integer', 'between:0,30'],
            'no_peserta_un' => ['nullable', 'string', 'max:30'],
            'no_seri_ijazah' => ['nullable', 'string', 'max:50'],
            'skhun' => ['nullable', 'string', 'max:50'],
            'berat_badan' => ['nullable', 'numeric', 'between:0,300'],
            'tinggi_badan' => ['nullable', 'numeric', 'between:0,300'],
            'lingkar_kepala' => ['nullable', 'numeric', 'between:0,200'],

            // Batas panjang disamakan dengan kolom tabel orang_tua
            'nama_ayah' => ['nullable', 'string', 'max:150'],
            'tahun_lahir_ayah' => $tahunLahir,
            'pendidikan_ayah' => ['nullable', 'string', 'max:50'],
            'nik_ayah' => ['nullable', $nik],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:100'],
            'penghasilan_ayah' => ['nullable', 'string', 'max:60'],
            'nama_ibu' => ['nullable', 'string', 'max:150'],
            'tahun_lahir_ibu' => $tahunLahir,
            'pendidikan_ibu' => ['nullable', 'string', 'max:50'],
            'nik_ibu' => ['nullable', $nik],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:100'],
            'penghasilan_ibu' => ['nullable', 'string', 'max:60'],

            // Tabel wali_siswa
            'nama_wali' => ['nullable', 'string', 'max:150'],
            'tahun_lahir_wali' => $tahunLahir,
            'pendidikan_wali' => ['nullable', 'string', 'max:50'],
            'nik_wali' => ['nullable', $nik],
            'pekerjaan_wali' => ['nullable', 'string', 'max:100'],
            'penghasilan_wali' => ['nullable', 'string', 'max:60'],

            'alamat' => ['nullable', 'string', 'max:255'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'dusun' => ['nullable', 'string', 'max:100'],
            'desa_kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kabupaten_kota' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'kode_pos' => ['nullable', 'regex:/^\d{5}$/'],
            'jenis_tinggal' => ['nullable', 'string', 'max:50'],
            'alat_transportasi' => ['nullable', 'string', 'max:50'],
            'lintang' => ['nullable', 'numeric', 'between:-90,90'],
            'bujur' => ['nullable', 'numeric', 'between:-180,180'],
            'jarak_ke_sekolah' => ['nullable', 'numeric', 'between:0,9999'],

            'telepon_rumah' => ['nullable', $hp],
            'no_hp_siswa' => ['nullable', $hp],
            'no_hp_ayah' => ['nullable', $hp],
            'no_hp_ibu' => ['nullable', $hp],
            'email' => ['nullable', 'email', 'max:100'],
            'email_siswa' => ['nullable', 'email', 'max:100'],
            'email_ayah' => ['nullable', 'email', 'max:100'],
            'email_ibu' => ['nullable', 'email', 'max:100'],

            // Tabel bantuan_siswa
            'penerima_kps' => ['nullable', 'boolean'],
            'no_kps' => ['nullable', 'string', 'max:50'],
            'penerima_kip' => ['nullable', 'boolean'],
            'no_kip' => ['nullable', 'string', 'max:50'],
            'nama_di_kip' => ['nullable', 'string', 'max:150'],
            'no_kks' => ['nullable', 'string', 'max:50'],
            'layak_pip' => ['nullable', 'boolean'],
            'alasan_layak_pip' => ['nullable', 'string', 'max:255'],
            'bank' => ['nullable', 'string', 'max:100'],
            'no_rekening' => ['nullable', 'string', 'max:30'],
            'rekening_atas_nama' => ['nullable', 'string', 'max:150'],

            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public static function messages(): array
    {
        $teks = ' harus 16 digit angka. Pastikan kolom di Excel berformat Teks.';

        return [
            'required' => ':attribute wajib diisi.',
            'max.string' => ':attribute maksimal :max karakter.',
            'date' => 'Format :attribute tidak valid.',
            'email' => 'Format email tidak valid.',
            'in' => ':attribute tidak valid.',
            'exists' => ':attribute tidak ditemukan.',
            'integer' => ':attribute harus berupa angka bulat.',
            'numeric' => ':attribute harus berupa angka.',
            'between.numeric' => ':attribute harus antara :min dan :max.',
            'boolean' => ':attribute harus diisi Ya atau Tidak.',
            'nis.unique' => 'NIS sudah dipakai siswa lain.',
            'nisn.unique' => 'NISN sudah dipakai siswa lain.',
            'nisn.regex' => 'NISN harus 10 digit angka.',
            'nik.regex' => 'NIK siswa' . $teks,
            'no_kk.regex' => 'No KK' . $teks,
            'nik_ayah.regex' => 'NIK ayah' . $teks,
            'nik_ibu.regex' => 'NIK ibu' . $teks,
            'nik_wali.regex' => 'NIK wali' . $teks,
            'kode_pos.regex' => 'Kode pos harus 5 digit angka.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'telepon_rumah.regex' => 'Nomor telepon tidak valid (contoh: 0271123456).',
            'no_hp_siswa.regex' => 'Nomor HP siswa tidak valid (contoh: 081234567890).',
            'no_hp_ayah.regex' => 'Nomor HP tidak valid (contoh: 081234567890).',
            'no_hp_ibu.regex' => 'Nomor HP tidak valid (contoh: 081234567890).',
        ];
    }

    public static function attributes(): array
    {
        return [
            'nis' => 'NIS',
            'nisn' => 'NISN',
            'nik' => 'NIK siswa',
            'no_kk' => 'No KK',
            'nama_lengkap' => 'Nama lengkap',
            'jenis_kelamin' => 'Jenis kelamin',
            'tempat_lahir' => 'Tempat lahir',
            'tanggal_lahir' => 'Tanggal lahir',
            'agama' => 'Agama',
            'asal_sekolah' => 'Asal sekolah',
            'tanggal_masuk' => 'Tanggal masuk',
            'status_siswa' => 'Status siswa',
            'no_akta_lahir' => 'No registrasi akta lahir',
            'kebutuhan_khusus' => 'Kebutuhan khusus',
            'anak_ke' => 'Anak ke-berapa',
            'jumlah_saudara' => 'Jumlah saudara kandung',
            'no_peserta_un' => 'No peserta ujian nasional',
            'no_seri_ijazah' => 'No seri ijazah',
            'skhun' => 'SKHUN',
            'berat_badan' => 'Berat badan',
            'tinggi_badan' => 'Tinggi badan',
            'lingkar_kepala' => 'Lingkar kepala',
            'nama_ayah' => 'Nama ayah',
            'tahun_lahir_ayah' => 'Tahun lahir ayah',
            'pendidikan_ayah' => 'Pendidikan ayah',
            'nik_ayah' => 'NIK ayah',
            'pekerjaan_ayah' => 'Pekerjaan ayah',
            'penghasilan_ayah' => 'Penghasilan ayah',
            'nama_ibu' => 'Nama ibu',
            'tahun_lahir_ibu' => 'Tahun lahir ibu',
            'pendidikan_ibu' => 'Pendidikan ibu',
            'nik_ibu' => 'NIK ibu',
            'pekerjaan_ibu' => 'Pekerjaan ibu',
            'penghasilan_ibu' => 'Penghasilan ibu',
            'nama_wali' => 'Nama wali',
            'tahun_lahir_wali' => 'Tahun lahir wali',
            'pendidikan_wali' => 'Pendidikan wali',
            'nik_wali' => 'NIK wali',
            'pekerjaan_wali' => 'Pekerjaan wali',
            'penghasilan_wali' => 'Penghasilan wali',
            'alamat' => 'Alamat',
            'rt' => 'RT',
            'rw' => 'RW',
            'dusun' => 'Dusun',
            'desa_kelurahan' => 'Desa/kelurahan',
            'kecamatan' => 'Kecamatan',
            'kabupaten_kota' => 'Kabupaten/kota',
            'provinsi' => 'Provinsi',
            'kode_pos' => 'Kode pos',
            'jenis_tinggal' => 'Jenis tinggal',
            'alat_transportasi' => 'Alat transportasi',
            'lintang' => 'Lintang',
            'bujur' => 'Bujur',
            'jarak_ke_sekolah' => 'Jarak rumah ke sekolah',
            'telepon_rumah' => 'Telepon rumah',
            'no_hp_siswa' => 'No. HP siswa',
            'no_hp_ayah' => 'No. HP ayah',
            'no_hp_ibu' => 'No. HP ibu',
            'email' => 'Email',
            'email_siswa' => 'Email siswa',
            'email_ayah' => 'Email ayah',
            'email_ibu' => 'Email ibu',
            'penerima_kps' => 'Penerima KPS',
            'no_kps' => 'No. KPS',
            'penerima_kip' => 'Penerima KIP',
            'no_kip' => 'Nomor KIP',
            'nama_di_kip' => 'Nama di KIP',
            'no_kks' => 'Nomor KKS',
            'layak_pip' => 'Layak PIP',
            'alasan_layak_pip' => 'Alasan layak PIP',
            'bank' => 'Bank',
            'no_rekening' => 'Nomor rekening',
            'rekening_atas_nama' => 'Rekening atas nama',
            'kelas_id' => 'Kelas',
            'catatan' => 'Catatan',
            'foto' => 'Foto',
        ];
    }

    /* ---------------------------- Penyimpanan ---------------------------- */

    /**
     * Simpan siswa baru ($id = null) atau perbarui. Mengembalikan id siswa.
     *
     * Hanya kolom yang benar-benar dikirim yang ditulis, jadi form/import yang
     * belum memuat kolom baru tidak akan menimpa kolom tersebut dengan kosong.
     */
    public static function save(array $d, ?int $id = null, ?string $foto = null): int
    {
        return DB::transaction(function () use ($d, $id, $foto) {
            $now = now();

            $siswa = self::pickPresent($d, self::SISWA);
            if (!$id) {
                $siswa['status_siswa'] = $siswa['status_siswa'] ?? 'Aktif';
            }
            if ($foto) {
                $siswa['foto'] = $foto;
            }

            if ($id) {
                DB::table('siswa')->where('id', $id)->update($siswa + ['updated_at' => $now]);
            } else {
                $id = DB::table('siswa')->insertGetId($siswa + ['created_at' => $now, 'updated_at' => $now]);
            }

            self::upsert('orang_tua', ['siswa_id' => $id], self::pickPresent($d, self::ORANG_TUA));
            self::upsert('alamat_siswa', ['siswa_id' => $id], self::pickPresent($d, self::ALAMAT));
            self::upsert('kontak_siswa', ['siswa_id' => $id], self::pickPresent($d, self::KONTAK));
            // Tabel baru: baris dibuat hanya jika ada isiannya
            self::upsert('wali_siswa', ['siswa_id' => $id], self::pickPresent($d, self::WALI), true);
            self::upsert('bantuan_siswa', ['siswa_id' => $id], self::pickPresent($d, self::BANTUAN), true);

            if (!empty($d['kelas_id'])) {
                $kelas = DB::table('kelas')->where('id', $d['kelas_id'])->first();

                if ($kelas) {
                    $status = $siswa['status_siswa']
                        ?? DB::table('siswa')->where('id', $id)->value('status_siswa')
                        ?? 'Aktif';

                    self::upsert(
                        'riwayat_kelas',
                        ['siswa_id' => $id, 'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id],
                        [
                            'kelas_id' => $kelas->id,
                            'status' => $status,
                            'catatan' => $d['catatan'] ?? null,
                        ]
                    );
                }
            }

            return $id;
        });
    }

    /** Data lengkap satu siswa (siswa + orang tua + wali + alamat + kontak + bantuan + kelas terakhir). */
    public static function load(int $id): ?array
    {
        $siswa = DB::table('siswa')->where('id', $id)->first();
        if (!$siswa) {
            return null;
        }

        $data = (array) $siswa;

        foreach (['orang_tua', 'wali_siswa', 'alamat_siswa', 'kontak_siswa', 'bantuan_siswa'] as $table) {
            $row = DB::table($table)->where('siswa_id', $id)->first();
            if ($row) {
                $row = (array) $row;
                unset($row['id'], $row['siswa_id'], $row['created_at'], $row['updated_at']);
                $data += $row;
            }
        }

        $rk = DB::table('riwayat_kelas')->where('siswa_id', $id)->orderByDesc('tahun_pelajaran_id')->first();
        if ($rk) {
            $data['kelas_id'] = $rk->kelas_id;
            $data['catatan'] = $rk->catatan;
        }

        return $data;
    }

    /** Hanya kolom yang benar-benar dikirim, agar kolom lain tidak ditimpa kosong. */
    private static function pickPresent(array $d, array $keys): array
    {
        return array_intersect_key($d, array_flip($keys));
    }

    private static function upsert(string $table, array $where, array $values, bool $lewatiJikaKosong = false): void
    {
        $now = now();
        $query = DB::table($table)->where($where);

        if ($query->exists()) {
            $query->update($values + ['updated_at' => $now]);
            return;
        }

        if ($lewatiJikaKosong && !array_filter($values, fn ($v) => $v !== null && $v !== '')) {
            return;
        }

        DB::table($table)->insert($where + $values + ['created_at' => $now, 'updated_at' => $now]);
    }
}