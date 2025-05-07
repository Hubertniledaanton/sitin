-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2025 at 05:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sitinmonitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `TITLE` varchar(255) NOT NULL,
  `CONTENT` varchar(255) NOT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`TITLE`, `CONTENT`, `CREATED_AT`) VALUES
('ICT Congress', 'This coming 2026 all the College of Computer Studies will required to attend the ICT Congress this coming April 22, 2026.', '2025-04-23 18:19:44');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `USER_ID` int(11) NOT NULL,
  `FEEDBACK` text NOT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LAB_ROOM` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`USER_ID`, `FEEDBACK`, `CREATED_AT`, `LAB_ROOM`) VALUES
(121003, 'The computer\'s in lab 526 are so lag, i think you need to upgrade the computer\'s ', '2025-03-19 18:22:58', ''),
(121003, 'The Working there is so strict.', '2025-03-20 17:35:32', '542'),
(121003, 'the room is so gross', '2025-03-20 17:36:22', '542'),
(121003, 'asdas', '2025-03-20 17:36:28', '542'),
(121003, 'asdasdasd', '2025-03-20 17:36:32', '542'),
(121003, 'asdasdasdasd', '2025-03-20 17:36:36', '542'),
(121003, 'aasdsddsds', '2025-03-20 17:36:44', '542'),
(121003, 'asdadasd', '2025-03-20 17:36:51', '542'),
(121003, 'asdadasdasdasd', '2025-03-20 17:36:59', '542'),
(121003, 'asdadasdasdasd', '2025-03-20 17:39:24', '542'),
(121003, 'asdadasdasdasd', '2025-03-20 17:39:41', '542'),
(121003, 'asdadasdasdasd', '2025-03-20 17:39:50', '542'),
(121003, 'asdadasdasdasd', '2025-03-20 18:14:10', '542'),
(123, 'asdasd', '2025-04-09 17:33:50', '524'),
(123, 'hello world', '2025-04-02 14:43:33', '524');

-- --------------------------------------------------------

--
-- Table structure for table `lab_resources`
--

CREATE TABLE `lab_resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `resource_type` varchar(50) NOT NULL,
  `link` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `upload_date` datetime NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_resources`
--

INSERT INTO `lab_resources` (`id`, `title`, `description`, `category`, `resource_type`, `link`, `file_path`, `upload_date`, `file_name`, `file_type`) VALUES
(1, 'JAVA Programming', 'This is for java', 'Programming', 'link', 'https://drive.google.com/file/d/1TQwa6iLSPJyyvmimugZ4Nizc7qjc0psw/preview', NULL, '2025-04-09 23:28:43', NULL, NULL),
(4, 'C# Programming', 'For C# Programmer', 'Programming', '', 'https://www.c-sharpcorner.com/UploadFile/EBooks/08252011003846AM/PdfFile/CsharpProgramming.pdf', NULL, '2025-04-10 00:04:45', NULL, NULL),
(5, 'Database', 'Database', 'Database', '', 'https://www.nagwa.com/en/plans/879185752938/', NULL, '2025-04-10 00:09:07', NULL, NULL),
(9, 'chatgpt', 'Chat', 'Programming', '', 'https://chatgpt.com/', NULL, '2025-04-10 00:57:39', NULL, NULL),
(16, 'asda', 'sadasd', 'Database', '', 'https://links-lang.org/', NULL, '2025-04-10 01:06:34', NULL, NULL),
(17, 'adasd', 'adasda', 'Web Development', '', 'https://links-lang.org/', NULL, '2025-04-10 01:06:39', NULL, NULL),
(18, 'sadsaa', 'daada', 'Other', '', 'https://links-lang.org/', NULL, '2025-04-10 01:06:44', NULL, NULL),
(19, 'sadsda', 'sdasda', 'Database', '', 'https://links-lang.org/', NULL, '2025-04-10 01:06:52', NULL, NULL),
(20, 'Python Reviewer', 'Python makes all easy', 'Programming', '', 'https://github.com/KhushiRokade/AI-CodeReviewer.git', NULL, '2025-04-24 01:52:37', NULL, NULL),
(21, 'Python Reviewer', 'Python makes all easy', 'Programming', '', 'https://github.com/KhushiRokade/AI-CodeReviewer.git', NULL, '2025-04-24 01:53:13', NULL, NULL),
(22, 'Python Reviewer', 'Python makes all easy whauahha', 'Programming', '', 'https://github.com/KhushiRokade/AI-CodeReviewer.git', NULL, '2025-04-24 01:53:16', NULL, NULL),
(23, 'Sitin Records', 'All sitin records are in this file', 'Other', '', '', '680a694431505_sitin_filtered_records (2).csv', '2025-04-25 00:39:32', 'sitin_filtered_records (2).csv', 'csv'),
(24, 'Sitin Records', 'All sitin records are in this file', 'Other', '', '', '680a6eecd8116_image-removebg-preview (3).png', '2025-04-25 01:03:40', 'image-removebg-preview (3).png', 'png'),
(25, 'Sitin Records', 'All sitin records are in sitin', 'Database', '', '', '680a6f10b2200_image-removebg-preview (3).png', '2025-04-25 01:04:16', 'image-removebg-preview (3).png', 'png'),
(26, 'TEST', '1', 'Programming', '', '', '680e869b02a89_491026982_999001989011370_1798172304621960380_n.jpg', '2025-04-28 03:33:47', '491026982_999001989011370_1798172304621960380_n.jpg', 'jpg'),
(27, 'asdasdas', 'dasdasdsadasdasdasdasda', 'Programming', '', 'https://github.com/KhushiRokade/AI-CodeReviewer.git', '', '2025-04-28 03:59:27', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `lab_schedules`
--

CREATE TABLE `lab_schedules` (
  `id` int(11) NOT NULL,
  `room_number` varchar(10) DEFAULT NULL,
  `day_group` varchar(10) DEFAULT NULL,
  `time_slot` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_schedules`
--

