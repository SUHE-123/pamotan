-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 11, 2025 at 02:47 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `caiwarav2`
--

-- --------------------------------------------------------

--
-- Table structure for table `pemupukan`
--

CREATE TABLE `pemupukan` (
  `id` int NOT NULL,
  `jenis` enum('N','P','K') NOT NULL,
  `tanggal` date NOT NULL,
  `jam` int NOT NULL,
  `menit` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pemupukan`
--

INSERT INTO `pemupukan` (`id`, `jenis`, `tanggal`, `jam`, `menit`) VALUES
(1, 'N', '2025-06-25', 6, 0),
(2, 'P', '2025-06-25', 7, 0),
(3, 'K', '2025-06-25', 8, 0),
(4, 'N', '2025-06-27', 7, 10),
(5, 'N', '2025-06-27', 7, 10),
(6, 'N', '2025-06-27', 8, 0),
(7, 'N', '2025-06-27', 8, 0),
(8, 'N', '2025-06-27', 8, 0),
(9, 'N', '2025-07-10', 10, 52),
(10, 'N', '2025-07-10', 12, 0);

-- --------------------------------------------------------

--
-- Table structure for table `penyiraman_air`
--

CREATE TABLE `penyiraman_air` (
  `id` int NOT NULL,
  `mode` enum('AUTO','SCHEDULE') NOT NULL DEFAULT 'AUTO',
  `jam1` int DEFAULT NULL,
  `menit1` int DEFAULT NULL,
  `jam2` int DEFAULT NULL,
  `menit2` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `penyiraman_air`
--

INSERT INTO `penyiraman_air` (`id`, `mode`, `jam1`, `menit1`, `jam2`, `menit2`) VALUES
(1, 'SCHEDULE', 8, 0, 9, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sensor_data`
--

CREATE TABLE `sensor_data` (
  `id` int NOT NULL,
  `soil_moisture` float NOT NULL,
  `ph` float NOT NULL,
  `waktu` datetime DEFAULT CURRENT_TIMESTAMP,
  `pompa_status` varchar(10) DEFAULT 'OFF'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sensor_data`
--

INSERT INTO `sensor_data` (`id`, `soil_moisture`, `ph`, `waktu`, `pompa_status`) VALUES
(1, 1, 2, '2025-06-24 22:06:41', 'OFF'),
(2, 2, 3, '2025-06-24 22:06:41', 'OFF'),
(3, 5, 4, '2025-06-24 23:17:26', 'OFF'),
(4, 45.2, 6.5, '2025-06-25 13:13:43', 'ON'),
(5, 50.2, 6.7, '2025-06-25 13:34:59', 'ON'),
(6, 50, 6.7, '2025-06-25 13:40:43', 'ON'),
(7, 55, 6.7, '2025-06-25 13:48:21', 'ON'),
(8, 45.2, 6.5, '2025-06-25 13:51:52', 'ON'),
(9, 90, 10.1, '2025-07-10 11:54:50', 'ON');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pemupukan`
--
ALTER TABLE `pemupukan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `penyiraman_air`
--
ALTER TABLE `penyiraman_air`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pemupukan`
--
ALTER TABLE `pemupukan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
