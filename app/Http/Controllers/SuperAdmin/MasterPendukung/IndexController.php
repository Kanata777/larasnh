<?php

namespace App\Http\Controllers\SuperAdmin\MasterPendukung;

use App\Models\OrangTua;
use App\Support\RoleAccess;
use App\Support\RoleRoute;
use App\Support\RoleView;
use App\Support\SiswaData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IndexController extends BaseController
{
    private const KETERANGAN_STATUS = [
        'Aktif' => 'Siswa yang sedang belajar di sekolah.',
        'Pindah' => 'Siswa yang pindah ke sekolah lain.',
        'Lulus' => 'Siswa yang telah menyelesaikan pendidikan.',
        'Nonaktif' => 'Siswa yang tidak lagi aktif (berhenti atau cuti).',
    ];

    public function __invoke(Request $request)
    {
        $tahunPelajaran = DB::table('tahun_pelajaran')->orderByDesc('nama')->get();

        return RoleView::make('master-pendukung.index', [
            'tahunPelajaran' => $tahunPelajaran,
            'tahunAktifId' => $tahunPelajaran->firstWhere('status', 'aktif')->id ?? null,
            'kelas' => $this->daftarKelas(),
            'waliKelasList' => $this->daftarWaliKelas(),
            'waliPerKelas' => $this->daftarWaliPerKelas(),
            'guru' => $this->daftarGuru(),
            'statusSiswa' => $this->daftarStatusSiswa(),
            'referensi' => $this->daftarReferensi(),
            'orangTua' => $this->daftarOrangTua($request),
            'siswaList' => $this->daftarSiswa(),
            'akses' => $this->akses(),
        ]);
    }

    /**
     * Hak akses role yang sedang login untuk tiap tab master.
     * Dipakai di view: @if($akses['tahun_ubah']) <tombol tambah/ubah> @endif
     * Nilainya otomatis mengikuti route yang dimiliki role (routes/role/*.php).
     */
    private function akses(): array
    {
        $ada = fn(string $nama) => RoleRoute::has('master-pendukung.' . $nama);

        return [
            'tahun_ubah' => $ada('tahun-pelajaran.store'),
            'kelas_ubah' => $ada('kelas.store'),
            'kelas_import' => $ada('kelas.import.preview'),
            'kelas_wali' => $ada('kelas.wali'),
            'referensi_ubah' => $ada('referensi.store'),
            'referensi_hapus' => $ada('referensi.destroy'),
            'orang_tua_ubah' => $ada('orang-tua.store'),
            'orang_tua_hapus' => $ada('orang-tua.destroy'),
            'lihat_sensitif' => RoleAccess::lihatDataSensitif(),
        ];
    }

    private function daftarKelas()
    {
        return DB::table('kelas')
            ->join('tahun_pelajaran as tp', 'tp.id', '=', 'kelas.tahun_pelajaran_id')
            ->select('kelas.*', 'tp.nama as tahun_pelajaran_nama')
            ->selectRaw("(select count(*) from riwayat_kelas rk where rk.kelas_id = kelas.id and rk.status = 'Aktif') as jumlah_siswa")
            ->orderByDesc('tp.nama')
            ->orderBy('kelas.tingkat')
            ->orderBy('kelas.nama_kelas')
            ->get();
    }

    private function daftarWaliKelas()
    {
        return DB::table('wali_kelas')
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get(['id', 'nama']);
    }

    private function daftarWaliPerKelas()
    {
        return DB::table('kelas_wali_kelas as kw')
            ->join('wali_kelas as w', 'w.id', '=', 'kw.wali_kelas_id')
            ->orderBy('w.nama')
            ->get(['kw.kelas_id', 'w.id', 'w.nama'])
            ->groupBy('kelas_id');
    }

    private function daftarGuru()
    {
        return Schema::hasTable('guru')
            ? DB::table('guru')->orderBy('nama')->get(['id', 'nama'])
            : collect();
    }

    private function daftarStatusSiswa()
    {
        $jumlah = DB::table('siswa')
            ->select('status_siswa', DB::raw('count(*) as total'))
            ->groupBy('status_siswa')
            ->pluck('total', 'status_siswa');

        return collect(SiswaData::STATUS)->map(fn($s) => (object) [
            'nama' => $s,
            'keterangan' => self::KETERANGAN_STATUS[$s] ?? '-',
            'jumlah_siswa' => $jumlah[$s] ?? 0,
        ]);
    }

    private function daftarReferensi()
    {
        return Schema::hasTable('referensi')
            ? DB::table('referensi')->orderBy('nama')->get()->groupBy('jenis')
            : collect();
    }

    private function daftarOrangTua(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        // Hanya dua nilai yang valid; selain 'siswa' dianggap 'orang_tua'
        $cariDi = $request->query('cari_di') === 'siswa' ? 'siswa' : 'orang_tua';
        $tahunId = (int) $request->query('tahun_id', 0);
        $kelasId = (int) $request->query('kelas_id', 0);
        $lihatNik = RoleAccess::lihatDataSensitif();

        $hasil = OrangTua::query()
            ->leftJoin('siswa', 'siswa.id', '=', 'orang_tua.siswa_id')
            ->select(
                'orang_tua.*',
                'siswa.nama_lengkap as siswa_nama',
                'siswa.nis as siswa_nis',
                'siswa.nisn as siswa_nisn'
            )
            // kelas aktif siswa (ditampilkan di tabel)
            ->selectSub(function ($s) {
                $s->from('riwayat_kelas as rk')
                    ->join('kelas as k', 'k.id', '=', 'rk.kelas_id')
                    ->whereColumn('rk.siswa_id', 'orang_tua.siswa_id')
                    ->where('rk.status', 'Aktif')
                    ->orderByDesc('rk.id')
                    ->limit(1)
                    ->select('k.nama_kelas');
            }, 'kelas_nama')
            ->when($q !== '', function ($query) use ($q, $cariDi, $lihatNik) {
                $query->where(function ($w) use ($q, $cariDi, $lihatNik) {
                    if ($cariDi === 'siswa') {
                        // Mode Siswa: hanya data siswa
                        $w->where('siswa.nama_lengkap', 'like', "%{$q}%")
                            ->orWhere('siswa.nis', 'like', "%{$q}%")
                            ->orWhere('siswa.nisn', 'like', "%{$q}%");
                    } else {
                        // Mode Orang tua: nama siswa TIDAK ikut dicari
                        $w->where('orang_tua.nama_ayah', 'like', "%{$q}%")
                            ->orWhere('orang_tua.nama_ibu', 'like', "%{$q}%");

                        // NIK hanya bisa dicari oleh role yang berhak melihatnya
                        if ($lihatNik) {
                            $w->orWhere('orang_tua.nik_ayah', 'like', "%{$q}%")
                                ->orWhere('orang_tua.nik_ibu', 'like', "%{$q}%");
                        }
                    }
                });
            })
            // filter tahun pelajaran dan/atau kelas lewat riwayat kelas siswa
            ->when($tahunId || $kelasId, function ($query) use ($tahunId, $kelasId) {
                $query->whereExists(function ($sub) use ($tahunId, $kelasId) {
                    $sub->selectRaw('1')
                        ->from('riwayat_kelas as rk')
                        ->join('kelas as k', 'k.id', '=', 'rk.kelas_id')
                        ->whereColumn('rk.siswa_id', 'orang_tua.siswa_id')
                        ->when($tahunId, fn($s) => $s->where('k.tahun_pelajaran_id', $tahunId))
                        ->when($kelasId, fn($s) => $s->where('k.id', $kelasId));
                });
            })
            ->orderByDesc('orang_tua.id')
            ->paginate(15)
            ->withQueryString();

        // Role tanpa hak melihat data sensitif: NIK dikosongkan sebelum sampai ke view
        if (!$lihatNik) {
            $hasil->getCollection()->transform(function ($row) {
                $row->nik_ayah = null;
                $row->nik_ibu = null;

                return $row;
            });
        }

        return $hasil;
    }

    private function daftarSiswa()
    {
        return DB::table('siswa')
            ->select('id', 'nama_lengkap as nama')
            ->orderBy('nama_lengkap')
            ->get();
    }
}