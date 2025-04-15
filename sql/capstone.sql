-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2025 at 05:40 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `capstone`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_joiner`
--

CREATE TABLE `account_joiner` (
  `id` int(11) NOT NULL,
  `firstName` varchar(30) DEFAULT NULL,
  `lastName` varchar(30) DEFAULT NULL,
  `pwd` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contactNumber` int(11) DEFAULT NULL,
  `emergencyCname` varchar(255) DEFAULT NULL,
  `emergencyCnumber` int(11) DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `otp_sent_at` datetime DEFAULT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(10) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_joiner`
--

INSERT INTO `account_joiner` (`id`, `firstName`, `lastName`, `pwd`, `email`, `gender`, `address`, `contactNumber`, `emergencyCname`, `emergencyCnumber`, `otp`, `otp_sent_at`, `created_at`, `status`) VALUES
(10, 'Rae Allen', 'Retuta', '$2y$10$VylvQ4WyW1HW91n4T1bhJux4CJnJZ5IUZqfQTRz3Y3nkZ2BY/n5cK', 'allenretuta@yahoo.com', 'Male', 'a, a, a', 2147483647, 'Alejo Retuta', 2147483647, '532501', '2025-04-14 04:50:36', '2025-04-14', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `account_org`
--

CREATE TABLE `account_org` (
  `id` int(11) NOT NULL,
  `orgname` varchar(60) DEFAULT NULL,
  `orgpass` varchar(60) DEFAULT NULL,
  `orgemail` varchar(100) DEFAULT NULL,
  `ceo` varchar(60) DEFAULT NULL,
  `orgadd` varchar(100) DEFAULT NULL,
  `orgnumber` varchar(11) DEFAULT NULL,
  `file_paths` text NOT NULL,
  `status` varchar(11) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_org`
--

INSERT INTO `account_org` (`id`, `orgname`, `orgpass`, `orgemail`, `ceo`, `orgadd`, `orgnumber`, `file_paths`, `status`) VALUES
(21, 'Allen Tours', '$2y$10$Cr3d831Qyr2gKMd9gJUds.EeLzxNzQPRKedXSMgXmZMGtRMpSqDXG', 'allenretuta@yahoo.com', 'Allen', 'asd, asd, asd', '09878371283', 'C:/xampp/htdocs/Capstone/files/1.jpg,C:/xampp/htdocs/Capstone/files/2.jpg', 'pending'),
(22, 'Rae Tours', '$2y$10$ilVkaiXveybnfF4F/BqIN.4WgMIbOCEDk40Oozj4Ekx4DE3xs4CLK', 'retutaraeallen@gmail.com', '123123', 'asd, asd, asd', '09787893123', 'C:/xampp/htdocs/Capstone/files/page 5.jpg,C:/xampp/htdocs/Capstone/files/page1.jpg', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_joiner`
--
ALTER TABLE `account_joiner`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `account_org`
--
ALTER TABLE `account_org`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_joiner`
--
ALTER TABLE `account_joiner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `account_org`
--
ALTER TABLE `account_org`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
