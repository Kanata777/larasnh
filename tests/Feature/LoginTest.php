<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private function buatUser(array $attr = []): User
    {
        return User::create($attr + [
            'name' => 'Tester', 'email' => 't@sekolah.test', 'password' => 'rahasia123',
            'role' => Role::Operator, 'is_active' => true,
        ]);
    }

    public function test_login_berhasil(): void
    {
        $this->buatUser();
        $this->post('/login', ['email' => 't@sekolah.test', 'password' => 'rahasia123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_password_salah_ditolak(): void
    {
        $this->buatUser();
        $this->post('/login', ['email' => 't@sekolah.test', 'password' => 'salah'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_akun_nonaktif_ditolak(): void
    {
        $this->buatUser(['is_active' => false]);
        $this->post('/login', ['email' => 't@sekolah.test', 'password' => 'rahasia123'])
            ->assertSessionHasErrors('email');
    }

    public function test_dashboard_butuh_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
