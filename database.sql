-- 1. Hapus database lama jika ada (RESET TOTAL)
DROP DATABASE IF EXISTS `db_lab_alat`;

-- 2. Buat Database Baru
CREATE DATABASE `db_lab_alat` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_lab_alat`;

-- ========================================================
-- A. TABEL USERS
-- ========================================================
CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Password Hash',
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `foto_user` varchar(255) NOT NULL DEFAULT 'default_user.png',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data User Default (Password: admin123)
INSERT INTO `tbl_users` (`user_id`, `username`, `password`, `full_name`, `email`, `role`, `foto_user`) VALUES
(1, 'admin', '$2y$10$fA1y6.fwShzzqUfJ2wz/l.yFEiPRAi.xGsp2P5HtdymXlVafvR.J.', 'Admin Lab', 'admin@lab.com', 'admin', 'default_user.png'),
(2, 'staff', '$2y$10$fA1y6.fwShzzqUfJ2wz/l.yFEiPRAi.xGsp2P5HtdymXlVafvR.J.', 'Staff Peneliti', 'staff@lab.com', 'staff', 'default_user.png');


-- ========================================================
-- B. TABEL ALAT (INVENTORY)
-- ========================================================
CREATE TABLE `tbl_alat` (
  `alat_id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_alat` varchar(20) NOT NULL,
  `nama_alat` varchar(150) NOT NULL,
  `tipe_alat` varchar(150) NOT NULL DEFAULT 'Umum' COMMENT 'Untuk pengelompokan stok di katalog',
  `deskripsi` text DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `foto_alat` varchar(255) DEFAULT 'default_alat.png',
  `status_ketersediaan` enum('Tersedia','Dipinjam','Perawatan') NOT NULL DEFAULT 'Tersedia',
  `status_sterilisasi` enum('Steril','Non-Steril','Tidak Perlu') NOT NULL DEFAULT 'Tidak Perlu',
  PRIMARY KEY (`alat_id`),
  UNIQUE KEY `kode_alat` (`kode_alat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Alat Dummy (25 Unit, 5 Tipe)
INSERT INTO `tbl_alat` (`kode_alat`, `nama_alat`, `tipe_alat`, `lokasi`, `status_ketersediaan`, `status_sterilisasi`) VALUES
-- Autoclave (5 Unit)
('AUC-01', 'Autoclave Meja #1', 'Autoclave Meja', 'Ruang Sterilisasi', 'Tersedia', 'Tidak Perlu'),
('AUC-02', 'Autoclave Meja #2', 'Autoclave Meja', 'Ruang Sterilisasi', 'Tersedia', 'Tidak Perlu'),
('AUC-03', 'Autoclave Meja #3', 'Autoclave Meja', 'Ruang Sterilisasi', 'Tersedia', 'Tidak Perlu'),
('AUC-04', 'Autoclave Meja #4', 'Autoclave Meja', 'Ruang Sterilisasi', 'Tersedia', 'Tidak Perlu'),
('AUC-05', 'Autoclave Meja #5', 'Autoclave Meja', 'Ruang Sterilisasi', 'Tersedia', 'Tidak Perlu'),
-- Mikroskop (5 Unit)
('MIC-01', 'Mikroskop Binokuler #1', 'Mikroskop Binokuler', 'Lemari A1', 'Tersedia', 'Tidak Perlu'),
('MIC-02', 'Mikroskop Binokuler #2', 'Mikroskop Binokuler', 'Lemari A1', 'Tersedia', 'Tidak Perlu'),
('MIC-03', 'Mikroskop Binokuler #3', 'Mikroskop Binokuler', 'Lemari A2', 'Tersedia', 'Tidak Perlu'),
('MIC-04', 'Mikroskop Binokuler #4', 'Mikroskop Binokuler', 'Lemari A2', 'Tersedia', 'Tidak Perlu'),
('MIC-05', 'Mikroskop Binokuler #5', 'Mikroskop Binokuler', 'Lemari A2', 'Tersedia', 'Tidak Perlu'),
-- Centrifuge (5 Unit)
('CEN-01', 'Centrifuge 16-Tube #1', 'Centrifuge 16-Tube', 'Meja B1', 'Tersedia', 'Steril'),
('CEN-02', 'Centrifuge 16-Tube #2', 'Centrifuge 16-Tube', 'Meja B1', 'Tersedia', 'Steril'),
('CEN-03', 'Centrifuge 16-Tube #3', 'Centrifuge 16-Tube', 'Meja B2', 'Tersedia', 'Steril'),
('CEN-04', 'Centrifuge 16-Tube #4', 'Centrifuge 16-Tube', 'Meja B2', 'Tersedia', 'Steril'),
('CEN-05', 'Centrifuge 16-Tube #5', 'Centrifuge 16-Tube', 'Meja B2', 'Tersedia', 'Steril'),
-- Timbangan (5 Unit)
('TIM-01', 'Timbangan Analitik #1', 'Timbangan Analitik', 'Ruang Timbang', 'Tersedia', 'Tidak Perlu'),
('TIM-02', 'Timbangan Analitik #2', 'Timbangan Analitik', 'Ruang Timbang', 'Tersedia', 'Tidak Perlu'),
('TIM-03', 'Timbangan Analitik #3', 'Timbangan Analitik', 'Ruang Timbang', 'Tersedia', 'Tidak Perlu'),
('TIM-04', 'Timbangan Analitik #4', 'Timbangan Analitik', 'Ruang Timbang', 'Perawatan', 'Tidak Perlu'),
('TIM-05', 'Timbangan Analitik #5', 'Timbangan Analitik', 'Ruang Timbang', 'Tersedia', 'Tidak Perlu'),
-- Pipet (5 Unit)
('PIP-01', 'Pipet Otomatis 100-1000µL #1', 'Pipet Otomatis (100-1000µL)', 'Rak C1', 'Tersedia', 'Steril'),
('PIP-02', 'Pipet Otomatis 100-1000µL #2', 'Pipet Otomatis (100-1000µL)', 'Rak C1', 'Tersedia', 'Steril'),
('PIP-03', 'Pipet Otomatis 100-1000µL #3', 'Pipet Otomatis (100-1000µL)', 'Rak C1', 'Tersedia', 'Non-Steril'),
('PIP-04', 'Pipet Otomatis 100-1000µL #4', 'Pipet Otomatis (100-1000µL)', 'Rak C1', 'Tersedia', 'Steril'),
('PIP-05', 'Pipet Otomatis 100-1000µL #5', 'Pipet Otomatis (100-1000µL)', 'Rak C1', 'Tersedia', 'Steril');


