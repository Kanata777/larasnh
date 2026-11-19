<?php

namespace App\Models\Konseling;

use App\Enums\KomponenLayanan;
use App\Enums\Semester;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Laporan layanan konseling per siswa (tabel laras_bk.laporan_konseling). */
class LaporanKonseling extends Model
{
    use SoftDeletes;

    protected $table = 'laporan_konseling';

    protected $fillable = [
        'siswa_id', 'kelas_id', 'tahun_pelajaran_id', 'semester', 'komponen_layanan',
        'bidang_id', 'tanggal', 'topik', 'tujuan', 'durasi_menit', 'materi',
        'tindak_lanjut', 'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'semester' => Semester::class,
        'komponen_layanan' => KomponenLayanan::class,
        'durasi_menit' => 'integer',
    ];

    // Siswa & kelas ada di database sekolah (laras_nh)
    public function siswa()   { return $this->belongsTo(Siswa::class, 'siswa_id')->withTrashed(); }
    public function kelas()   { return $this->belongsTo(Kelas::class, 'kelas_id'); }
    public function bidang()  { return $this->belongsTo(BidangLayanan::class, 'bidang_id'); }
    public function pembuat() { return $this->belongsTo(User::class, 'dibuat_oleh'); }
}
