-- Modul Bimbingan Konseling (dari laras_bk) untuk DITERAPKAN KE DATABASE laras_nh YANG SUDAH ADA.
-- Backup database dulu. File ini hanya menambah tabel BK dan mengubah tabel users (role + kolom baru).
-- Untuk database baru/kosong pakai laras_gabungan.sql.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================
-- PENGGABUNGAN: modul Bimbingan Konseling (asal: laras_bk)
-- Tabel users/sessions/password_reset_tokens memakai milik laras_nh.
-- ============================================================

-- 1. Tabel modul BK

CREATE TABLE `akses_kelas_guru` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `kelas_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `akses_orang_tua` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `hubungan` enum('ayah','ibu','wali') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `aturan_kelas_gender` (
  `id` bigint UNSIGNED NOT NULL,
  `tingkat` tinyint UNSIGNED NOT NULL DEFAULT '3',
  `nama_kelas` varchar(20) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `audit_log` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `aksi` enum('lihat','tambah','ubah','hapus','cetak','unduh','login') NOT NULL,
  `tabel` varchar(60) DEFAULT NULL,
  `record_id` bigint UNSIGNED DEFAULT NULL,
  `detail` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `bidang_layanan` (
  `id` tinyint UNSIGNED NOT NULL,
  `nama` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `hasil_tes` (
  `id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `jenis_tes` varchar(100) NOT NULL,
  `tanggal_tes` date NOT NULL,
  `skor` varchar(100) DEFAULT NULL,
  `interpretasi` text,
  `dicatat_oleh` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `kasus` (
  `id` bigint UNSIGNED NOT NULL,
  `kode_kasus` varchar(30) NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `kelas_id` bigint UNSIGNED DEFAULT NULL,
  `tahun_pelajaran_id` bigint UNSIGNED DEFAULT NULL,
  `bidang_id` tinyint UNSIGNED NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text,
  `prioritas` enum('rendah','sedang','tinggi','darurat') NOT NULL DEFAULT 'sedang',
  `sumber` enum('siswa','wali_kelas','orang_tua','guru_mapel','observasi','lainnya') NOT NULL DEFAULT 'lainnya',
  `status` enum('baru','proses','berlanjut','tuntas') NOT NULL DEFAULT 'baru',
  `tanggal_masuk` date NOT NULL,
  `tanggal_tuntas` date DEFAULT NULL,
  `dibuat_oleh` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `kasus_konseling` (
  `id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `kelas_id` bigint UNSIGNED DEFAULT NULL,
  `nama_kelas` varchar(50) DEFAULT NULL,
  `semester` varchar(10) NOT NULL,
  `tanggal` date NOT NULL,
  `komponen_layanan` varchar(60) NOT NULL,
  `bidang_layanan` varchar(60) NOT NULL,
  `topik_layanan` varchar(200) NOT NULL,
  `tujuan_layanan` text NOT NULL,
  `durasi_menit` smallint UNSIGNED NOT NULL,
  `materi` text NOT NULL,
  `tindak_lanjut` text NOT NULL,
  `dibuat_oleh` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `lampiran` (
  `id` bigint UNSIGNED NOT NULL,
  `lampiran_type` varchar(50) NOT NULL,
  `lampiran_id` bigint UNSIGNED NOT NULL,
  `jenis` enum('foto','video','audio','dokumen') NOT NULL,
  `nama_asli` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `mime` varchar(100) NOT NULL,
  `ukuran_byte` bigint UNSIGNED NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `diunggah_oleh` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `laporan_konseling` (
  `id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL COMMENT 'laras_nh.siswa.id',
  `kelas_id` bigint UNSIGNED NOT NULL COMMENT 'laras_nh.kelas.id (kelas yang dipilih guru BK)',
  `tahun_pelajaran_id` bigint UNSIGNED DEFAULT NULL COMMENT 'laras_nh.tahun_pelajaran.id (diambil dari kelas)',
  `semester` enum('ganjil','genap') NOT NULL,
  `komponen_layanan` varchar(30) NOT NULL COMMENT 'layanan_dasar | layanan_responsif | perencanaan_individual | dukungan_sistem',
  `bidang_id` tinyint UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `topik` varchar(200) NOT NULL,
  `tujuan` text NOT NULL,
  `durasi_menit` smallint UNSIGNED NOT NULL,
  `materi` text NOT NULL,
  `tindak_lanjut` text NOT NULL,
  `dibuat_oleh` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `laporan_layanan` (
  `id` bigint UNSIGNED NOT NULL,
  `kasus_id` bigint UNSIGNED NOT NULL,
  `ringkasan` text NOT NULL,
  `intervensi` text,
  `hasil_akhir` text,
  `rekomendasi` text,
  `dibuat_oleh` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `observasi` (
  `id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `guru_id` bigint UNSIGNED DEFAULT NULL,
  `tanggal` date NOT NULL,
  `aspek` varchar(100) DEFAULT NULL,
  `catatan` text NOT NULL,
  `dicatat_oleh` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `observasi_potensi` (
  `id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL COMMENT 'laras_nh.siswa.id',
  `kelas_id` bigint UNSIGNED DEFAULT NULL COMMENT 'laras_nh.kelas.id (kelas aktif saat dicatat)',
  `tanggal` date NOT NULL,
  `kategori` varchar(30) NOT NULL COMMENT 'akademik | bahasa_komunikasi | sains_teknologi | seni | olahraga | kepemimpinan | sosial_emosional',
  `tingkat_potensi` varchar(20) NOT NULL COMMENT 'menonjol | berkembang | perlu_stimulasi',
  `bakat_minat` varchar(150) DEFAULT NULL COMMENT 'contoh: menggambar, bermain piano',
  `temuan` text NOT NULL COMMENT 'perilaku/hasil pengamatan sebagai bukti',
  `rekomendasi` text COMMENT 'saran pengembangan',
  `pengamat` varchar(150) NOT NULL COMMENT 'siapa yang mengamati (nama guru)',
  `dicatat_oleh` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `pengajuan_pindah_kelas` (
  `id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `kelas_asal_id` bigint UNSIGNED NOT NULL,
  `kelas_tujuan_id` bigint UNSIGNED NOT NULL,
  `alasan` text,
  `status` enum('diajukan_bk','final_wali_kelas','selesai','ditolak') NOT NULL DEFAULT 'diajukan_bk',
  `diajukan_oleh` bigint UNSIGNED NOT NULL,
  `difinalkan_oleh` bigint UNSIGNED DEFAULT NULL,
  `difinalkan_at` datetime DEFAULT NULL,
  `catatan_penolakan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `penugasan_observasi` (
  `id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `guru_id` bigint UNSIGNED NOT NULL,
  `tahun_pelajaran_id` bigint UNSIGNED DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `pindah_kelas` (
  `id` bigint UNSIGNED NOT NULL,
  `batch` char(36) NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `kelas_asal_id` bigint UNSIGNED NOT NULL,
  `kelas_tujuan_id` bigint UNSIGNED NOT NULL,
  `mode` varchar(10) NOT NULL DEFAULT 'terpilih',
  `tanggal` date NOT NULL,
  `alasan` varchar(500) DEFAULT NULL,
  `dipindah_oleh` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `prestasi` (
  `id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `nama_prestasi` varchar(200) NOT NULL,
  `kategori` varchar(30) DEFAULT NULL,
  `tingkat` enum('sekolah','kecamatan','kabupaten','provinsi','nasional','internasional') NOT NULL,
  `peringkat` varchar(50) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `dicatat_oleh` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `profil_siswa_bk` (
  `id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `cita_cita` varchar(150) DEFAULT NULL,
  `hobi` varchar(150) DEFAULT NULL,
  `tinggal_dengan` varchar(100) DEFAULT NULL,
  `catatan_kesehatan` text,
  `catatan_bk` text,
  `diubah_oleh` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `psikolog` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(150) NOT NULL,
  `lembaga` varchar(150) DEFAULT NULL,
  `telepon` varchar(40) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `rujukan_hasil_tes` (
  `rujukan_id` bigint UNSIGNED NOT NULL,
  `hasil_tes_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `rujukan_psikolog` (
  `id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `kasus_konseling_id` bigint UNSIGNED NOT NULL,
  `psikolog_id` bigint UNSIGNED NOT NULL,
  `tanggal_rujukan` date NOT NULL,
  `alasan` text NOT NULL,
  `ringkasan_kasus` text NOT NULL,
  `catatan` text,
  `status` varchar(30) NOT NULL DEFAULT 'Diajukan',
  `hasil_catatan` text,
  `tindak_lanjut` text,
  `tanggal_selesai` date DEFAULT NULL,
  `dibuat_oleh` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `sesi_konseling` (
  `id` bigint UNSIGNED NOT NULL,
  `kasus_id` bigint UNSIGNED NOT NULL,
  `jenis_layanan` enum('individu','wali_kelas','orang_tua','kelompok') NOT NULL,
  `tanggal` datetime NOT NULL,
  `pihak_hadir` varchar(255) DEFAULT NULL,
  `uraian` text NOT NULL,
  `hasil` text,
  `tindak_lanjut` text,
  `status_setelah` enum('berlanjut','tuntas') NOT NULL DEFAULT 'berlanjut',
  `jadwal_berikutnya` datetime DEFAULT NULL,
  `dicatat_oleh` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 2. Index & primary key modul BK

ALTER TABLE `akses_kelas_guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_akses_kelas_guru` (`user_id`,`kelas_id`);

ALTER TABLE `akses_orang_tua`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_akses_ortu` (`user_id`,`siswa_id`);

ALTER TABLE `aturan_kelas_gender`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_nama_kelas` (`nama_kelas`);

ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`,`created_at`),
  ADD KEY `idx_audit_rec` (`tabel`,`record_id`);

ALTER TABLE `bidang_layanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama` (`nama`);

ALTER TABLE `hasil_tes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dicatat_oleh` (`dicatat_oleh`),
  ADD KEY `idx_tes_siswa` (`siswa_id`);

ALTER TABLE `kasus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_kasus` (`kode_kasus`),
  ADD KEY `bidang_id` (`bidang_id`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`),
  ADD KEY `idx_kasus_siswa` (`siswa_id`),
  ADD KEY `idx_kasus_status_prioritas` (`status`,`prioritas`);

ALTER TABLE `kasus_konseling`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kasus_konseling_siswa_id_index` (`siswa_id`);

ALTER TABLE `lampiran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `diunggah_oleh` (`diunggah_oleh`),
  ADD KEY `idx_lampiran_induk` (`lampiran_type`,`lampiran_id`);

ALTER TABLE `laporan_konseling`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bidang_id` (`bidang_id`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`),
  ADD KEY `idx_laporan_siswa_tanggal` (`siswa_id`,`tanggal`),
  ADD KEY `idx_laporan_kelas_semester` (`kelas_id`,`semester`);

ALTER TABLE `laporan_layanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kasus_id` (`kasus_id`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`);

ALTER TABLE `observasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guru_id` (`guru_id`),
  ADD KEY `dicatat_oleh` (`dicatat_oleh`),
  ADD KEY `idx_observasi_siswa` (`siswa_id`);

ALTER TABLE `observasi_potensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dicatat_oleh` (`dicatat_oleh`),
  ADD KEY `idx_observasi_siswa_tanggal` (`siswa_id`,`tanggal`),
  ADD KEY `idx_observasi_kategori` (`kategori`,`tingkat_potensi`);

ALTER TABLE `pengajuan_pindah_kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `diajukan_oleh` (`diajukan_oleh`),
  ADD KEY `difinalkan_oleh` (`difinalkan_oleh`),
  ADD KEY `idx_pindah_status` (`status`),
  ADD KEY `idx_pindah_siswa` (`siswa_id`);

ALTER TABLE `penugasan_observasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_obs` (`siswa_id`,`guru_id`,`tahun_pelajaran_id`),
  ADD KEY `guru_id` (`guru_id`);

ALTER TABLE `pindah_kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pindah_kelas_siswa_id_index` (`siswa_id`),
  ADD KEY `pindah_kelas_batch_index` (`batch`),
  ADD KEY `pindah_kelas_tanggal_index` (`tanggal`);

ALTER TABLE `prestasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dicatat_oleh` (`dicatat_oleh`),
  ADD KEY `idx_prestasi_siswa` (`siswa_id`);

ALTER TABLE `profil_siswa_bk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `siswa_id` (`siswa_id`),
  ADD KEY `diubah_oleh` (`diubah_oleh`);

ALTER TABLE `psikolog`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `rujukan_hasil_tes`
  ADD PRIMARY KEY (`rujukan_id`,`hasil_tes_id`),
  ADD KEY `hasil_tes_id` (`hasil_tes_id`);

ALTER TABLE `rujukan_psikolog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rujukan_psikolog_siswa_id_index` (`siswa_id`),
  ADD KEY `rujukan_psikolog_status_index` (`status`),
  ADD KEY `rujukan_psikolog_kasus_konseling_id_foreign` (`kasus_konseling_id`),
  ADD KEY `rujukan_psikolog_psikolog_id_foreign` (`psikolog_id`);

ALTER TABLE `sesi_konseling`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dicatat_oleh` (`dicatat_oleh`),
  ADD KEY `idx_sesi_kasus_tgl` (`kasus_id`,`tanggal`);

-- 3. Perubahan tabel users (gabungan role)

-- Role gabungan nh + bk. Pemetaan: bk.tu -> staff_tu ; bk.nama -> users.name (kolom nh dipakai)
ALTER TABLE `users`
  MODIFY `role` enum('super_admin','kepala_tu','staff_tu','operator','kepala_sekolah','guru',
                     'guru_bk','kesiswaan','wali_kelas','guru_mapel','orang_tua') NOT NULL,
  ADD COLUMN `email_verified_at` timestamp NULL DEFAULT NULL AFTER `email`,
  ADD COLUMN `no_hp` varchar(20) DEFAULT NULL AFTER `role`,
  ADD COLUMN `wali_kelas_sekolah_id` bigint UNSIGNED DEFAULT NULL AFTER `no_hp`,
  ADD COLUMN `remember_token` varchar(100) DEFAULT NULL AFTER `last_login_at`,
  ADD KEY `idx_users_role` (`role`),
  ADD CONSTRAINT `fk_users_wali_kelas` FOREIGN KEY (`wali_kelas_sekolah_id`) REFERENCES `wali_kelas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 4. AUTO_INCREMENT modul BK

ALTER TABLE `akses_kelas_guru`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `akses_orang_tua`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `aturan_kelas_gender`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `audit_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `bidang_layanan`
  MODIFY `id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `hasil_tes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `kasus`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `kasus_konseling`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `lampiran`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `laporan_konseling`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `laporan_layanan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `observasi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `observasi_potensi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `pengajuan_pindah_kelas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `penugasan_observasi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `pindah_kelas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `prestasi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `profil_siswa_bk`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `psikolog`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `rujukan_psikolog`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `sesi_konseling`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

-- 5. Foreign key bawaan modul BK (users, bidang_layanan, kasus, dst.)

ALTER TABLE `akses_kelas_guru`
  ADD CONSTRAINT `akses_kelas_guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `akses_orang_tua`
  ADD CONSTRAINT `akses_orang_tua_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `hasil_tes`
  ADD CONSTRAINT `hasil_tes_ibfk_1` FOREIGN KEY (`dicatat_oleh`) REFERENCES `users` (`id`);

ALTER TABLE `kasus`
  ADD CONSTRAINT `kasus_ibfk_1` FOREIGN KEY (`bidang_id`) REFERENCES `bidang_layanan` (`id`),
  ADD CONSTRAINT `kasus_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`);

ALTER TABLE `lampiran`
  ADD CONSTRAINT `lampiran_ibfk_1` FOREIGN KEY (`diunggah_oleh`) REFERENCES `users` (`id`);

ALTER TABLE `laporan_konseling`
  ADD CONSTRAINT `laporan_konseling_ibfk_1` FOREIGN KEY (`bidang_id`) REFERENCES `bidang_layanan` (`id`),
  ADD CONSTRAINT `laporan_konseling_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`);

ALTER TABLE `laporan_layanan`
  ADD CONSTRAINT `laporan_layanan_ibfk_1` FOREIGN KEY (`kasus_id`) REFERENCES `kasus` (`id`),
  ADD CONSTRAINT `laporan_layanan_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`);

ALTER TABLE `observasi`
  ADD CONSTRAINT `observasi_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `observasi_ibfk_2` FOREIGN KEY (`dicatat_oleh`) REFERENCES `users` (`id`);

ALTER TABLE `observasi_potensi`
  ADD CONSTRAINT `observasi_potensi_ibfk_1` FOREIGN KEY (`dicatat_oleh`) REFERENCES `users` (`id`);

ALTER TABLE `pengajuan_pindah_kelas`
  ADD CONSTRAINT `pengajuan_pindah_kelas_ibfk_1` FOREIGN KEY (`diajukan_oleh`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pengajuan_pindah_kelas_ibfk_2` FOREIGN KEY (`difinalkan_oleh`) REFERENCES `users` (`id`);

ALTER TABLE `penugasan_observasi`
  ADD CONSTRAINT `penugasan_observasi_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `users` (`id`);

ALTER TABLE `prestasi`
  ADD CONSTRAINT `prestasi_ibfk_1` FOREIGN KEY (`dicatat_oleh`) REFERENCES `users` (`id`);

ALTER TABLE `profil_siswa_bk`
  ADD CONSTRAINT `profil_siswa_bk_ibfk_1` FOREIGN KEY (`diubah_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `rujukan_hasil_tes`
  ADD CONSTRAINT `rujukan_hasil_tes_ibfk_1` FOREIGN KEY (`rujukan_id`) REFERENCES `rujukan_psikolog` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rujukan_hasil_tes_ibfk_2` FOREIGN KEY (`hasil_tes_id`) REFERENCES `hasil_tes` (`id`) ON DELETE CASCADE;

ALTER TABLE `rujukan_psikolog`
  ADD CONSTRAINT `rujukan_psikolog_kasus_konseling_id_foreign` FOREIGN KEY (`kasus_konseling_id`) REFERENCES `kasus_konseling` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rujukan_psikolog_psikolog_id_foreign` FOREIGN KEY (`psikolog_id`) REFERENCES `psikolog` (`id`) ON DELETE RESTRICT;

ALTER TABLE `sesi_konseling`
  ADD CONSTRAINT `sesi_konseling_ibfk_1` FOREIGN KEY (`kasus_id`) REFERENCES `kasus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sesi_konseling_ibfk_2` FOREIGN KEY (`dicatat_oleh`) REFERENCES `users` (`id`);

-- 6. Foreign key BARU ke siswa / kelas / tahun_pelajaran
-- (di laras_bk hanya berupa komentar; sekarang satu database sehingga bisa dijaga FK)

ALTER TABLE `akses_orang_tua`
  ADD CONSTRAINT `fk_akses_orang_tua_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `hasil_tes`
  ADD CONSTRAINT `fk_hasil_tes_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `kasus`
  ADD CONSTRAINT `fk_kasus_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kasus_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kasus_tahun` FOREIGN KEY (`tahun_pelajaran_id`) REFERENCES `tahun_pelajaran` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `kasus_konseling`
  ADD CONSTRAINT `fk_kasus_konseling_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kasus_konseling_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `laporan_konseling`
  ADD CONSTRAINT `fk_laporan_konseling_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_laporan_konseling_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_laporan_konseling_tahun` FOREIGN KEY (`tahun_pelajaran_id`) REFERENCES `tahun_pelajaran` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `observasi`
  ADD CONSTRAINT `fk_observasi_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `observasi_potensi`
  ADD CONSTRAINT `fk_observasi_potensi_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_observasi_potensi_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `pengajuan_pindah_kelas`
  ADD CONSTRAINT `fk_pengajuan_pindah_kelas_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pengajuan_kelas_asal` FOREIGN KEY (`kelas_asal_id`) REFERENCES `kelas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pengajuan_kelas_tujuan` FOREIGN KEY (`kelas_tujuan_id`) REFERENCES `kelas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `penugasan_observasi`
  ADD CONSTRAINT `fk_penugasan_observasi_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_penugasan_observasi_tahun` FOREIGN KEY (`tahun_pelajaran_id`) REFERENCES `tahun_pelajaran` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `pindah_kelas`
  ADD CONSTRAINT `fk_pindah_kelas_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pindah_kelas_asal` FOREIGN KEY (`kelas_asal_id`) REFERENCES `kelas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pindah_kelas_tujuan` FOREIGN KEY (`kelas_tujuan_id`) REFERENCES `kelas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `prestasi`
  ADD CONSTRAINT `fk_prestasi_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `profil_siswa_bk`
  ADD CONSTRAINT `fk_profil_siswa_bk_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `rujukan_psikolog`
  ADD CONSTRAINT `fk_rujukan_psikolog_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `akses_kelas_guru`
  ADD CONSTRAINT `fk_akses_kelas_guru_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

