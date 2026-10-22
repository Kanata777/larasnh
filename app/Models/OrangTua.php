<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrangTua extends Model
{
    protected $table = 'orang_tua';

    protected $fillable = [
        'siswa_id',
        'nama_ayah', 'nik_ayah', 'pekerjaan_ayah', 'penghasilan_ayah',
        'nama_ibu',  'nik_ibu',  'pekerjaan_ibu',  'penghasilan_ibu',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
