<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RujukanPsikolog extends Model
{
    protected $table = 'rujukan_psikolog';

    public const STATUS = ['Diajukan', 'Diproses', 'Dijadwalkan', 'Selesai', 'Dibatalkan'];
    public const STATUS_AKTIF = ['Diajukan', 'Diproses', 'Dijadwalkan'];

    protected $fillable = [
        'siswa_id', 'kasus_konseling_id', 'psikolog_id', 'tanggal_rujukan',
        'alasan', 'ringkasan_kasus', 'catatan', 'status',
        'hasil_catatan', 'tindak_lanjut', 'tanggal_selesai', 'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_rujukan' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function psikolog()
    {
        return $this->belongsTo(Psikolog::class, 'psikolog_id');
    }

    public function kasus()
    {
        return $this->belongsTo(KasusKonseling::class, 'kasus_konseling_id');
    }
}