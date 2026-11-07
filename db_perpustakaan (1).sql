-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2026 at 05:32 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_perpustakaan`
--

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id_buku` int(10) UNSIGNED NOT NULL,
  `judul_buku` varchar(200) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `tahun_terbit` year(4) NOT NULL,
  `stok` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status_ketersediaan` varchar(10) GENERATED ALWAYS AS (case when `stok` > 0 then 'Tersedia' else 'Dipinjam' end) STORED,
  `sampul` varchar(255) NOT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id_buku`, `judul_buku`, `kategori`, `penulis`, `tahun_terbit`, `stok`, `sampul`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'Laskar P', 'Novel', 'Andrea Hirata', '2005', 5, 'a85a230a7fd1deea989b48b2b56982ec.jpg', 1, '2026-11-05 04:47:26', '2026-11-06 04:16:24');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL,
  `id_buku` int(10) UNSIGNED NOT NULL,
  `tanggal_pengajuan` datetime NOT NULL DEFAULT current_timestamp(),
  `tanggal_pinjam` date DEFAULT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status` enum('Menunggu','Dipinjam','Ditolak','Dikembalikan') NOT NULL DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_peminjaman`, `id_user`, `id_buku`, `tanggal_pengajuan`, `tanggal_pinjam`, `tanggal_jatuh_tempo`, `tanggal_kembali`, `status`) VALUES
(1, 1, 2, '2026-11-06 10:50:08', '2026-11-06', '2026-11-13', '2026-11-06', 'Dikembalikan');

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id_token` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL,
  `selector` char(24) NOT NULL,
  `validator_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Anggota') NOT NULL DEFAULT 'Anggota',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Elsa Dwi', 'elsa02', '$2y$10$9jVxJ1OK9Zk.qsJodQYusu9KbI4z9Vn1XOu3fr7Bm7d0N4pGnT2zS', 'Anggota', '2026-11-04 18:01:36'),
(2, 'Maulinda Putri Tufah', 'maulinda_admin', '$2y$10$yhsdl0zU1MScRnW3YoDGiuPBfLmqD4kfu99rKJwIFTntWzbQo4FU.', 'Admin', '2026-11-05 12:57:23'),
(3, 'elsa penguji', 'elsa_test01', '$2y$10$tUT6W12aUolFzrLIiSi5z.CIi.BZm6HPOO5wiYZwanldQKFcJjPOW', 'Anggota', '2026-11-06 02:20:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id_buku`),
  ADD KEY `fk_buku_users` (`created_by`),
  ADD KEY `idx_buku_judul` (`judul_buku`),
  ADD KEY `idx_buku_kategori` (`kategori`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `fk_peminjaman_user` (`id_user`),
  ADD KEY `fk_peminjaman_buku` (`id_buku`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id_token`),
  ADD UNIQUE KEY `selector` (`selector`),
  ADD KEY `fk_remember_user` (`id_user`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id_buku` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id_token` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `fk_buku_users` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `fk_peminjaman_buku` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_peminjaman_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `fk_remember_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
