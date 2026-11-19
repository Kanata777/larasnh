# Penggabungan larasbk ke larasnh

Kode BK dari larasbk sekarang ada di dalam larasnh. Satu aplikasi, satu database (`laras_nh`), satu login.
Kode ini belum dijalankan (tidak ada PHP di lingkungan penyusunan); lakukan uji di salinan dulu.

## Cara memasang
1. Backup project dan database larasnh.
2. Database:
   - Database baru: impor `database/sql/laras_gabungan.sql`.
   - Database laras_nh yang sudah berisi data: jalankan `database/sql/modul_bk.sql`.
3. `.env`: pakai satu database saja (`DB_DATABASE=laras_nh`). Variabel `DB_SEKOLAH_*` dari larasbk tidak dipakai lagi.
4. Salin isi folder ini ke project larasnh, lalu `php artisan optimize:clear` dan `php artisan route:list --name=bk`.
5. Buat akun uji untuk tiap role BK (`guru_bk`, `kesiswaan`, `wali_kelas`) dan coba satu alur: penempatan -> naik kelas -> pindah kelas.

## Yang berubah
**File baru:** `routes/bk.php`, `app/Http/Controllers/Bk/*`, `resources/views/bk/*`, `public/css/bk.css`, `app/Models/{KasusKonseling,PindahKelas,Psikolog,RujukanPsikolog,WaliSiswa}.php`, `app/Models/{Konseling,Observasi}/*`, `app/Enums/*` (enum BK), `app/Http/Requests/*`, `app/Support/BatasAksesKelas.php`, `database/sql/*`.

**File larasnh yang diubah:**
- `bootstrap/app.php`: memuat `routes/bk.php`; redirect setelah login untuk role BK.
- `app/Http/Controllers/Auth/LoginController.php`: redirect role BK.
- `app/Enums/Role.php`: tambah `GuruBk, Kesiswaan, WaliKelas, GuruMapel, OrangTua`.
- `app/Support/RoleRoute.php`: prefix untuk role baru (supaya `match` tidak error).
- `app/Models/User.php`: `no_hp`, `wali_kelas_sekolah_id` boleh diisi.
- `app/Models/Siswa.php`: tambah `riwayatAktif()` (riwayat kelas berstatus Aktif) dan `waliSiswa()`.

**Aturan penamaan modul BK:** URL `/bk/...`, nama rute `bk.*`, view `bk.*`, controller `App\Http\Controllers\Bk\*`.

**Dibuang (digantikan milik larasnh):** login/logout larasbk, model `User` larasbk, model `Sekolah\*` (read-only), middleware `EnsureRole` dan `HanyaGuruBk` (diganti `RoleMiddleware`), koneksi database `sekolah`, `routes/web-tambahan.php`.

## Penyesuaian
- `users.nama` -> `users.name`; `users.aktif` -> `users.status`; role `tu` -> `kepala_tu` / `staff_tu`.
- `$user->role` di larasnh adalah enum `Role`; kode BK memakai `->role->value` untuk membandingkan string.
- Relasi `kelasAktif` di BK diganti `riwayatAktif` supaya `kelasAktif` milik larasnh tidak berubah artinya.
- Pagination BK memakai `links('pagination::bootstrap-4')` per halaman, tidak mengubah pagination larasnh.

## Perlu Anda cek / putuskan
1. `routes/web.php` dan `resources/views` larasnh tidak ada di zip, jadi belum saya cocokkan. Layout BK memakai `route('logout')` dan login memakai `route('login')`: pastikan nama rute itu ada di larasnh.
2. Menu larasnh belum punya tautan ke `/bk` (sidebar larasnh tidak ada di zip). Tambahkan link ke `route('bk.home')`.
3. Halaman login larasnh memakai `Role::cases()`; role baru ikut muncul di sana jika daftarnya ditampilkan.
4. Dua versi konseling ada berdampingan: `kasus-konseling` (tabel `kasus_konseling`, terhubung ke rujukan psikolog) dan `laporan-konseling` (tabel `laporan_konseling`, versi lebih baru). Putuskan mana yang dipertahankan.
5. Belum ada di larasbk sendiri: controller `Laporan Layanan` (menu tampil "Segera") dan aksi `finalkan` pengajuan pindah kelas (rutenya tidak saya pasang).
6. Naik kelas baru mencakup penempatan kelas 1, 1->2 otomatis, 2->3 manual. Kelas 3->4 dan 4->lulus belum ada.
7. `super_admin` tidak otomatis boleh membuka modul BK (data konseling sensitif). Tambahkan di `routes/bk.php` bila perlu.
8. Jika `laras_bk` sudah berisi data: users harus dipetakan (`nama`->`name`, `aktif`->`status`, `tu`->`staff_tu`) dan ID user di tabel BK ikut diganti.
