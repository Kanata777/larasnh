<?php

namespace App\Models\Observasi;

use App\Enums\KategoriPotensi;
use App\Enums\TingkatPrestasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Prestasi siswa, diinput oleh kesiswaan (tabel laras_bk.prestasi). */
class Prestasi extends Model
{
    use SoftDeletes;

    protected $table = 'prestasi';

    protected $fillable = ['siswa_id', 'nama_prestasi', 'kategori', 'tingkat', 'peringkat', 'tanggal', 'dicatat_oleh'];

    protected $casts = [
        'tanggal' => 'date',
        'kategori' => KategoriPotensi::class,
        'tingkat' => TingkatPrestasi::class,
    ];

    public function pembuat() { return $this->belongsTo(User::class, 'dicatat_oleh'); }
}
