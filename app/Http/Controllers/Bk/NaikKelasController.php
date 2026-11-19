<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Alur (dari PMB sampai lulus):
 *  1. penempatan : siswa pendaftaran ("Belum ditempatkan") -> Kelas 1A-1D          (Guru BK)
 *  2. naik kelas 1->2 dan 2->3                                                    (Staff TU)
 *  3. naik kelas 3->4, wajib difilter laki-laki / perempuan                       (Guru BK)
 *  4. naik kelas 4->5 dan 5->6, lalu kelulusan kelas 6 -> Lulus (alumni)          (Staff TU)
 *
 * Naik kelas: huruf kelas tetap, siswa DIPILIH (semua sekaligus atau satu per satu).
 */
class NaikKelasController extends Controller
{
    /** Tingkat yang bisa naik kelas (huruf kelas dipertahankan). */
    private const TINGKAT_OTOMATIS = [1, 2, 3, 4, 5];

    /** Pembagian tugas: tingkat yang dikelola tiap role. */
    private const TINGKAT_GURU_BK  = [3];
    private const TINGKAT_STAFF_TU = [1, 2, 4, 5];

    /** Tingkat yang wajib difilter laki-laki/perempuan. */
    private const TINGKAT_FILTER_GENDER = 3;

    /** Nama tabel dipakai apa adanya (dulu 'laras_nh.' + tabel, saat masih dua database). */
    private function t(string $tabel): string
    {
        return $tabel;
    }

    private function role(): string
    {
        return auth()->user()->role->value;
    }

    /** Hentikan jika role yang login bukan salah satu dari $roles. */
    private function wajib(string ...$roles): void
    {
        abort_unless(in_array($this->role(), $roles, true), 403, 'Fitur ini tidak tersedia untuk role Anda.');
    }

    private function tingkatOtomatis(Request $r): int
    {
        $boleh = match ($this->role()) {
            'guru_bk'  => self::TINGKAT_GURU_BK,
            'staff_tu' => self::TINGKAT_STAFF_TU,
            default    => abort(403, 'Fitur ini tidak tersedia untuk role Anda.'),
        };

        $t = (int) $r->input('tingkat', $boleh[0]);

        abort_unless(in_array($t, self::TINGKAT_OTOMATIS, true), 404);
        abort_unless(
            in_array($t, $boleh, true),
            403,
            $this->role() === 'guru_bk'
                ? 'Guru BK hanya mengelola kenaikan kelas 3 ke 4.'
                : 'Staff TU mengelola kenaikan kelas 1-2, 2-3, 4-5, dan 5-6.'
        );

        return $t;
    }

    /**
     * Daftar tahun pelajaran (terbaru dulu).
     * Nama kolom label/aktif dideteksi otomatis lewat SHOW COLUMNS.
     * @return \Illuminate\Support\Collection<int, object{id:int,label:string,aktif:bool}>
     */
    private function daftarTahun()
    {
        $tabel = $this->t('tahun_pelajaran');
        $kolom = collect(DB::select('SHOW COLUMNS FROM ' . $tabel))->pluck('Field')->all();
        $pilih = fn (array $kandidat) => collect($kandidat)->first(fn ($c) => in_array($c, $kolom, true));

        $kolomLabel    = $pilih(['nama', 'tahun_pelajaran', 'nama_tahun', 'tahun_ajaran', 'tahun', 'kode']);
        $kolomSemester = $pilih(['semester']);
        $kolomAktif    = $pilih(['is_aktif', 'aktif', 'status']);

        return DB::table($tabel)->orderByDesc('id')->get()->map(function ($r) use ($kolomLabel, $kolomSemester, $kolomAktif) {
            $teks = $kolomLabel ? trim((string) ($r->{$kolomLabel} ?? '')) : '';
            if ($teks !== '' && $kolomSemester && ($r->{$kolomSemester} ?? '') !== '') {
                $teks .= ' - ' . $r->{$kolomSemester};
            }
            $aktif = $kolomAktif
                && in_array(strtolower((string) $r->{$kolomAktif}), ['1', 'aktif', 'active', 'true', 'y', 'ya'], true);

            return (object) [
                'id'    => (int) $r->id,
                'label' => ($teks !== '' ? $teks : 'Tahun pelajaran #' . $r->id) . ($aktif ? ' (aktif)' : ''),
                'aktif' => $aktif,
            ];
        });
    }

    /** Tahun yang ditandai aktif; bila tidak ada penanda, tahun terbaru. */
    private function tahunDefault($daftarTahun): int
    {
        return (int) (($daftarTahun->firstWhere('aktif', true) ?? $daftarTahun->first())->id ?? 0);
    }

