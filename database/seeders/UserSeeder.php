<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $akun = [
            ['Super Admin', 'admin@sekolah.test', Role::SuperAdmin],
            ['Kepala TU', 'kepalatu@sekolah.test', Role::KepalaTU],
            ['Staff TU', 'staff@sekolah.test', Role::StaffTU],
            ['Operator', 'operator@sekolah.test', Role::Operator],
            ['Kepala Sekolah', 'kepsek@sekolah.test', Role::KepalaSekolah],
            ['Guru', 'guru@sekolah.test', Role::Guru],
        ];

        foreach ($akun as [$nama, $email, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                ['name' => $nama, 'password' => 'password123', 'role' => $role, 'is_active' => true]
            );
        }
    }
}
