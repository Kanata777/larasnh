<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Siswa extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'siswa';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
    ];

    public function orangTua()
    {
        return $this->hasOne(OrangTua::class, 'siswa_id');
    }

    public function alamat()
    {
        return $this->hasOne(AlamatSiswa::class, 'siswa_id');
    }

    public function riwayatKelas()
    {
        return $this->hasMany(RiwayatKelas::class, 'siswa_id');
    }
    public function kelasAktif()
    {
        return $this->hasOne(RiwayatKelas::class, 'siswa_id')
            ->whereHas('kelas', fn($q) => $q->whereHas(
                'tahunPelajaran',
                fn($t) => $t->where('status', 'aktif')
            ))   // sesuaikan kolom status tahun pelajaran
            ->latestOfMany();
    }
    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_siswa', 'siswa_id', 'kelas_id')
            ->withTimestamps();
    }
}
