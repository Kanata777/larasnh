<?php

namespace App\Http\Requests\Konseling;

use App\Enums\KomponenLayanan;
use App\Enums\Semester;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi form buat/ubah laporan konseling. Hanya Guru BK. */
class SimpanLaporanKonselingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->value === 'guru_bk';
    }

    public function rules(): array
    {
        return [
            'komponen_layanan' => ['required', Rule::enum(KomponenLayanan::class)],
            'bidang_id'        => ['required', 'integer', 'exists:bidang_layanan,id'],
            'kelas_id'         => ['required', 'integer', 'exists:kelas,id'],
            'semester'         => ['required', Rule::enum(Semester::class)],
            'tanggal'          => ['required', 'date', 'before_or_equal:today'],
            'topik'            => ['required', 'string', 'max:200'],
            'tujuan'           => ['required', 'string', 'max:3000'],
            'durasi_menit'     => ['required', 'integer', 'between:1,480'],
            'materi'           => ['required', 'string', 'max:5000'],
            'tindak_lanjut'    => ['required', 'string', 'max:3000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'komponen_layanan' => 'Komponen layanan',
            'bidang_id'        => 'Bidang layanan',
            'kelas_id'         => 'Kelas',
            'semester'         => 'Semester',
            'tanggal'          => 'Hari/tanggal',
            'topik'            => 'Topik layanan',
            'tujuan'           => 'Tujuan layanan',
            'durasi_menit'     => 'Durasi pertemuan',
            'materi'           => 'Materi',
            'tindak_lanjut'    => 'Tindak lanjut',
        ];
    }

    public function messages(): array
    {
        return [
            'required'              => ':attribute wajib diisi.',
            'enum'                  => ':attribute yang dipilih tidak valid.',
            'exists'                => ':attribute yang dipilih tidak ditemukan.',
            'integer'               => ':attribute harus berupa angka.',
            'date'                  => ':attribute bukan tanggal yang benar.',
            'before_or_equal'       => ':attribute tidak boleh melebihi hari ini.',
            'max'                   => ':attribute terlalu panjang (maksimal :max karakter).',
            'durasi_menit.between'  => 'Durasi pertemuan harus antara :min dan :max menit.',
        ];
    }
}
