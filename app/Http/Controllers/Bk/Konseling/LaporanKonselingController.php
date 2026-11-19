<?php

namespace App\Http\Controllers\Bk\Konseling;

use App\Enums\KomponenLayanan;
use App\Enums\Semester;
use App\Http\Controllers\Controller;
use App\Http\Requests\Konseling\SimpanLaporanKonselingRequest;
use App\Models\Konseling\BidangLayanan;
use App\Models\Konseling\LaporanKonseling;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Support\BatasAksesKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Menu "Kasus & Konseling": laporan layanan konseling per siswa.
 * - Guru BK            : lihat, buat, ubah, hapus
 * - Wali kelas         : hanya melihat laporan kelasnya
 * - Kepala sekolah     : hanya melihat
 * (pembatasan role dipasang di routes/web.php)
 */
class LaporanKonselingController extends Controller
{
    /** Daftar semua laporan. */
    public function index(Request $request)
    {
        $laporan = LaporanKonseling::with(['siswa', 'kelas', 'bidang'])
            ->orderByDesc('tanggal')->orderByDesc('id');

        $kelasBoleh = BatasAksesKelas::idKelasBoleh($request->user());
        if ($kelasBoleh !== null) {
            $laporan->whereIn('kelas_id', $kelasBoleh);
        }

        if ($request->filled('q')) {
            // nama siswa ada di database sekolah: cari ID-nya dulu
            $idSiswa = Siswa::where('nama_lengkap', 'like', '%' . $request->input('q') . '%')->pluck('id');
            $laporan->whereIn('siswa_id', $idSiswa);
        }
        if ($request->filled('bidang')) {
            $laporan->where('bidang_id', $request->input('bidang'));
        }
        if ($request->filled('komponen')) {
            $laporan->where('komponen_layanan', $request->input('komponen'));
        }

        return view('bk.laporan-konseling.daftar-laporan', [
            'daftarLaporan'  => $laporan->paginate(15)->withQueryString(),
            'daftarBidang'   => BidangLayanan::orderBy('id')->get(),
            'daftarKomponen' => KomponenLayanan::cases(),
        ]);
    }

    /** Langkah 1 membuat laporan: guru BK memilih siswa. */
    public function pilihSiswa(Request $request)
    {
        $siswa = Siswa::with('riwayatAktif.kelas')->where('status_siswa', 'Aktif');

        if ($request->filled('q')) {
            $kata = '%' . $request->input('q') . '%';
            $siswa->where(fn ($w) => $w->where('nama_lengkap', 'like', $kata)
                ->orWhere('nis', 'like', $kata)->orWhere('nisn', 'like', $kata));
        }
        if ($request->filled('kelas')) {
            $siswa->whereHas('riwayatAktif', fn ($x) => $x->where('kelas_id', $request->input('kelas')));
        }

        $daftarSiswa = $siswa->orderBy('nama_lengkap')->paginate(15)->withQueryString();

        // jumlah laporan yang sudah dimiliki tiap siswa di halaman ini
        $jumlahLaporan = LaporanKonseling::whereIn('siswa_id', $daftarSiswa->pluck('id'))
            ->selectRaw('siswa_id, count(*) as jumlah')->groupBy('siswa_id')->pluck('jumlah', 'siswa_id');

        return view('bk.laporan-konseling.pilih-siswa', [
            'daftarSiswa'   => $daftarSiswa,
            'jumlahLaporan' => $jumlahLaporan,
            'daftarKelas'   => Kelas::where('status', 'aktif')->orderBy('tingkat')->orderBy('nama_kelas')->get(),
        ]);
    }

    /** Langkah 2: formulir laporan untuk siswa yang dipilih. */
    public function create(Siswa $siswa)
    {
        abort_unless($siswa->status_siswa === 'Aktif', 404);

        return view('bk.laporan-konseling.buat-laporan', $this->dataFormulir($siswa));
    }

    public function store(SimpanLaporanKonselingRequest $request, Siswa $siswa)
    {
        abort_unless($siswa->status_siswa === 'Aktif', 404);

        $data = $request->validated();
        $kelas = Kelas::findOrFail($data['kelas_id']);

        $laporan = LaporanKonseling::create($data + [
            'siswa_id'           => $siswa->id,
            'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
            'dibuat_oleh'        => $request->user()->id,
        ]);

        return redirect()->route('bk.laporan-konseling.show', $laporan)
            ->with('success', 'Laporan konseling berhasil disimpan.');
    }

    public function show(Request $request, LaporanKonseling $laporan)
    {
        $kelasBoleh = BatasAksesKelas::idKelasBoleh($request->user());
        abort_if($kelasBoleh !== null && ! $kelasBoleh->contains($laporan->kelas_id), 403, 'Laporan ini bukan untuk kelas Anda.');

        $laporan->load(['siswa.riwayatAktif.kelas', 'kelas', 'bidang', 'pembuat']);

        return view('bk.laporan-konseling.detail-laporan', compact('laporan'));
    }

    public function edit(LaporanKonseling $laporan)
    {
        $laporan->load('siswa');
        abort_if($laporan->siswa === null, 404);

        return view('bk.laporan-konseling.ubah-laporan', $this->dataFormulir($laporan->siswa, $laporan));
    }

    public function update(SimpanLaporanKonselingRequest $request, LaporanKonseling $laporan)
    {
        $data = $request->validated();
        $kelas = Kelas::findOrFail($data['kelas_id']);

        $laporan->update($data + ['tahun_pelajaran_id' => $kelas->tahun_pelajaran_id]);

        return redirect()->route('bk.laporan-konseling.show', $laporan)
            ->with('success', 'Laporan konseling berhasil diperbarui.');
    }

    public function destroy(LaporanKonseling $laporan)
    {
        $laporan->delete();   // soft delete: data tetap tersimpan di database

        return redirect()->route('bk.laporan-konseling.index')
            ->with('success', 'Laporan konseling dihapus.');
    }

    /** Data pilihan untuk formulir (dipakai halaman buat dan ubah). */
    private function dataFormulir(Siswa $siswa, ?LaporanKonseling $laporan = null): array
    {
        $siswa->loadMissing('riwayatAktif.kelas');

        // Hanya kelas yang sudah ditempatkan siswa (ada siswa aktif di riwayat_kelas)
        $kelasTerisi = DB::table('riwayat_kelas as r')
            ->join('siswa as s', 's.id', '=', 'r.siswa_id')
            ->where('r.status', 'Aktif')
            ->whereNull('s.deleted_at')
            ->select('r.kelas_id');

        $kelas = Kelas::where(function ($q) use ($laporan, $kelasTerisi) {
            $q->where(fn ($k) => $k->where('status', 'aktif')->whereIn('id', $kelasTerisi));
            if ($laporan) {
                $q->orWhere('id', $laporan->kelas_id);   // kelas lama tetap muncul saat ubah
            }
        })->orderBy('tingkat')->orderBy('nama_kelas')->get();

        return [
            'siswa'          => $siswa,
            'laporan'        => $laporan,
            'daftarKomponen' => KomponenLayanan::cases(),
            'daftarBidang'   => BidangLayanan::orderBy('id')->get(),
            'daftarKelas'    => $kelas,
            'daftarSemester' => Semester::cases(),
            'kelasBawaan'    => $siswa->riwayatAktif?->kelas_id,
            'semesterBawaan' => Semester::dariTanggal(now())->value,
        ];
    }
}
