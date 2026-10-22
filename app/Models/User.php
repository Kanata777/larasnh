<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => Role::class,
            'last_login_at' => 'datetime',
            'status' => 'string',
        ];
    }

    /**
     * Memeriksa apakah pengguna memiliki salah satu role.
     */
    public function hasRole(Role ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Memeriksa apakah akun aktif.
     */
    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * Tambah dan ubah data siswa.
     */
    public function canWrite(): bool
    {
        return $this->isActive()
            && $this->hasRole(
                Role::SuperAdmin,
                Role::KepalaTU,
                Role::StaffTU,
                Role::Operator
            );
    }

    /**
     * Hapus atau nonaktifkan siswa.
     */
    public function canDelete(): bool
    {
        return $this->isActive()
            && $this->hasRole(
                Role::SuperAdmin,
                Role::KepalaTU,
                Role::StaffTU
            );
    }

    /**
     * Kelola master pendukung.
     */
    public function canManageMaster(): bool
    {
        return $this->isActive()
            && $this->hasRole(
                Role::SuperAdmin,
                Role::KepalaTU
            );
    }

    /**
     * Kelola akun pengguna.
     */
    public function canManageUsers(): bool
    {
        return $this->isActive()
            && $this->hasRole(Role::SuperAdmin);
    }
}
