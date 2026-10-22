<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->redirectUsersTo(function ($request) {
            $user = $request->user();

            // Jika pengguna belum login
            if (!$user) {
                return route('login');
            }

            // Arahkan pengguna berdasarkan role
            $role = $user->role;

            if ($role instanceof \BackedEnum) {
                $role = $role->value;
            }

            return match ($role) {
                'super_admin' => route('super-admin.dashboard'),
                'kepala_tu', 'staff_tu' => route('tu.dashboard'),
                'operator' => route('operator.dashboard'),
                'kepala_sekolah' => route('kepala-sekolah.dashboard'),
                'guru' => route('guru.dashboard'),
                default => route('login'),
            };
        });

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();