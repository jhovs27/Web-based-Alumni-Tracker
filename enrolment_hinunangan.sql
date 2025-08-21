-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 17, 2025 at 01:48 PM
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
-- Database: `enrolment_hinunangan`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `mobile_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_photo_path` varchar(255) DEFAULT 'uploads/profile_photos/default-avatar.png',
  `role` varchar(50) NOT NULL DEFAULT 'Admin',
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `last_login` datetime DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`, `mobile_number`, `address`, `profile_photo_path`, `role`, `status`, `last_login`, `reset_token`, `reset_token_expires`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$JiuaW9z/iQRcj4v7fwjnc.ZB3wslPHchw8BakCrMWglkCdQQJPsoq', '2025-06-24 16:10:14', '', '', 'uploads/profile_photos/68692ff403119_view-old-tree-lake-with-snow-covered-mountains-cloudy-day.jpg', 'Admin', 'Active', NULL, NULL, NULL),
(2, 'Jhovan', 'jhovanbalbuena27@gmail.com', '$2y$10$aMexDnlOOJTq5vHx9iqP8ey21Pv.JPhCx7qL.i7mbRggjPiXzWHfO', '2025-06-24 16:59:34', NULL, NULL, 'uploads/profile_photos/default-avatar.png', 'Admin', 'Inactive', NULL, '5923c8f87301a5c2df7a98a0fb18f6713c0d4d5b076fdccd7c9c8653a2951804', '2025-06-26 16:09:34'),
(3, 'admin2', 'admin.edu@gmail.com', '$2y$10$lS9U1JQNYwnPMYEVyWMiWuuAlVhP7nV.XlA5IVDYHVenwzNwwAwvq', '2025-07-07 22:50:56', NULL, NULL, 'uploads/profile_photos/default-avatar.png', 'Admin', 'Inactive', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_access_codes`
--