    private function kelasTingkat(int $tahun, int $tingkat)
    {
        return DB::table($this->t('kelas'))
            ->where('tahun_pelajaran_id', $tahun)
            ->where('tingkat', $tingkat)
            ->where('status', 'aktif')
            ->orderBy('nama_kelas')
            ->get();
    }

    /** Catat ke pindah_kelas. $baris: array of [siswa_id, asal, tujuan] */
    private function catat(string $batch, string $mode, array $baris, string $tanggal, ?string $alasan): void
    {
        $now = now();
        $rows = [];
        foreach ($baris as $b) {
            $rows[] = [
                'batch'           => $batch,
                'siswa_id'        => $b['siswa_id'],
                'kelas_asal_id'   => $b['asal'] ?? 0,
                'kelas_tujuan_id' => $b['tujuan'],
                'mode'            => $mode,
                'tanggal'         => $tanggal,
                'alasan'          => $alasan,
                'dipindah_oleh'   => auth()->id(),
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('pindah_kelas')->insert($chunk);
        }
    }

    /**
     * Naikkan siswa. $baris: [riwayat_id, siswa_id, kelas(obj)]
     *
     * riwayat_kelas hanya boleh punya SATU baris per (siswa, tahun pelajaran) (uq_siswa_tahun), jadi:
     *  - tahun tujuan sama dengan tahun baris lama : baris itu dipindah ke kelas tujuan (tetap Aktif)
     *  - tahun tujuan beda, siswa belum punya baris di tahun itu : baris lama ditutup (Naik), baris Aktif baru dibuat
     *  - tahun tujuan beda, siswa sudah punya baris di tahun itu : baris lama ditutup (Naik), baris yang ada dijadikan Aktif di kelas tujuan
     * Riwayat perpindahan tetap tercatat lengkap di tabel pindah_kelas.
     */
    private function naikkan(array $baris, string $tanggal, string $catatan): void
    {
        $now = now();

        // baris riwayat yang sudah ada pada tahun tujuan: "siswa|tahun" => id
        $tahunTujuan = array_values(array_unique(array_map(fn ($b) => $b['kelas']->tahun_pelajaran_id, $baris)));
        $ada = [];
        foreach (array_chunk(array_column($baris, 'siswa_id'), 500) as $chunk) {
            $rows = DB::table($this->t('riwayat_kelas'))
                ->whereIn('siswa_id', $chunk)
                ->whereIn('tahun_pelajaran_id', $tahunTujuan)
                ->get(['id', 'siswa_id', 'tahun_pelajaran_id']);
            foreach ($rows as $r) {
                $ada[$r->siswa_id . '|' . $r->tahun_pelajaran_id] = (int) $r->id;
            }
        }

        $tutup = [];
        $baru = [];
        foreach ($baris as $b) {
            $kelas = $b['kelas'];
            $id = $ada[$b['siswa_id'] . '|' . $kelas->tahun_pelajaran_id] ?? null;
            $isi = [
                'kelas_id'           => $kelas->id,
                'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                'status'             => 'Aktif',
                'tanggal_masuk'      => $tanggal,
                'catatan'            => $catatan,
                'tanggal_keluar'     => null,
                'updated_at'         => $now,
            ];

            if ($id === (int) $b['riwayat_id']) {
                // tahun sama: pindahkan baris yang sama ke kelas tujuan
                DB::table($this->t('riwayat_kelas'))->where('id', $id)->update($isi);
                continue;
            }

            $tutup[] = $b['riwayat_id'];

            if ($id) {
                DB::table($this->t('riwayat_kelas'))->where('id', $id)->update($isi);
            } else {
                $baru[] = ['siswa_id' => $b['siswa_id'], 'created_at' => $now] + $isi;
            }
        }

        foreach (array_chunk($tutup, 500) as $chunk) {
            DB::table($this->t('riwayat_kelas'))->whereIn('id', $chunk)->update([
                'status' => 'Naik', 'tanggal_keluar' => $tanggal, 'updated_at' => $now,
            ]);
        }
        foreach (array_chunk($baru, 200) as $chunk) {
            DB::table($this->t('riwayat_kelas'))->insert($chunk);
        }
    }

    // ------------------------------------------------------------------
    // 1. PENEMPATAN: pendaftaran -> kelas 1A-1D
    // ------------------------------------------------------------------

    private function siswaBelumDitempatkan()
    {
        return DB::table($this->t('siswa') . ' as s')
            ->leftJoin($this->t('riwayat_kelas') . ' as rk', function ($j) {
                $j->on('rk.siswa_id', '=', 's.id')->where('rk.status', 'Aktif');
            })
            ->leftJoin($this->t('kelas') . ' as k', 'k.id', '=', 'rk.kelas_id')
            ->where('s.status_siswa', 'Aktif')
            ->whereNull('s.deleted_at')
            ->where(function ($q) {
                $q->whereNull('rk.id')->orWhere('k.tingkat', 0);
            })
            ->select('s.id', 's.nama_lengkap', 's.nis', 's.jenis_kelamin',
                     'rk.id as riwayat_id', 'rk.kelas_id as kelas_asal_id')
            ->orderBy('s.nama_lengkap');
    }

    public function penempatan(Request $r)
    {
        $this->wajib('guru_bk');

        $daftarTahun = $this->daftarTahun();
        $tahun = (int) $r->query('tahun', $this->tahunDefault($daftarTahun));

        return view('bk.naik-kelas.penempatan', [
            'daftarTahun' => $daftarTahun,
            'tahun'       => $tahun,
            'kelasTujuan' => $this->kelasTingkat($tahun, 1),
            'daftarSiswa' => $this->siswaBelumDitempatkan()->get(),
        ]);
    }

    public function simpanPenempatan(Request $r)
    {
        $this->wajib('guru_bk');

        $data = $r->validate([
            'siswa_ids'       => 'required|array|min:1',
            'siswa_ids.*'     => 'integer',
            'kelas_tujuan_id' => 'required|integer',
            'tanggal'         => 'required|date|before_or_equal:today',
            'alasan'          => 'nullable|string|max:500',
        ]);

        $tujuan = DB::table($this->t('kelas'))
            ->where('id', $data['kelas_tujuan_id'])->where('tingkat', 1)->first();
        if (!$tujuan) {
            return back()->withInput()->withErrors(['kelas_tujuan_id' => 'Kelas tujuan harus kelas tingkat 1.']);
        }

        $siswa = $this->siswaBelumDitempatkan()->whereIn('s.id', $data['siswa_ids'])->get();
        if ($siswa->isEmpty()) {
            return back()->withInput()->withErrors(['siswa_ids' => 'Siswa yang dipilih sudah tidak berstatus belum ditempatkan.']);
        }

        $batch = (string) Str::uuid();
        DB::transaction(function () use ($siswa, $tujuan, $data, $batch) {
            $now = now();
            $log = [];
            foreach ($siswa as $s) {
                if ($s->riwayat_id) {
                    DB::table($this->t('riwayat_kelas'))->where('id', $s->riwayat_id)->update([
                        'kelas_id'           => $tujuan->id,
                        'tahun_pelajaran_id' => $tujuan->tahun_pelajaran_id,
                        'tanggal_masuk'      => $data['tanggal'],
                        'updated_at'         => $now,
                    ]);
                } else {
                    DB::table($this->t('riwayat_kelas'))->insert([
                        'siswa_id'           => $s->id,
                        'kelas_id'           => $tujuan->id,
                        'tahun_pelajaran_id' => $tujuan->tahun_pelajaran_id,
                        'status'             => 'Aktif',
                        'tanggal_masuk'      => $data['tanggal'],
                        'catatan'            => 'Penempatan dari pendaftaran',
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ]);
                }
                $log[] = ['siswa_id' => $s->id, 'asal' => $s->kelas_asal_id ?? 0, 'tujuan' => $tujuan->id];
            }
            $this->catat($batch, 'penempatan', $log, $data['tanggal'], $data['alasan'] ?? 'Penempatan siswa baru');
        });

        return redirect()->route('bk.naik-kelas.penempatan', ['tahun' => $tujuan->tahun_pelajaran_id])
            ->with('sukses', $siswa->count() . ' siswa ditempatkan ke ' . $tujuan->nama_kelas . '.');
    }

    // ------------------------------------------------------------------
    // 2. NAIK KELAS: tingkat 1->2, 2->3, 3->4, 4->5, 5->6 (huruf kelas sama, siswa dipilih)
    // ------------------------------------------------------------------

    private function barisOtomatis(int $tahunAsal, int $tahunTujuan, int $tingkat)
    {
        return DB::table($this->t('riwayat_kelas') . ' as rk')
            ->join($this->t('kelas') . ' as ka', 'ka.id', '=', 'rk.kelas_id')
            ->join($this->t('siswa') . ' as s', 's.id', '=', 'rk.siswa_id')
            ->leftJoin($this->t('kelas') . ' as kt', function ($j) use ($tahunTujuan, $tingkat) {
                $j->where('kt.tahun_pelajaran_id', $tahunTujuan)
                  ->where('kt.tingkat', $tingkat + 1)
                  ->where('kt.status', 'aktif')
                  ->whereRaw('RIGHT(kt.nama_kelas, 1) = RIGHT(ka.nama_kelas, 1)');
            })
            ->where('rk.status', 'Aktif')
            ->where('ka.tingkat', $tingkat)
            ->where('ka.tahun_pelajaran_id', $tahunAsal)
            ->where('s.status_siswa', 'Aktif')
            ->whereNull('s.deleted_at')
            ->select('rk.id as riwayat_id', 'rk.siswa_id',
                     's.nama_lengkap', 's.nis', 's.jenis_kelamin',
                     'ka.id as asal_id', 'ka.nama_kelas as asal_nama',
                     'kt.id as tujuan_id', 'kt.nama_kelas as tujuan_nama', 'kt.tahun_pelajaran_id as tujuan_tahun')
            ->orderBy('ka.nama_kelas')
            ->orderBy('s.nama_lengkap');
    }

    public function otomatis(Request $r)
    {
        $tingkat     = $this->tingkatOtomatis($r);
        $perluGender = $tingkat === self::TINGKAT_FILTER_GENDER;
        $daftarTahun = $this->daftarTahun();
        $tahunAsal   = (int) $r->query('tahun_asal', $this->tahunDefault($daftarTahun));
        $tahunTujuan = (int) $r->query('tahun_tujuan', $this->tahunDefault($daftarTahun));
        $kelasId     = (int) $r->query('kelas', 0);

        // Tingkat 3 wajib difilter L/P (default laki-laki)
        $jk = $perluGender ? (in_array($r->query('jk'), ['L', 'P'], true) ? $r->query('jk') : 'L') : null;

        $semua  = $this->barisOtomatis($tahunAsal, $tahunTujuan, $tingkat)->get();
        $daftarKelas = $semua->unique('asal_id')
            ->map(fn ($b) => (object) ['id' => (int) $b->asal_id, 'nama' => $b->asal_nama])
            ->values();

        $baris = $semua
            ->when($jk, fn ($c) => $c->where('jenis_kelamin', $jk))
            ->when($kelasId > 0, fn ($c) => $c->where('asal_id', $kelasId))
            ->values()
            ->each(function ($b) {
                $b->bisa = (bool) $b->tujuan_id;
            });

        return view('bk.naik-kelas.otomatis', [
            'tingkat'     => $tingkat,
            'perluGender' => $perluGender,
            'jk'          => $jk,
            'kelasId'     => $kelasId,
            'daftarTahun' => $daftarTahun,
            'tahunAsal'   => $tahunAsal,
            'tahunTujuan' => $tahunTujuan,
            'daftarKelas' => $daftarKelas,
            'baris'       => $baris,
            'jumlahL'     => $semua->where('jenis_kelamin', 'L')->count(),
            'jumlahP'     => $semua->where('jenis_kelamin', 'P')->count(),
        ]);
    }

    public function simpanOtomatis(Request $r)
    {
        $tingkat = $this->tingkatOtomatis($r);
        $data = $r->validate([
            'tingkat'      => 'required|integer',
            'tahun_asal'   => 'required|integer',
            'tahun_tujuan' => 'required|integer',
            'tanggal'      => 'required|date|before_or_equal:today',
            'jk'           => 'nullable|in:L,P',
            'siswa_ids'    => 'required|array|min:1',
            'siswa_ids.*'  => 'integer',
        ], [
            'siswa_ids.required' => 'Centang minimal satu siswa yang akan dinaikkan.',
        ]);
        $ke = $tingkat + 1;

        $jk = null;
        if ($tingkat === self::TINGKAT_FILTER_GENDER) {
            $jk = $data['jk'] ?? null;
            if (!$jk) {
                return back()->withInput()->withErrors(['jk' => 'Pilih filter laki-laki atau perempuan dulu.']);
            }
        }

        $baris = $this->barisOtomatis((int) $data['tahun_asal'], (int) $data['tahun_tujuan'], $tingkat)
            ->get()
            ->whereIn('siswa_id', $data['siswa_ids'])
            ->when($jk, fn ($c) => $c->where('jenis_kelamin', $jk))
            ->values();

        if ($baris->isEmpty()) {
            return back()->withInput()->withErrors(['siswa_ids' => "Siswa yang dipilih tidak ditemukan sebagai siswa aktif kelas {$tingkat}."]);
        }
        if ($baris->whereNull('tujuan_id')->isNotEmpty()) {
            return back()->withInput()->withErrors(['siswa_ids' => "Ada siswa yang kelas {$ke}-nya belum tersedia di tahun pelajaran tujuan. Buat kelasnya dulu di Master Data."]);
        }

        $batch = (string) Str::uuid();
        DB::transaction(function () use ($baris, $data, $batch, $tingkat, $ke) {
            $kelasCache = [];
            $naik = [];
            $log = [];
            foreach ($baris as $b) {
                $kelasCache[$b->tujuan_id] ??= (object) ['id' => $b->tujuan_id, 'tahun_pelajaran_id' => $b->tujuan_tahun];
                $naik[] = ['riwayat_id' => $b->riwayat_id, 'siswa_id' => $b->siswa_id, 'kelas' => $kelasCache[$b->tujuan_id]];
                $log[] = ['siswa_id' => $b->siswa_id, 'asal' => $b->asal_id, 'tujuan' => $b->tujuan_id];
            }
            $this->naikkan($naik, $data['tanggal'], "Naik kelas {$tingkat} ke {$ke}");
            $this->catat($batch, 'naik_auto', $log, $data['tanggal'], "Naik kelas {$tingkat} ke {$ke}");
        });

        return redirect()->route('bk.naik-kelas.otomatis', array_filter([
            'tingkat'      => $tingkat,
            'tahun_asal'   => $data['tahun_asal'],
            'tahun_tujuan' => $data['tahun_tujuan'],
            'jk'           => $jk,
        ]))->with('sukses', $baris->count() . " siswa kelas {$tingkat} naik ke kelas {$ke}. Siswa yang tidak dicentang tetap di kelasnya.");
    }

    // ------------------------------------------------------------------
    // 3. KELULUSAN (Staff TU): kelas 6 -> Lulus (alumni)
    // ------------------------------------------------------------------

    private function siswaKelas6(int $tahun)
    {
        return DB::table($this->t('riwayat_kelas') . ' as rk')
            ->join($this->t('kelas') . ' as k', 'k.id', '=', 'rk.kelas_id')
            ->join($this->t('siswa') . ' as s', 's.id', '=', 'rk.siswa_id')
            ->where('rk.status', 'Aktif')
            ->where('k.tingkat', 6)
            ->where('k.tahun_pelajaran_id', $tahun)
            ->where('s.status_siswa', 'Aktif')
            ->whereNull('s.deleted_at')
            ->select('s.id', 's.nama_lengkap', 's.nis', 's.jenis_kelamin',
                     'k.nama_kelas', 'rk.id as riwayat_id')
            ->orderBy('k.nama_kelas')
            ->orderBy('s.nama_lengkap');
    }

    public function kelulusan(Request $r)
    {
        $this->wajib('staff_tu');

        $daftarTahun = $this->daftarTahun();
        $tahun = (int) $r->query('tahun', $this->tahunDefault($daftarTahun));

        return view('bk.naik-kelas.kelulusan', [
            'daftarTahun' => $daftarTahun,
            'tahun'       => $tahun,
            'daftarSiswa' => $this->siswaKelas6($tahun)->get(),
        ]);
    }

    public function simpanKelulusan(Request $r)
    {
        $this->wajib('staff_tu');

        $data = $r->validate([
            'tahun'       => 'required|integer',
            'siswa_ids'   => 'required|array|min:1',
            'siswa_ids.*' => 'integer',
            'tanggal'     => 'required|date|before_or_equal:today',
        ]);

        $rows = $this->siswaKelas6((int) $data['tahun'])->whereIn('s.id', $data['siswa_ids'])->get();
        if ($rows->isEmpty()) {
            return back()->withInput()->withErrors(['siswa_ids' => 'Tidak ada siswa kelas 6 yang valid dipilih.']);
        }

        DB::transaction(function () use ($rows, $data) {
            $now = now();
            DB::table($this->t('riwayat_kelas'))->whereIn('id', $rows->pluck('riwayat_id'))->update([
                'status' => 'Lulus', 'tanggal_keluar' => $data['tanggal'], 'updated_at' => $now,
            ]);
            DB::table($this->t('siswa'))->whereIn('id', $rows->pluck('id'))->update([
                'status_siswa' => 'Lulus', 'updated_at' => $now,
            ]);
        });

        return redirect()->route('bk.naik-kelas.kelulusan', ['tahun' => $data['tahun']])
            ->with('sukses', $rows->count() . ' siswa diluluskan (menjadi alumni).');
    }
}