<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;

use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Dashboard hanya untuk Guru BK
        abort_unless(auth()->user()->role?->value === 'guru_bk', 403, 'Dashboard hanya untuk Guru BK.');

        $total = Siswa::where('status_siswa', 'Aktif')->count();

        $jk = Siswa::where('status_siswa', 'Aktif')
            ->selectRaw('jenis_kelamin, count(*) as n')
            ->groupBy('jenis_kelamin')->pluck('n', 'jenis_kelamin');

        // Jumlah siswa per kelas, sekaligus dipisah laki-laki (L) dan perempuan (P)
        $perKelas = DB::table('riwayat_kelas as r')
            ->join('kelas as k', 'k.id', '=', 'r.kelas_id')
            ->join('siswa as s', 's.id', '=', 'r.siswa_id')
            ->where('r.status', 'Aktif')
            ->where('s.status_siswa', 'Aktif')
            ->whereNull('s.deleted_at')
            ->groupBy('k.id', 'k.nama_kelas', 'k.tingkat')
            ->orderBy('k.tingkat')->orderBy('k.nama_kelas')
            ->get([
                'k.id', 'k.nama_kelas', 'k.tingkat',
                DB::raw('count(*) as jumlah'),
                DB::raw("sum(case when s.jenis_kelamin = 'L' then 1 else 0 end) as laki"),
                DB::raw("sum(case when s.jenis_kelamin = 'P' then 1 else 0 end) as perempuan"),
            ]);

        $terbaru = Siswa::with('riwayatAktif.kelas')
            ->where('status_siswa', 'Aktif')->latest('id')->limit(8)->get();

        return view('bk.dashboard', [
            'total' => $total, 'laki' => $jk['L'] ?? 0, 'perempuan' => $jk['P'] ?? 0,
            'perKelas' => $perKelas, 'terbaru' => $terbaru,
        ]);
    }
}