<?php

namespace App\Http\Requests\Observasi;

use App\Enums\KategoriPotensi;
use App\Enums\TingkatPotensi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi form catatan observasi potensi. Hanya Guru BK. */
class SimpanObservasiPotensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->value === 'guru_bk';
    }

    public function rules(): array
    {
        return [
            'tanggal'         => ['required', 'date', 'before_or_equal:today'],
            'kategori'        => ['required', Rule::enum(KategoriPotensi::class)],
            'tingkat_potensi' => ['required', Rule::enum(TingkatPotensi::class)],
            'bakat_minat'     => ['nullable', 'string', 'max:150'],
            'pengamat'        => ['required', 'string', 'max:150'],
            'temuan'          => ['required', 'string', 'max:3000'],
            'rekomendasi'     => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'tanggal' => 'Tanggal', 'kategori' => 'Kategori potensi', 'tingkat_potensi' => 'Tingkat potensi',
            'bakat_minat' => 'Bakat/minat', 'pengamat' => 'Pengamat', 'temuan' => 'Hasil pengamatan',
            'rekomendasi' => 'Rekomendasi',
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
