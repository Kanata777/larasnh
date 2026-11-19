<?php

namespace App\Models\Observasi;

use App\Enums\KategoriPotensi;
use App\Enums\TingkatPotensi;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Catatan observasi potensi/bakat siswa (tabel laras_bk.observasi_potensi). */
class ObservasiPotensi extends Model
{
    use SoftDeletes;

    protected $table = 'observasi_potensi';

    protected $fillable = [
        'siswa_id', 'kelas_id', 'tanggal', 'kategori', 'tingkat_potensi', 'bakat_minat',
        'temuan', 'rekomendasi', 'pengamat', 'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'kategori' => KategoriPotensi::class,
        'tingkat_potensi' => TingkatPotensi::class,
    ];

    public function siswa()   { return $this->belongsTo(Siswa::class, 'siswa_id')->withTrashed(); }
    public function kelas()   { return $this->belongsTo(Kelas::class, 'kelas_id'); }
    public function pembuat() { return $this->belongsTo(User::class, 'dicatat_oleh'); }
}