INSERT INTO `lab_schedules` (`id`, `room_number`, `day_group`, `time_slot`, `status`, `notes`, `last_updated`) VALUES
(78, '528', 'MW', '7:30AM-9:00AM', 'Occupied', NULL, '2025-04-24 17:41:41'),
(79, '528', 'TTH', '7:30AM-9:00AM', 'Available', NULL, '2025-04-24 17:41:41'),
(80, '528', 'F', '7:30AM-9:00AM', 'Available', NULL, '2025-04-24 17:41:41'),
(81, '528', 'S', '7:30AM-9:00AM', 'Available', NULL, '2025-04-24 17:41:41'),
(82, '528', 'MW', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 17:41:41'),
(83, '528', 'TTH', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 17:41:41'),
(84, '528', 'F', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 17:41:41'),
(85, '528', 'S', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 17:41:41'),
(86, '528', 'MW', '10:30AM-12:00PM', 'Occupied', NULL, '2025-04-24 17:41:41'),
(87, '528', 'TTH', '10:30AM-12:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(88, '528', 'F', '10:30AM-12:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(89, '528', 'S', '10:30AM-12:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(90, '528', 'MW', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(91, '528', 'TTH', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(92, '528', 'F', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(93, '528', 'S', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(94, '528', 'MW', '1:00PM-3:00PM', 'Occupied', NULL, '2025-04-24 17:41:41'),
(95, '528', 'TTH', '1:00PM-3:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(96, '528', 'F', '1:00PM-3:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(97, '528', 'S', '1:00PM-3:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(98, '528', 'MW', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 17:41:41'),
(99, '528', 'TTH', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 17:41:41'),
(100, '528', 'F', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 17:41:41'),
(101, '528', 'S', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 17:41:41'),
(102, '528', 'MW', '4:30PM-6:00PM', 'Occupied', NULL, '2025-04-24 17:41:41'),
(103, '528', 'TTH', '4:30PM-6:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(104, '528', 'F', '4:30PM-6:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(105, '528', 'S', '4:30PM-6:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(106, '528', 'MW', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 17:41:41'),
(107, '528', 'TTH', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 17:41:41'),
(108, '528', 'F', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 17:41:41'),
(109, '528', 'S', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 17:41:41'),
(110, '528', 'MW', '7:30PM-9:00PM', 'Occupied', NULL, '2025-04-24 17:41:41'),
(111, '528', 'TTH', '7:30PM-9:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(112, '528', 'F', '7:30PM-9:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(113, '528', 'S', '7:30PM-9:00PM', 'Available', NULL, '2025-04-24 17:41:41'),
(114, '530', 'MW', '7:30AM-9:00AM', 'Available', NULL, '2025-04-24 17:41:47'),
(115, '530', 'TTH', '7:30AM-9:00AM', 'Occupied', NULL, '2025-04-24 17:41:47'),
(116, '530', 'F', '7:30AM-9:00AM', 'Available', NULL, '2025-04-24 17:41:47'),
(117, '530', 'S', '7:30AM-9:00AM', 'Available', NULL, '2025-04-24 17:41:47'),
(118, '530', 'MW', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 17:41:47'),
(119, '530', 'TTH', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 17:41:47'),
(120, '530', 'F', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 17:41:47'),
(121, '530', 'S', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 17:41:47'),
(122, '530', 'MW', '10:30AM-12:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(123, '530', 'TTH', '10:30AM-12:00PM', 'Occupied', NULL, '2025-04-24 17:41:47'),
(124, '530', 'F', '10:30AM-12:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(125, '530', 'S', '10:30AM-12:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(126, '530', 'MW', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(127, '530', 'TTH', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(128, '530', 'F', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(129, '530', 'S', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(130, '530', 'MW', '1:00PM-3:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(131, '530', 'TTH', '1:00PM-3:00PM', 'Occupied', NULL, '2025-04-24 17:41:47'),
(132, '530', 'F', '1:00PM-3:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(133, '530', 'S', '1:00PM-3:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(134, '530', 'MW', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 17:41:47'),
(135, '530', 'TTH', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 17:41:47'),
(136, '530', 'F', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 17:41:47'),
(137, '530', 'S', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 17:41:47'),
(138, '530', 'MW', '4:30PM-6:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(139, '530', 'TTH', '4:30PM-6:00PM', 'Occupied', NULL, '2025-04-24 17:41:47'),
(140, '530', 'F', '4:30PM-6:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(141, '530', 'S', '4:30PM-6:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(142, '530', 'MW', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 17:41:47'),
(143, '530', 'TTH', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 17:41:47'),
(144, '530', 'F', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 17:41:47'),
(145, '530', 'S', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 17:41:47'),
(146, '530', 'MW', '7:30PM-9:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(147, '530', 'TTH', '7:30PM-9:00PM', 'Occupied', NULL, '2025-04-24 17:41:47'),
(148, '530', 'F', '7:30PM-9:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(149, '530', 'S', '7:30PM-9:00PM', 'Available', NULL, '2025-04-24 17:41:47'),
(186, '544', 'MW', '7:30AM-9:00AM', 'Available', NULL, '2025-04-24 17:42:00'),
(187, '544', 'TTH', '7:30AM-9:00AM', 'Available', NULL, '2025-04-24 17:42:00'),
(188, '544', 'F', '7:30AM-9:00AM', 'Available', NULL, '2025-04-24 17:42:00'),
(189, '544', 'S', '7:30AM-9:00AM', 'Occupied', NULL, '2025-04-24 17:42:00'),
(190, '544', 'MW', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 17:42:00'),
(191, '544', 'TTH', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 17:42:00'),
(192, '544', 'F', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 17:42:00'),
(193, '544', 'S', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 17:42:00'),
(194, '544', 'MW', '10:30AM-12:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(195, '544', 'TTH', '10:30AM-12:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(196, '544', 'F', '10:30AM-12:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(197, '544', 'S', '10:30AM-12:00PM', 'Occupied', NULL, '2025-04-24 17:42:00'),
(198, '544', 'MW', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(199, '544', 'TTH', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(200, '544', 'F', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(201, '544', 'S', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(202, '544', 'MW', '1:00PM-3:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(203, '544', 'TTH', '1:00PM-3:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(204, '544', 'F', '1:00PM-3:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(205, '544', 'S', '1:00PM-3:00PM', 'Occupied', NULL, '2025-04-24 17:42:00'),
(206, '544', 'MW', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 17:42:00'),
(207, '544', 'TTH', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 17:42:00'),
(208, '544', 'F', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 17:42:00'),
(209, '544', 'S', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 17:42:00'),
(210, '544', 'MW', '4:30PM-6:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(211, '544', 'TTH', '4:30PM-6:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(212, '544', 'F', '4:30PM-6:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(213, '544', 'S', '4:30PM-6:00PM', 'Occupied', NULL, '2025-04-24 17:42:00'),
(214, '544', 'MW', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 17:42:00'),
(215, '544', 'TTH', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 17:42:00'),
(216, '544', 'F', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 17:42:00'),
(217, '544', 'S', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 17:42:00'),
(218, '544', 'MW', '7:30PM-9:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(219, '544', 'TTH', '7:30PM-9:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(220, '544', 'F', '7:30PM-9:00PM', 'Available', NULL, '2025-04-24 17:42:00'),
(221, '544', 'S', '7:30PM-9:00PM', 'Occupied', NULL, '2025-04-24 17:42:00'),
(258, '542', 'MW', '7:30AM-9:00AM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(259, '542', 'TTH', '7:30AM-9:00AM', 'Available', NULL, '2025-04-24 19:10:24'),
(260, '542', 'F', '7:30AM-9:00AM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(261, '542', 'S', '7:30AM-9:00AM', 'Available', NULL, '2025-04-24 19:10:24'),
(262, '542', 'MW', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 19:10:24'),
(263, '542', 'TTH', '9:00AM-10:30AM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(264, '542', 'F', '9:00AM-10:30AM', 'Available', NULL, '2025-04-24 19:10:24'),
(265, '542', 'S', '9:00AM-10:30AM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(266, '542', 'MW', '10:30AM-12:00PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(267, '542', 'TTH', '10:30AM-12:00PM', 'Available', NULL, '2025-04-24 19:10:24'),
(268, '542', 'F', '10:30AM-12:00PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(269, '542', 'S', '10:30AM-12:00PM', 'Available', NULL, '2025-04-24 19:10:24'),
(270, '542', 'MW', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 19:10:24'),
(271, '542', 'TTH', '12:00PM-1:00PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(272, '542', 'F', '12:00PM-1:00PM', 'Available', NULL, '2025-04-24 19:10:24'),
(273, '542', 'S', '12:00PM-1:00PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(274, '542', 'MW', '1:00PM-3:00PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(275, '542', 'TTH', '1:00PM-3:00PM', 'Available', NULL, '2025-04-24 19:10:24'),
(276, '542', 'F', '1:00PM-3:00PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(277, '542', 'S', '1:00PM-3:00PM', 'Available', NULL, '2025-04-24 19:10:24'),
(278, '542', 'MW', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 19:10:24'),
(279, '542', 'TTH', '3:00PM-4:30PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(280, '542', 'F', '3:00PM-4:30PM', 'Available', NULL, '2025-04-24 19:10:24'),
(281, '542', 'S', '3:00PM-4:30PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(282, '542', 'MW', '4:30PM-6:00PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(283, '542', 'TTH', '4:30PM-6:00PM', 'Available', NULL, '2025-04-24 19:10:24'),
(284, '542', 'F', '4:30PM-6:00PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(285, '542', 'S', '4:30PM-6:00PM', 'Available', NULL, '2025-04-24 19:10:24'),
(286, '542', 'MW', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 19:10:24'),
(287, '542', 'TTH', '6:00PM-7:30PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(288, '542', 'F', '6:00PM-7:30PM', 'Available', NULL, '2025-04-24 19:10:24'),
(289, '542', 'S', '6:00PM-7:30PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(290, '542', 'MW', '7:30PM-9:00PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(291, '542', 'TTH', '7:30PM-9:00PM', 'Available', NULL, '2025-04-24 19:10:24'),
(292, '542', 'F', '7:30PM-9:00PM', 'Occupied', NULL, '2025-04-24 19:10:24'),
(293, '542', 'S', '7:30PM-9:00PM', 'Available', NULL, '2025-04-24 19:10:24'),
(654, '524', 'MW', '7:30AM-9:00AM', 'Available', NULL, '2025-05-01 17:31:23'),
(655, '524', 'TTH', '7:30AM-9:00AM', 'Available', NULL, '2025-05-01 17:31:23'),
(656, '524', 'F', '7:30AM-9:00AM', 'Available', NULL, '2025-05-01 17:31:23'),
(657, '524', 'S', '7:30AM-9:00AM', 'Available', NULL, '2025-05-01 17:31:23'),
(658, '524', 'MW', '9:00AM-10:30AM', 'Available', NULL, '2025-05-01 17:31:23'),
(659, '524', 'TTH', '9:00AM-10:30AM', 'Available', NULL, '2025-05-01 17:31:23'),
(660, '524', 'F', '9:00AM-10:30AM', 'Available', NULL, '2025-05-01 17:31:23'),
(661, '524', 'S', '9:00AM-10:30AM', 'Available', NULL, '2025-05-01 17:31:23'),
(662, '524', 'MW', '10:30AM-12:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(663, '524', 'TTH', '10:30AM-12:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(664, '524', 'F', '10:30AM-12:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(665, '524', 'S', '10:30AM-12:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(666, '524', 'MW', '12:00PM-1:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(667, '524', 'TTH', '12:00PM-1:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(668, '524', 'F', '12:00PM-1:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(669, '524', 'S', '12:00PM-1:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(670, '524', 'MW', '1:00PM-3:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(671, '524', 'TTH', '1:00PM-3:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(672, '524', 'F', '1:00PM-3:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(673, '524', 'S', '1:00PM-3:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(674, '524', 'MW', '3:00PM-4:30PM', 'Available', NULL, '2025-05-01 17:31:23'),
(675, '524', 'TTH', '3:00PM-4:30PM', 'Available', NULL, '2025-05-01 17:31:23'),
(676, '524', 'F', '3:00PM-4:30PM', 'Available', NULL, '2025-05-01 17:31:23'),
(677, '524', 'S', '3:00PM-4:30PM', 'Available', NULL, '2025-05-01 17:31:23'),
(678, '524', 'MW', '4:30PM-6:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(679, '524', 'TTH', '4:30PM-6:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(680, '524', 'F', '4:30PM-6:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(681, '524', 'S', '4:30PM-6:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(682, '524', 'MW', '6:00PM-7:30PM', 'Available', NULL, '2025-05-01 17:31:23'),
(683, '524', 'TTH', '6:00PM-7:30PM', 'Available', NULL, '2025-05-01 17:31:23'),
(684, '524', 'F', '6:00PM-7:30PM', 'Available', NULL, '2025-05-01 17:31:23'),
(685, '524', 'S', '6:00PM-7:30PM', 'Available', NULL, '2025-05-01 17:31:23'),
(686, '524', 'MW', '7:30PM-9:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(687, '524', 'TTH', '7:30PM-9:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(688, '524', 'F', '7:30PM-9:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(689, '524', 'S', '7:30PM-9:00PM', 'Available', NULL, '2025-05-01 17:31:23'),
(690, '526', 'MW', '7:30AM-9:00AM', 'Available', NULL, '2025-05-01 19:42:10'),
(691, '526', 'TTH', '7:30AM-9:00AM', 'Available', NULL, '2025-05-01 19:42:10'),
(692, '526', 'F', '7:30AM-9:00AM', 'Available', NULL, '2025-05-01 19:42:10'),
(693, '526', 'S', '7:30AM-9:00AM', 'Available', NULL, '2025-05-01 19:42:10'),
(694, '526', 'MW', '9:00AM-10:30AM', 'Available', NULL, '2025-05-01 19:42:10'),
(695, '526', 'TTH', '9:00AM-10:30AM', 'Available', NULL, '2025-05-01 19:42:10'),
(696, '526', 'F', '9:00AM-10:30AM', 'Available', NULL, '2025-05-01 19:42:10'),
(697, '526', 'S', '9:00AM-10:30AM', 'Available', NULL, '2025-05-01 19:42:10'),
(698, '526', 'MW', '10:30AM-12:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(699, '526', 'TTH', '10:30AM-12:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(700, '526', 'F', '10:30AM-12:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(701, '526', 'S', '10:30AM-12:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(702, '526', 'MW', '12:00PM-1:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(703, '526', 'TTH', '12:00PM-1:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(704, '526', 'F', '12:00PM-1:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(705, '526', 'S', '12:00PM-1:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(706, '526', 'MW', '1:00PM-3:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(707, '526', 'TTH', '1:00PM-3:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(708, '526', 'F', '1:00PM-3:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(709, '526', 'S', '1:00PM-3:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(710, '526', 'MW', '3:00PM-4:30PM', 'Available', NULL, '2025-05-01 19:42:10'),
(711, '526', 'TTH', '3:00PM-4:30PM', 'Available', NULL, '2025-05-01 19:42:10'),
(712, '526', 'F', '3:00PM-4:30PM', 'Available', NULL, '2025-05-01 19:42:10'),
(713, '526', 'S', '3:00PM-4:30PM', 'Available', NULL, '2025-05-01 19:42:10'),
(714, '526', 'MW', '4:30PM-6:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(715, '526', 'TTH', '4:30PM-6:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(716, '526', 'F', '4:30PM-6:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(717, '526', 'S', '4:30PM-6:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(718, '526', 'MW', '6:00PM-7:30PM', 'Available', NULL, '2025-05-01 19:42:10'),
(719, '526', 'TTH', '6:00PM-7:30PM', 'Available', NULL, '2025-05-01 19:42:10'),
(720, '526', 'F', '6:00PM-7:30PM', 'Available', NULL, '2025-05-01 19:42:10'),
(721, '526', 'S', '6:00PM-7:30PM', 'Available', NULL, '2025-05-01 19:42:10'),
(722, '526', 'MW', '7:30PM-9:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(723, '526', 'TTH', '7:30PM-9:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(724, '526', 'F', '7:30PM-9:00PM', 'Available', NULL, '2025-05-01 19:42:10'),
(725, '526', 'S', '7:30PM-9:00PM', 'Available', NULL, '2025-05-01 19:42:10');

-- --------------------------------------------------------

--
-- Table structure for table `login_records`
--

CREATE TABLE `login_records` (
  `IDNO` int(11) NOT NULL,
  `FULLNAME` varchar(255) NOT NULL,
  `TIME_IN` datetime NOT NULL,
  `TIME_OUT` datetime NOT NULL,
  `LAB_ROOM` enum('524','526','528','530','542','544') NOT NULL,
  `PURPOSE` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_records`
--

INSERT INTO `login_records` (`IDNO`, `FULLNAME`, `TIME_IN`, `TIME_OUT`, `LAB_ROOM`, `PURPOSE`) VALUES
(3232, '3 c student', '2025-03-21 00:04:50', '2025-03-21 02:05:21', '524', 'python'),
(123, 'jan di doeo', '2025-03-21 00:04:58', '2025-03-21 02:15:23', '524', 'C#'),
(133, '2 B student', '2025-03-21 00:05:16', '2025-03-21 02:17:56', '524', 'lecture'),
(4343, '1 A student', '2025-03-21 00:05:52', '2025-03-21 02:19:34', '542', 'javascript'),
(3233, '4 d student', '2025-03-21 00:06:14', '2025-03-23 22:27:07', '542', 'javascript'),
(5356, '5 E student', '2025-03-21 00:06:31', '2025-03-23 22:27:15', '530', 'java'),
(98675, '6 F student', '2025-03-21 00:08:02', '2025-03-24 13:19:45', '528', 'javascript'),
(123, 'jan di doeo', '2025-04-02 22:43:33', '2025-04-02 22:43:39', '524', 'javascript'),
(123, 'jan di doeo', '2025-04-10 03:25:10', '2025-04-10 03:25:12', '524', 'Digital logic & Design'),
(98675, '6 F student', '2025-04-10 03:25:35', '2025-04-10 03:25:53', '524', 'System Integration & Architecture'),
(3233, '4 d student', '2025-04-10 03:26:29', '2025-04-10 03:26:30', '524', 'Java Programming'),
(123, 'jan di doeo', '2025-04-24 23:01:19', '2025-04-24 23:01:20', '524', 'Select'),
(123, 'jan di doeo', '2025-04-24 23:01:32', '2025-04-24 23:01:33', '524', 'Select'),
(123, 'jan di doeo', '2025-04-24 23:01:43', '2025-04-24 23:01:51', '524', 'Select'),
(123, 'jan di doeo', '2025-04-24 23:10:58', '2025-04-24 23:11:00', '524', 'Digital logic & Design'),
(123, 'jan di doeo', '2025-04-24 23:11:07', '2025-04-24 23:11:09', '524', 'Select'),
(123, 'jan di doeo', '2025-04-24 23:11:16', '2025-04-24 23:11:18', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 02:26:47', '2025-04-25 02:27:33', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 02:29:58', '2025-04-25 02:30:38', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 02:30:57', '2025-04-25 02:30:59', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 02:35:01', '2025-04-25 02:35:11', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 02:35:21', '2025-04-25 02:35:23', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 02:35:30', '2025-04-25 02:35:32', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 02:35:41', '2025-04-25 02:35:48', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 03:09:23', '2025-04-25 08:39:25', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 08:38:55', '2025-04-25 08:39:25', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 09:09:13', '2025-04-25 09:09:15', '524', 'System Integration & Architecture'),
(123, 'jan di doeo', '2025-04-25 11:25:28', '2025-04-25 11:25:33', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 11:25:49', '2025-04-25 11:25:52', '524', 'Embedded System & IoT'),
(123, 'jan di doeo', '2025-04-25 11:26:12', '2025-04-25 11:26:13', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 11:26:25', '2025-04-25 11:26:27', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 11:26:38', '2025-04-25 11:26:43', '524', 'Select'),
(123, 'jan di doeo', '2025-04-25 11:34:27', '2025-04-26 13:55:40', '524', 'System Integration & Architecture'),
(123, 'jan di doeo', '2025-04-27 23:32:58', '2025-04-27 23:33:01', '524', 'C# Programming'),
(123, 'jan di doeo', '2025-04-27 23:40:14', '2025-04-27 23:40:22', '524', 'C# Programming'),
(123, 'jan di doeo', '2025-04-27 23:46:49', '2025-04-27 23:46:55', '524', 'Embedded System & IoT'),
(123, 'jan di doeo', '2025-04-27 23:53:39', '2025-04-27 23:53:41', '524', 'System Integration & Architecture'),
(123, 'jan di doeo', '2025-04-27 23:56:14', '2025-04-27 23:58:25', '524', 'C# Programming'),
(123, 'jan di doeo', '2025-04-28 00:05:51', '2025-04-28 00:05:59', '524', 'Java Programming'),
(123, 'jan di doeo', '2025-04-28 00:06:22', '2025-04-28 00:06:25', '524', 'C Programming'),
(123, 'jan di doeo', '2025-04-28 00:07:44', '2025-04-28 00:07:46', '524', 'C# Programming'),
(123, 'jan di doeo', '2025-04-28 00:07:59', '2025-04-28 00:08:00', '524', 'Embedded System & IoT'),
(123, 'jan di doeo', '2025-04-28 00:08:31', '2025-04-28 00:08:32', '524', 'C# Programming'),
(123, 'jan di doeo', '2025-04-28 00:13:53', '2025-04-28 00:13:54', '524', 'C Programming'),
(123, 'jan di doeo', '2025-04-28 00:14:10', '2025-04-28 00:14:13', '524', 'Embedded System & IoT'),
(123, 'jan di doeo', '2025-04-28 00:14:38', '2025-04-28 00:14:40', '524', 'Digital logic & Design'),
(123, 'jan di doeo', '2025-04-28 00:14:49', '2025-04-28 00:14:57', '524', 'C# Programming'),
(123, 'jan di doeo', '2025-04-28 00:15:10', '2025-04-28 00:15:14', '524', 'System Integration & Architecture'),
(98675, '6 F student', '2025-04-28 00:31:56', '2025-04-28 00:31:57', '524', 'Java Programming'),
(133, '2 B student', '2025-04-28 01:14:54', '2025-04-28 01:14:55', '524', 'System Integration & Architecture'),
(133, '2 B student', '2025-04-28 01:15:03', '2025-04-28 01:15:04', '524', 'Digital logic & Design'),
(3233, '4 d student', '2025-04-28 01:15:15', '2025-04-28 01:15:17', '524', 'System Integration & Architecture'),
(5356, '5 E student', '2025-04-28 01:16:05', '2025-04-28 01:16:06', '524', 'Java Programming'),
(123, 'jan di doeo', '2025-04-28 03:24:17', '2025-04-28 03:24:20', '524', 'C# Programming'),
(123, 'jan di doeo', '2025-04-28 03:24:31', '2025-04-28 03:24:32', '524', 'C# Programming'),
(123, 'jan di doeo', '2025-04-28 03:24:40', '2025-04-28 03:24:41', '524', 'C# Programming'),
(123, 'jan di doeo', '2025-04-28 03:30:59', '2025-04-28 12:43:27', '530', 'Digital logic & Design'),
(133, '2 B student', '2025-04-28 03:31:06', '2025-04-28 12:43:28', '530', 'Digital logic & Design'),
(98675, '6 F student', '2025-04-28 12:41:33', '2025-04-28 12:41:58', '524', 'C# Programming'),
(98675, '6 F student', '2025-04-28 12:42:46', '2025-04-28 12:42:47', '524', 'System Integration & Architecture'),
(133, '2 B student', '2025-04-28 12:44:00', '2025-04-28 12:44:01', '524', 'C# Programming'),
(20213005, 'recamel GEORPE FLORES', '2025-04-28 13:23:35', '2025-04-28 13:23:36', '524', 'C# Programming'),
(20213005, 'recamel GEORPE FLORES', '2025-05-01 21:59:47', '2025-05-01 22:05:44', '524', 'C# Programming'),
(20213005, 'FLORES, recamel G.', '0000-00-00 00:00:00', '2025-05-01 22:10:27', '528', 'Digital logic & Design'),
(20213005, 'FLORES, recamel G.', '0000-00-00 00:00:00', '2025-05-01 22:18:22', '524', 'Embedded System & IoT'),
(20213005, 'FLORES, recamel G.', '0000-00-00 00:00:00', '2025-05-01 22:20:50', '524', 'C# Programming'),
(20213005, 'FLORES, recamel G.', '2025-05-01 07:30:00', '2025-05-01 22:26:16', '524', 'C# Programming'),
(20213005, 'FLORES, recamel G.', '2025-05-01 00:00:00', '2025-05-01 22:27:27', '524', 'C# Programming'),
(20213005, 'FLORES, recamel G.', '1970-01-01 08:00:00', '2025-05-01 22:31:44', '524', 'Embedded System & IoT'),
(20213005, 'FLORES, recamel G.', '0000-00-00 00:00:00', '2025-05-02 01:32:15', '524', 'Embedded System & IoT'),
(20213005, 'FLORES, recamel G.', '0000-00-00 00:00:00', '2025-05-02 01:32:15', '524', 'Embedded System & IoT'),
(20213005, 'FLORES, recamel G.', '0000-00-00 00:00:00', '2025-05-02 01:49:02', '524', 'python'),
(20213005, 'recamel GEORPE FLORES', '2025-05-02 01:49:09', '2025-05-02 01:49:13', '524', 'Java Programming'),
(20213005, 'FLORES, recamel G.', '0000-00-00 00:00:00', '2025-05-02 02:00:14', '524', 'Embedded System & IoT'),
(20213005, 'FLORES, recamel G.', '0000-00-00 00:00:00', '2025-05-02 02:01:49', '524', 'C# Programming'),
(20213005, 'FLORES, recamel G.', '0000-00-00 00:00:00', '2025-05-02 02:09:27', '524', 'C Programming'),
(20213005, 'FLORES, recamel G.', '0000-00-00 00:00:00', '2025-05-02 02:13:09', '524', 'Digital logic & Design'),
(20213005, 'FLORES, recamel G.', '2025-05-02 02:13:33', '2025-05-02 02:25:12', '524', 'C# Programming'),
(20213005, 'FLORES, recamel G.', '2025-05-02 02:25:30', '2025-05-02 02:33:19', '524', 'Embedded System & IoT'),
(20213005, 'FLORES, recamel G.', '2025-05-02 02:30:17', '2025-05-02 02:33:19', '524', 'C# Programming'),
(20213005, 'FLORES, recamel G.', '2025-05-02 02:34:24', '2025-05-02 03:43:23', '524', 'C# Programming'),
(20213005, 'FLORES, recamel G.', '2025-05-02 02:38:45', '2025-05-02 03:43:23', '524', 'C# Programming'),
(20213005, 'FLORES, recamel G.', '2025-05-02 02:38:55', '2025-05-02 03:43:23', '524', 'C Programming'),
(20213005, 'FLORES, recamel G.', '2025-05-02 03:43:11', '2025-05-02 03:43:23', '526', 'Embedded System & IoT'),
(20213005, 'FLORES, recamel G.', '2025-05-02 03:51:22', '2025-05-02 03:51:38', '524', 'Select'),
(20213005, 'FLORES, recamel G.', '2025-05-02 03:52:02', '2025-05-02 03:54:31', '524', 'C# Programming'),
(20213005, 'FLORES, recamel G.', '2025-05-02 03:58:29', '2025-05-02 03:58:44', '524', 'Embedded System & IoT'),
(20213005, 'FLORES, recamel G.', '2025-05-02 04:01:58', '2025-05-02 04:02:10', '524', 'Java Programming'),
(20213005, 'FLORES, recamel G.', '2025-05-02 04:22:10', '2025-05-02 04:22:17', '524', 'C# Programming'),
(20213005, 'FLORES, recamel G.', '2025-05-02 04:35:25', '2025-05-02 22:39:00', '524', 'Embedded System & IoT'),
(20213005, 'FLORES, recamel G.', '2025-05-02 22:53:12', '0000-00-00 00:00:00', '528', 'C Programming'),
(20213005, 'FLORES, recamel G.', '2025-05-03 02:08:10', '0000-00-00 00:00:00', '524', 'C# Programming');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) DEFAULT 'general',
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `details`, `is_read`, `created_at`) VALUES
(40, 20213005, 'You received 1 point for completing your lab session', 'points', NULL, 1, '2025-04-28 05:23:36'),
(41, 20213005, 'Your reservation for Room 524 has been rejected.', 'reservation', NULL, 1, '2025-04-30 20:06:14'),
(42, 20213005, 'Your reservation for Room 524, PC 2 has been approved.', 'reservation', NULL, 1, '2025-05-01 07:13:48'),
(43, 20213005, 'Your reservation for Room 524, PC 1 has been approved.', 'reservation', NULL, 1, '2025-05-01 13:29:43'),
(44, 20213005, 'You received 1 point for completing your lab session', 'points', NULL, 1, '2025-05-01 14:05:44'),
(45, 20213005, 'You received 1 point for completing your lab session', 'points', NULL, 1, '2025-05-01 14:18:22'),
(46, 20213005, 'Congratulations! You\'ve earned a new session for collecting 3 points!', 'session', NULL, 1, '2025-05-01 14:18:22'),
(47, 20213005, 'You received 1 point for completing your lab session', 'points', NULL, 1, '2025-05-01 17:49:02'),
(48, 20213005, 'You received 1 point for completing your lab session', 'points', NULL, 1, '2025-05-01 18:25:12'),
(49, 20213005, 'Your reservation has been approved', 'approval', '{\"room\":\"524\",\"pc_number\":15,\"date\":\"May 02, 2025\",\"time\":\"07:30 AM\"}', 1, '2025-05-01 19:58:29'),
(50, NULL, 'Your reservation has been rejected', 'rejection', '{\"room\":null,\"pc_number\":null,\"date\":\"Jan 01, 1970\",\"time\":\"08:00 AM\"}', 0, '2025-05-01 19:59:04'),
(51, 20213005, 'Your reservation has been rejected', 'rejection', '{\"room\":\"524\",\"pc_number\":14,\"date\":\"May 02, 2025\",\"time\":\"07:30 AM\"}', 1, '2025-05-01 20:01:16'),
(52, 20213005, 'Your reservation has been approved', 'approval', '{\"room\":\"524\",\"pc_number\":4,\"date\":\"May 09, 2025\",\"time\":\"07:30 AM\"}', 1, '2025-05-01 20:01:58'),
(53, 20213005, 'You received 1 point for completing your lab session', 'points', NULL, 1, '2025-05-01 20:02:10'),
(54, 20213005, 'Congratulations! You\'ve earned a new session for collecting 3 points!', 'session', NULL, 1, '2025-05-01 20:02:10'),
(55, 20213005, 'Your reservation has been approved', 'approval', '{\"room\":\"524\",\"pc_number\":14,\"date\":\"May 02, 2025\",\"time\":\"07:30 AM\"}', 1, '2025-05-01 20:22:10'),
(56, 20213005, 'Your reservation has been rejected', 'rejection', '{\"room\":\"524\",\"pc_number\":16,\"date\":\"May 02, 2025\",\"time\":\"07:30 AM\"}', 1, '2025-05-01 20:22:47'),
(57, 20213005, 'Your reservation has been approved', 'approval', '{\"room\":\"524\",\"pc_number\":17,\"date\":\"May 02, 2025\",\"time\":\"07:30 AM\"}', 1, '2025-05-01 20:35:25'),
(58, 20213005, 'Your reservation has been approved', 'approval', '{\"room\":\"528\",\"pc_number\":4,\"date\":\"May 02, 2025\",\"time\":\"09:00 AM\"}', 1, '2025-05-02 14:53:12'),
(59, 20213005, 'Your reservation has been rejected', 'rejection', '{\"room\":\"524\",\"pc_number\":1,\"date\":\"May 02, 2025\",\"time\":\"04:00 PM\"}', 1, '2025-05-02 15:06:07'),
(60, 20213005, 'Your reservation has been approved', 'approval', '{\"room\":\"524\",\"pc_number\":1,\"date\":\"May 03, 2025\",\"time\":\"07:30 AM\"}', 1, '2025-05-02 18:08:10'),
(61, 20213005, 'Your reservation has been rejected', 'rejection', '{\"room\":\"524\",\"pc_number\":2,\"date\":\"May 03, 2025\",\"time\":\"07:30 AM\"}', 1, '2025-05-02 18:23:06');

-- --------------------------------------------------------

--
-- Table structure for table `pc_status`
--

CREATE TABLE `pc_status` (
  `id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `pc_number` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pc_status`
--

INSERT INTO `pc_status` (`id`, `room_number`, `pc_number`, `status`, `last_updated`) VALUES
(1, '524', 1, 'used', '2025-05-02 18:08:10'),
(2, '528', 1, 'used', '2025-05-01 13:52:35'),
(3, '528', 2, 'used', '2025-05-01 13:52:35'),
(4, '528', 3, 'maintenance', '2025-04-30 19:15:55'),
(5, '528', 4, 'used', '2025-05-02 14:53:12'),
(6, '528', 5, 'available', '2025-05-01 13:52:39'),
(7, '528', 6, 'used', '2025-05-01 13:52:35'),
(8, '528', 7, 'used', '2025-05-01 13:52:35'),
(9, '528', 8, 'maintenance', '2025-04-30 19:15:55'),
(10, '528', 9, 'available', '2025-05-01 13:52:39'),
(11, '528', 10, 'available', '2025-05-01 13:52:39'),
(12, '528', 11, 'used', '2025-05-01 13:52:35'),
(13, '528', 12, 'used', '2025-05-01 13:52:35'),
(14, '528', 13, 'maintenance', '2025-04-30 19:15:55'),
(15, '528', 14, 'available', '2025-05-01 13:52:39'),
(16, '528', 15, 'available', '2025-05-01 13:52:39'),
(17, '528', 16, 'used', '2025-05-01 13:52:35'),
(18, '528', 17, 'used', '2025-05-01 13:52:35'),
(19, '528', 18, 'maintenance', '2025-04-30 19:15:55'),
(20, '528', 19, 'available', '2025-05-01 13:52:39'),
(21, '528', 20, 'available', '2025-05-01 13:52:39'),
(22, '528', 21, 'maintenance', '2025-04-30 19:15:55'),
(23, '528', 22, 'maintenance', '2025-04-30 19:15:55'),
(24, '528', 23, 'maintenance', '2025-04-30 19:15:55'),
(25, '528', 24, 'maintenance', '2025-04-30 19:15:55'),
(26, '528', 25, 'maintenance', '2025-04-30 19:15:55'),
(27, '528', 26, 'maintenance', '2025-04-30 19:15:55'),
(28, '528', 27, 'maintenance', '2025-04-30 19:15:55'),
(29, '528', 28, 'maintenance', '2025-04-30 19:15:55'),
(30, '528', 29, 'maintenance', '2025-04-30 19:15:55'),
(31, '528', 30, 'maintenance', '2025-04-30 19:15:55'),
(32, '528', 31, 'maintenance', '2025-04-30 19:15:55'),
(33, '528', 32, 'maintenance', '2025-04-30 19:15:55'),
(34, '528', 33, 'maintenance', '2025-04-30 19:15:55'),
(35, '528', 34, 'maintenance', '2025-04-30 19:15:55'),
(36, '528', 35, 'maintenance', '2025-04-30 19:15:55'),
(37, '528', 36, 'maintenance', '2025-04-30 19:15:55'),
(38, '528', 37, 'maintenance', '2025-04-30 19:15:55'),
(39, '528', 38, 'maintenance', '2025-04-30 19:15:55'),
(40, '528', 39, 'maintenance', '2025-04-30 19:15:55'),
(41, '528', 40, 'maintenance', '2025-04-30 19:15:55'),
(122, '', 1, 'used', '2025-04-30 19:16:46'),
(123, '', 2, 'used', '2025-04-30 19:16:46'),
(124, '', 3, 'used', '2025-04-30 19:16:46'),
(125, '', 4, 'used', '2025-04-30 19:16:46'),
(126, '', 5, 'used', '2025-04-30 19:16:46'),
(127, '', 6, 'used', '2025-04-30 19:16:46'),
(128, '', 7, 'used', '2025-04-30 19:16:46'),
(129, '', 8, 'used', '2025-04-30 19:16:46'),
(130, '', 9, 'used', '2025-04-30 19:16:46'),
(131, '', 10, 'used', '2025-04-30 19:16:46'),
(132, '', 11, 'used', '2025-04-30 19:16:46'),
(133, '', 12, 'used', '2025-04-30 19:16:46'),
(134, '', 13, 'used', '2025-04-30 19:16:46'),
(135, '', 14, 'used', '2025-04-30 19:16:46'),
(136, '', 15, 'used', '2025-04-30 19:16:46'),
(137, '', 16, 'used', '2025-04-30 19:16:46'),
(138, '', 17, 'used', '2025-04-30 19:16:46'),
(139, '', 18, 'used', '2025-04-30 19:16:46'),
(140, '', 19, 'used', '2025-04-30 19:16:46'),
(141, '', 20, 'used', '2025-04-30 19:16:46'),
(142, '', 21, 'used', '2025-04-30 19:16:46'),
(143, '', 22, 'used', '2025-04-30 19:16:46'),
(144, '', 23, 'used', '2025-04-30 19:16:46'),
(145, '', 24, 'used', '2025-04-30 19:16:46'),
(146, '', 25, 'used', '2025-04-30 19:16:46'),
(147, '', 26, 'used', '2025-04-30 19:16:46'),
(148, '', 27, 'used', '2025-04-30 19:16:46'),
(149, '', 28, 'used', '2025-04-30 19:16:46'),
(150, '', 29, 'used', '2025-04-30 19:16:47'),
(151, '', 30, 'used', '2025-04-30 19:16:47'),
(152, '', 31, 'used', '2025-04-30 19:16:47'),
(153, '', 32, 'used', '2025-04-30 19:16:47'),
(154, '', 33, 'used', '2025-04-30 19:16:47'),
(155, '', 34, 'used', '2025-04-30 19:16:47'),
(156, '', 35, 'used', '2025-04-30 19:16:47'),
(157, '', 36, 'used', '2025-04-30 19:16:47'),
(158, '', 37, 'used', '2025-04-30 19:16:47'),
(159, '', 38, 'used', '2025-04-30 19:16:47'),
(160, '', 39, 'used', '2025-04-30 19:16:47'),
(161, '', 40, 'used', '2025-04-30 19:16:47'),
(203, '524', 2, 'available', '2025-05-01 20:38:45'),
(204, '524', 3, 'available', '2025-05-01 20:38:45'),
(205, '524', 4, 'available', '2025-05-01 20:38:45'),
(206, '524', 5, 'available', '2025-05-01 20:38:45'),
(207, '524', 6, 'available', '2025-05-01 20:38:45'),
(208, '524', 7, 'available', '2025-05-01 20:38:45'),
(209, '524', 8, 'available', '2025-05-01 20:38:45'),
(210, '524', 9, 'available', '2025-05-01 20:38:45'),
(211, '524', 10, 'available', '2025-05-01 20:38:45'),
(212, '524', 11, 'available', '2025-05-01 20:38:45'),
(213, '524', 12, 'available', '2025-05-01 20:38:45'),
(214, '524', 13, 'available', '2025-05-01 20:38:45'),
(215, '524', 14, 'available', '2025-05-01 20:38:45'),
(216, '524', 15, 'available', '2025-05-01 20:38:45'),
(217, '524', 16, 'available', '2025-05-01 20:38:45'),
(218, '524', 17, 'available', '2025-05-01 20:38:45'),
(219, '524', 18, 'available', '2025-05-01 20:38:45'),
(220, '524', 19, 'available', '2025-05-01 20:38:45'),
(221, '524', 20, 'available', '2025-05-01 20:38:45'),
(222, '524', 21, 'available', '2025-05-01 20:38:45'),
(223, '524', 22, 'available', '2025-05-01 20:38:45'),
(224, '524', 23, 'available', '2025-05-01 20:38:45'),
(225, '524', 24, 'available', '2025-05-01 20:38:45'),
(226, '524', 25, 'available', '2025-05-01 20:38:45'),
(227, '524', 26, 'available', '2025-05-01 20:38:45'),
(228, '524', 27, 'available', '2025-05-01 20:38:45'),
(229, '524', 28, 'available', '2025-05-01 20:38:45'),
(230, '524', 29, 'available', '2025-05-01 20:38:45'),
(231, '524', 30, 'available', '2025-05-01 20:38:45'),
(232, '524', 31, 'available', '2025-05-01 20:38:45'),
(233, '524', 32, 'available', '2025-05-01 20:38:45'),
(234, '524', 33, 'available', '2025-05-01 20:38:45'),
(235, '524', 34, 'available', '2025-05-01 20:38:45'),
(236, '524', 35, 'available', '2025-05-01 20:38:45'),
(237, '524', 36, 'available', '2025-05-01 20:38:45'),
(238, '524', 37, 'available', '2025-05-01 20:38:45'),
(239, '524', 38, 'available', '2025-05-01 20:38:45'),
(240, '524', 39, 'available', '2025-05-01 20:38:45'),
(241, '524', 40, 'available', '2025-05-01 20:38:45'),
(242, '526', 1, 'maintenance', '2025-05-01 09:54:50'),
(243, '526', 2, 'maintenance', '2025-05-01 09:54:50'),
(244, '526', 3, 'available', '2025-05-01 09:49:28'),
(245, '526', 4, 'available', '2025-05-01 09:49:28'),
(246, '526', 5, 'used', '2025-05-01 19:43:11'),
(247, '526', 6, 'available', '2025-04-30 19:24:17'),
(248, '526', 7, 'available', '2025-05-01 09:49:28'),
(249, '526', 8, 'available', '2025-05-01 09:49:28'),
(250, '526', 9, 'available', '2025-05-01 09:49:28'),
(251, '526', 10, 'available', '2025-04-30 19:24:17'),
(252, '526', 11, 'available', '2025-04-30 19:24:17'),
(253, '526', 12, 'available', '2025-04-30 19:24:17'),
(254, '526', 13, 'available', '2025-04-30 19:24:17'),
(255, '526', 14, 'available', '2025-04-30 19:24:17'),
(256, '526', 15, 'available', '2025-04-30 19:24:17'),
(257, '526', 16, 'available', '2025-04-30 19:24:17'),
(258, '526', 17, 'available', '2025-04-30 19:24:17'),
(259, '526', 18, 'available', '2025-04-30 19:24:17'),
(260, '526', 19, 'available', '2025-04-30 19:24:17'),
(261, '526', 20, 'available', '2025-04-30 19:24:17'),
(262, '526', 21, 'available', '2025-04-30 19:24:17'),
(263, '526', 22, 'available', '2025-04-30 19:24:17'),
(264, '526', 23, 'available', '2025-04-30 19:24:17'),
(265, '526', 24, 'available', '2025-04-30 19:24:17'),
(266, '526', 25, 'available', '2025-04-30 19:24:17'),
(267, '526', 26, 'available', '2025-04-30 19:24:17'),
(268, '526', 27, 'available', '2025-04-30 19:24:17'),
(269, '526', 28, 'available', '2025-04-30 19:24:17'),
(270, '526', 29, 'available', '2025-04-30 19:24:17'),
(271, '526', 30, 'available', '2025-04-30 19:24:17'),
(272, '526', 31, 'available', '2025-04-30 19:24:17'),
(273, '526', 32, 'available', '2025-04-30 19:24:17'),
(274, '526', 33, 'available', '2025-04-30 19:24:17'),
(275, '526', 34, 'available', '2025-04-30 19:24:17'),
(276, '526', 35, 'available', '2025-04-30 19:24:17'),
(277, '526', 36, 'available', '2025-04-30 19:24:17'),
(278, '526', 37, 'available', '2025-04-30 19:24:17'),
(279, '526', 38, 'available', '2025-04-30 19:24:17'),
(280, '526', 39, 'available', '2025-04-30 19:24:17'),
(281, '526', 40, 'available', '2025-04-30 19:24:17'),
(366, '530', 1, 'maintenance', '2025-05-01 09:40:34'),
(367, '530', 2, 'maintenance', '2025-05-01 09:40:34'),
(368, '530', 3, 'maintenance', '2025-05-01 09:40:34'),
(369, '530', 4, 'maintenance', '2025-05-01 09:40:34'),
(370, '530', 5, 'maintenance', '2025-05-01 09:40:34'),
(371, '530', 6, 'maintenance', '2025-05-01 09:40:34'),
(372, '530', 7, 'maintenance', '2025-05-01 09:40:34'),
(373, '530', 8, 'maintenance', '2025-05-01 09:40:34'),
(374, '530', 9, 'maintenance', '2025-05-01 09:40:34'),
(375, '530', 10, 'maintenance', '2025-05-01 09:40:34');

-- --------------------------------------------------------

--
-- Table structure for table `points_history`
--

CREATE TABLE `points_history` (
  `ID` int(11) NOT NULL,
  `IDNO` varchar(20) NOT NULL,
  `FULLNAME` varchar(100) NOT NULL,
  `POINTS_EARNED` int(11) DEFAULT 1,
  `CONVERTED_TO_SESSION` tinyint(1) DEFAULT 0,
  `CONVERSION_DATE` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `points_history`
--

INSERT INTO `points_history` (`ID`, `IDNO`, `FULLNAME`, `POINTS_EARNED`, `CONVERTED_TO_SESSION`, `CONVERSION_DATE`) VALUES
(1, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-24 00:14:54'),
(2, '20213005', 'Recamel Georpe Flores', 3, 1, '2025-04-24 00:17:59'),
(3, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-24 00:18:19'),
(4, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-24 00:18:26'),
(5, '20213005', 'Recamel Georpe Flores', 3, 1, '2025-04-24 00:18:54'),
(6, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-24 00:21:40'),
(7, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-24 00:21:47'),
(8, '20213005', 'Recamel Georpe Flores', 3, 1, '2025-04-24 00:22:11'),
(9, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-24 00:26:23'),
(10, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-24 00:26:36'),
(11, '20213005', 'Recamel Georpe Flores', 3, 1, '2025-04-24 00:26:43'),
(12, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-24 00:36:35'),
(13, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-24 00:37:11'),
(14, '20213005', 'Recamel Georpe Flores', 3, 1, '2025-04-24 00:37:31'),
(15, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-24 00:39:35'),
(16, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-24 00:39:59'),
(17, '20213005', 'Recamel Georpe Flores', 6, 1, '2025-04-24 00:40:16'),
(18, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-24 02:01:14'),
(19, '121003', 'karisse Flores Georpe', 1, 0, '2025-04-24 02:13:21'),
(20, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-24 02:36:21'),
(21, '121003', 'karisse Flores Georpe', 1, 0, '2025-04-24 02:36:58'),
(22, '20213005', 'Recamel Georpe Flores', 9, 1, '2025-04-24 02:38:10'),
(23, '123', 'jan di doeo', 1, 0, '2025-04-24 23:01:21'),
(24, '123', 'jan di doeo', 1, 0, '2025-04-24 23:01:33'),
(25, '123', 'jan di doeo', 3, 1, '2025-04-24 23:01:51'),
(26, '123', 'jan di doeo', 1, 0, '2025-04-24 23:11:00'),
(27, '123', 'jan di doeo', 1, 0, '2025-04-24 23:11:09'),
(28, '123', 'jan di doeo', 6, 1, '2025-04-24 23:11:18'),
(29, '123', 'jan di doeo', 1, 0, '2025-04-25 02:30:38'),
(30, '123', 'jan di doeo', 1, 0, '2025-04-25 02:30:59'),
(31, '123', 'jan di doeo', 9, 1, '2025-04-25 02:35:11'),
(32, '123', 'jan di doeo', 1, 0, '2025-04-25 02:35:23'),
(33, '123', 'jan di doeo', 1, 0, '2025-04-25 02:35:32'),
(34, '123', 'jan di doeo', 12, 1, '2025-04-25 02:35:48'),
(35, '123', 'jan di doeo', 1, 0, '2025-04-25 09:09:15'),
(36, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-25 09:12:22'),
(37, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-25 09:12:37'),
(38, '20213005', 'Recamel Georpe Flores', 12, 1, '2025-04-25 09:12:51'),
(39, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-25 09:13:07'),
(40, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-25 09:13:21'),
(41, '20213005', 'Recamel Georpe Flores', 15, 1, '2025-04-25 09:13:52'),
(42, '123', 'jan di doeo', 1, 0, '2025-04-25 11:25:33'),
(43, '123', 'jan di doeo', 15, 1, '2025-04-25 11:25:52'),
(44, '123', 'jan di doeo', 1, 0, '2025-04-25 11:26:13'),
(45, '123', 'jan di doeo', 1, 0, '2025-04-25 11:26:27'),
(46, '123', 'jan di doeo', 18, 1, '2025-04-25 11:26:43'),
(47, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-25 11:33:50'),
(48, '123', 'jan di doeo', 1, 0, '2025-04-26 13:55:40'),
(49, '123', 'jan di doeo', 1, 0, '2025-04-27 23:33:01'),
(50, '123', 'jan di doeo', 21, 1, '2025-04-27 23:40:22'),
(51, '123', 'jan di doeo', 1, 0, '2025-04-27 23:46:55'),
(52, '123', 'jan di doeo', 1, 0, '2025-04-27 23:53:41'),
(53, '123', 'jan di doeo', 24, 1, '2025-04-27 23:58:25'),
(54, '123', 'jan di doeo', 1, 0, '2025-04-28 00:05:59'),
(55, '123', 'jan di doeo', 1, 0, '2025-04-28 00:06:25'),
(56, '123', 'jan di doeo', 27, 1, '2025-04-28 00:08:00'),
(57, '123', 'jan di doeo', 1, 0, '2025-04-28 00:08:32'),
(58, '123', 'jan di doeo', 1, 0, '2025-04-28 00:13:54'),
(59, '123', 'jan di doeo', 30, 1, '2025-04-28 00:14:13'),
(60, '123', 'jan di doeo', 1, 0, '2025-04-28 00:14:40'),
(61, '123', 'jan di doeo', 1, 0, '2025-04-28 00:14:57'),
(62, '123', 'jan di doeo', 33, 1, '2025-04-28 00:15:14'),
(63, '98675', '6 F student', 1, 0, '2025-04-28 00:31:57'),
(64, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-28 00:46:37'),
(65, '20213005', 'Recamel Georpe Flores', 18, 1, '2025-04-28 00:50:37'),
(66, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-28 00:51:31'),
(67, '133', '2 B student', 1, 0, '2025-04-28 01:14:55'),
(68, '133', '2 B student', 1, 0, '2025-04-28 01:15:04'),
(69, '3233', '4 d student', 1, 0, '2025-04-28 01:15:17'),
(70, '5356', '5 E student', 1, 0, '2025-04-28 01:16:06'),
(71, '20213005', 'Recamel Georpe Flores', 1, 0, '2025-04-28 02:39:44'),
(72, '123', 'jan di doeo', 1, 0, '2025-04-28 03:24:20'),
(73, '123', 'jan di doeo', 1, 0, '2025-04-28 03:24:32'),
(74, '123', 'jan di doeo', 36, 1, '2025-04-28 03:24:41'),
(75, '20213005', 'Recamel Georpe Flores', 21, 1, '2025-04-28 12:41:36'),
(76, '98675', '6 F student', 1, 0, '2025-04-28 12:41:58'),
(77, '121003', 'karisse Flores Georpe', 3, 1, '2025-04-28 12:42:33'),
(78, '98675', '6 F student', 3, 1, '2025-04-28 12:42:47'),
(79, '123', 'jan di doeo', 1, 0, '2025-04-28 12:43:27'),
(80, '133', '2 B student', 3, 1, '2025-04-28 12:43:28'),
(81, '133', '2 B student', 1, 0, '2025-04-28 12:44:01'),
(82, '20213005', 'recamel GEORPE FLORES', 1, 0, '2025-04-28 13:23:36'),
(83, '20213005', 'recamel GEORPE FLORES', 1, 0, '2025-05-01 22:05:44'),
(84, '20213005', 'recamel GEORPE FLORES', 3, 1, '2025-05-01 22:18:22'),
(85, '20213005', 'recamel GEORPE FLORES', 1, 0, '2025-05-02 01:49:02'),
(86, '20213005', 'recamel GEORPE FLORES', 1, 0, '2025-05-02 02:25:12'),
(87, '20213005', 'recamel GEORPE FLORES', 6, 1, '2025-05-02 04:02:10');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `purpose` varchar(100) DEFAULT NULL,
  `seat_number` int(11) DEFAULT NULL,
  `remaining_sessions` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `student_id`, `fullname`, `room`, `date`, `time`, `purpose`, `seat_number`, `remaining_sessions`, `status`, `created_at`) VALUES
(3, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-01', '09:00:00', 'Embedded System & IoT', 2, 29, 'rejected', '2025-04-30 18:08:54'),
(4, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-01', '09:00:00', 'C Programming', 1, 29, 'approved', '2025-04-30 19:13:39'),
(5, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'System Integration & Architecture', 2, 29, 'approved', '2025-05-01 07:13:39'),
(6, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-01', '17:30:00', 'Embedded System & IoT', 1, 29, 'approved', '2025-05-01 13:29:25'),
(7, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-01', '09:00:00', 'C# Programming', 3, 29, 'rejected', '2025-05-01 13:47:20'),
(8, '20213005', 'recamel GEORPE FLORES', '528', '2025-05-02', '14:30:00', 'Digital logic & Design', 4, 28, 'approved', '2025-05-01 14:06:03'),
(9, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-01', '07:30:00', 'Embedded System & IoT', 4, 27, 'approved', '2025-05-01 14:11:39'),
(10, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-01', '07:30:00', 'C# Programming', 3, 27, 'approved', '2025-05-01 14:18:57'),
(11, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-01', '07:30:00', 'C# Programming', 2, 26, 'approved', '2025-05-01 14:21:10'),
(12, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-01', '07:30:00', 'C# Programming', 9, 25, 'approved', '2025-05-01 14:26:27'),
(13, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-01', '07:30:00', 'Embedded System & IoT', 7, 24, 'approved', '2025-05-01 14:27:38'),
(14, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-01', '07:30:00', 'C# Programming', 8, 23, 'rejected', '2025-05-01 14:33:10'),
(15, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-01', '07:30:00', 'Embedded System & IoT', 8, 23, '', '2025-05-01 15:45:49'),
(16, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'System Integration & Architecture', 3, 23, 'rejected', '2025-05-01 16:03:57'),
(17, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'Embedded System & IoT', 1, 23, 'rejected', '2025-05-01 16:09:58'),
(18, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'C#', 3, 23, 'rejected', '2025-05-01 16:16:07'),
(19, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'python', 3, 23, 'rejected', '2025-05-01 16:21:00'),
(20, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'Embedded System & IoT', 3, 23, 'rejected', '2025-05-01 16:46:11'),
(21, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'Embedded System & IoT', 3, 23, 'approved', '2025-05-01 17:23:02'),
(22, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '09:00:00', 'Embedded System & IoT', 14, 23, 'approved', '2025-05-01 17:31:36'),
(23, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'python', 1, 22, 'approved', '2025-05-01 17:32:28'),
(24, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'Embedded System & IoT', 4, 20, 'approved', '2025-05-01 17:52:32'),
(25, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '09:00:00', 'C# Programming', 3, 19, 'approved', '2025-05-01 18:00:26'),
(26, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'C Programming', 5, 18, 'approved', '2025-05-01 18:02:19'),
(27, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'Digital logic & Design', 6, 17, 'approved', '2025-05-01 18:09:41'),
(28, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'C# Programming', 7, 16, 'approved', '2025-05-01 18:13:23'),
(29, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'Embedded System & IoT', 8, 15, 'approved', '2025-05-01 18:25:23'),
(30, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'C# Programming', 9, 15, 'approved', '2025-05-01 18:30:13'),
(31, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'C# Programming', 10, 14, 'approved', '2025-05-01 18:34:18'),
(32, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'C Programming', 11, 14, 'approved', '2025-05-01 18:38:21'),
(33, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'C# Programming', 12, 14, 'approved', '2025-05-01 18:38:32'),
(34, '20213005', 'recamel GEORPE FLORES', '526', '2025-05-02', '07:30:00', 'Embedded System & IoT', 5, 14, 'approved', '2025-05-01 19:42:22'),
(35, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'Select', 13, 13, 'approved', '2025-05-01 19:51:13'),
(36, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '09:00:00', 'C# Programming', 6, 12, 'approved', '2025-05-01 19:51:55'),
(37, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'Embedded System & IoT', 15, 11, 'approved', '2025-05-01 19:54:44'),
(38, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-03', '07:30:00', 'C Programming', 7, 10, 'rejected', '2025-05-01 19:58:59'),
(39, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'Embedded System & IoT', 14, 10, 'rejected', '2025-05-01 20:01:12'),
(40, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-09', '07:30:00', 'Java Programming', 4, 10, 'approved', '2025-05-01 20:01:49'),
(41, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'C# Programming', 14, 10, 'approved', '2025-05-01 20:22:05'),
(42, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'C# Programming', 16, 9, 'rejected', '2025-05-01 20:22:31'),
(43, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'C# Programming', 16, 9, '', '2025-05-01 20:26:35'),
(44, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '07:30:00', 'Embedded System & IoT', 17, 9, 'approved', '2025-05-01 20:35:15'),
(45, '20213005', 'recamel GEORPE FLORES', '528', '2025-05-02', '09:00:00', 'C Programming', 4, 8, 'approved', '2025-05-02 14:49:56'),
(46, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-02', '16:00:00', 'C Programming', 1, 8, 'rejected', '2025-05-02 15:05:58'),
(47, '20213005', 'recamel GEORPE FLORES', '524', '2025-05-03', '07:30:00', 'C# Programming', 1, 8, 'approved', '2025-05-02 18:08:02'),
(48, '20213005', 'RECAMEL GEORPE FLORES', '524', '2025-05-03', '07:30:00', 'Embedded System & IoT', 2, 8, 'rejected', '2025-05-02 18:20:15'),
(49, '20213005', 'RECAMEL GEORPE FLORES', '524', '2025-05-03', '07:30:00', 'C# Programming', 2, 8, '', '2025-05-02 18:24:22'),
(50, '20213005', 'RECAMEL GEORPE FLORES', '524', '2025-05-03', '09:00:00', 'Select', 2, 8, 'pending', '2025-05-02 18:30:04');

--
-- Triggers `reservations`
--
DELIMITER $$
CREATE TRIGGER `before_reservation_insert` BEFORE INSERT ON `reservations` FOR EACH ROW BEGIN
    DECLARE student_exists INT;
    SELECT COUNT(*) INTO student_exists 
    FROM user 
    WHERE IDNO = NEW.student_id;
    
    IF student_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid student ID';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `reward_points`
--

CREATE TABLE `reward_points` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `last_reward_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reward_points`
--

INSERT INTO `reward_points` (`id`, `student_id`, `points`, `last_reward_date`) VALUES
(2, '98675', 1, '2025-04-09');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `IDNO` int(11) NOT NULL,
  `LASTNAME` varchar(255) NOT NULL,
  `FIRSTNAME` varchar(255) NOT NULL,
  `MIDNAME` varchar(255) NOT NULL,
  `COURSE` varchar(255) NOT NULL,
  `YEARLEVEL` int(11) NOT NULL,
  `USERNAME` varchar(255) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `PROFILE_PIC` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `REMAINING_SESSIONS` int(30) NOT NULL DEFAULT 30,
  `POINTS` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`IDNO`, `LASTNAME`, `FIRSTNAME`, `MIDNAME`, `COURSE`, `YEARLEVEL`, `USERNAME`, `PASSWORD`, `EMAIL`, `PROFILE_PIC`, `REMAINING_SESSIONS`, `POINTS`) VALUES
(20213005, 'FLORES', 'RECAMEL', 'GEORPE', 'BSIT', 3, 'recamel', '1234', 'flores@gmail.com', 'default.jpg', 8, 6);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lab_resources`
--
ALTER TABLE `lab_resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_schedules`
--
ALTER TABLE `lab_schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pc_status`
--
ALTER TABLE `pc_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_pc` (`room_number`,`pc_number`);

--
-- Indexes for table `points_history`
--
ALTER TABLE `points_history`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDNO` (`IDNO`),
  ADD KEY `CONVERSION_DATE` (`CONVERSION_DATE`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `reward_points`
--
ALTER TABLE `reward_points`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`IDNO`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lab_resources`
--
ALTER TABLE `lab_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `lab_schedules`
--
ALTER TABLE `lab_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=726;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `pc_status`
--
ALTER TABLE `pc_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=890;

--
-- AUTO_INCREMENT for table `points_history`
--
ALTER TABLE `points_history`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `reward_points`
--
ALTER TABLE `reward_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`IDNO`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
