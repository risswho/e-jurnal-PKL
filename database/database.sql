-- =====================================================
-- E-JURNAL SMK — Skema fresh + data contoh
-- Database: jurnal (sesuai config/koneksi.php)
-- Cara pakai (XAMPP Windows, root tanpa password):
--   "C:\xampp\mysql\bin\mysql.exe" -u root < database\database.sql
--   atau import file ini via phpMyAdmin.
-- Akun seed:
--   admin : username "admin" / password "admin123"
--   guru  : NIP "1001" / "1002" / password "guru123"
--   siswa : NISN "001".."003" / password "siswa123"
-- Password tersimpan ter-hash (bcrypt, password_hash).
-- =====================================================

CREATE DATABASE IF NOT EXISTS `jurnal`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `jurnal`;

-- ---------------- Tabel admin ----------------
CREATE TABLE IF NOT EXISTS `admin` (
  `id_admin` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `kode_unik` VARCHAR(255) NOT NULL COMMENT 'hash password admin',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------- Tabel guru ----------------
CREATE TABLE IF NOT EXISTS `guru` (
  `id_guru` INT(11) NOT NULL AUTO_INCREMENT,
  `nip` VARCHAR(30) NOT NULL,
  `nama_guru` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL COMMENT 'hash password (bcrypt)',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_guru`),
  UNIQUE KEY `nip` (`nip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------- Tabel siswa ----------------
CREATE TABLE IF NOT EXISTS `siswa` (
  `id_siswa` INT(11) NOT NULL AUTO_INCREMENT,
  `nisn` VARCHAR(20) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `kelas` VARCHAR(20) NOT NULL,
  `jurusan` VARCHAR(100) NOT NULL,
  `dudi_magang` VARCHAR(150) NOT NULL,
  `id_guru` INT(11) DEFAULT NULL COMMENT 'guru pembimbing (diatur admin)',
  `password` VARCHAR(255) NOT NULL COMMENT 'hash password (bcrypt)',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_siswa`),
  UNIQUE KEY `nisn` (`nisn`),
  KEY `id_guru` (`id_guru`),
  CONSTRAINT `fk_siswa_guru` FOREIGN KEY (`id_guru`)
    REFERENCES `guru` (`id_guru`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------- Tabel jurnal ----------------
CREATE TABLE IF NOT EXISTS `jurnal` (
  `id_jurnal` INT(11) NOT NULL AUTO_INCREMENT,
  `id_siswa` INT(11) NOT NULL,
  `tanggal_kegiatan` DATE NOT NULL,
  `nama_dudi` VARCHAR(150) NOT NULL,
  `pembimbing` VARCHAR(100) NOT NULL COMMENT 'pembimbing lapangan di DUDI',
  `kegiatan_magang` TEXT NOT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_jurnal`),
  KEY `id_siswa` (`id_siswa`),
  CONSTRAINT `fk_jurnal_siswa` FOREIGN KEY (`id_siswa`)
    REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------- Tabel monitoring ----------------
CREATE TABLE IF NOT EXISTS `monitoring` (
  `id_monitoring` INT(11) NOT NULL AUTO_INCREMENT,
  `id_guru` INT(11) NOT NULL,
  `id_siswa` INT(11) NOT NULL,
  `tanggal_monitoring` DATE NOT NULL,
  `nama_dudi` VARCHAR(150) NOT NULL,
  `keterangan` TEXT NOT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_monitoring`),
  KEY `id_guru` (`id_guru`),
  KEY `id_siswa` (`id_siswa`),
  CONSTRAINT `fk_monitoring_guru` FOREIGN KEY (`id_guru`)
    REFERENCES `guru` (`id_guru`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_monitoring_siswa` FOREIGN KEY (`id_siswa`)
    REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATA CONTOH (aman dihapus / ditimpa via aplikasi)
-- =====================================================

INSERT INTO `admin` (`username`, `kode_unik`) VALUES
('admin', '$2y$10$BGcEifqSCNeWcyHR8gZVXuXh0D27fnm3XXI/rLWWsddJ/qOMMuZn6'); -- admin123

INSERT INTO `guru` (`nip`, `nama_guru`, `password`) VALUES
('1001', 'Budi Santoso', '$2y$10$zl5L.UJF3Kuo5meiHAO1Fek3.rUjccAzm1JRj1nbAHei0QvDms3Xa'), -- guru123
('1002', 'Siti Aminah', '$2y$10$zl5L.UJF3Kuo5meiHAO1Fek3.rUjccAzm1JRj1nbAHei0QvDms3Xa');   -- guru123

INSERT INTO `siswa` (`nisn`, `nama_lengkap`, `kelas`, `jurusan`, `dudi_magang`, `id_guru`, `password`) VALUES
('001', 'Andi Pratama', 'XI RPL A', 'Rekayasa Perangkat Lunak', 'PT Maju Jaya', 1, '$2y$10$bEydhB24Fd5VqKBrhi402elkhTuMYp5FtK65Q2uEgNs690tMrT2Qa'), -- siswa123
('002', 'Dewi Lestari', 'XI RPL A', 'Rekayasa Perangkat Lunak', 'CV Karya Digital', 1, '$2y$10$bEydhB24Fd5VqKBrhi402elkhTuMYp5FtK65Q2uEgNs690tMrT2Qa'), -- siswa123
('003', 'Rian Hidayat', 'XI TKJ B', 'Teknik Komputer dan Jaringan', 'PT Solusi Net', 2, '$2y$10$bEydhB24Fd5VqKBrhi402elkhTuMYp5FtK65Q2uEgNs690tMrT2Qa'); -- siswa123

INSERT INTO `jurnal` (`id_siswa`, `tanggal_kegiatan`, `nama_dudi`, `pembimbing`, `kegiatan_magang`, `keterangan`) VALUES
(1, CURDATE(), 'PT Maju Jaya', 'Pak Dedi', 'Membuat layout halaman profil perusahaan dengan HTML dan CSS', 'Menyelesaikan header dan navigasi'),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'PT Maju Jaya', 'Pak Dedi', 'Mempelajari instalasi dan konfigurasi web server lokal', 'Berhasil menjalankan XAMPP'),
(2, CURDATE(), 'CV Karya Digital', 'Bu Rina', 'Membuat desain banner promosi dengan aplikasi grafis', NULL),
(3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'PT Solusi Net', 'Pak Agus', 'Melakukan crimping kabel UTP dan uji koneksi jaringan', '8 kabel lolos uji tester');

INSERT INTO `monitoring` (`id_guru`, `id_siswa`, `tanggal_monitoring`, `nama_dudi`, `keterangan`) VALUES
(1, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'PT Maju Jaya', 'Monitoring minggu ke-2: progres logbook baik, arahan kerapian dokumentasi foto.'),
(1, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'CV Karya Digital', 'Monitoring minggu ke-1: progres logbook baik, arahan kerapian dokumentasi foto.'),
(2, 3, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'PT Solusi Net', 'Kunjungan awal: serah terima siswa dan koordinasi jadwal dengan pembimbing lapangan.');