CREATE TABLE `admin_access_codes` (
  `id` int(11) NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `code_plain` varchar(100) NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_access_codes`
--

INSERT INTO `admin_access_codes` (`id`, `code_hash`, `code_plain`, `is_enabled`, `created_at`, `updated_at`, `updated_by`) VALUES
(1, '$2y$10$5/pe2xGeQGK3VdA3hU9s2urtzjrxT1zWZsN3P/M1cu58ADKKMkniC', 'SLSU-HC_ADMIN_2025', 1, '2025-06-24 23:52:02', '2025-07-18 08:37:40', 0);

-- --------------------------------------------------------

--
-- Table structure for table `admin_access_logs`
--

CREATE TABLE `admin_access_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `action_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_access_logs`
--

INSERT INTO `admin_access_logs` (`id`, `admin_id`, `action`, `action_time`) VALUES
(1, 1, 'Initial admin access code created', '2025-06-24 23:52:02'),
(2, 0, 'Disabled admin access code', '2025-06-25 00:06:33'),
(3, 0, 'Enabled admin access code', '2025-06-25 00:06:39'),
(4, 0, 'Updated admin access code', '2025-06-25 07:35:59'),
(5, 0, 'Disabled admin access code', '2025-06-25 07:36:41'),
(6, 0, 'Enabled admin access code', '2025-06-25 07:36:52'),
(7, 1, 'Updated profile information', '2025-07-05 14:00:20'),
(8, 0, 'Disabled admin access code', '2025-07-07 05:34:32'),
(9, 0, 'Enabled admin access code', '2025-07-07 05:34:54'),
(10, 0, 'Disabled admin access code', '2025-07-16 10:41:36'),
(11, 0, 'Enabled admin access code', '2025-07-16 10:47:03'),
(12, 0, 'Disabled admin access code', '2025-07-18 08:37:08'),
(13, 0, 'Enabled admin access code', '2025-07-18 08:37:40');

-- --------------------------------------------------------

--
-- Table structure for table `alumni`
--

CREATE TABLE `alumni` (
  `alumni_id` varchar(50) NOT NULL,
  `student_no` varchar(50) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `course` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `address` text NOT NULL,
  `civil_status` varchar(50) NOT NULL,
  `employment_status` enum('employed','self-employed','further studying','unemployed') NOT NULL,
  `payment_option` enum('GCASH','Cash on Hand') NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alumni`
--

INSERT INTO `alumni` (`alumni_id`, `student_no`, `last_name`, `first_name`, `middle_name`, `course`, `email`, `address`, `civil_status`, `employment_status`, `payment_option`, `password`) VALUES
('252-0006', '2210013-1', 'BALBUENA', 'JHOVAN', 'PEREZ', 'BSIT', 'jhovanbalbuena27@gmail.com', 'Laguma, SILAGO, SOUTHERN LEYTE', 'Single', 'employed', 'Cash on Hand', '$2y$10$gS98KSWQhvqyBm/o6TsswOKGetZsH99tLJM6LqZNRTMUp6GHw6RUC');

-- --------------------------------------------------------

--
-- Table structure for table `alumni_events`
--

CREATE TABLE `alumni_events` (
  `id` int(11) NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_description` text DEFAULT NULL,
  `event_type` enum('Reunion','Seminar','Webinar','Career Fair','Outreach','Other') NOT NULL,
  `event_category` enum('Academic','Social','Career','Other') DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `physical_address` text DEFAULT NULL,
  `online_link` varchar(255) DEFAULT NULL,
  `timezone` varchar(50) NOT NULL,
  `poster_image` varchar(255) DEFAULT NULL,
  `event_document` varchar(255) DEFAULT NULL,
  `registration_required` tinyint(1) DEFAULT 1,
  `max_attendees` int(11) DEFAULT NULL,
  `allow_guests` tinyint(1) DEFAULT 0,
  `auto_confirm` tinyint(1) DEFAULT 0,
  `contact_person` varchar(100) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `visibility` enum('public','private') DEFAULT 'public',
  `status` enum('Draft','Published','Cancelled','Completed') DEFAULT 'Draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alumni_events`
--

INSERT INTO `alumni_events` (`id`, `event_title`, `event_description`, `event_type`, `event_category`, `start_datetime`, `end_datetime`, `physical_address`, `online_link`, `timezone`, `poster_image`, `event_document`, `registration_required`, `max_attendees`, `allow_guests`, `auto_confirm`, `contact_person`, `contact_email`, `contact_phone`, `visibility`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Alumni Homecoming 2025', 'SDSFSD', 'Reunion', 'Social', '2025-08-25 08:30:00', '2025-08-25 12:00:00', 'SLSU-HC, Ambacon Hinunangan Southern Leyte', '', 'Asia/Manila', 'uploads/events/posters/1751518749_A rainy night in beautiful amazonic forest in autumn, with buterfly and with view of starry sky.jpg', '', 1, NULL, 1, 1, 'Jhovan', 'jhvs@gmail.com', '09123456789', 'public', 'Published', '2025-06-10 06:45:46', '2025-07-03 04:59:09'),
(2, 'Alumni Homecoming 2025', 'SDSFSD', 'Reunion', 'Social', '2025-06-25 08:30:00', '2025-06-25 17:45:00', 'SLSU-HC, Ambacon Hinunangan Southern Leyte', '', 'Asia/Manila', 'uploads/events/posters/1751519463_Untitled design.jpg', '', 1, NULL, 1, 1, 'Jhovan', 'jhvs@gmail.com', '09123456789', 'public', 'Cancelled', '2025-06-10 06:48:04', '2025-07-03 05:11:03'),
(4, 'Alumni Homecoming 2026', 'Lets go Guys!', 'Outreach', 'Social', '2025-08-25 17:48:00', '2025-08-25 12:00:00', 'SLSU-HC, Ambacon Hinunangan Southern Leyte', '', 'Asia/Manila', 'uploads/events/posters/1751519383_images (4).jpg', '', 0, NULL, 1, 0, 'Jhovan', 'jhovanbalbuena27@gmail.com', '09123456789', 'public', 'Published', '2025-06-28 09:50:19', '2025-07-03 05:09:43'),
(5, 'Seminar for Alumni', 'Career guidance for all Alumni', 'Seminar', 'Career', '2025-08-25 08:30:00', '2025-08-25 12:00:00', 'SLSU-HC, Ambacon Hinunangan Southern Leyte', '', 'Asia/Manila', 'uploads/events/posters/1751117799_1000003595-removebg-preview.png', '', 0, NULL, 1, 0, 'Jhovan', 'jhovanbalbuena27@gmail.com', '09123456789', 'public', 'Published', '2025-06-28 11:56:43', '2025-06-28 13:36:39');

-- --------------------------------------------------------

--
-- Table structure for table `alumni_ids`
--

CREATE TABLE `alumni_ids` (
  `id` int(11) NOT NULL,
  `student_no` varchar(20) NOT NULL,
  `alumni_id` varchar(20) NOT NULL,
  `graduation_year` int(11) NOT NULL,
  `registration_id` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alumni_ids`
--

INSERT INTO `alumni_ids` (`id`, `student_no`, `alumni_id`, `graduation_year`, `registration_id`, `created_at`) VALUES
(1, '2210013-1', '252-0006', 2025, '6', '2025-07-19 11:39:07');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `id` varchar(45) DEFAULT NULL,
  `course_title` varchar(100) DEFAULT NULL,
  `accro` varchar(45) DEFAULT NULL,
  `lvl` varchar(45) DEFAULT NULL,
  `pri` int(10) UNSIGNED NOT NULL,
  `Department` int(11) DEFAULT NULL,
  `YearGranted` int(11) DEFAULT NULL,
  `AuthorityNumber` varchar(150) DEFAULT NULL,
  `isActive` int(11) DEFAULT 0 COMMENT '0-Active 1-InActive',
  `authorizedby` varchar(10) DEFAULT NULL,
  `InitCode` varchar(5) DEFAULT '',
  `showInAdmission` int(11) DEFAULT 0,
  `Window` int(11) DEFAULT 0,
  `ProgramCode` varchar(50) DEFAULT '',
  `old_AuthorityNumber` varchar(150) DEFAULT NULL,
  `yr_granted` int(11) DEFAULT NULL,
  `LMS_category_idnumber` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`id`, `course_title`, `accro`, `lvl`, `pri`, `Department`, `YearGranted`, `AuthorityNumber`, `isActive`, `authorizedby`, `InitCode`, `showInAdmission`, `Window`, `ProgramCode`, `old_AuthorityNumber`, `yr_granted`, `LMS_category_idnumber`) VALUES
('2013050153508', 'Bachelor of Science in Information Technology', 'BSIT', 'Under Graduate', 1, 6, 2007, 'Board of Regents Resolution No. 16', 0, 'BR', 'ICT', 1, 0, '464108', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `id` int(11) NOT NULL,
  `Description` varchar(60) DEFAULT '',
  `DepartmentHead` int(11) DEFAULT 0,
  `DepartmentName` varchar(100) DEFAULT '',
  `Designation` varchar(20) DEFAULT '',
  `Active` int(11) DEFAULT 0,
  `AllowOnlineEnrolment` int(11) DEFAULT 0,
  `RestrictIP` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`id`, `Description`, `DepartmentHead`, `DepartmentName`, `Designation`, `Active`, `AllowOnlineEnrolment`, `RestrictIP`) VALUES
(1, 'For Staff of SLSU CAFES Accessing Enrollment System', 100, 'Staff', 'Head, CISA', 0, 1, 0),
(2, 'Agricultural Sciences', 671222028, 'BSA', 'Program Chair', 1, 1, 0),
(3, 'AgriBusiness and Agricultural Entrepreneurship', 671222028, 'BSAB', 'Program Chair', 1, 1, 0),
(4, 'Environmental Sciences', 671222028, 'BSES', 'Program Chair', 1, 1, 0),
(5, 'College of Teacher Education', 270916005, 'BSEd', 'Program Chair', 1, 1, 0),
(6, 'Information Technology', 671222028, 'BSIT', 'Program Chair', 1, 1, 0),
(7, 'Dept of Technology and Livelihood Education', 270916005, 'TLE', 'Program Chair', 1, 1, 0),
(8, 'Academic Affairs, Research and Innovations', 671222028, 'AARi', 'Assistant Director', 1, 1, 0),
(9, 'Agricultural Technology', 671222028, 'BAT', 'Program Chair', 1, 1, 0),
(10, 'College of Agriculture Food and Environmental Sciences', 671222028, 'CAFES', 'Program Chair', 0, 1, 0),
(11, 'Clearance-BSEd', 270916005, 'Clearance-BSEd', 'Program Chair', 0, 1, 0),
(12, 'Clearance-BTLEd', 29070207, 'Clearance-BTLEd', 'Program Chair', 0, 1, 0),
(13, 'Clearance-BAT', 141, 'Clearance-BAT', 'Program Chair', 0, 1, 0),
(14, 'Clearance-BSIT', 123456, 'Clearance-BSIT', 'Program Chair', 0, 1, 0),
(15, 'Clearance-BSAB', 94, 'Clearance-BSAB', 'Program Chair', 0, 1, 0),
(16, 'Clearance-BSA', 671222094, 'Clearance-BSA', 'Program Chair', 0, 1, 0),
(17, 'Clearance-BSES', 187, 'Clearance-BSES', 'Program Chair', 0, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `employment`
--

CREATE TABLE `employment` (
  `alumni_id` varchar(50) DEFAULT NULL,
  `employment_status` enum('employed','self-employed','further studying') NOT NULL,
  `employee_id_number` varchar(100) DEFAULT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `business_name` varchar(150) DEFAULT NULL,
  `business_location` text DEFAULT NULL,
  `school_name` varchar(150) DEFAULT NULL,
  `level_of_study` varchar(100) DEFAULT NULL,
  `type_of_study` varchar(100) DEFAULT NULL,
  `proof_document_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employment`
--

INSERT INTO `employment` (`alumni_id`, `employment_status`, `employee_id_number`, `company_name`, `company_address`, `business_name`, `business_location`, `school_name`, `level_of_study`, `type_of_study`, `proof_document_path`, `created_at`) VALUES
('252-0006', 'employed', '', 'Makato', 'Cebu City', NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-29 11:56:05');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `job_post_id` int(11) NOT NULL,
  `student_no` varchar(20) NOT NULL,
  `application_date` datetime NOT NULL,
  `status` enum('pending','reviewed','shortlisted','rejected') NOT NULL DEFAULT 'pending',
  `resume_file` varchar(255) DEFAULT NULL,
  `cover_letter` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_posts`
--

CREATE TABLE `job_posts` (
  `id` int(11) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `job_type` varchar(50) NOT NULL,
  `job_category` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `salary_min` decimal(10,2) DEFAULT NULL,
  `salary_max` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `deadline` date NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_phone` varchar(50) NOT NULL,
  `job_description` text NOT NULL,
  `qualifications` text NOT NULL,
  `how_to_apply` text NOT NULL,
  `job_link` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `posted_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `job_posts`
--

INSERT INTO `job_posts` (`id`, `job_title`, `company_name`, `company_logo`, `job_type`, `job_category`, `location`, `salary_min`, `salary_max`, `currency`, `deadline`, `contact_email`, `contact_phone`, `job_description`, `qualifications`, `how_to_apply`, `job_link`, `status`, `posted_date`, `created_at`, `updated_at`) VALUES
(1, 'Junior Software Developer', 'TechNova', NULL, 'Part-time', 'IT', 'Manila, Philippines', 1000.00, 1500.00, 'USD', '2025-06-11', 'career@gmail.com', '+63 963 331 6076', 'asda', 'safdas', 'adsfasd', NULL, 'archived', '2025-06-09 14:22:57', '2025-06-09 06:22:57', '2025-06-09 07:36:15'),
(2, 'Full Stack Software Developer', 'TechNova II', 'uploads/company_logos/68468abecf369.png', 'Part-time', 'IT', 'Manila, Philippines', 50000.00, 75000.00, 'PHP', '2025-06-15', 'career@gmail.com', '09633316076', 'erterf', 'sdfsdfsf', 'sdfasdfa', NULL, 'published', '2025-06-09 15:18:22', '2025-06-09 07:18:22', '2025-06-09 07:18:22'),
(3, 'Courier ', 'TechNova III', 'uploads/company_logos/687053aa6ebf4.jpg', 'Remote', 'Business', 'Manila, Philippines', 15000.00, 15000.00, 'PHP', '2025-07-25', 'career@gmail.com', '+63 963 331 6076', 'FGD', 'SDFS', 'SDFS', 'https://courier.com', 'published', '2025-07-11 07:58:34', '2025-07-10 23:58:34', '2025-07-10 23:58:34');

-- --------------------------------------------------------

--
-- Table structure for table `listgradmain`
--

CREATE TABLE `listgradmain` (
  `id` int(10) UNSIGNED NOT NULL,
  `SchoolYear` int(11) NOT NULL DEFAULT 0,
  `Semester` int(11) NOT NULL DEFAULT 0,
  `BORDate` date NOT NULL,
  `BORNumber` int(11) NOT NULL DEFAULT 0,
  `DateOfGraduation` date NOT NULL,
  `AddedBy` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listgradmain`
--

INSERT INTO `listgradmain` (`id`, `SchoolYear`, `Semester`, `BORDate`, `BORNumber`, `DateOfGraduation`, `AddedBy`, `created_at`, `updated_at`) VALUES
(1, 2024, 2, '2025-04-29', 30, '2025-06-13', 'jonathan', NULL, NULL),
(2, 2018, 2, '2019-04-20', 3, '2019-05-29', 'jonathan', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `listgradsub`
--

CREATE TABLE `listgradsub` (
  `id` int(10) UNSIGNED NOT NULL,
  `StudentNo` varchar(255) NOT NULL DEFAULT '0',
  `ORNo` int(11) NOT NULL DEFAULT 0,
  `ORDate` date DEFAULT NULL,
  `LatinHonor` varchar(255) NOT NULL DEFAULT '0',
  `MainID` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `CertORNo` int(11) DEFAULT 0,
  `CertORDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listgradsub`
--

INSERT INTO `listgradsub` (`id`, `StudentNo`, `ORNo`, `ORDate`, `LatinHonor`, `MainID`, `created_at`, `updated_at`, `CertORNo`, `CertORDate`) VALUES
(1, '2210013-1', 8708780, '2025-05-27', '', 1, '2025-05-28 03:56:40', '2025-05-28 03:56:40', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `major`
--

CREATE TABLE `major` (
  `id` varchar(45) NOT NULL,
  `course_major` varchar(45) NOT NULL,
  `isActive` int(11) DEFAULT 0,
  `CourseID` varchar(30) DEFAULT '',
  `showInAdmission` int(11) DEFAULT 0,
  `uid` int(11) NOT NULL,
  `YearGranted` int(11) DEFAULT NULL,
  `AuthorityNumber` varchar(150) DEFAULT NULL,
  `isspecialize` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `major`
--

INSERT INTO `major` (`id`, `course_major`, `isActive`, `CourseID`, `showInAdmission`, `uid`, `YearGranted`, `AuthorityNumber`, `isspecialize`) VALUES
('2013050153553', 'Mathematics', 0, '2013050153533', 0, 1, NULL, NULL, 0),
('2013050153557', 'Biological Science', 0, '', 1, 2, NULL, NULL, 0),
('2013050153600', 'English', 0, '2013050153533', 1, 3, NULL, NULL, 0),
('20180530131425', 'Crop Science', 0, '20180530131414', 0, 4, NULL, NULL, 0),
('20180530131430', 'Animal Science', 0, '20180530131414', 0, 5, NULL, NULL, 0),
('20180601112046', 'Agri-Fishery Arts', 0, '20180601112013', 0, 6, NULL, NULL, 0),
('20180601112102', 'Information Communication Technology', 0, '20180601112013', 0, 7, NULL, NULL, 0),
('2018112892930', 'Science', 0, '2013050153533', 0, 8, NULL, NULL, 0),
('20190505113108', 'General', 0, '20180530131414', 0, 9, NULL, NULL, 0),
('0', '', 0, '', 0, 10, NULL, NULL, 0),
('2024052990729', 'Programming', 0, '2013050153508', 0, 11, NULL, NULL, 0),
('20241128142734', 'Horticulture', 0, '20180530131414', 0, 12, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `alumni_id` varchar(50) NOT NULL,
  `payment_option` enum('GCASH','Cash on Hand') NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `proof_document` varchar(255) DEFAULT NULL,
  `status` enum('Under Review','Pending Payment','Confirmed','Rejected') NOT NULL DEFAULT 'Pending Payment',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`alumni_id`, `payment_option`, `reference_number`, `proof_document`, `status`, `created_at`) VALUES
('252-0006', 'Cash on Hand', '', NULL, 'Pending Payment', '2025-07-29 11:56:05');

-- --------------------------------------------------------

--
-- Table structure for table `program_chairs`
--

CREATE TABLE `program_chairs` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `program` int(11) NOT NULL,
  `Designation` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_chairs`
--

INSERT INTO `program_chairs` (`id`, `full_name`, `email`, `username`, `password`, `program`, `Designation`, `profile_picture`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Ruther Manun-og', 'bsit.edu@gmail.com', 'BSIT', '$2y$10$q/x2HVTU09Aq0ZDwjSrVRe1/78jPKeE0io.IWoBGZascR0ZWaWSMC', 6, 'Program Chair', '6869484429c31.jpeg', 'Active', '2025-07-01 11:57:33', '2025-07-05 15:44:04'),
(2, 'Naruto Uzumaki', 'bsed@gmail.com', 'BSED', '$2y$10$DhUVE9/bSJSLsqH9bIhk.Otc/zd.eFb2pWGEJebnAXBl4X9lNc82C', 5, 'Program Chair', '68666f4979a50.jpg', 'Active', '2025-07-03 11:51:03', '2025-07-26 00:37:25');

-- --------------------------------------------------------

--
-- Table structure for table `registration`
--

CREATE TABLE `registration` (
  `RegistrationID` varchar(50) DEFAULT NULL,
  `StudentNo` varchar(45) DEFAULT NULL,
  `SchoolLevel` varchar(45) DEFAULT NULL,
  `Period` varchar(45) DEFAULT NULL,
  `SchoolYear` varchar(45) DEFAULT NULL,
  `Semester` int(11) DEFAULT NULL,
  `StudentYear` varchar(45) DEFAULT NULL,
  `Section` varchar(45) DEFAULT NULL,
  `Course` varchar(45) DEFAULT NULL,
  `Major` varchar(45) DEFAULT NULL,
  `UnitCost` varchar(45) DEFAULT NULL,
  `Scholar` varchar(45) DEFAULT NULL,
  `Adjust` varchar(45) DEFAULT NULL,
  `ditCheck` varchar(30) DEFAULT NULL,
  `finalize` int(11) DEFAULT NULL,
  `sumUnits` int(11) DEFAULT NULL,
  `assessprint` int(11) DEFAULT NULL,
  `StudentStatus` varchar(50) DEFAULT NULL,
  `cur_num` int(11) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `Source` int(11) DEFAULT 0,
  `Note` varchar(100) DEFAULT '',
  `Balance` double(15,2) DEFAULT 0.00,
  `isCleared` int(11) DEFAULT 1,
  `EnrollingOfficer` varchar(50) DEFAULT '',
  `DateEnrolled` date DEFAULT '2009-01-01',
  `DisapproveDeptReason` varchar(250) DEFAULT '',
  `TES` int(11) DEFAULT 0,
  `TESDate` date DEFAULT NULL,
  `TESBy` varchar(50) NOT NULL DEFAULT '',
  `TESReason` varchar(250) DEFAULT '',
  `Cashier` varchar(50) DEFAULT '',
  `CashierDate` date DEFAULT NULL,
  `DateEncoded` date DEFAULT NULL,
  `EncodedBy` varchar(50) DEFAULT '',
  `DateValidated` date DEFAULT NULL,
  `ValidatedBy` varchar(50) DEFAULT '',
  `TimeEncoded` varchar(50) DEFAULT '0',
  `TimeEnrolled` varchar(50) DEFAULT '0',
  `TimeTES` varchar(50) DEFAULT '0',
  `TimeValidated` varchar(50) DEFAULT '0',
  `TimeCashier` varchar(15) DEFAULT '0',
  `WhereEnrolled` varchar(20) NOT NULL DEFAULT '',
  `isUnifastPaid` int(11) DEFAULT 0,
  `ByPassAssess` int(11) DEFAULT 0,
  `ByPassAutoAssess` int(11) DEFAULT 0,
  `AdditionalIns` text DEFAULT NULL,
  `forAR` double(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `registration`
--

INSERT INTO `registration` (`RegistrationID`, `StudentNo`, `SchoolLevel`, `Period`, `SchoolYear`, `Semester`, `StudentYear`, `Section`, `Course`, `Major`, `UnitCost`, `Scholar`, `Adjust`, `ditCheck`, `finalize`, `sumUnits`, `assessprint`, `StudentStatus`, `cur_num`, `id`, `Source`, `Note`, `Balance`, `isCleared`, `EnrollingOfficer`, `DateEnrolled`, `DisapproveDeptReason`, `TES`, `TESDate`, `TESBy`, `TESReason`, `Cashier`, `CashierDate`, `DateEncoded`, `EncodedBy`, `DateValidated`, `ValidatedBy`, `TimeEncoded`, `TimeEnrolled`, `TimeTES`, `TimeValidated`, `TimeCashier`, `WhereEnrolled`, `isUnifastPaid`, `ByPassAssess`, `ByPassAutoAssess`, `AdditionalIns`, `forAR`) VALUES
('202207211102382210013-1', '2210013-1', 'Under Graduate', NULL, '2022', 1, '1', 'B', '2013050153508', '0', '150.00', NULL, NULL, NULL, 1, NULL, NULL, 'New', NULL, 15363, 0, '', 0.00, 1, 'ruther', '2022-07-21', '', 1, '2022-07-21', 'fhe_leslie', '', '', NULL, NULL, '', '2022-07-21', 'Titeet1986', '11:02:38', '11:02:38', '11:11:06', '11:15:53', '0', '', 1, 0, 0, NULL, NULL),
('202301091129262210013-1', '2210013-1', 'Under Graduate', NULL, '2022', 2, '1', 'B', '2013050153508', '0', '150.00', '', NULL, NULL, 1, NULL, NULL, 'Continuing', NULL, 16822, 0, '', 0.00, 1, 'ruther', '2023-01-09', '', 1, '2023-01-09', 'fhe_leslie', '', '', NULL, NULL, '', '2023-01-10', 'amalia', '11:29:26', '11:29:26', '11:35:43', '08:49:12', '0', '', 1, 0, 0, NULL, NULL),
('202307061025222210013-1', '2210013-1', 'Under Graduate', NULL, '2023', 1, '2', 'B', '2013050153508', '0', '150.00', NULL, NULL, NULL, 1, NULL, NULL, 'Continuing', NULL, 17995, 0, '', 0.00, 1, 'ruther', '2023-07-06', '', 1, '2023-07-06', 'fhe_leslie', '', '', NULL, NULL, '', '2023-07-06', 'Titeet1986', '10:25:22', '10:25:22', '10:29:21', '10:52:00', '0', '', 0, 0, 0, NULL, NULL),
('202401081033142210013-1', '2210013-1', 'Under Graduate', NULL, '2023', 2, '2', 'B', '2013050153508', '0', '150.00', NULL, NULL, NULL, 1, NULL, NULL, 'Continuing', NULL, 19833, 0, '', 0.00, 1, 'ruther', '2024-01-08', '', 1, '2024-01-08', 'fhe_leslie', '', '', NULL, NULL, '', '2024-01-18', 'amalia', '10:33:15', '10:33:15', '11:16:46', '16:42:46', '0', '', 0, 0, 0, NULL, NULL),
('202407230959292210013-1', '2210013-1', 'Under Graduate', NULL, '2024', 1, '3', 'B', '2013050153508', '0', '150.00', NULL, NULL, NULL, 1, NULL, NULL, 'Continuing', 2021, 21588, 0, '', 0.00, 1, 'dept_mondani', '2024-07-23', '', 1, '2024-07-23', 'fhe_leslie', '', '', NULL, NULL, '', '2024-07-23', 'cjsinahon', '09:59:29', '09:59:29', '10:04:21', '10:08:04', '0', '', 0, 0, 0, NULL, NULL),
('202501061753462210013-1', '2210013-1', 'Under Graduate', '', '2024', 2, '3', 'B', '2013050153508', '0', '150.00', '0', NULL, NULL, 1, NULL, NULL, 'Continuing', 2021, 22738, 0, '', 0.00, 1, 'ruther', '2025-01-13', '', 1, '2025-01-14', 'leslieannecaberte', '', 'Jest', '2025-01-14', '2025-01-06', '2210013-1', '2025-01-16', 'cjsinahon', '17:53:46', '10:52:54', '13:15:56', '10:18:13', '13:23:27', 'online', 0, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `StudentNo` varchar(20) NOT NULL,
  `LastName` varchar(45) DEFAULT NULL,
  `FirstName` varchar(45) DEFAULT NULL,
  `MiddleName` varchar(45) DEFAULT NULL,
  `Sex` varchar(5) DEFAULT NULL,
  `Course` varchar(45) DEFAULT NULL,
  `StudentYear` varchar(5) DEFAULT NULL,
  `Section` varchar(5) DEFAULT NULL,
  `BirthDate` varchar(45) DEFAULT NULL,
  `BirthPlace` text DEFAULT NULL,
  `AdmissionDate` varchar(45) DEFAULT NULL,
  `major` varchar(45) DEFAULT NULL,
  `temp_id` varchar(45) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `elem_school` varchar(100) DEFAULT NULL,
  `elem_add` varchar(100) DEFAULT NULL,
  `elem_year` varchar(5) DEFAULT NULL,
  `high_school` varchar(100) DEFAULT NULL,
  `high_add` varchar(100) DEFAULT NULL,
  `high_year` varchar(5) DEFAULT NULL,
  `col_school` varchar(100) DEFAULT NULL,
  `col_add` varchar(100) DEFAULT NULL,
  `col_year` varchar(5) DEFAULT NULL,
  `father_name` varchar(45) DEFAULT NULL,
  `father_occu` varchar(45) DEFAULT NULL,
  `mother_name` varchar(45) DEFAULT NULL,
  `mother_occu` varchar(45) DEFAULT NULL,
  `emer_name` varchar(45) DEFAULT NULL,
  `emer_relation` varchar(45) DEFAULT NULL,
  `emer_zip` varchar(45) DEFAULT NULL,
  `emer_street` varchar(45) DEFAULT NULL,
  `emer_city` varchar(45) DEFAULT NULL,
  `emer_province` varchar(45) DEFAULT NULL,
  `nationality` varchar(45) DEFAULT NULL,
  `civil_status` varchar(45) DEFAULT NULL,
  `religion` varchar(70) DEFAULT '',
  `p_no` varchar(45) DEFAULT NULL,
  `p_zip` varchar(45) DEFAULT NULL,
  `p_municipality` varchar(45) DEFAULT NULL,
  `p_street` varchar(45) DEFAULT NULL,
  `p_province` varchar(45) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `notes` blob DEFAULT NULL,
  `bor` varchar(200) DEFAULT NULL,
  `grad` varchar(50) DEFAULT NULL,
  `pass1` varchar(25) DEFAULT NULL,
  `datelastused` datetime DEFAULT NULL,
  `pass` varchar(25) DEFAULT NULL,
  `emer_contact` varchar(12) DEFAULT NULL,
  `sendSMS` varchar(15) DEFAULT 'sent',
  `txtBlast` varchar(15) DEFAULT 'sent',
  `ContactNo` varchar(15) DEFAULT NULL,
  `inetuser` varchar(25) DEFAULT NULL,
  `inetpass` varchar(25) DEFAULT NULL,
  `entrance` int(11) NOT NULL DEFAULT 0,
  `exam_take_no` int(11) NOT NULL DEFAULT 0,
  `CExam` varchar(50) DEFAULT '',
  `POD` varchar(50) DEFAULT '',
  `FOD` varchar(50) DEFAULT '',
  `CRemarks` varchar(10) DEFAULT '',
  `PRemarks` varchar(10) DEFAULT '',
  `FRemarks` varchar(10) DEFAULT '',
  `Welcome` varchar(15) DEFAULT '',
  `latitude` varchar(20) DEFAULT '',
  `longitude` varchar(20) DEFAULT '',
  `name_ext` varchar(10) DEFAULT NULL,
  `icafe` binary(1) DEFAULT '0',
  `anual_income` int(11) DEFAULT 1,
  `Credential` varchar(150) DEFAULT '',
  `notes2` blob DEFAULT NULL,
  `notuse` int(11) DEFAULT 0 COMMENT '1-Inactive',
  `cur_num` int(11) DEFAULT 0,
  `tmpContactNo` varchar(15) DEFAULT '',
  `tmpCode` varchar(15) DEFAULT '',
  `email` varchar(100) DEFAULT NULL,
  `AcademicYear` varchar(45) DEFAULT NULL,
  `Semester` int(11) DEFAULT NULL,
  `mmaidenfname` varchar(100) DEFAULT NULL,
  `mmaidenmname` varchar(100) DEFAULT NULL,
  `mmaidenlname` varchar(100) DEFAULT NULL,
  `ffname` varchar(100) DEFAULT NULL,
  `fmname` varchar(100) DEFAULT NULL,
  `flname` varchar(100) DEFAULT NULL,
  `lrn` varchar(20) DEFAULT NULL,
  `dswd_id` varchar(30) DEFAULT NULL,
  `CardNo` varchar(50) DEFAULT '',
  `AccessGroup` varchar(15) DEFAULT '',
  `NSTPSerial` varchar(25) DEFAULT '',
  `dswdincom` varchar(50) DEFAULT '0',
  `Disability` varchar(50) DEFAULT '',
  `Picture` varchar(100) DEFAULT '',
  `NetCategory` int(11) DEFAULT 0,
  `fb_account` text DEFAULT NULL,
  `1NetCategory` int(11) DEFAULT 0,
  `ContactNoChange` int(11) DEFAULT 0,
  `ExamineeNumber` varchar(25) DEFAULT '',
  `Rebuild` int(11) DEFAULT 1,
  `ResetPassword` varchar(250) DEFAULT '',
  `Consent` int(11) DEFAULT 0,
  `DateSigned` date DEFAULT NULL,
  `ConsentMother` varchar(50) DEFAULT '',
  `ConsentFather` varchar(50) DEFAULT '',
  `ConsentGuardian` varchar(50) DEFAULT '',
  `ConsentOthers` varchar(50) DEFAULT '',
  `ConsentRelation` varchar(50) DEFAULT '',
  `AdmissionTime` varchar(15) DEFAULT '',
  `AdmittedBy` varchar(50) DEFAULT '',
  `TourHome` int(11) DEFAULT 0,
  `TourErolment` int(11) DEFAULT 0,
  `OSASEvaluatedBy` varchar(150) DEFAULT '',
  `OSASEvaluatedDate` date DEFAULT NULL,
  `OSASEvaluatedStart` varchar(15) DEFAULT '',
  `OSASEvaluatedEnd` varchar(15) DEFAULT '',
  `CompleteEForm` int(11) DEFAULT 0,
  `ElemType` varchar(20) DEFAULT '',
  `SecondaryType` varchar(20) DEFAULT '',
  `SHSType` varchar(20) DEFAULT '',
  `CollegeType` varchar(20) DEFAULT '',
  `YearLevelAdviser` int(11) DEFAULT 0,
  `bordate` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`StudentNo`, `LastName`, `FirstName`, `MiddleName`, `Sex`, `Course`, `StudentYear`, `Section`, `BirthDate`, `BirthPlace`, `AdmissionDate`, `major`, `temp_id`, `status`, `elem_school`, `elem_add`, `elem_year`, `high_school`, `high_add`, `high_year`, `col_school`, `col_add`, `col_year`, `father_name`, `father_occu`, `mother_name`, `mother_occu`, `emer_name`, `emer_relation`, `emer_zip`, `emer_street`, `emer_city`, `emer_province`, `nationality`, `civil_status`, `religion`, `p_no`, `p_zip`, `p_municipality`, `p_street`, `p_province`, `remarks`, `notes`, `bor`, `grad`, `pass1`, `datelastused`, `pass`, `emer_contact`, `sendSMS`, `txtBlast`, `ContactNo`, `inetuser`, `inetpass`, `entrance`, `exam_take_no`, `CExam`, `POD`, `FOD`, `CRemarks`, `PRemarks`, `FRemarks`, `Welcome`, `latitude`, `longitude`, `name_ext`, `icafe`, `anual_income`, `Credential`, `notes2`, `notuse`, `cur_num`, `tmpContactNo`, `tmpCode`, `email`, `AcademicYear`, `Semester`, `mmaidenfname`, `mmaidenmname`, `mmaidenlname`, `ffname`, `fmname`, `flname`, `lrn`, `dswd_id`, `CardNo`, `AccessGroup`, `NSTPSerial`, `dswdincom`, `Disability`, `Picture`, `NetCategory`, `fb_account`, `1NetCategory`, `ContactNoChange`, `ExamineeNumber`, `Rebuild`, `ResetPassword`, `Consent`, `DateSigned`, `ConsentMother`, `ConsentFather`, `ConsentGuardian`, `ConsentOthers`, `ConsentRelation`, `AdmissionTime`, `AdmittedBy`, `TourHome`, `TourErolment`, `OSASEvaluatedBy`, `OSASEvaluatedDate`, `OSASEvaluatedStart`, `OSASEvaluatedEnd`, `CompleteEForm`, `ElemType`, `SecondaryType`, `SHSType`, `CollegeType`, `YearLevelAdviser`, `bordate`) VALUES
('2210013-1', 'BALBUENA', 'JHOVAN', 'PEREZ', 'M', '2013050153508', NULL, NULL, '01 27 2004', 'Laguma, Silago, Southern Leyte', 'June 24, 2022', '0', '2210013', NULL, 'Laguma Elementary School', 'Laguma, Silago, Southern Leyte', '2016', '', '', '', '', '', '', NULL, 'Farmer', NULL, 'Housewife', 'Carlito A. Balbuena', 'Father', '6607', 'Laguma', 'Silago', 'Southern Leyte', 'Filipino', 'Single', 'Roman Catholic', NULL, '6607', 'SILAGO', 'Laguma', 'SOUTHERN LEYTE', 'FOR OWWA Scholarship Application', '', '', '', NULL, NULL, '405108', '', 'sent', 'sent', '09633316076', NULL, NULL, 0, 0, '', '', '', '', '', '', '', '', '', NULL, 0x30, 1, 'Form 138, Good Moral & PSA', NULL, 0, 2021, '', '', 'jhovanbalbuena27@gmail.com', NULL, NULL, 'Elizabeth', 'Labrador', 'Perez', 'Carlito', 'Austero', 'Balbuena', '122056090007', '', '', '', '', '36000', '', 'e0a58871ade0fb38526316a577336b40fcd18f62.jpg', 2, NULL, 0, 0, '', 1, '', 1, '2022-08-20', 'Elizabeth Perez', 'Carlito Balbuena', '', 'Carlito A. Balbuena', 'Father', '10:19:46', 'beverlyjane', 0, 0, 'Nova Marie O. Maranguit', '2022-06-24', '09:02', '10:05', 0, '', '', '', '', -1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `survey`
--

CREATE TABLE `survey` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `target_alumni` varchar(100) DEFAULT NULL,
  `survey_type` varchar(100) DEFAULT NULL,
  `questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`questions`)),
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `anonymous` tinyint(1) DEFAULT 0,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `survey`
--

INSERT INTO `survey` (`id`, `title`, `description`, `target_alumni`, `survey_type`, `questions`, `start_date`, `end_date`, `anonymous`, `status`, `created_by`, `created_at`) VALUES
(1, 'CISA', 'CISA TEST SURVEY', '2024-2025', 'Feedback', '{\"1\":{\"required\":\"1\",\"type\":\"short\",\"text\":\"aSDFGHJKLL;;\"}}', '2025-07-10', NULL, 1, 'draft', 'admin', '2025-07-03 02:18:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_access_codes`
--
ALTER TABLE `admin_access_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_access_logs`
--
ALTER TABLE `admin_access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `action_time` (`action_time`);

--
-- Indexes for table `alumni`
--
ALTER TABLE `alumni`
  ADD PRIMARY KEY (`alumni_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `alumni_events`
--
ALTER TABLE `alumni_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `alumni_ids`
--
ALTER TABLE `alumni_ids`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `alumni_id` (`alumni_id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`pri`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employment`
--
ALTER TABLE `employment`
  ADD KEY `alumni_id` (`alumni_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_post_id` (`job_post_id`),
  ADD KEY `idx_student_no` (`student_no`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `job_posts`
--
ALTER TABLE `job_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_posted_date` (`posted_date`);

--
-- Indexes for table `listgradmain`
--
ALTER TABLE `listgradmain`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `listgradsub`
--
ALTER TABLE `listgradsub`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `major`
--
ALTER TABLE `major`
  ADD PRIMARY KEY (`uid`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD KEY `alumni_id` (`alumni_id`);

--
-- Indexes for table `program_chairs`
--
ALTER TABLE `program_chairs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `program` (`program`);

--
-- Indexes for table `registration`
--
ALTER TABLE `registration`
  ADD PRIMARY KEY (`id`,`TESBy`),
  ADD KEY `StudentNo_index` (`StudentNo`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`StudentNo`),
  ADD KEY `studentNo_index` (`StudentNo`),
  ADD KEY `notuse` (`notuse`),
  ADD KEY `CardNo` (`CardNo`);

--
-- Indexes for table `survey`
--
ALTER TABLE `survey`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin_access_codes`
--
ALTER TABLE `admin_access_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_access_logs`
--
ALTER TABLE `admin_access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `alumni_events`
--
ALTER TABLE `alumni_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `alumni_ids`
--
ALTER TABLE `alumni_ids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `pri` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_posts`
--
ALTER TABLE `job_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `listgradmain`
--
ALTER TABLE `listgradmain`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `listgradsub`
--
ALTER TABLE `listgradsub`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;

--
-- AUTO_INCREMENT for table `major`
--
ALTER TABLE `major`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `program_chairs`
--
ALTER TABLE `program_chairs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `registration`
--
ALTER TABLE `registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23813;

--
-- AUTO_INCREMENT for table `survey`
--
ALTER TABLE `survey`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employment`
--
ALTER TABLE `employment`
  ADD CONSTRAINT `employment_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `fk_job_applications_job_post` FOREIGN KEY (`job_post_id`) REFERENCES `job_posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`);

--
-- Constraints for table `program_chairs`
--
ALTER TABLE `program_chairs`
  ADD CONSTRAINT `program_chairs_ibfk_1` FOREIGN KEY (`program`) REFERENCES `department` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
