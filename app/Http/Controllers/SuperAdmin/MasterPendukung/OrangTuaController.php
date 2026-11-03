<?php

namespace App\Http\Controllers\SuperAdmin\MasterPendukung;

use App\Models\OrangTua;
use App\Support\RoleAccess;
use App\Support\RoleRoute;
use App\Support\RoleView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrangTuaController extends BaseController
{
    private function rules(): array
    {
        $tahunMaks = (int) date('Y');

        return [
            'siswa_id' => ['required', 'integer', 'exists:siswa,id'],

            'nama_ayah' => ['nullable', 'string', 'max:150'],
            'nik_ayah' => ['nullable', 'string', 'max:20'],
            'tahun_lahir_ayah' => ['nullable', 'integer', 'between:1900,' . $tahunMaks],
            'pendidikan_ayah' => ['nullable', 'string', 'max:50'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:100'],
            'penghasilan_ayah' => ['nullable', 'string', 'max:60'],

            'nama_ibu' => ['nullable', 'string', 'max:150'],
            'nik_ibu' => ['nullable', 'string', 'max:20'],
            'tahun_lahir_ibu' => ['nullable', 'integer', 'between:1900,' . $tahunMaks],
            'pendidikan_ibu' => ['nullable', 'string', 'max:50'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:100'],
            'penghasilan_ibu' => ['nullable', 'string', 'max:60'],
        ];
    }
    public function index(Request $request)
    {
        return RoleView::make('orang-tua.index', [
            'tahunPelajaran' => DB::table('tahun_pelajaran')->orderByDesc('nama')->get(),
            'kelas' => $this->daftarKelas(),
            'referensi' => $this->daftarReferensi(),
            'orangTua' => $this->daftarOrangTua($request),
            'siswaList' => $this->daftarSiswa(),
            'akses' => $this->akses(),
        ]);
    }

    public function store(Request $request)
    {
        OrangTua::create($request->validate($this->rules()));

        return back()->with('success', 'Data orang tua berhasil ditambahkan.');
    }

    public function update(Request $request, OrangTua $orangTua)
    {
        $orangTua->update($request->validate($this->rules()));

        return back()->with('success', 'Data orang tua berhasil diperbarui.');
    }

    public function destroy(OrangTua $orangTua)
    {
        $orangTua->delete();

        return back()->with('success', 'Data orang tua berhasil dihapus.');
    }

    private function akses(): array
    {
        return [
            'orang_tua_ubah' => RoleRoute::has('orang-tua.store'),
            'orang_tua_hapus' => RoleRoute::has('orang-tua.destroy'),
            'lihat_sensitif' => RoleAccess::lihatDataSensitif(),
        ];
    }

    private function daftarKelas()
    {
        return DB::table('kelas')
            ->join('tahun_pelajaran as tp', 'tp.id', '=', 'kelas.tahun_pelajaran_id')
            ->select('kelas.*', 'tp.nama as tahun_pelajaran_nama')
            ->orderByDesc('tp.nama')
            ->orderBy('kelas.tingkat')
            ->orderBy('kelas.nama_kelas')
            ->get();
    }

    private function daftarReferensi()
    {
        return Schema::hasTable('referensi')
            ? DB::table('referensi')->orderBy('nama')->get()->groupBy('jenis')
            : collect();
    }

    private function daftarSiswa()
    {
        return DB::table('siswa')
            ->select('id', 'nama_lengkap as nama')
            ->orderBy('nama_lengkap')
            ->get();
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
                        $w->where('siswa.nama_lengkap', 'like', "%{$q}%")
                            ->orWhere('siswa.nis', 'like', "%{$q}%")
                            ->orWhere('siswa.nisn', 'like', "%{$q}%");
                    } else {
                        $w->where('orang_tua.nama_ayah', 'like', "%{$q}%")
                            ->orWhere('orang_tua.nama_ibu', 'like', "%{$q}%");

                        if ($lihatNik) {
                            $w->orWhere('orang_tua.nik_ayah', 'like', "%{$q}%")
                                ->orWhere('orang_tua.nik_ibu', 'like', "%{$q}%");
                        }
                    }
                });
            })
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
}