<?php

namespace App\Http\Controllers\SuperAdmin\MasterPendukung;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

abstract class BaseController extends Controller
{
    /** Kembali ke halaman master pendukung dan langsung membuka tab terkait. */
    protected function kembali(string $tab, string $pesan): RedirectResponse
    {
        return redirect()
            ->to(route('super-admin.master-pendukung.index') . '#' . $tab)
            ->with('success', $pesan);
    }

    protected function pesan(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute maksimal :max karakter.',
            'between' => ':attribute harus antara :min dan :max.',
            'date' => 'Format :attribute tidak valid.',
            'in' => ':attribute tidak valid.',
            'exists' => ':attribute tidak ditemukan.',
            'unique' => ':attribute sudah ada.',
        ];
    }
}