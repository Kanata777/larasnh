<?php

namespace App\Http\Requests\Observasi;

use App\Enums\KategoriPotensi;
use App\Enums\TingkatPrestasi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi form prestasi. Hanya kesiswaan. */
class SimpanPrestasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->value === 'kesiswaan';
    }

    public function rules(): array
    {
        return [
            'nama_prestasi' => ['required', 'string', 'max:200'],
            'kategori'      => ['nullable', Rule::enum(KategoriPotensi::class)],
            'tingkat'       => ['required', Rule::enum(TingkatPrestasi::class)],
            'peringkat'     => ['nullable', 'string', 'max:50'],
            'tanggal'       => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_prestasi' => 'Nama prestasi', 'kategori' => 'Bidang', 'tingkat' => 'Tingkat',
            'peringkat' => 'Peringkat', 'tanggal' => 'Tanggal',
        ];
    }

    public function messages(): array
    {
        return [
            'required'        => ':attribute wajib diisi.',
            'enum'            => ':attribute yang dipilih tidak valid.',
            'date'            => ':attribute bukan tanggal yang benar.',
            'before_or_equal' => ':attribute tidak boleh melebihi hari ini.',
            'max'             => ':attribute terlalu panjang (maksimal :max karakter).',
        ];
    }
}
