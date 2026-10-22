<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\RiwayatKelas;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Ringkasan data siswa
        $totalSiswa = Siswa::count();

        $siswaAktif = Siswa::where(
            'status_siswa',
            'Aktif'
        )->count();

        $siswaBaru = Siswa::whereDate(
            'tanggal_masuk',
            today()
        )->count();

        $siswaLakiLaki = Siswa::where(
            'jenis_kelamin',
            'L'
        )->count();

        $siswaPerempuan = Siswa::where(
            'jenis_kelamin',
            'P'
        )->count();

        // Total kelas yang aktif
        $totalKelas = Kelas::where(
            'status',
            'aktif'
        )->count();

        // Riwayat kelas aktif, dikelompokkan per kelas.
        // Belum dibatasi tahun pelajaran karena struktur
        // tabel tahun_pelajaran belum diperiksa.
        $jumlahPerKelas = Kelas::query()
            ->leftJoin(
                'riwayat_kelas',
                function ($join) {
                    $join->on(
                        'kelas.id',
                        '=',
                        'riwayat_kelas.kelas_id'
                    )->where(
                        'riwayat_kelas.status',
                        'Aktif'
                    );
                }
            )
            ->select('kelas.id', 'kelas.nama_kelas')
            ->selectRaw(
                'COUNT(DISTINCT riwayat_kelas.siswa_id) as total'
            )
            ->where('kelas.status', 'aktif')
            ->groupBy('kelas.id', 'kelas.nama_kelas')
            ->orderBy('kelas.tingkat')
            ->orderBy('kelas.nama_kelas')
            ->get();

        $maksimumSiswaKelas = $jumlahPerKelas->max('total') ?? 0;

        // ---------------------------------------------------------
        // TAMBAHAN: tahun pelajaran (dari tabel tahun_pelajaran)
        // ---------------------------------------------------------

        // Tahun pelajaran yang sedang aktif
        $tahunAktif = DB::table('tahun_pelajaran')
            ->where('status', 'aktif')
            ->orderByDesc('id')
            ->first();

        // Daftar semua tahun pelajaran (untuk panel "Tahun Pelajaran")
        $daftarTahun = DB::table('tahun_pelajaran')
            ->select(
                'tahun_pelajaran.id',
                'tahun_pelajaran.nama',
                'tahun_pelajaran.status'
            )
            ->selectSub(function ($q) {
                $q->from('kelas')
                    ->whereColumn('kelas.tahun_pelajaran_id', 'tahun_pelajaran.id')
                    ->selectRaw('count(*)');
            }, 'jumlah_kelas')
            ->selectSub(function ($q) {
                $q->from('riwayat_kelas as rk')
                    ->join('kelas as k', 'k.id', '=', 'rk.kelas_id')
                    ->whereColumn('k.tahun_pelajaran_id', 'tahun_pelajaran.id')
                    ->where('rk.status', 'Aktif')
                    ->selectRaw('count(*)');
            }, 'jumlah_siswa')
            ->orderByDesc('tahun_pelajaran.nama')
            ->get();

        // ---------------------------------------------------------
        // TAMBAHAN: siswa yang ditambahkan dalam 1 bulan terakhir
        // ---------------------------------------------------------
        $sejak = now()->subMonth()->startOfDay();

        $siswaSebulan = Siswa::where('created_at', '>=', $sejak)->count();

        $siswaSebulanList = Siswa::query()
            ->select(
                'siswa.id',
                'siswa.nis',
                'siswa.nama_lengkap as nama',
                'siswa.jenis_kelamin',
                'siswa.status_siswa',
                'siswa.created_at'
            )
            ->selectSub(function ($q) {
                $q->from('riwayat_kelas as rk')
                    ->join('kelas as k', 'k.id', '=', 'rk.kelas_id')
                    ->whereColumn('rk.siswa_id', 'siswa.id')
                    ->where('rk.status', 'Aktif')
                    ->orderByDesc('rk.id')
                    ->limit(1)
                    ->select('k.nama_kelas');
            }, 'nama_kelas')
            ->where('siswa.created_at', '>=', $sejak)
            ->orderByDesc('siswa.created_at')
            ->limit(10)
            ->get();

        // Notifikasi awal
        $notifikasi = [];

        if ($totalSiswa === 0) {
            $notifikasi[] = 'Belum ada data siswa yang tersimpan.';
        }

        return view('super-admin.dashboard', compact(
            'totalSiswa',
            'siswaAktif',
            'siswaBaru',
            'totalKelas',
            'siswaLakiLaki',
            'siswaPerempuan',
            'jumlahPerKelas',
            'maksimumSiswaKelas',
            'tahunAktif',
            'daftarTahun',
            'siswaSebulan',
            'siswaSebulanList',
            'notifikasi'
        ));
    }
}