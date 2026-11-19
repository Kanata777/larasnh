<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    // Role yang boleh MELIHAT data siswa (semuanya read-only di tahap ini)
    private const BOLEH = ['guru_bk', 'kesiswaan', 'wali_kelas', 'kepala_tu', 'staff_tu', 'kepala_sekolah'];

    public function index(Request $r)
    {
        $this->izin();

        $q = $this->batas(Siswa::with('riwayatAktif.kelas')->where('status_siswa', 'Aktif'));

        if ($r->filled('q')) {
            $kata = '%' . $r->input('q') . '%';
            $q->where(fn ($w) => $w->where('nama_lengkap', 'like', $kata)
                ->orWhere('nis', 'like', $kata)->orWhere('nisn', 'like', $kata));
        }
        if ($r->filled('kelas')) {
            $q->whereHas('riwayatAktif', fn ($x) => $x->where('kelas_id', $r->input('kelas')));
        }

        // Pilihan filter: hanya kelas yang sudah ditempatkan siswa (ada siswa aktif di riwayat_kelas)
        $kelasTerisi = DB::table('riwayat_kelas as r')
            ->join('siswa as s', 's.id', '=', 'r.siswa_id')
            ->where('r.status', 'Aktif')
            ->whereNull('s.deleted_at')
            ->select('r.kelas_id');

        $ids = $this->kelasBoleh();
        $kelas = Kelas::where('status', 'aktif')
            ->whereIn('id', $kelasTerisi)
            ->when($ids !== null, fn ($k) => $k->whereIn('id', $ids))
            ->orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('bk.siswa.index', [
            'siswa' => $q->orderBy('nama_lengkap')->paginate(15)->withQueryString(),
            'kelas' => $kelas,
        ]);
    }

    public function show($id)
    {
        $this->izin();

        $siswa = $this->batas(Siswa::with(['riwayatAktif.kelas', 'orangTua', 'waliSiswa']))
            ->findOrFail($id);

        return view('bk.siswa.show', compact('siswa'));
    }

    private function izin(): void
    {
        abort_unless(in_array(auth()->user()->role->value, self::BOLEH), 403, 'Anda tidak punya akses ke data siswa.');
    }

    // Wali kelas hanya melihat kelasnya (utama + sebagai asisten/pengganti)
    private function kelasBoleh()
    {
        $u = auth()->user();
        if ($u->role?->value !== 'wali_kelas') return null;

        return Kelas::where('wali_kelas_id', $u->wali_kelas_sekolah_id)->pluck('id')
            ->merge(DB::table('kelas_wali_kelas')
                ->where('wali_kelas_id', $u->wali_kelas_sekolah_id)->pluck('kelas_id'))
            ->unique()->values();
    }

    private function batas($q)
    {
        $ids = $this->kelasBoleh();
        return $ids === null ? $q
            : $q->whereHas('riwayatAktif', fn ($r) => $r->whereIn('kelas_id', $ids));
    }
}
