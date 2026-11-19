<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Bk\Concerns\MencariSiswa;
use App\Models\KasusKonseling;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KasusKonselingController extends Controller
{
    use MencariSiswa;

    // Daftar semua laporan kasus
    public function index(Request $request)
    {
        $query = KasusKonseling::with('siswa')->orderByDesc('tanggal')->orderByDesc('id');

        $q = trim((string) $request->input('q'));
        if ($q !== '') {
            // Nama siswa ada di database sekolah, jadi cari id-nya dulu
            $idSiswa = Siswa::where('nama_lengkap', 'like', "%{$q}%")
                ->orWhere('nis', 'like', "%{$q}%")
                ->limit(500)->pluck('id');
            $query->whereIn('siswa_id', $idSiswa);
        }

        if ($bidang = $request->input('bidang')) {
            $query->where('bidang_layanan', $bidang);
        }

        $daftarKasus = $query->paginate(15)->withQueryString();

        return view('bk.kasus-konseling.index', compact('daftarKasus'));
    }

    // Langkah 1: guru BK mencari dan memilih siswa
    public function pilihSiswa(Request $request)
    {
        $query = Siswa::with('riwayatAktif.kelas')->where('status_siswa', 'Aktif')->orderBy('nama_lengkap');
        $this->terapkanFilterSiswa($query, $request);

        $daftarSiswa = $query->paginate(15)->withQueryString();
        $daftarKelas = $this->daftarKelas();

        return view('bk.kasus-konseling.pilih-siswa', compact('daftarSiswa', 'daftarKelas'));
    }

    // Langkah 2: form laporan untuk siswa yang dipilih
    public function create(Siswa $siswa)
    {
        $siswa->load('riwayatAktif.kelas');
        $daftarKelas = $this->daftarKelas();
        $semesterSekarang = now()->month >= 7 ? 'Ganjil' : 'Genap';

        return view('bk.kasus-konseling.create', compact('siswa', 'daftarKelas', 'semesterSekarang'));
    }

    public function store(Request $request, Siswa $siswa)
    {
        $data = $request->validate([
            'komponen_layanan' => ['required', Rule::in(KasusKonseling::KOMPONEN)],
            'bidang_layanan'   => ['required', Rule::in(KasusKonseling::BIDANG)],
            'kelas_id'         => ['required', 'integer', Rule::exists('kelas', 'id')],
            'semester'         => ['required', Rule::in(KasusKonseling::SEMESTER)],
            'tanggal'          => ['required', 'date', 'before_or_equal:today'],
            'topik_layanan'    => ['required', 'string', 'max:200'],
            'tujuan_layanan'   => ['required', 'string', 'max:3000'],
            'durasi_menit'     => ['required', 'integer', 'min:1', 'max:600'],
            'materi'           => ['required', 'string', 'max:5000'],
            'tindak_lanjut'    => ['required', 'string', 'max:3000'],
        ]);

        $data['siswa_id']    = $siswa->id;
        $data['nama_kelas']  = Kelas::find($data['kelas_id'])?->nama_kelas;
        $data['dibuat_oleh'] = $request->user()->id;

        $kasus = KasusKonseling::create($data);

        return redirect()->route('bk.kasus-konseling.show', $kasus)->with('ok', 'Laporan kasus berhasil disimpan.');
    }

    public function show(KasusKonseling $kasus)
    {
        $kasus->load('siswa.riwayatAktif.kelas');

        return view('bk.kasus-konseling.show', compact('kasus'));
    }

    public function destroy(KasusKonseling $kasus)
    {
        $kasus->delete();

        return redirect()->route('bk.kasus-konseling.index')->with('ok', 'Laporan kasus dihapus.');
    }
}
