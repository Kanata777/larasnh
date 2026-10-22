<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // Tahap 2 nanti akan mengisi ringkasan data siswa di sini.
        return view('dashboard');
    }
}
