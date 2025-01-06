-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 06, 2025 at 02:39 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lancarapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `atasan_sekolah`
--

CREATE TABLE `atasan_sekolah` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `nip` varchar(20) DEFAULT NULL,
  `pangkat` varchar(255) NOT NULL,
  `golongan` varchar(255) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `instansi` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `atasan_sekolah`
--

INSERT INTO `atasan_sekolah` (`id`, `nama`, `nip`, `pangkat`, `golongan`, `jabatan`, `instansi`) VALUES
(1, 'Upie Indrakusuma, S.Pd., MM.', '199605131992011001', 'Pembina', 'IVa', 'Kepala Sekolah', 'SMKN 1 MAJALAYA'),
(4, 'ucok baba', '12345678997564543', 'jbhjkghfgy', 'hvhjkk', 'kepala', 'smkn 1 majalaya');

-- --------------------------------------------------------

--
-- Table structure for table `form_status`
--

CREATE TABLE `form_status` (
  `id` int(11) NOT NULL,
  `status` enum('active','inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_status`
--

INSERT INTO `form_status` (`id`, `status`) VALUES
(1, 'active'),
(2, 'inactive');

-- --------------------------------------------------------

--
-- Table structure for table `guru_piket`
--

CREATE TABLE `guru_piket` (
  `id` int(11) NOT NULL,
  `nama_guru` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nip` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guru_piket`
--

INSERT INTO `guru_piket` (`id`, `nama_guru`, `email`, `nip`) VALUES
(1, 'Dr. Bambang Sudibyo', 'bambang@smk.sch.id', '197505021993021001'),
(2, 'Dr. Maria Indriati', 'maria@smk.sch.id', '198008152005012001');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 2, '996d854b08afdd71f1af0d96d804481f1b00b43f2f129647720a5f77a689d3b3', '2024-12-20 21:30:33', '2024-12-21 02:58:52'),
(4, 2, '7d9797fcece399d98cd05e3901ab72964dae5317dfb4353306f25a60f8021abb', '2024-12-20 21:14:32', '2024-12-21 02:59:32'),
(5, 2, '6d5e70f47ff96673a111b5717d639fe8e1a1ac16c570cc099334fc0e29067f49', '2024-12-20 21:15:02', '2024-12-21 03:00:02'),
(6, 2, '3f033d478659c2f33f682770506e0fee9d163679108efe77d2fbe620cbb9c065', '2024-12-20 21:15:08', '2024-12-21 03:00:08'),
(7, 2, '11142520440ce3f1ed5dc9e13320678ae4b4c3321ade4ad24ff8a9a859a3813c', '2024-12-20 21:15:14', '2024-12-21 03:00:14'),
(8, 2, '8dc012b69cc14b54fe1a7edb1677ed5ae7c3ba40af44bb3eb03c329337765c87', '2024-12-30 06:22:30', '2024-12-30 12:07:30'),
(9, 2, '2c9e4d940acb653f43816e014bf35d80047d672b281019b409f3bb62311b2a42', '2024-12-30 06:22:49', '2024-12-30 12:07:49'),
(10, 2, '958ebe61266d1b69f0cea57db1bb80d151508ca1dab87c77ef7a544584bc000b', '2024-12-30 06:22:58', '2024-12-30 12:07:58'),
(11, 2, 'c962db08e98a69cecb72e417ebe63a3ec71900299a8fd953f7ee8d06b2b29e30', '2024-12-30 06:23:07', '2024-12-30 12:08:07'),
(12, 2, '4fddf7a45a19dd369290dcda19989fc4d54f6589e913131eab5435e51f37e08b', '2024-12-30 06:23:15', '2024-12-30 12:08:15'),
(13, 2, '5b1930ecfc1b316f94d052bfc5c2303f81de2aed3f7f782f755061602bbe152a', '2025-01-04 06:19:24', '2025-01-04 12:04:24'),
(14, 2, '50c81ef15fbd520e0f147ab63a0bdc8e03dd1bd8b27fcffe5d84f7a2aa4e3e05', '2025-01-04 06:22:14', '2025-01-04 12:07:14'),
(15, 2, '69beb3d4cae2ac1c71f85a47d998c573028a992fb9414e7465166ed2b6f231ca', '2025-01-04 06:22:35', '2025-01-04 12:07:35'),
(16, 2, '485bf1143ffaaa845c068f0048355c55425a4f684069cbfe7883cb8c24c0275c', '2025-01-04 06:28:51', '2025-01-04 12:13:51');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan`
--

CREATE TABLE `pengajuan` (
  `id` int(11) NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `guru_piket_id` int(11) DEFAULT NULL,
  `alasan` text NOT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `tanggal_akhir` varchar(40) NOT NULL,
  `dokumen_lampiran` varchar(255) DEFAULT NULL,
  `status` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `tanggal_disetujui` datetime DEFAULT NULL,
  `keputusan_admin` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `nama_lengkap` varchar(255) NOT NULL,
  `nis` int(50) NOT NULL,
  `jurusan` varchar(40) DEFAULT NULL,
  `kelas` varchar(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  `noHp` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengajuan`
--

INSERT INTO `pengajuan` (`id`, `kelas_id`, `guru_piket_id`, `alasan`, `lokasi`, `tanggal_pengajuan`, `tanggal_akhir`, `dokumen_lampiran`, `status`, `tanggal_disetujui`, `keputusan_admin`, `updated_at`, `nama_lengkap`, `nis`, `jurusan`, `kelas`, `email`, `noHp`) VALUES
(41, NULL, NULL, 'izin mengikuti lomba', 'smkn 1 bandung', '2025-01-02', '2025-01-02', 'lampiran_6776b472590c40.72832467.pdf', 'disetujui', '2025-01-04 11:52:26', NULL, '2025-01-04 10:52:26', 'sumitra adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'XII-TKJ 4', 'sumitraadriansyahmuhammad25@gmail.com', '081223434545'),
(44, NULL, NULL, 'mengikuti lomba olimpiade', 'smkn 1 majalaya', '2025-01-04', '2025-01-04', 'lampiran_6779571fe08250.52241879.pdf', 'disetujui', '2025-01-04 17:23:40', NULL, '2025-01-04 16:23:40', 'sumitra adriansyah', 12170137, 'teknik elektronika', 'X-TE 2', 'sumitraadriansyah@gmail.com', '084564675765'),
(45, NULL, NULL, 'mengikuti lomba senam', 'smkn 1 majalaya', '2025-01-04', '2025-01-04', 'lampiran_677961a26aaa08.67295067.pdf', 'ditolak', NULL, NULL, '2025-01-04 16:35:21', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(46, NULL, NULL, 'mengikuti lomba jaipong', 'smkn 2  majalaya', '2025-01-04', '2025-01-04', 'lampiran_67796203a7fb95.65907802.pdf', 'disetujui', '2025-01-04 17:36:23', NULL, '2025-01-04 16:36:23', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(47, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-04', '2025-01-04', 'lampiran_67796251378417.68191108.pdf', 'ditolak', NULL, NULL, '2025-01-04 16:42:29', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(48, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-04', '2025-01-04', 'lampiran_67796283458c45.38928232.pdf', 'disetujui', '2025-01-04 17:47:10', NULL, '2025-01-04 16:47:10', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(49, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-04', '2025-01-04', 'lampiran_677962c583b9e2.33261997.pdf', 'pending', NULL, NULL, '2025-01-04 16:33:09', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(50, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-04', '2025-01-04', 'lampiran_677963080902b0.48053462.pdf', 'pending', NULL, NULL, '2025-01-04 16:34:16', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(51, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_67796a06c366d5.27322142.pdf', 'pending', NULL, NULL, '2025-01-04 17:04:06', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(52, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_67796b5562f509.56641792.pdf', 'pending', NULL, NULL, '2025-01-04 17:09:41', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(53, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_67796c1eca9a66.14706106.pdf', 'pending', NULL, NULL, '2025-01-04 17:13:02', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(54, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_677970f1e98598.23475377.pdf', 'pending', NULL, NULL, '2025-01-04 17:33:37', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(55, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_677972a70a3110.30535482.pdf', 'pending', NULL, NULL, '2025-01-04 17:40:55', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(56, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_677973453a6677.81698922.pdf', 'pending', NULL, NULL, '2025-01-04 17:43:33', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(57, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_677973b290e4f9.26079790.pdf', 'pending', NULL, NULL, '2025-01-04 17:45:22', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(58, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_677974000e55a7.09641244.pdf', 'pending', NULL, NULL, '2025-01-04 17:46:40', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(59, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_6779743c6af070.87804400.pdf', 'pending', NULL, NULL, '2025-01-04 17:47:40', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(60, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_677974cbb853c5.61452160.pdf', 'pending', NULL, NULL, '2025-01-04 17:50:03', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(61, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_67797558375472.11148105.pdf', 'pending', NULL, NULL, '2025-01-04 17:52:24', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(62, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_6779761094f4d8.91482025.pdf', 'pending', NULL, NULL, '2025-01-04 17:55:28', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(63, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_677976a13525d5.06008147.pdf', 'pending', NULL, NULL, '2025-01-04 17:57:53', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694'),
(64, NULL, NULL, 'Sumitra Adriansyah', 'Sumitra Adriansyah', '2025-01-05', '2025-01-05', 'lampiran_677977241befa2.35065495.pdf', 'pending', NULL, NULL, '2025-01-04 18:00:04', 'Sumitra Adriansyah', 1217050137, 'Teknik Komputer Jaringan', 'X-TKJ 2', 'sumitraadriansyahmuhammad25@gmail.com', '081930593694');

-- --------------------------------------------------------

--
-- Table structure for table `smtp_config`
--

CREATE TABLE `smtp_config` (
  `id` int(11) NOT NULL,
  `smtp_username` varchar(255) NOT NULL,
  `smtp_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `smtp_config`
--

INSERT INTO `smtp_config` (`id`, `smtp_username`, `smtp_password`) VALUES
(1, 'adriansyahsumitra@gmail.com', 'kfdr rqev aneh gsgo');

-- --------------------------------------------------------

--
-- Table structure for table `surat`
--

CREATE TABLE `surat` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `no_surat` varchar(50) NOT NULL,
  `jenis_surat` varchar(20) NOT NULL,
  `perihal` varchar(255) NOT NULL,
  `lampiran` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `surat`
--

INSERT INTO `surat` (`id`, `tanggal`, `no_surat`, `jenis_surat`, `perihal`, `lampiran`) VALUES
(2, '2024-12-17', '00/2', 'Surat Masuk', 'seminar', 'lampiran_675fec31d3b8c3.61333811.pdf'),
(4, '2024-12-18', '00/4', 'Surat Masuk', 'kegiatan', 'lampiran_6762622f49dd50.60912388.pdf'),
(5, '2025-01-01', '00/4/6', 'Surat Keluar', 'Tes', 'lampiran_67754b27583f03.74280471.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `surat_dispensasi`
--

CREATE TABLE `surat_dispensasi` (
  `id` int(11) NOT NULL,
  `pengajuan_id` int(11) DEFAULT NULL,
  `nama_file` varchar(255) DEFAULT NULL,
  `path_file` varchar(255) DEFAULT NULL,
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `surat_dispensasi`
--

INSERT INTO `surat_dispensasi` (`id`, `pengajuan_id`, `nama_file`, `path_file`, `tanggal_dibuat`) VALUES
(7, 41, 'surat_dispensasi_41.pdf', 'arsip/2024-2025/surat_dispensasi_41.pdf', '2025-01-04 10:52:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('siswa','admin') DEFAULT 'siswa',
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`, `created_at`) VALUES
(1, 'siswa1', '$2a$12$VTfyMqb0J6nyaMZjRpaezOq34sK5qRMLX.B.gGauI9./c9qH9zodG', 'siswa', 'mahasiswa1@univ.ac.id', '2024-10-27 17:35:12'),
(2, 'admin', '$2y$10$JO09DcY9OUTwnL3PtgRDL.HMViQkBs9u43KDBrsWVDC.cmVhyHcm2', 'admin', 'sumitraadriansyah@gmail.com', '2024-10-27 17:35:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `atasan_sekolah`
--
ALTER TABLE `atasan_sekolah`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form_status`
--
ALTER TABLE `form_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guru_piket`
--
ALTER TABLE `guru_piket`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `guru_piket_id` (`guru_piket_id`);

--
-- Indexes for table `smtp_config`
--
ALTER TABLE `smtp_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `surat`
--
ALTER TABLE `surat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `surat_dispensasi`
--
ALTER TABLE `surat_dispensasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengajuan_id` (`pengajuan_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `atasan_sekolah`
--
ALTER TABLE `atasan_sekolah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `form_status`
--
ALTER TABLE `form_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `guru_piket`
--
ALTER TABLE `guru_piket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pengajuan`
--
ALTER TABLE `pengajuan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `smtp_config`
--
ALTER TABLE `smtp_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `surat`
--
ALTER TABLE `surat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `surat_dispensasi`
--
ALTER TABLE `surat_dispensasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD CONSTRAINT `pengajuan_ibfk_3` FOREIGN KEY (`guru_piket_id`) REFERENCES `guru_piket` (`id`);

--
-- Constraints for table `surat_dispensasi`
--
ALTER TABLE `surat_dispensasi`
  ADD CONSTRAINT `surat_dispensasi_ibfk_1` FOREIGN KEY (`pengajuan_id`) REFERENCES `pengajuan` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
