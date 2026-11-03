<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Support\RoleAccess;
use App\Support\RoleRoute;
use App\Support\RoleView;
use App\Support\SiswaData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Dipakai bersama oleh semua role. Route menentukan SIAPA yang boleh masuk;
 * controller ini menentukan APA yang terlihat/tersimpan untuk role tersebut:
 *  - Guru     : hanya siswa di kelas yang diampu
 *  - Operator : data sensitif disembunyikan, status siswa tidak bisa diubah
 */
class SiswaController extends Controller
{
    /** Nama kelas khusus untuk siswa yang belum punya kelas pada suatu tahun pelajaran. */
    private const KELAS_BELUM = 'Belum ditempatkan';

    /* ===================== DAFTAR SISWA ===================== */

    public function index(Request $request)
    {
        $guru = RoleAccess::hanyaKelasSendiri();

        $tahunOptions = DB::table('tahun_pelajaran')
            ->orderByDesc('nama')
            ->get(['id', 'nama', 'status']);

        // Tahun terpilih; jika kosong atau tidak valid, pakai tahun aktif
        $tahunId = (int) $request->query('tahun_id') ?: $this->tahunAktifId();
        if ($tahunId && !$tahunOptions->contains('id', $tahunId)) {
            $tahunId = $this->tahunAktifId();
        }

        // Hanya kelas milik tahun pelajaran yang sedang dipilih.
        // Guru: hanya kelas yang ia ampu sebagai wali kelas.
        $kelasOptions = DB::table('kelas')
            ->where('tahun_pelajaran_id', $tahunId ?? 0)
            ->when($guru, fn($q) => $q->whereIn('id', RoleAccess::kelasDiampu()))
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'tahun_pelajaran_id']);

        // Filter kelas: id kelas, "tanpa" (siswa yang belum punya kelas), atau kosong (semua).
        // Id kelas yang bukan milik tahun terpilih diabaikan.
        $kelasFilter = $request->query('kelas_id');
        if ($kelasFilter === 'tanpa' && !$guru) {
            // tetap 'tanpa'
        } else {
            $kelasFilter = ($kelasFilter && $kelasOptions->contains('id', (int) $kelasFilter))
                ? (int) $kelasFilter
                : null;
        }

        $query = DB::table('siswa')
            ->leftJoin('riwayat_kelas as rk', function ($join) use ($tahunId) {
                $join->on('rk.siswa_id', '=', 'siswa.id')
                    ->where('rk.tahun_pelajaran_id', '=', $tahunId ?? 0);
            })
            ->leftJoin('kelas', 'kelas.id', '=', 'rk.kelas_id')
            ->select(
                'siswa.id',
                'siswa.nis',
                'siswa.nisn',
                'siswa.nama_lengkap',
                'siswa.jenis_kelamin',
                'siswa.asal_sekolah',
                'siswa.status_siswa',
                'kelas.nama_kelas'
            );
        $query->whereNull('siswa.deleted_at');

        // Siswa yang belum pernah ditempatkan di kelas sungguhan (tahun apa pun).
        // Kelas khusus "Belum ditempatkan" tidak dihitung sebagai kelas.
        $belumPunyaKelas = fn($q) => $q->select(DB::raw(1))
            ->from('riwayat_kelas as x')
            ->join('kelas as kx', 'kx.id', '=', 'x.kelas_id')
            ->whereColumn('x.siswa_id', 'siswa.id')
            ->where('kx.nama_kelas', '!=', self::KELAS_BELUM);

        if ($guru) {
            // Guru: hanya siswa di kelas yang diampunya (tanpa "semua" / "tanpa kelas")
            $query->whereIn('rk.kelas_id', $kelasFilter ? [$kelasFilter] : $kelasOptions->pluck('id')->all());
        } elseif ($kelasFilter === 'tanpa') {
            // Hanya siswa yang belum punya kelas, supaya mudah dicari dan ditempatkan
            $query->whereNotExists($belumPunyaKelas);
        } elseif ($kelasFilter) {
            $query->where('rk.kelas_id', $kelasFilter);
        } else {
            // Semua kelas: siswa yang punya kelas di tahun terpilih, ditambah siswa
            // yang belum punya kelas sama sekali. Siswa yang hanya punya kelas di
            // tahun lain (mis. 2026 saat melihat 2025) tidak ikut tampil.
            $query->where(function ($w) use ($belumPunyaKelas) {
                $w->whereNotNull('rk.kelas_id')
                    ->orWhereNotExists($belumPunyaKelas);
            });
        }

        if ($cari = trim((string) $request->query('q'))) {
            $query->where(function ($w) use ($cari) {
                $w->where('siswa.nama_lengkap', 'like', "%{$cari}%")
                    ->orWhere('siswa.nis', 'like', "%{$cari}%")
                    ->orWhere('siswa.nisn', 'like', "%{$cari}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('siswa.status_siswa', $status);
        }

        $siswa = $query->orderBy('siswa.nama_lengkap')->paginate(15)->withQueryString();

        return RoleView::make('siswa.index', [
            'siswa' => $siswa,
            'tahunOptions' => $tahunOptions,
            'tahunTerpilih' => $tahunId,
            'kelasOptions' => $kelasOptions,
            'kelasTerpilih' => $kelasFilter,
            'statusOptions' => SiswaData::STATUS,
            'ubahStatus' => RoleAccess::ubahStatusSiswa(),
        ]);
    }

    /* ===================== TAMBAH ===================== */

    public function create()
    {
        return RoleView::make('siswa.form', $this->formData(null, [
            'status_siswa' => 'Aktif',
            'tanggal_masuk' => now()->toDateString(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        // Hanya yang berhak mengubah status yang boleh menentukan status saat menambah siswa
        if (!RoleAccess::ubahStatusSiswa()) {
            $request->merge(['status_siswa' => 'Aktif']);
        }

        $data = RoleAccess::buang($this->validated($request));

        $foto = $request->file('foto')?->store('foto-siswa', 'public');
        $id = SiswaData::save($data, null, $foto);

        return redirect(RoleRoute::to('siswa.show', $id))
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    /* ===================== DETAIL ===================== */

    public function show(int $id)
    {
        $data = SiswaData::load($id);
        abort_unless($data, 404);
        $this->pastikanBolehDilihat($id);

        $riwayat = DB::table('riwayat_kelas as rk')
            ->join('kelas', 'kelas.id', '=', 'rk.kelas_id')
            ->join('tahun_pelajaran as tp', 'tp.id', '=', 'rk.tahun_pelajaran_id')
            ->where('rk.siswa_id', $id)
            ->orderByDesc('tp.nama')
            ->get(['kelas.nama_kelas', 'tp.nama as tahun_pelajaran', 'rk.status', 'rk.catatan']);

        return RoleView::make('siswa.show', [
            'd' => RoleAccess::saring($data),
            'riwayat' => $riwayat,
            'fotoUrl' => !empty($data['foto']) ? Storage::disk('public')->url($data['foto']) : null,
            'lihatSensitif' => RoleAccess::lihatDataSensitif(),
            'ubahStatus' => RoleAccess::ubahStatusSiswa(),
        ]);
    }

    /* ===================== EDIT ===================== */

    public function edit(int $id)
    {
        $data = SiswaData::load($id);
        abort_unless($data, 404);
        $this->pastikanBolehDilihat($id);

        $form = $this->formData($id, RoleAccess::saring($data));

        // Tahun pelajaran penempatan terakhir (tetap ada walau kelasnya "Belum ditempatkan")
        $form['data']['tahun_pelajaran_id'] = DB::table('riwayat_kelas as rk')
            ->join('tahun_pelajaran as tp', 'tp.id', '=', 'rk.tahun_pelajaran_id')
            ->where('rk.siswa_id', $id)
            ->orderByDesc('tp.nama')
            ->value('rk.tahun_pelajaran_id');

        return RoleView::make('siswa.form', $form);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $lama = DB::table('siswa')->where('id', $id)->first();
        abort_unless($lama, 404);
        $this->pastikanBolehDilihat($id);

        // Role tanpa hak ubah status: status lama dipertahankan
        if (!RoleAccess::ubahStatusSiswa()) {
            $request->merge(['status_siswa' => $lama->status_siswa]);
        }

        $data = RoleAccess::buang($this->validated($request, $id));

        $foto = null;
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto')->store('foto-siswa', 'public');
            if ($lama->foto) {
                Storage::disk('public')->delete($lama->foto);
            }
        }

        SiswaData::save($data, $id, $foto);

        // "Belum ditempatkan": simpan sebagai kelas khusus pada tahun yang dipilih,
        // sehingga tahun pelajaran tetap tercatat.
        if (!$request->filled('kelas_id')) {
            $tahunBaru = (int) $request->input('tahun_pelajaran_id') ?: $this->tahunAktifId();
            $tahunAsal = (int) $request->input('tahun_asal');

            if ($tahunBaru) {
                $statusSiswa = DB::table('siswa')->where('id', $id)->value('status_siswa');
                // Kolom riwayat_kelas.status hanya menerima Aktif/Naik/Pindah/Lulus
                $statusRiwayat = in_array($statusSiswa, ['Aktif', 'Naik', 'Pindah', 'Lulus'], true)
                    ? $statusSiswa
                    : 'Aktif';

                DB::transaction(function () use ($id, $tahunBaru, $tahunAsal, $statusRiwayat, $request) {
                    DB::table('riwayat_kelas')
                        ->where('siswa_id', $id)
                        ->whereIn('tahun_pelajaran_id', array_filter(array_unique([$tahunAsal, $tahunBaru])))
                        ->delete();

                    DB::table('riwayat_kelas')->insert([
                        'siswa_id' => $id,
                        'kelas_id' => $this->kelasBelumDitempatkan($tahunBaru),
                        'tahun_pelajaran_id' => $tahunBaru,
                        'status' => $statusRiwayat,
                        'catatan' => $request->input('catatan'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
            }
        }

        return redirect(RoleRoute::to('siswa.show', $id))
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    /* ===================== NONAKTIFKAN / AKTIFKAN ===================== */

    public function status(Request $request, int $id): RedirectResponse
    {
        abort_unless(RoleAccess::ubahStatusSiswa(), 403);
        abort_unless(DB::table('siswa')->where('id', $id)->exists(), 404);

        $data = $request->validate([
            'status_siswa' => ['required', Rule::in(SiswaData::STATUS)],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $tahunId = $this->tahunAktifId();

        DB::transaction(function () use ($id, $data, $tahunId) {
            DB::table('siswa')->where('id', $id)->update([
                'status_siswa' => $data['status_siswa'],
                'updated_at' => now(),
            ]);

            if ($tahunId) {
                $riwayat = ['status' => $data['status_siswa'], 'updated_at' => now()];
                if (!empty($data['catatan'])) {
                    $riwayat['catatan'] = $data['catatan'];
                }

                DB::table('riwayat_kelas')
                    ->where('siswa_id', $id)
                    ->where('tahun_pelajaran_id', $tahunId)
                    ->update($riwayat);
            }
        });

        $pesan = $data['status_siswa'] === 'Aktif'
            ? 'Siswa berhasil diaktifkan kembali.'
            : 'Status siswa diubah menjadi ' . $data['status_siswa'] . '.';

        return back()->with('success', $pesan);
    }

    public function destroy(int $id): RedirectResponse
    {
        $siswa = DB::table('siswa')->where('id', $id)->whereNull('deleted_at')->first();
        abort_unless($siswa, 404);

        DB::table('siswa')->where('id', $id)->update(['deleted_at' => now()]);

        return redirect(RoleRoute::to('siswa.index'))
            ->with('success', 'Siswa "' . $siswa->nama_lengkap . '" dihapus dari daftar. Bisa dipulihkan di Siswa Terhapus.');
    }

    public function terhapus(Request $request)
    {
        $cari = trim((string) $request->query('q'));

        $siswa = DB::table('siswa')
            ->whereNotNull('deleted_at')
            ->when($cari !== '', fn($q) => $q->where(function ($w) use ($cari) {
                $w->where('nama_lengkap', 'like', "%{$cari}%")
                    ->orWhere('nis', 'like', "%{$cari}%")
                    ->orWhere('nisn', 'like', "%{$cari}%");
            }))
            ->orderByDesc('deleted_at')
            ->paginate(15, ['id', 'nis', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'status_siswa', 'deleted_at'])
            ->withQueryString();

        return RoleView::make('siswa.terhapus', ['siswa' => $siswa]);
    }

    public function restore(int $id): RedirectResponse
    {
        $siswa = DB::table('siswa')->where('id', $id)->whereNotNull('deleted_at')->first();
        abort_unless($siswa, 404);

        DB::table('siswa')->where('id', $id)->update(['deleted_at' => null]);

        return redirect(RoleRoute::to('siswa.terhapus'))
            ->with('success', 'Siswa "' . $siswa->nama_lengkap . '" berhasil dipulihkan.');
    }

    /* ===================== HELPER ===================== */

    /** Ambil (atau buat) kelas "Belum ditempatkan" untuk satu tahun pelajaran. */
    private function kelasBelumDitempatkan(int $tahunId): int
    {
        $id = DB::table('kelas')
            ->where('tahun_pelajaran_id', $tahunId)
            ->where('nama_kelas', self::KELAS_BELUM)
            ->value('id');

        if ($id) {
            return (int) $id;
        }

        // Sesuaikan kolom lain tabel kelas Anda jika ada yang wajib (cek DESCRIBE kelas)
        return (int) DB::table('kelas')->insertGetId([
            'tahun_pelajaran_id' => $tahunId,
            'nama_kelas' => self::KELAS_BELUM,
            'tingkat' => 0,
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Guru hanya boleh membuka siswa dari kelas yang ia ampu. */
    private function pastikanBolehDilihat(int $siswaId): void
    {
        if (!RoleAccess::hanyaKelasSendiri()) {
            return;
        }

        $boleh = DB::table('riwayat_kelas as rk')
            ->join('kelas', 'kelas.id', '=', 'rk.kelas_id')
            ->where('rk.siswa_id', $siswaId)
            ->whereIn('kelas.id', RoleAccess::kelasDiampu())
            ->exists();

        abort_unless($boleh, 403, 'Siswa ini bukan dari kelas yang Anda ampu.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $request->merge(SiswaData::normalize($request->all()));

        return $request->validate(
            SiswaData::rules($ignoreId) + ['foto' => ['nullable', 'image', 'max:2048']],
            SiswaData::messages(),
            SiswaData::attributes()
        );
    }

    private function formData(?int $id, array $data): array
    {
        $tahunOptions = DB::table('tahun_pelajaran')
            ->orderByDesc('nama')
            ->pluck('nama', 'id')
            ->all();

        // Kelas khusus "Belum ditempatkan" tidak ditampilkan; sudah diwakili opsi kosong di form
        $kelasList = DB::table('kelas')
            ->where('status', 'aktif')
            ->where('nama_kelas', '!=', self::KELAS_BELUM)
            ->orderBy('tingkat')->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'tahun_pelajaran_id']);

        $ref = ['pekerjaan' => [], 'penghasilan' => [], 'asal_sekolah' => []];
        if (Schema::hasTable('referensi')) {
            foreach (DB::table('referensi')->orderBy('nama')->get() as $r) {
                $ref[$r->jenis][] = $r->nama;
            }
        }

        return [
            'siswaId' => $id,
            'data' => $data,
            'tahunOptions' => $tahunOptions,
            'kelasList' => $kelasList,
            'tahunAktifId' => $this->tahunAktifId(),
            'ref' => $ref,
            'fotoUrl' => !empty($data['foto']) ? Storage::disk('public')->url($data['foto']) : null,
            'lihatSensitif' => RoleAccess::lihatDataSensitif(),
            'ubahStatus' => RoleAccess::ubahStatusSiswa(),
        ];
    }

    private function tahunAktifId(): ?int
    {
        return DB::table('tahun_pelajaran')->where('status', 'aktif')->orderByDesc('id')->value('id');
    }
}