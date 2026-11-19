<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->aktif) {
                return $this->redirectByRole($user);
            }

            Auth::logout();
        }

        $roles = Role::cases();

        return view('auth.login', compact('roles'));
    }

    /**
     * Mengarahkan pengguna sesuai role.
     */
    private function redirectByRole(User $user)
    {
        $role = $user->role;

        if ($role instanceof \BackedEnum) {
            $role = $role->value;
        }

        $route = match ($role) {
            'super_admin' => 'super-admin.dashboard',
            'kepala_tu', 'staff_tu' => 'tu.dashboard',
            'operator' => 'operator.dashboard',
            'kepala_sekolah' => 'kepala-sekolah.dashboard',
            'guru' => 'guru.dashboard',
            'guru_bk' => 'bk.dashboard',
            'kesiswaan', 'wali_kelas' => 'bk.siswa.index',
            default => null,
        };

        if (!$route || !\Illuminate\Support\Facades\Route::has($route)) {
            abort(500, 'Route dashboard untuk role ini belum tersedia.');
        }

        return redirect()->route($route);
    }

    /**
     * Memproses login.
     * 1) Cek akun di laras_bk.users (kolom `aktif`).
     * 2) Jika tidak ada / password tidak cocok, cek laras_nh.users (kolom `status`),
     *    lalu salin akunnya ke laras_bk.users. laras_nh hanya dibaca.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = trim($validated['email']);
        $password = $validated['password'];

        $user = $this->cariDiBk($email, $password) ?? $this->cariDiSekolah($email, $password);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah, atau akun tidak aktif.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->isFillable('last_login_at')) {
            $user->last_login_at = now();
            $user->save();
        }

        return $this->redirectByRole($user)
            ->with('success', 'Login berhasil.');
    }

    /**
     * Akun milik website BK (laras_bk.users).
     */
    private function cariDiBk(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();

        if ($user && $user->aktif && Hash::check($password, $user->password)) {
            return $user;
        }

        return null;
    }

    /**
     * Akun milik website sekolah (laras_nh.users). Hanya dibaca,
     * lalu disalin / diperbarui di laras_bk.users agar sesi login tetap satu tabel.
     */
    private function cariDiSekolah(string $email, string $password): ?User
    {
        try {
            $row = DB::connection('sekolah')->table('users')->where('email', $email)->first();
        } catch (\Throwable $e) {
            Log::warning('Login: database sekolah tidak terbaca: ' . $e->getMessage());
            return null;
        }

        if (!$row || ($row->status ?? null) !== 'aktif') {
            return null;
        }

        if (!Hash::check($password, $row->password)) {
            return null;
        }

        $role = $row->role ?? null;

        DB::table('users')->updateOrInsert(
            ['email' => $email],
            [
                'nama' => $row->nama ?? $row->name ?? $email,
                'password' => $row->password,
                'role' => $role,
                'aktif' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return User::where('email', $email)->first();
    }

    /**
     * Logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Kamu berhasil logout.');
    }
}