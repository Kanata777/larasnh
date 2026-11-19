<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Bk\Concerns\MencariSiswa;
use App\Models\PindahKelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PindahKelasController extends Controller
{
    use MencariSiswa;

    // Riwayat perpindahan (Guru BK, wali kelas, Kepala TU, Staff TU)
    public function index(Request $request)
    {
        $riwayat = PindahKelas::with(['siswa', 'kelasAsal', 'kelasTujuan', 'pelaksana'])
            ->when($request->input('q'), function ($q, $kata) {
                $q->whereHas('siswa', fn ($s) => $s->where('nama_lengkap', 'like', "%{$kata}%")
                    ->orWhere('nis', 'like', "%{$kata}%"));
            })
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->paginate(15)->withQueryString();

        return view('bk.pindah-kelas.index', compact('riwayat'));
    }

    // Form pindah kelas (Guru BK)
    public function create(Request $request)
    {
        $daftarKelas = $this->kelasTersedia();

        // ?siswa=ID dari halaman lain: otomatis pilih kelas asalnya
        $siswaDipilih = $request->integer('siswa') ?: null;
        $kelasId = $request->integer('kelas') ?: null;

        if (! $kelasId && $siswaDipilih) {
            $kelasId = Siswa::with('riwayatAktif')->find($siswaDipilih)?->riwayatAktif?->kelas_id;
        }

        $kelasAsal = $kelasId ? $daftarKelas->firstWhere('id', $kelasId) : null;
        $daftarSiswa = collect();

        if ($kelasAsal) {
            $daftarSiswa = $this->siswaDiKelas((int) $kelasAsal->id)->get();
        }

        return view('bk.pindah-kelas.create', [
            'daftarKelas'  => $daftarKelas,
            'kelasAsal'    => $kelasAsal,
            'daftarSiswa'  => $daftarSiswa,
            'siswaDipilih' => $siswaDipilih,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mode'            => ['required', Rule::in(['terpilih', 'semua'])],
            'kelas_asal_id'   => ['required', 'integer'],
            'kelas_tujuan_id' => ['required', 'integer', 'different:kelas_asal_id'],
            'siswa_ids'       => ['required_if:mode,terpilih', 'array'],
            'siswa_ids.*'     => ['integer'],
            'tanggal'         => ['required', 'date', 'before_or_equal:today'],
            'alasan'          => ['nullable', 'string', 'max:500'],
        ], [
            'kelas_tujuan_id.different' => 'Kelas tujuan harus berbeda dengan kelas asal.',
            'siswa_ids.required_if'     => 'Centang minimal satu siswa yang akan dipindahkan.',
        ]);

        // kedua kelas harus kelas sungguhan (bukan opsi semu)
        $idKelasValid = $this->kelasTersedia()->pluck('id')->map(fn ($i) => (int) $i);
        abort_unless(
            $idKelasValid->contains((int) $data['kelas_asal_id'])
                && $idKelasValid->contains((int) $data['kelas_tujuan_id']),
            422,
            'Kelas tidak valid.'
        );

        // hanya siswa aktif yang memang masih di kelas asal
        $query = $this->siswaDiKelas((int) $data['kelas_asal_id']);
        if ($data['mode'] === 'terpilih') {
            $query->whereIn('id', $data['siswa_ids']);
        }
        $siswaList = $query->get();

        if ($siswaList->isEmpty()) {
            return back()->withInput()->with('galat', 'Tidak ada siswa yang bisa dipindahkan.');
        }

        $tujuan = (int) $data['kelas_tujuan_id'];

        // Kelas khusus L/P (tabel aturan_kelas_gender): jenis kelamin siswa harus sesuai
        $namaTujuan = DB::table('kelas')->where('id', $tujuan)->value('nama_kelas');
        $wajibGender = DB::table('aturan_kelas_gender')->where('nama_kelas', $namaTujuan)->value('jenis_kelamin');
        if ($wajibGender) {
            $tidakSesuai = $siswaList->filter(fn ($s) => $s->jenis_kelamin !== $wajibGender);
            if ($tidakSesuai->isNotEmpty()) {
                return back()->withInput()->with(
                    'galat',
                    "Kelas {$namaTujuan} khusus " . ($wajibGender === 'L' ? 'laki-laki' : 'perempuan')
                    . '. Tidak sesuai: ' . $tidakSesuai->pluck('nama_lengkap')->take(5)->implode(', ')
                    . ($tidakSesuai->count() > 5 ? ', dst.' : '.')
                );
            }
        }

        $batch = (string) Str::uuid();

        DB::transaction(function () use ($siswaList, $data, $batch, $tujuan, $request) {
            foreach ($siswaList as $siswa) {
                $siswa->riwayatAktif->update(['kelas_id' => $tujuan]);

                PindahKelas::create([
                    'batch'           => $batch,
                    'siswa_id'        => $siswa->id,
                    'kelas_asal_id'   => $data['kelas_asal_id'],
                    'kelas_tujuan_id' => $tujuan,
                    'mode'            => $data['mode'] === 'semua' ? 'massal' : 'satuan',
                    'tanggal'         => $data['tanggal'],
                    'alasan'          => $data['alasan'] ?? null,
                    'dipindah_oleh'   => $request->user()->id,
                ]);
            }
        });

        return redirect()->route('bk.pindah-kelas.index')
            ->with('ok', $siswaList->count() . ' siswa berhasil dipindahkan.');
    }

    private function siswaDiKelas(int $kelasId)
    {
        return Siswa::with('riwayatAktif.kelas')
            ->where('status_siswa', 'Aktif')
            ->whereHas('riwayatAktif', fn ($q) => $q->where('kelas_id', $kelasId))
            ->orderBy('nama_lengkap');
    }

    // Hanya kelas sungguhan: buang opsi semu seperti "Belum ditempatkan"
    private function kelasTersedia()
    {
        return $this->daftarKelas()
            ->filter(fn ($k) => is_numeric($k->id)
                && (int) $k->id > 0
                && ! str_contains(mb_strtolower((string) $k->nama_kelas), 'belum'))
            ->values();
    }
}