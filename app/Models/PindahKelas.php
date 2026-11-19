<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PindahKelas extends Model
{
    protected $table = 'pindah_kelas';

    protected $fillable = [
        'batch', 'siswa_id', 'kelas_asal_id', 'kelas_tujuan_id',
        'mode', 'tanggal', 'alasan', 'dipindah_oleh',
    ];

    protected $casts = ['tanggal' => 'date'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    // sesuaikan nama class Kelas dengan model Anda
    public function kelasAsal()
    {
        return $this->belongsTo(Kelas::class, 'kelas_asal_id');
    }

    public function kelasTujuan()
    {
        return $this->belongsTo(Kelas::class, 'kelas_tujuan_id');
    }

    public function pelaksana()
    {
        return $this->belongsTo(User::class, 'dipindah_oleh');
    }
}