<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian di route:
 *   ->middleware(RoleMiddleware::untuk(Role::KepalaTU, Role::StaffTU))
 * atau (jika alias 'role' terdaftar):
 *   ->middleware('role:kepala_tu,staff_tu')
 */
class RoleMiddleware
{
    /** Membuat string middleware dari enum Role. */
    public static function untuk(Role ...$roles): string
    {
        return static::class . ':' . implode(',', array_map(fn (Role $r) => $r->value, $roles));
    }

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role->value, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}