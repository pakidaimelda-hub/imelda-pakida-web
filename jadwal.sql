-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2020 at 12:15 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.3.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jadwal`
--

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id_guru` int(11) NOT NULL,
  `nama` varchar(30) NOT NULL,
  `jk` varchar(30) NOT NULL,
  `alamat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id_guru`, `nama`, `jk`, `alamat`) VALUES
(5, 'Bill Gates', 'laki-laki', 'Amerika'),
(6, 'Marissa Mayer', 'perempuan', 'Amerika'),
(7, 'Jeff Bezos', 'laki-laki', 'Amerika'),
(8, 'Crystal Wijaya', 'perempuan', 'Jakarta'),
(9, 'Ahmad Dahlan', 'laki-laki', 'Depok'),
(10, 'Cut Nyak Dien', 'perempuan', 'Aceh'),
(11, 'DN Aidit', 'laki-laki', 'Jember'),
(12, 'Steve Jobs', 'laki-laki', 'Amerika'),
(13, 'Fidel Castro', 'laki-laki', 'Cuba'),
(14, 'Bill Gates 1', 'laki-laki', 'Amerika'),
(15, 'Bill Gates 2', 'laki-laki', 'Amerika'),
(16, 'Bill Gates 3', 'laki-laki', 'Amerika'),
(17, 'Lemine', 'laki-laki', 'Korea');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id_jadwal` int(11) NOT NULL,
  `id_guru` int(11) DEFAULT NULL,
  `id_jam` int(11) DEFAULT NULL,
  `hari` varchar(30) DEFAULT NULL,
  `id_pelajaran` int(11) DEFAULT NULL,
  `id_kelas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `jadwal`
--

INSERT INTO `jadwal` (`id_jadwal`, `id_guru`, `id_jam`, `hari`, `id_pelajaran`, `id_kelas`) VALUES
(76, 5, 6, 'senin', 13, 14),
(77, 5, 7, 'senin', 12, 14),
(78, 6, 9, 'senin', 13, 14),
(79, 6, 10, 'senin', 13, 14),
(80, 7, 11, 'senin', 17, 14),
(81, 7, 12, 'senin', 17, 14),
(82, 8, 13, 'senin', 14, 14),
(83, 8, 14, 'senin', 14, 14),
(84, 6, 6, 'senin', 21, 15),
(85, 6, 7, 'senin', 21, 15),
(86, 5, 9, 'senin', 22, 15),
(87, 5, 10, 'senin', 22, 15),
(88, 5, 6, 'selasa', 14, 14),
(89, 11, 6, 'senin', 14, 24),
(90, 5, 7, 'senin', 12, 24),
(91, NULL, 7, 'selasa', 12, 14);

-- --------------------------------------------------------

--
-- Table structure for table `jam`
--

CREATE TABLE `jam` (
  `id_jam` int(11) NOT NULL,
  `jam` varchar(30) NOT NULL,
  `mulai` varchar(30) NOT NULL,
  `akhir` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `jam`
--

INSERT INTO `jam` (`id_jam`, `jam`, `mulai`, `akhir`) VALUES
(6, '1', '07:30', '08:00'),
(7, '2', '08:00', '08:30'),
(9, '3', '08:30', '09:00'),
(10, '4', '09:00', '09:30'),
(11, '5', '10:00', '10:30'),
(12, '6', '10:30', '11:00'),
(13, '7', '11:00', '11:30'),
(14, '8', '11:30', '12:00');

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id_kelas` int(11) NOT NULL,
  `kelas` varchar(30) NOT NULL,
  `id_semester` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id_kelas`, `kelas`, `id_semester`) VALUES
(14, '1A', 4),
(15, '1B', 4),
(16, '2A', 4),
(17, '2B', 4),
(18, '3A', 4),
(19, '3B', 4),
(20, '4A', 4),
(21, '4B', 4),
(22, '5', 4),
(23, '6', 4),
(24, '1A', 10);

-- --------------------------------------------------------

--
-- Table structure for table `pelajaran`
--

CREATE TABLE `pelajaran` (
  `id_pelajaran` int(11) NOT NULL,
  `pelajaran` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pelajaran`
--

INSERT INTO `pelajaran` (`id_pelajaran`, `pelajaran`) VALUES
(12, 'Bahasa Indonesia'),
(13, 'Bahasa Inggris'),
(14, 'Matematika'),
(15, 'IPA'),
(16, 'IPS'),
(17, 'Kesenian'),
(18, 'BTA'),
(19, 'Olahraga'),
(20, 'PAI'),
(21, 'Bahasa Arab'),
(22, 'Hadist');

-- --------------------------------------------------------

--
-- Table structure for table `semester`
--

CREATE TABLE `semester` (
  `id_semester` int(11) NOT NULL,
  `tahun` varchar(30) NOT NULL,
  `semester` enum('ganjil','genap','','') NOT NULL,
  `status` enum('0','1','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `semester`
--

INSERT INTO `semester` (`id_semester`, `tahun`, `semester`, `status`) VALUES
(4, '2020/2021', 'ganjil', '1'),
(10, '2020/2021', 'genap', '0');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id_guru`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_guru` (`id_guru`),
  ADD KEY `id_jam` (`id_jam`),
  ADD KEY `id_pelajaran` (`id_pelajaran`),
  ADD KEY `id_kelas` (`id_kelas`);

--
-- Indexes for table `jam`
--
ALTER TABLE `jam`
  ADD PRIMARY KEY (`id_jam`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_kelas`),
  ADD KEY `id_semester` (`id_semester`);

--
-- Indexes for table `pelajaran`
--
ALTER TABLE `pelajaran`
  ADD PRIMARY KEY (`id_pelajaran`);

--
-- Indexes for table `semester`
--
ALTER TABLE `semester`
  ADD PRIMARY KEY (`id_semester`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id_guru` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `jam`
--
ALTER TABLE `jam`
  MODIFY `id_jam` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_kelas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `pelajaran`
--
ALTER TABLE `pelajaran`
  MODIFY `id_pelajaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `semester`
--
ALTER TABLE `semester`
  MODIFY `id_semester` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `id_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_jam` FOREIGN KEY (`id_jam`) REFERENCES `jam` (`id_jam`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_pelajaran` FOREIGN KEY (`id_pelajaran`) REFERENCES `pelajaran` (`id_pelajaran`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `id_semester` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
