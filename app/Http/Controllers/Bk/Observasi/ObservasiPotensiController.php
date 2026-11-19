<?php

namespace App\Http\Controllers\Bk\Observasi;

use App\Enums\KategoriPotensi;
use App\Enums\TingkatPotensi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Observasi\SimpanObservasiPotensiRequest;
use App\Models\Observasi\ObservasiPotensi;
use App\Models\Observasi\Prestasi;
use App\Models\Observasi\ProfilSiswaBk;
use App\Models\Siswa;
use App\Support\BatasAksesKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Menu "Observasi & Potensi": melihat potensi, bakat/minat, dan prestasi siswa.
 * - Guru BK        : lihat + catat/ubah/hapus observasi
 * - Kesiswaan      : lihat + catat prestasi (lewat PrestasiSiswaController)
 * - Wali kelas     : lihat siswa kelasnya
 * - Kepala sekolah : lihat
 * (pembatasan role dipasang di routes/web.php)
 */
class ObservasiPotensiController extends Controller
{
    /** Daftar siswa beserta ringkasan potensi menonjol dan jumlah prestasi. */
    public function index(Request $request)
    {
        $siswa = Siswa::with('riwayatAktif.kelas')->where('status_siswa', 'Aktif');

        $kelasBoleh = BatasAksesKelas::idKelasBoleh($request->user());
        if ($kelasBoleh !== null) {
            $siswa->whereHas('riwayatAktif', fn ($x) => $x->whereIn('kelas_id', $kelasBoleh));
        }

        if ($request->filled('q')) {
            $kata = '%' . $request->input('q') . '%';
            $siswa->where(fn ($w) => $w->where('nama_lengkap', 'like', $kata)
                ->orWhere('nis', 'like', $kata)->orWhere('nisn', 'like', $kata));
        }
        if ($request->filled('kelas')) {
            $siswa->whereHas('riwayatAktif', fn ($x) => $x->where('kelas_id', $request->input('kelas')));
        }

        // Filter "menonjol di bidang X": tingkat dilihat dari catatan TERBARU pada bidang itu
        $kategori = KategoriPotensi::tryFrom((string) $request->input('kategori'));
        if ($kategori) {
            $idMenonjol = ObservasiPotensi::where('kategori', $kategori->value)
                ->orderByDesc('tanggal')->orderByDesc('id')
                ->get(['siswa_id', 'tingkat_potensi'])
                ->unique('siswa_id')
                ->filter(fn ($o) => $o->tingkat_potensi === TingkatPotensi::Menonjol)
                ->pluck('siswa_id');
            $siswa->whereIn('id', $idMenonjol);
        }

        $daftarSiswa = $siswa->orderBy('nama_lengkap')->paginate(15)->withQueryString();
        $idSiswa = $daftarSiswa->pluck('id');

        $catatan = ObservasiPotensi::whereIn('siswa_id', $idSiswa)
            ->orderByDesc('tanggal')->orderByDesc('id')->get()->groupBy('siswa_id');

        $jumlahPrestasi = Prestasi::whereIn('siswa_id', $idSiswa)
            ->selectRaw('siswa_id, count(*) as jumlah')->groupBy('siswa_id')->pluck('jumlah', 'siswa_id');

        // Ringkasan per siswa: bidang yang menonjol (menurut catatan terbaru tiap bidang)
        $ringkasan = $daftarSiswa->mapWithKeys(function ($s) use ($catatan) {
            $milikSiswa = $catatan->get($s->id, collect());
            $terbaruTiapBidang = $milikSiswa->unique(fn ($o) => $o->kategori->value);

            return [$s->id => [
                'jumlah'   => $milikSiswa->count(),
                'menonjol' => $terbaruTiapBidang
                    ->filter(fn ($o) => $o->tingkat_potensi === TingkatPotensi::Menonjol)
                    ->map(fn ($o) => $o->kategori)->values(),
            ]];
        });

        return view('bk.observasi-potensi.daftar-siswa', [
            'daftarSiswa'    => $daftarSiswa,
            'ringkasan'      => $ringkasan,
            'jumlahPrestasi' => $jumlahPrestasi,
            'daftarKelas'    => BatasAksesKelas::kelasUntukFilter($request->user()),
            'daftarKategori' => KategoriPotensi::cases(),
        ]);
    }

