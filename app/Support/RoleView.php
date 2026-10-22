<?php

namespace App\Support;

/**
 * Memilih view sesuai role, dengan cadangan ke view bersama.
 *
 *   RoleView::make('siswa.index', $data)
 *     → pakai  {prefix-role}.siswa.index  jika ada (mis. kepala-tu.siswa.index)
 *     → jika tidak ada, pakai  super-admin.siswa.index  (view bersama)
 */
class RoleView
{
    public const VIEW_BERSAMA = 'super-admin';

    public static function make(string $view, array $data = [])
    {
        $khusus = RoleRoute::prefix() . '.' . $view;

        return view(view()->exists($khusus) ? $khusus : self::VIEW_BERSAMA . '.' . $view, $data);
    }
}