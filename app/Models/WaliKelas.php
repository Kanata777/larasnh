<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaliKelas extends Model
{
    protected $table = 'wali_kelas';

    protected $fillable = [
        'sim_pegawai_id', 'nip', 'nama', 'jenis_kelamin', 'email',
        'no_hp', 'status', 'tugas_tambahan', 'disinkron_pada',
    ];

    protected $casts = ['disinkron_pada' => 'datetime'];
}