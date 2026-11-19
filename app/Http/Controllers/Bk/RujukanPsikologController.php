<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Bk\Concerns\MencariSiswa;
use App\Models\KasusKonseling;
use App\Models\Psikolog;
use App\Models\RujukanPsikolog;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RujukanPsikologController extends Controller
{
    use MencariSiswa;

    /**
     * Satu halaman, tiga tab:
     *  1. Pilih Siswa      -> hanya siswa aktif yang punya atau pernah punya kasus konseling
     *  2. Riwayat Rujukan  -> semua rujukan yang pernah dibuat
     *  3. Daftar Psikolog  -> kelola psikolog / lembaga tujuan
     */
    public function index(Request $request)
    {
        // --- Tab 1: siswa kandidat ---
        $idBerkasus = KasusKonseling::query()->distinct()->pluck('siswa_id');

        $siswaQuery = Siswa::with('riwayatAktif.kelas')
            ->where('status_siswa', 'Aktif')
            ->whereIn('id', $idBerkasus)
            ->orderBy('nama_lengkap');
        $this->terapkanFilterSiswa($siswaQuery, $request);

        $daftarSiswa = $siswaQuery->paginate(10, ['*'], 'halaman_siswa')->withQueryString()->fragment('siswa');
        $idHalaman = $daftarSiswa->pluck('id');

        // Ringkasan kasus per siswa (jumlah + tanggal terakhir)
        $ringkasKasus = KasusKonseling::selectRaw('siswa_id, count(*) as jumlah, max(tanggal) as terakhir')
            ->whereIn('siswa_id', $idHalaman)
            ->groupBy('siswa_id')->get()->keyBy('siswa_id');

        // Rujukan yang masih berjalan, dan total rujukan per siswa
        $rujukanAktif = RujukanPsikolog::whereIn('siswa_id', $idHalaman)
            ->whereIn('status', RujukanPsikolog::STATUS_AKTIF)
            ->get()->keyBy('siswa_id');
        $totalRujukan = RujukanPsikolog::selectRaw('siswa_id, count(*) as n')
            ->whereIn('siswa_id', $idHalaman)->groupBy('siswa_id')->pluck('n', 'siswa_id');

        $daftarKelas = $this->daftarKelas();

        // --- Tab 2: riwayat rujukan ---
        $riwayat = RujukanPsikolog::with(['siswa', 'psikolog'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('tanggal_rujukan')->orderByDesc('id')
            ->paginate(10, ['*'], 'halaman_riwayat')->withQueryString()->fragment('riwayat');
        $jumlahRiwayat = RujukanPsikolog::count();

        // --- Tab 3: psikolog ---
        $daftarPsikolog = Psikolog::withCount('rujukan')->orderBy('nama')->get();

        return view('bk.rujukan-psikolog.index', [
            'daftarSiswa'    => $daftarSiswa,
            'ringkasKasus'   => $ringkasKasus,
            'rujukanAktif'   => $rujukanAktif,
            'totalRujukan'   => $totalRujukan,
            'daftarKelas'    => $daftarKelas,
            'riwayat'        => $riwayat,
            'jumlahRiwayat'  => $jumlahRiwayat,
            'daftarPsikolog' => $daftarPsikolog,
            'daftarStatus'   => RujukanPsikolog::STATUS,
        ]);
    }

    // Form rujukan untuk satu siswa
    public function create(Siswa $siswa)
    {
        $kasus = KasusKonseling::where('siswa_id', $siswa->id)
            ->orderByDesc('tanggal')->orderByDesc('id')->get();

        if ($kasus->isEmpty()) {
            return redirect()->route('bk.rujukan-psikolog.index')
                ->with('galat', 'Siswa ini belum punya kasus konseling, jadi belum bisa dirujuk.');
        }

        $aktif = RujukanPsikolog::where('siswa_id', $siswa->id)
            ->whereIn('status', RujukanPsikolog::STATUS_AKTIF)->first();
        if ($aktif) {
            return redirect()->route('bk.rujukan-psikolog.show', $aktif)
                ->with('galat', 'Siswa ini masih punya rujukan yang berjalan. Selesaikan atau batalkan dulu sebelum merujuk lagi.');
        }

        $daftarPsikolog = Psikolog::where('aktif', true)->orderBy('nama')->get();
        if ($daftarPsikolog->isEmpty()) {
            return redirect(route('bk.rujukan-psikolog.index') . '#psikolog')
                ->with('galat', 'Belum ada psikolog. Tambahkan dulu di tab Daftar Psikolog.');
        }

        $siswa->load('riwayatAktif.kelas');

        return view('bk.rujukan-psikolog.create', compact('siswa', 'kasus', 'daftarPsikolog'));
    }

    public function store(Request $request, Siswa $siswa)
    {
        $data = $request->validate([
            // kasus harus milik siswa yang sedang dirujuk
            'kasus_konseling_id' => ['required', 'integer',
                Rule::exists('kasus_konseling', 'id')->where('siswa_id', $siswa->id)],
            'psikolog_id'        => ['required', 'integer', Rule::exists('psikolog', 'id')->where('aktif', true)],
            'tanggal_rujukan'    => ['required', 'date', 'before_or_equal:today'],
            'alasan'             => ['required', 'string', 'max:2000'],
            'ringkasan_kasus'    => ['required', 'string', 'max:5000'],
            'catatan'            => ['nullable', 'string', 'max:2000'],
        ]);

        $sudahAktif = RujukanPsikolog::where('siswa_id', $siswa->id)
            ->whereIn('status', RujukanPsikolog::STATUS_AKTIF)->exists();
        if ($sudahAktif) {
            return back()->withInput()->with('galat', 'Siswa ini masih punya rujukan yang berjalan.');
        }

        $data['siswa_id']    = $siswa->id;
        $data['status']      = 'Diajukan';
        $data['dibuat_oleh'] = $request->user()->id;

        $rujukan = RujukanPsikolog::create($data);

        return redirect()->route('bk.rujukan-psikolog.show', $rujukan)->with('ok', 'Rujukan berhasil dibuat.');
    }

    public function show(RujukanPsikolog $rujukan)
    {
        $rujukan->load(['siswa.riwayatAktif.kelas', 'psikolog', 'kasus']);

        return view('bk.rujukan-psikolog.show', [
            'rujukan'      => $rujukan,
            'daftarStatus' => RujukanPsikolog::STATUS,
        ]);
    }

    // Perbarui status + hasil dari psikolog
    public function updateStatus(Request $request, RujukanPsikolog $rujukan)
    {
        $data = $request->validate([
            'status'          => ['required', Rule::in(RujukanPsikolog::STATUS)],
            'hasil_catatan'   => ['nullable', 'string', 'max:5000'],
            'tindak_lanjut'   => ['nullable', 'string', 'max:3000'],
            'tanggal_selesai' => ['nullable', 'date'],
        ]);

        if ($data['status'] === 'Selesai' && empty($data['tanggal_selesai'])) {
            $data['tanggal_selesai'] = now()->toDateString();
        }
        if (! in_array($data['status'], ['Selesai', 'Dibatalkan'], true)) {
            $data['tanggal_selesai'] = null;
        }

        $rujukan->update($data);

        return redirect()->route('bk.rujukan-psikolog.show', $rujukan)->with('ok', 'Status rujukan diperbarui.');
    }

    // Surat rujukan siap cetak
    public function surat(RujukanPsikolog $rujukan)
    {
        $rujukan->load(['siswa.riwayatAktif.kelas', 'psikolog', 'kasus']);

        return view('bk.rujukan-psikolog.surat', compact('rujukan'));
    }

    public function destroy(RujukanPsikolog $rujukan)
    {
        $rujukan->delete();

        return redirect(route('bk.rujukan-psikolog.index') . '#riwayat')->with('ok', 'Rujukan dihapus.');
    }

    // ---------- Daftar psikolog ----------

    public function psikologStore(Request $request)
    {
        $data = $request->validate([
            'nama'    => ['required', 'string', 'max:150'],
            'lembaga' => ['nullable', 'string', 'max:150'],
            'telepon' => ['nullable', 'string', 'max:40'],
            'email'   => ['nullable', 'email', 'max:150'],
        ]);

        Psikolog::create($data + ['aktif' => true]);

        return redirect(route('bk.rujukan-psikolog.index') . '#psikolog')->with('ok', 'Psikolog ditambahkan.');
    }

    public function psikologToggle(Psikolog $psikolog)
    {
        $psikolog->update(['aktif' => ! $psikolog->aktif]);

        return redirect(route('bk.rujukan-psikolog.index') . '#psikolog')
            ->with('ok', $psikolog->aktif ? 'Psikolog diaktifkan.' : 'Psikolog dinonaktifkan.');
    }

    public function psikologDestroy(Psikolog $psikolog)
    {
        if ($psikolog->rujukan()->exists()) {
            return redirect(route('bk.rujukan-psikolog.index') . '#psikolog')
                ->with('galat', 'Psikolog ini sudah dipakai di rujukan. Nonaktifkan saja, jangan dihapus.');
        }

        $psikolog->delete();

        return redirect(route('bk.rujukan-psikolog.index') . '#psikolog')->with('ok', 'Psikolog dihapus.');
    }
}
