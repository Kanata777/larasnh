<?php

namespace App\Http\Controllers\SuperAdmin\MasterPendukung;

use App\Http\Controllers\Controller;
use App\Support\RoleRoute;
use Illuminate\Http\RedirectResponse;

abstract class BaseController extends Controller
{
    /**
     * Kembali ke halaman master pendukung (sesuai role yang login) dan langsung membuka tab terkait.
     * Sebelumnya dikunci ke route super-admin, sehingga role lain (mis. Staff TU) kena 403 setelah menyimpan.
     */
    protected function kembali(string $tab, string $pesan): RedirectResponse
    {
        return redirect()
            ->to(RoleRoute::to('master-pendukung.index') . '#' . $tab)
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