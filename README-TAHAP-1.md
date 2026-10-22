# Tahap 1 — Akses Sistem (Laravel 11 / 12)

## Instalasi
1. composer create-project laravel/laravel sim-siswa
2. cd sim-siswa
3. Ekstrak isi ZIP ini ke folder proyek (timpa file jika diminta).
4. Atur database di .env (contoh MySQL):
   DB_CONNECTION=mysql
   DB_DATABASE=sim_siswa
   DB_USERNAME=root
   DB_PASSWORD=
   APP_TIMEZONE=Asia/Jakarta   (opsional; atau ubah di config/app.php)
5. php artisan migrate --seed
6. php artisan serve   → buka http://127.0.0.1:8000

## Akun demo (password: password123)
admin@sekolah.test, kepalatu@sekolah.test, staff@sekolah.test,
operator@sekolah.test, kepsek@sekolah.test, guru@sekolah.test

## Tes
php artisan test --filter=LoginTest