    /** Profil potensi satu siswa: potensi per bidang, bakat/minat, prestasi, riwayat observasi. */
    public function profil(Request $request, Siswa $siswa)
    {
        $this->pastikanBolehLihat($request, $siswa);

        $catatan = ObservasiPotensi::with('pembuat')->where('siswa_id', $siswa->id)
            ->orderByDesc('tanggal')->orderByDesc('id')->get();

        // Potensi per bidang = catatan terbaru pada bidang itu
        $potensiPerBidang = collect(KategoriPotensi::cases())->map(fn ($k) => [
            'kategori' => $k,
            'terbaru'  => $catatan->first(fn ($o) => $o->kategori === $k),
        ]);

        // Bakat/minat: kata yang sering muncul pada catatan (dipisah koma atau titik koma)
        $bakat = $catatan->pluck('bakat_minat')->filter()
            ->flatMap(fn ($teks) => preg_split('/[,;]+/', $teks))
            ->map(fn ($t) => Str::lower(trim($t)))->filter()
            ->countBy()->sortDesc();

        return view('bk.observasi-potensi.profil-potensi', [
            'siswa'            => $siswa,
            'catatan'          => $catatan,
            'potensiPerBidang' => $potensiPerBidang,
            'bakat'            => $bakat,
            'daftarPrestasi'   => Prestasi::where('siswa_id', $siswa->id)->orderByDesc('tanggal')->get(),
            'profilBk'         => ProfilSiswaBk::where('siswa_id', $siswa->id)->first(),
            'daftarKategori'   => KategoriPotensi::cases(),
            'daftarTingkatPrestasi' => \App\Enums\TingkatPrestasi::cases(),
        ]);
    }

    public function create(Request $request, Siswa $siswa)
    {
        $siswa->loadMissing('riwayatAktif.kelas');

        return view('bk.observasi-potensi.buat-observasi', $this->dataFormulir($siswa, null, $request));
    }

    public function store(SimpanObservasiPotensiRequest $request, Siswa $siswa)
    {
        $siswa->loadMissing('riwayatAktif');

        ObservasiPotensi::create($request->validated() + [
            'siswa_id'     => $siswa->id,
            'kelas_id'     => $siswa->riwayatAktif?->kelas_id,
            'dicatat_oleh' => $request->user()->id,
        ]);

        return redirect()->route('bk.observasi-potensi.profil', $siswa)
            ->with('success', 'Catatan observasi berhasil disimpan.');
    }

    public function edit(Request $request, ObservasiPotensi $observasi)
    {
        $observasi->load('siswa.riwayatAktif.kelas');
        abort_if($observasi->siswa === null, 404);

        return view('bk.observasi-potensi.ubah-observasi', $this->dataFormulir($observasi->siswa, $observasi, $request));
    }

    public function update(SimpanObservasiPotensiRequest $request, ObservasiPotensi $observasi)
    {
        $observasi->update($request->validated());

        return redirect()->route('bk.observasi-potensi.profil', $observasi->siswa_id)
            ->with('success', 'Catatan observasi berhasil diperbarui.');
    }

    public function destroy(ObservasiPotensi $observasi)
    {
        $idSiswa = $observasi->siswa_id;
        $observasi->delete();   // soft delete: data tetap tersimpan di database

        return redirect()->route('bk.observasi-potensi.profil', $idSiswa)
            ->with('success', 'Catatan observasi dihapus.');
    }

    /** Wali kelas hanya boleh melihat siswa di kelasnya. */
    private function pastikanBolehLihat(Request $request, Siswa $siswa): void
    {
        $kelasBoleh = BatasAksesKelas::idKelasBoleh($request->user());
        if ($kelasBoleh === null) {
            return;
        }

        $siswa->loadMissing('riwayatAktif');
        abort_unless($kelasBoleh->contains($siswa->riwayatAktif?->kelas_id), 403, 'Siswa ini bukan di kelas Anda.');
    }

    private function dataFormulir(Siswa $siswa, ?ObservasiPotensi $observasi, Request $request): array
    {
        return [
            'siswa'           => $siswa,
            'observasi'       => $observasi,
            'daftarKategori'  => KategoriPotensi::cases(),
            'daftarTingkat'   => TingkatPotensi::cases(),
            'pengamatBawaan'  => $request->user()->name,
        ];
    }
}