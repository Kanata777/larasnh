<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Psikolog extends Model
{
    protected $table = 'psikolog';

    protected $fillable = ['nama', 'lembaga', 'telepon', 'email', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function rujukan()
    {
        return $this->hasMany(RujukanPsikolog::class, 'psikolog_id');
    }
}