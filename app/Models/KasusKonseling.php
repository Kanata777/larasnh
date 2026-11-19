<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasusKonseling extends Model
{
    protected $table = 'kasus_konseling';

    protected $fillable = [
        'siswa_id', 'kelas_id', 'nama_kelas', 'semester', 'tanggal',
        'komponen_layanan', 'bidang_layanan', 'topik_layanan', 'tujuan_layanan',
        'durasi_menit', 'materi', 'tindak_lanjut', 'dibuat_oleh',
    ];

    protected $casts = ['tanggal' => 'date'];

    // Isi dropdown form laporan. Ubah di sini jika sekolah memakai istilah lain.
    public const KOMPONEN = ['Layanan Dasar', 'Layanan Responsif', 'Perencanaan Individual', 'Dukungan Sistem'];
    public const BIDANG   = ['Pribadi', 'Sosial', 'Belajar', 'Karir'];
    public const SEMESTER = ['Ganjil', 'Genap'];

    // Siswa ada di database 'sekolah' (koneksi berbeda); relasi tetap bisa dipakai.
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function rujukan()
    {
        return $this->hasMany(RujukanPsikolog::class, 'kasus_konseling_id');
    }
}
