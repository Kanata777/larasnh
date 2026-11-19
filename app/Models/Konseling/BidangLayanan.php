<?php

namespace App\Models\Konseling;

use Illuminate\Database\Eloquent\Model;

/** Bidang layanan: pribadi, sosial, belajar, karir. */
class BidangLayanan extends Model
{
    protected $table = 'bidang_layanan';
    public $timestamps = false;
}