-- ========================================================
-- C. TABEL BOOKING (PEMINJAMAN)
-- ========================================================
CREATE TABLE `tbl_booking` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tipe_alat_diminta` varchar(150) NOT NULL,
  `alat_terpilih_id` int(11) DEFAULT NULL COMMENT 'Diisi Admin saat Approve',
  `waktu_pengajuan` timestamp NOT NULL DEFAULT current_timestamp(),
  `waktu_pinjam` datetime NOT NULL,
  `waktu_kembali` datetime NOT NULL,
  `status_booking` enum('Pending','Disetujui','Ditolak','Selesai','Dibatalkan') NOT NULL DEFAULT 'Pending',
  `catatan_admin` text DEFAULT NULL,
  PRIMARY KEY (`booking_id`),
  KEY `fk_user_id` (`user_id`),
  KEY `fk_alat_terpilih` (`alat_terpilih_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Dummy Booking (Agar grafik dashboard tidak kosong saat pertama kali)
INSERT INTO `tbl_booking` (`user_id`, `tipe_alat_diminta`, `alat_terpilih_id`, `waktu_pengajuan`, `waktu_pinjam`, `waktu_kembali`, `status_booking`) VALUES
(2, 'Mikroskop Binokuler', NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 2 DAY), 'Pending'),
(2, 'Centrifuge 16-Tube', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 4 DAY), 'Pending');


-- ========================================================
-- D. TABEL NOTIFIKASI
-- ========================================================
CREATE TABLE `tbl_notifikasi` (
  `notif_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT '#',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notif_id`),
  KEY `idx_user_notif` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================================
-- E. VIEW (TAMPILAN DATA UNTUK LAPORAN/DASHBOARD)
-- ========================================================

-- View Laporan Booking Lengkap
CREATE OR REPLACE VIEW `view_booking_lengkap` AS 
SELECT
    `b`.`booking_id`,
    `b`.`status_booking`,
    `b`.`waktu_pengajuan`,
    `b`.`waktu_pinjam`,
    `b`.`waktu_kembali`,
    `b`.`tipe_alat_diminta`,
    `b`.`alat_terpilih_id`,
    `a`.`nama_alat`,
    `a`.`kode_alat`,
    `u`.`full_name` AS `peminjam`,
    `u`.`role` AS `role`,
    `u`.`user_id`
FROM
    `tbl_booking` `b`
    JOIN `tbl_users` `u` ON `b`.`user_id` = `u`.`user_id`
    LEFT JOIN `tbl_alat` `a` ON `b`.`alat_terpilih_id` = `a`.`alat_id`
ORDER BY
    `b`.`waktu_pinjam` DESC;

-- View Statistik Dashboard
CREATE OR REPLACE VIEW `view_dashboard_stats` AS 
SELECT
    (SELECT COUNT(*) FROM `tbl_alat`) AS `total_alat`,
    (SELECT COUNT(*) FROM `tbl_alat` WHERE `status_ketersediaan` = 'Dipinjam') AS `total_dipinjam`,
    (SELECT COUNT(*) FROM `tbl_alat` WHERE `status_ketersediaan` = 'Tersedia') AS `total_tersedia`,
    (SELECT COUNT(*) FROM `tbl_alat` WHERE `status_ketersediaan` = 'Perawatan') AS `total_perawatan`,
    (SELECT COUNT(*) FROM `tbl_booking` WHERE `status_booking` = 'Pending') AS `total_pending`;


-- ========================================================
-- F. RELASI (FOREIGN KEYS)
-- ========================================================
ALTER TABLE `tbl_booking`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_alat_terpilih` FOREIGN KEY (`alat_terpilih_id`) REFERENCES `tbl_alat` (`alat_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `tbl_notifikasi`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;