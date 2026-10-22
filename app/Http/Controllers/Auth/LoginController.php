<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            if ($user->status === 'aktif') {
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

        // Mendukung role berupa enum maupun string.
        if ($role instanceof \BackedEnum) {
            $role = $role->value;
        }

        $route = match ($role) {
            'super_admin' => 'super-admin.dashboard',
            'kepala_tu', 'staff_tu' => 'tu.dashboard',
            'operator' => 'operator.dashboard',
            'kepala_sekolah' => 'kepala-sekolah.dashboard',
            'guru' => 'guru.dashboard',
            default => null,
        };

        if (!$route || !\Illuminate\Support\Facades\Route::has($route)) {
            abort(500, 'Route dashboard untuk role ini belum tersedia.');
        }

        return redirect()->route($route);
    }

    /**
     * Memproses login.
     */
    public function login(Request $request)
    {
        // Validasi hanya email dan password.
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Cari pengguna berdasarkan email.
        $user = User::where('email', $validated['email'])->first();

        // Pastikan akun ditemukan dan berstatus aktif.
        if (!$user || $user->status !== 'aktif') {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah, atau akun tidak aktif.',
            ]);
        }

        // Verifikasi email dan password tanpa meminta role.
        if (
            !Auth::attempt([
                'email' => $validated['email'],
                'password' => $validated['password'],
                'status' => 'aktif',
            ])
        ) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        // Perbarui session setelah login berhasil.
        $request->session()->regenerate();

        $authenticatedUser = Auth::user();

        // Catat login terakhir jika kolom tersedia dan dapat diisi.
        if ($authenticatedUser->isFillable('last_login_at')) {
            $authenticatedUser->last_login_at = now();
            $authenticatedUser->save();
        }

        // Role ditentukan dari database oleh redirectByRole().
        return $this->redirectByRole($authenticatedUser)
            ->with('success', 'Login berhasil.');
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