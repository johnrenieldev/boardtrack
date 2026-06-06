-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 06, 2026 at 04:11 AM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u536627044_boardtrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `event_date` date DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `event_date`, `priority`, `is_active`, `created_by`, `created_at`, `updated_at`, `expires_at`) VALUES
(1, 'dsdsds', 'dsdsdsds', '2026-05-30', 'high', 1, 1, '2026-05-30 12:26:59', '2026-05-30 12:26:59', NULL),
(2, 'bday', 'it\'s my bday', '2026-06-01', 'normal', 1, 1, '2026-05-30 16:21:08', '2026-05-30 16:21:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `created_at`) VALUES
(1, NULL, 'tenant_registered', 'user', 16, NULL, '{\"email\":\"zeroespinosa@gmail.com\",\"guardian_email\":\"jrbalsacao@gmail.com\",\"_description\":\"New tenant registration submitted\"}', '175.176.73.38', '2026-05-29 03:22:09'),
(2, 1, 'tenant_approved_waiting', 'tenant', 14, '{\"status\":\"pending\"}', '{\"status\":\"waiting_list\",\"_description\":\"Tenant approved, added to waiting list\"}', '175.176.73.38', '2026-05-29 03:25:37'),
(3, 1, 'tenant_approved_room', 'tenant', 14, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"room_id\":2,\"_description\":\"Tenant approved and assigned room\"}', '175.176.73.38', '2026-05-29 03:26:07'),
(4, 1, 'tenant_approved_room', 'tenant', 14, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"room_id\":2,\"_description\":\"Tenant approved and assigned room\"}', '175.176.73.38', '2026-05-29 03:26:07'),
(5, 1, 'bill_created', 'bill', 1, NULL, '{\"room_id\":2,\"tenant_id\":14,\"billing_type\":\"individual\",\"charge_category\":\"rent\",\"bill_name\":\"bayad sak\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":5000,\"due_date\":\"2026-05-29\",\"status\":\"unpaid\",\"created_by\":1,\"notes\":\"haaialaansnsnskkskskak\",\"_description\":\"Individual bill for tenant #14: bayad sak\"}', '175.176.73.38', '2026-05-29 03:30:40'),
(6, NULL, 'tenant_registered', 'user', 17, NULL, '{\"email\":\"langrioreancy22@gmail.com\",\"guardian_email\":\"jrbalsacao@gmail.com\",\"_description\":\"New tenant registration submitted\"}', '175.176.73.38', '2026-05-29 03:31:37'),
(7, 16, 'payment_submitted', 'payment', 1, NULL, '{\"amount_paid\":5000,\"payment_method\":\"gcash\",\"proof_file_path\":\"0604f55078b4efb4896b3bbd84b0ea6f.jpg\",\"proof_file_name\":\"landingpagebg.jpg\",\"notes\":\"sssssdsd\",\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b15,000.00 for \\\"bayad sak\\\"\"}', '49.145.180.247', '2026-05-30 03:06:31'),
(8, 1, 'payment_approved', 'payment', 1, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '49.145.180.247', '2026-05-30 03:07:17'),
(9, 1, 'bill_created', 'bill', 2, NULL, '{\"room_id\":2,\"tenant_id\":14,\"billing_type\":\"individual\",\"charge_category\":\"rent\",\"bill_name\":\"adsdsdsd\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":11212121212,\"due_date\":\"2026-05-31\",\"status\":\"unpaid\",\"created_by\":1,\"notes\":\"sadsdsds\",\"_description\":\"Individual bill for tenant #14: adsdsdsd\"}', '49.145.180.247', '2026-05-30 03:17:16'),
(10, 16, 'payment_submitted', 'payment', 2, NULL, '{\"amount_paid\":99999999.99,\"payment_method\":\"gcash\",\"proof_file_path\":\"74e93be1e82c66ea94822bbac16492bc.jpg\",\"proof_file_name\":\"landingpagebg.jpg\",\"notes\":\"sdsdsdsdsd\",\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b199,999,999.99 for \\\"adsdsdsd\\\"\"}', '49.145.180.247', '2026-05-30 03:18:00'),
(11, 1, 'payment_rejected', 'payment', 2, '{\"status\":\"pending\"}', '{\"status\":\"rejected\",\"_description\":\"asasasasas\"}', '49.145.180.247', '2026-05-30 03:18:56'),
(12, 1, 'bill_created', 'bill', 3, NULL, '{\"room_id\":2,\"tenant_id\":14,\"billing_type\":\"individual\",\"charge_category\":\"utility\",\"bill_name\":\"assasasasas\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":12121,\"due_date\":\"2026-05-31\",\"status\":\"unpaid\",\"created_by\":1,\"notes\":\"sasasas\",\"_description\":\"Individual bill for tenant #14: assasasasas\"}', '49.145.180.247', '2026-05-30 03:19:48'),
(13, 16, 'payment_submitted', 'payment', 3, NULL, '{\"amount_paid\":99999999.99,\"payment_method\":\"gcash\",\"proof_file_path\":\"63b13beacd9026a5959469969eacd14a.jpg\",\"proof_file_name\":\"landingpagebg.jpg\",\"notes\":\"asasasas\",\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b199,999,999.99 for \\\"adsdsdsd\\\"\"}', '49.145.180.247', '2026-05-30 03:20:16'),
(14, 16, 'payment_submitted', 'payment', 4, NULL, '{\"amount_paid\":12121,\"payment_method\":\"gcash\",\"proof_file_path\":\"fe3f90936e94c645de1516411909f2b2.jpg\",\"proof_file_name\":\"landingpagebg.jpg\",\"notes\":\"5555\",\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b112,121.00 for \\\"assasasasas\\\"\"}', '49.145.180.247', '2026-05-30 03:23:13'),
(15, 1, 'payment_rejected', 'payment', 4, '{\"status\":\"pending\"}', '{\"status\":\"rejected\",\"_description\":\"sos oi\"}', '49.145.180.247', '2026-05-30 03:23:45'),
(16, 1, 'complaint_updated', 'complaint', 1, '{\"status\":\"pending\"}', '{\"status\":\"in_progress\",\"_description\":\"Complaint status updated\"}', '49.145.180.247', '2026-05-30 03:26:09'),
(17, 16, 'payment_submitted', 'payment', 5, NULL, '{\"amount_paid\":12121,\"payment_method\":\"gcash\",\"proof_file_path\":\"bf2493741f19e6b8b130902ed09c4a3a.jpg\",\"proof_file_name\":\"landingpagebg.jpg\",\"notes\":\"saddsdssd\",\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b112,121.00 for \\\"assasasasas\\\"\"}', '49.145.180.247', '2026-05-30 03:27:56'),
(18, 1, 'payment_approved', 'payment', 5, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '49.145.180.247', '2026-05-30 04:05:03'),
(19, 1, 'payment_approved', 'payment', 3, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '49.145.180.247', '2026-05-30 04:27:00'),
(20, 1, 'room_created', 'room', 0, NULL, '{\"room_number\":\"101\",\"floor\":1,\"room_type\":\"single\",\"allowed_gender\":\"male\",\"max_occupants\":2,\"monthly_rent\":998.97,\"status\":\"available\",\"description\":\"ggrrtrtrtr\",\"air_conditioned\":1,\"_description\":\"Room 101 created\"}', '49.145.180.247', '2026-05-30 10:30:53'),
(21, 1, 'room_deleted', 'room', 5, '{\"id\":5,\"room_number\":\"101\",\"floor\":1,\"room_type\":\"single\",\"allowed_gender\":\"male\",\"max_occupants\":2,\"monthly_rent\":\"998.97\",\"status\":\"available\",\"description\":\"ggrrtrtrtr\",\"amenities\":null,\"created_at\":\"2026-05-30 10:30:53\",\"updated_at\":\"2026-05-30 10:30:53\",\"air_conditioned\":1}', '{\"_description\":\"Room 101 deleted\"}', '49.145.180.247', '2026-05-30 10:46:57'),
(22, 1, 'complaint_updated', 'complaint', 1, '{\"status\":\"in_progress\"}', '{\"status\":\"in_progress\",\"_description\":\"Complaint status updated\"}', '49.145.180.247', '2026-05-30 11:18:11'),
(23, 16, 'submitted', 'testimonial', 2, NULL, '{\"_description\":\"Submitted a review\\/testimonial\"}', '49.145.180.247', '2026-05-30 11:39:25'),
(24, 1, 'bill_created', 'bill', 4, NULL, '{\"room_id\":2,\"tenant_id\":14,\"billing_type\":\"individual\",\"charge_category\":\"rent\",\"bill_name\":\"dsdsdsdsdsd\",\"billing_period_start\":\"2026-05-30\",\"billing_period_end\":\"2026-05-31\",\"amount\":1000,\"due_date\":\"2026-06-01\",\"status\":\"unpaid\",\"created_by\":1,\"notes\":\"dsdsdsdsdsdsddsdsd\",\"_description\":\"Individual bill for tenant #14: dsdsdsdsdsd\"}', '49.145.180.247', '2026-05-30 12:29:30'),
(25, 1, 'bill_created', 'bill', 5, NULL, '{\"room_id\":2,\"tenant_id\":null,\"billing_type\":\"room_based\",\"charge_category\":\"rent\",\"bill_name\":\"sasasasasasa\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":1000,\"due_date\":\"2026-06-29\",\"status\":\"unpaid\",\"created_by\":1,\"notes\":\"sasasa\",\"_description\":\"Room bill for 9: sasasasasasa\"}', '49.145.180.247', '2026-05-30 12:36:14'),
(26, NULL, 'tenant_registered', 'user', 18, NULL, '{\"email\":\"reancylangrio22@gmail.com\",\"guardian_email\":\"rensi2228@gmail.com\",\"_description\":\"New tenant registration submitted\"}', '2001:4454:1b8:d900:2d5b:f826:dabf:3252', '2026-05-30 14:52:42'),
(27, 1, 'tenant_approved_room', 'tenant', 16, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"room_id\":2,\"_description\":\"Tenant approved and assigned room\"}', '49.145.180.247', '2026-05-30 15:09:04'),
(28, 16, 'updated', 'testimonial', 2, NULL, '{\"_description\":\"Updated a review\\/testimonial\"}', '49.145.180.247', '2026-05-30 15:19:22'),
(29, 18, 'payment_submitted', 'payment', 6, NULL, '{\"amount_paid\":1000,\"payment_method\":\"gcash\",\"proof_file_path\":\"b145780c892b4cd5b0b8abd9ebf166e9.jpg\",\"proof_file_name\":\"1000034355.jpg\",\"notes\":\"300 lng anay sir\",\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b11,000.00 for \\\"sasasasasasa\\\"\"}', '2001:4454:1b8:d900:2d5b:f826:dabf:3252', '2026-05-30 15:20:31'),
(30, 1, 'complaint_updated', 'complaint', 1, '{\"status\":\"in_progress\"}', '{\"status\":\"resolved\",\"_description\":\"Complaint status updated\"}', '49.145.180.247', '2026-05-30 15:21:32'),
(31, 18, 'submitted', 'testimonial', 3, NULL, '{\"_description\":\"Submitted a review\\/testimonial\"}', '2001:4454:1b8:d900:2d5b:f826:dabf:3252', '2026-05-30 15:30:51'),
(32, 18, 'password_changed', 'user', 18, NULL, '{\"_description\":\"User changed password via tenant portal\"}', '2001:4454:1b8:d900:2d5b:f826:dabf:3252', '2026-05-30 15:43:47'),
(33, 1, 'payment_approved', 'payment', 6, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '2001:4454:1b8:d900:2d5b:f826:dabf:3252', '2026-05-30 15:53:44'),
(34, 1, 'complaint_updated', 'complaint', 2, '{\"status\":\"pending\"}', '{\"status\":\"pending\",\"_description\":\"Complaint status updated\"}', '2001:4454:1b8:d900:2d5b:f826:dabf:3252', '2026-05-30 15:57:16'),
(35, 1, 'tenant_rejected', 'tenant', 15, '{\"status\":\"pending\"}', '{\"status\":\"rejected\",\"_description\":\"incomplete\"}', '49.147.122.63', '2026-05-30 16:07:13'),
(36, 1, 'room_created', 'room', 0, NULL, '{\"room_number\":\"1\",\"floor\":1,\"room_type\":\"single\",\"allowed_gender\":\"any\",\"max_occupants\":1,\"monthly_rent\":3000,\"status\":\"available\",\"description\":\"\",\"air_conditioned\":1,\"_description\":\"Room 1 created\"}', '2001:4454:1b8:d900:2d5b:f826:dabf:3252', '2026-05-30 16:11:36'),
(37, 1, 'bill_created', 'bill', 6, NULL, '{\"room_id\":2,\"tenant_id\":14,\"billing_type\":\"individual\",\"charge_category\":\"rent\",\"bill_name\":\"bayad po\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":6676677,\"due_date\":\"2026-06-01\",\"status\":\"unpaid\",\"created_by\":1,\"notes\":\"dadada\",\"_description\":\"Individual bill for tenant #14: bayad po\"}', '49.145.180.247', '2026-05-31 01:53:13'),
(38, 16, 'payment_submitted', 'payment', 7, NULL, '{\"amount_paid\":4999.99,\"payment_method\":\"gcash\",\"proof_file_path\":\"7c730388e6244101de83eda500e9c6ee.jpg\",\"proof_file_name\":\"1f886825-cd3a-4b16-91cd-0d01071a5793.jpg\",\"notes\":\"asasasasas\",\"is_partial\":true,\"_description\":\"Tenant submitted a partial payment of \\u20b14,999.99 for \\\"bayad po\\\"\"}', '49.145.180.247', '2026-05-31 01:54:48'),
(39, 1, 'payment_approved', 'payment', 7, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved: bayad nexttime ha\"}', '49.145.180.247', '2026-05-31 01:55:27'),
(40, 16, 'payment_submitted', 'payment', 8, NULL, '{\"amount_paid\":1000,\"payment_method\":\"gcash\",\"proof_file_path\":\"f74234555e14e348f32db3894d83f8bd.jpg\",\"proof_file_name\":\"landingpagebg.jpg\",\"notes\":\"adadadad\",\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b11,000.00 for \\\"dsdsdsdsdsd\\\"\"}', '49.145.180.247', '2026-05-31 01:58:01'),
(41, 1, 'payment_approved', 'payment', 8, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '49.145.180.247', '2026-05-31 01:58:32'),
(42, 16, 'payment_submitted', 'payment', 9, NULL, '{\"amount_paid\":6671677.01,\"payment_method\":\"gcash\",\"proof_file_path\":\"78951f5a125b055b25138dfe04eeafd7.jpg\",\"proof_file_name\":\"landingpagebg.jpg\",\"notes\":\"sfsssdsds\",\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b16,671,677.01 for \\\"bayad po\\\"\"}', '49.145.180.247', '2026-05-31 02:16:14'),
(43, 1, 'payment_approved', 'payment', 9, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '49.145.180.247', '2026-05-31 02:16:51'),
(44, 1, 'bill_created', 'bill', 7, NULL, '{\"room_id\":2,\"tenant_id\":null,\"billing_type\":\"room_based\",\"charge_category\":\"rent\",\"bill_name\":\"adadadad\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":1000,\"due_date\":\"2026-06-04\",\"status\":\"unpaid\",\"created_by\":1,\"notes\":\"asasass\",\"_description\":\"Room bill for 9: adadadad\"}', '49.145.180.247', '2026-05-31 02:20:36'),
(45, 1, 'tenant_approved_waiting', 'tenant', 12, '{\"status\":\"pending\"}', '{\"status\":\"waiting_list\",\"_description\":\"Tenant approved, added to waiting list\"}', '2001:fd8:c42b:ba00:834d:1c8c:dfe3:17d1', '2026-05-31 02:23:50'),
(46, 16, 'payment_submitted', 'payment', 10, NULL, '{\"amount_paid\":500,\"payment_method\":\"gcash\",\"proof_file_path\":\"d2608a6dcf847161401631d350c44bf3.jpg\",\"proof_file_name\":\"landingpagebg.jpg\",\"notes\":\"ddsda\",\"is_partial\":true,\"_description\":\"Tenant submitted a partial payment of \\u20b1500.00 for \\\"adadadad\\\"\"}', '49.145.180.247', '2026-05-31 02:24:07'),
(47, 1, 'payment_approved', 'payment', 10, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved: bayad na nexttime ha\"}', '49.145.180.247', '2026-05-31 02:24:48'),
(48, 1, 'tenant_approved_room', 'tenant', 12, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"room_id\":2,\"_description\":\"Tenant approved and assigned room\"}', '2001:fd8:c42b:ba00:834d:1c8c:dfe3:17d1', '2026-05-31 02:25:35'),
(49, 16, 'payment_submitted', 'payment', 11, NULL, '{\"amount_paid\":250,\"payment_method\":\"gcash\",\"proof_file_path\":\"8cbf25540196a7002808ee8f891ed63f.jpg\",\"proof_file_name\":\"landingpagebg.jpg\",\"notes\":null,\"is_partial\":true,\"_description\":\"Tenant submitted a partial payment of \\u20b1250.00 for \\\"adadadad\\\"\"}', '49.145.180.247', '2026-05-31 02:26:05'),
(50, 1, 'payment_approved', 'payment', 11, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved: baw a\"}', '49.145.180.247', '2026-05-31 02:26:41'),
(51, 14, 'payment_submitted', 'payment', 12, NULL, '{\"amount_paid\":250,\"payment_method\":\"cash\",\"proof_file_path\":\"bcf527124c931063f0906a1304446dc0.jpeg\",\"proof_file_name\":\"upscalemedia-transformed (1).jpeg\",\"notes\":null,\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b1250.00 for \\\"adadadad\\\"\"}', '2001:fd8:c42b:ba00:18c7:8e39:bbf3:509c', '2026-05-31 02:30:13'),
(52, 1, 'payment_approved', 'payment', 12, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '2001:fd8:c42b:ba00:834d:1c8c:dfe3:17d1', '2026-05-31 02:30:35'),
(53, 1, 'bill_created', 'bill', 8, NULL, '{\"room_id\":2,\"tenant_id\":null,\"billing_type\":\"room_based\",\"charge_category\":\"rent\",\"bill_name\":\"bayad bala\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":1500,\"due_date\":\"2026-06-10\",\"status\":\"unpaid\",\"created_by\":1,\"notes\":\"heheehee\",\"_description\":\"Room bill for 9: bayad bala\"}', '49.145.180.247', '2026-05-31 02:55:58'),
(54, 1, 'bill_created', 'bill', 9, NULL, '{\"room_id\":2,\"tenant_id\":null,\"billing_type\":\"room_based\",\"charge_category\":\"utility\",\"bill_name\":\"bayad once again\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":1000,\"due_date\":\"2026-06-10\",\"status\":\"unpaid\",\"created_by\":1,\"notes\":\"sasajsnakass\",\"_description\":\"Room bill for 9: bayad once again\"}', '49.145.180.247', '2026-05-31 03:19:18'),
(55, 16, 'updated', 'testimonial', 2, NULL, '{\"_description\":\"Updated a review\\/testimonial\"}', '49.145.180.247', '2026-05-31 06:30:41'),
(56, 16, 'submitted', 'testimonial', 4, NULL, '{\"_description\":\"Submitted a review\\/testimonial\"}', '49.145.180.247', '2026-05-31 06:35:03'),
(57, 16, 'updated', 'testimonial', 4, NULL, '{\"_description\":\"Updated a review\\/testimonial\"}', '49.145.180.247', '2026-05-31 06:38:00'),
(58, 18, 'payment_submitted', 'payment', 13, NULL, '{\"amount_paid\":500,\"payment_method\":\"gcash\",\"proof_file_path\":\"0a94c91e6d999ec501084e6a915f7c78.jpg\",\"proof_file_name\":\"1000034353.jpg\",\"notes\":null,\"is_partial\":true,\"_description\":\"Tenant submitted a partial payment of \\u20b1500.00 for \\\"bayad bala\\\"\"}', '2001:4454:1b8:d900:fcf6:9447:a7ab:b273', '2026-05-31 13:26:16'),
(59, 18, 'payment_submitted', 'payment', 14, NULL, '{\"amount_paid\":500,\"payment_method\":\"gcash\",\"proof_file_path\":\"06b56ca4e95994bd995f00ac3b4ad729.jpg\",\"proof_file_name\":\"1000034590.jpg\",\"notes\":null,\"is_partial\":true,\"_description\":\"Tenant submitted a partial payment of \\u20b1500.00 for \\\"bayad once again\\\"\"}', '2001:4454:1b8:d900:fcf6:9447:a7ab:b273', '2026-05-31 13:29:31'),
(60, 1, 'complaint_updated', 'complaint', 2, '{\"status\":\"pending\"}', '{\"status\":\"resolved\",\"_description\":\"Complaint status updated\"}', '49.145.180.247', '2026-05-31 13:45:12'),
(61, 1, 'complaint_updated', 'complaint', 2, '{\"status\":\"resolved\"}', '{\"status\":\"resolved\",\"_description\":\"Complaint status updated\"}', '2001:4454:1b8:d900:fcf6:9447:a7ab:b273', '2026-05-31 13:46:31'),
(62, 1, 'bill_updated', 'bill', 9, '{\"id\":9,\"tenant_id\":null,\"room_id\":2,\"billing_type\":\"room_based\",\"charge_category\":\"utility\",\"bill_name\":\"bayad once again\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":\"1000.00\",\"original_amount\":\"1000.00\",\"penalty_amount\":\"0.00\",\"penalty_applied_at\":null,\"missed_cycles\":0,\"last_penalty_applied_at\":null,\"last_penalty_notification_cycle\":null,\"overdue_notified_at\":null,\"last_penalty_notification_at\":null,\"amount_paid\":\"0.00\",\"partial_payment_status\":\"none\",\"last_payment_date\":null,\"penalty_count\":0,\"due_date\":\"2026-06-10\",\"status\":\"pending_verification\",\"created_by\":1,\"paid_at\":null,\"reminder_sent_1\":0,\"reminder_sent_2\":0,\"reminder_sent_3\":0,\"reminder_dates\":null,\"payment_plan_id\":null,\"notes\":\"sasajsnakass\",\"created_at\":\"2026-05-31 03:19:18\",\"updated_at\":\"2026-05-31 13:29:31\"}', '{\"bill_name\":\"bayad once again\",\"charge_category\":\"utility\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":1000,\"due_date\":\"2026-06-10\",\"notes\":\"sasajsnakass\",\"status\":\"paid\",\"_description\":\"Bill updated: bayad once again\"}', '49.145.180.247', '2026-05-31 13:53:10'),
(63, 1, 'bill_updated', 'bill', 8, '{\"id\":8,\"tenant_id\":null,\"room_id\":2,\"billing_type\":\"room_based\",\"charge_category\":\"rent\",\"bill_name\":\"bayad bala\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":\"1500.00\",\"original_amount\":\"1500.00\",\"penalty_amount\":\"0.00\",\"penalty_applied_at\":null,\"missed_cycles\":0,\"last_penalty_applied_at\":null,\"last_penalty_notification_cycle\":null,\"overdue_notified_at\":null,\"last_penalty_notification_at\":null,\"amount_paid\":\"0.00\",\"partial_payment_status\":\"none\",\"last_payment_date\":null,\"penalty_count\":0,\"due_date\":\"2026-06-10\",\"status\":\"pending_verification\",\"created_by\":1,\"paid_at\":null,\"reminder_sent_1\":0,\"reminder_sent_2\":0,\"reminder_sent_3\":0,\"reminder_dates\":null,\"payment_plan_id\":null,\"notes\":\"heheehee\",\"created_at\":\"2026-05-31 02:55:58\",\"updated_at\":\"2026-05-31 13:26:16\"}', '{\"bill_name\":\"bayad bala\",\"charge_category\":\"rent\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":1500,\"due_date\":\"2026-06-10\",\"notes\":\"heheehee\",\"status\":\"paid\",\"_description\":\"Bill updated: bayad bala\"}', '49.145.180.247', '2026-05-31 14:02:07'),
(64, 1, 'bill_created', 'bill', 10, NULL, '{\"room_id\":2,\"tenant_id\":null,\"billing_type\":\"room_based\",\"charge_category\":\"rent\",\"bill_name\":\"bayad oiii\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":1000,\"due_date\":\"2026-06-10\",\"status\":\"unpaid\",\"created_by\":1,\"notes\":\"adaasasas\",\"_description\":\"Room bill for 9: bayad oiii\"}', '49.145.180.247', '2026-05-31 15:28:16'),
(65, 16, 'payment_submitted', 'payment', 15, NULL, '{\"amount_paid\":500,\"payment_method\":\"gcash\",\"proof_file_path\":\"0d6e9367b3d76951141b3a76853b55dc.jpg\",\"proof_file_name\":\"landingpagebg.jpg\",\"notes\":\"ahhhh\",\"is_partial\":true,\"_description\":\"Tenant submitted a partial payment of \\u20b1500.00 for \\\"bayad oiii\\\"\"}', '49.145.180.247', '2026-05-31 15:28:56'),
(66, 1, 'payment_approved', 'payment', 15, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '49.145.180.247', '2026-05-31 16:41:15'),
(67, 1, 'payment_approved', 'payment', 14, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '98.98.43.123', '2026-05-31 16:53:11'),
(68, 14, 'payment_submitted', 'payment', 16, NULL, '{\"amount_paid\":500,\"payment_method\":\"cash\",\"proof_file_path\":\"dd428d8b6fae5c3e7c4b9c65a538f7ba.png\",\"proof_file_name\":\"Screenshot 2026-06-01 004332.png\",\"notes\":null,\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b1500.00 for \\\"bayad once again\\\"\"}', '98.98.43.123', '2026-05-31 16:54:57'),
(69, 14, 'payment_submitted', 'payment', 17, NULL, '{\"amount_paid\":500,\"payment_method\":\"cash\",\"proof_file_path\":\"dd0cbcabc356f45e0d38beb5c187dced.png\",\"proof_file_name\":\"Screenshot 2026-06-01 004332.png\",\"notes\":null,\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b1500.00 for \\\"bayad oiii\\\"\"}', '98.98.43.123', '2026-05-31 16:55:31'),
(70, 1, 'payment_approved', 'payment', 17, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '98.98.43.123', '2026-05-31 16:57:01'),
(71, 1, 'payment_approved', 'payment', 16, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '98.98.43.123', '2026-05-31 16:57:10'),
(72, 1, 'payment_approved', 'payment', 13, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved: thanks\"}', '2001:4454:1b8:d900:fcf6:9447:a7ab:b273', '2026-05-31 17:00:31'),
(73, 1, 'bill_created', 'bill', 11, NULL, '{\"room_id\":2,\"tenant_id\":16,\"billing_type\":\"individual\",\"charge_category\":\"rent\",\"bill_name\":\"try bill\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":1000,\"due_date\":\"2026-06-30\",\"status\":\"unpaid\",\"created_by\":1,\"notes\":null,\"_description\":\"Individual bill for tenant #16: try bill\"}', '2001:4454:1b8:d900:fcf6:9447:a7ab:b273', '2026-05-31 17:11:34'),
(74, 18, 'payment_submitted', 'payment', 18, NULL, '{\"amount_paid\":200,\"payment_method\":\"gcash\",\"proof_file_path\":\"59e886c67fd23bfd0560883278a9f1af.jpg\",\"proof_file_name\":\"1000034353.jpg\",\"notes\":\"200 anay sah\",\"is_partial\":true,\"_description\":\"Tenant submitted a partial payment of \\u20b1200.00 for \\\"try bill\\\"\"}', '2001:4454:1b8:d900:fcf6:9447:a7ab:b273', '2026-05-31 17:18:07'),
(75, 1, 'payment_approved', 'payment', 18, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '2001:4454:1b8:d900:fcf6:9447:a7ab:b273', '2026-05-31 17:34:10'),
(76, 18, 'payment_submitted', 'payment', 19, NULL, '{\"amount_paid\":400,\"payment_method\":\"gcash\",\"proof_file_path\":\"079a6afb74b48f951123df4d57d99d13.jpg\",\"proof_file_name\":\"1000034353.jpg\",\"notes\":null,\"is_partial\":true,\"_description\":\"Tenant submitted a partial payment of \\u20b1400.00 for \\\"try bill\\\"\"}', '2001:4454:1b8:d900:fcf6:9447:a7ab:b273', '2026-05-31 17:39:03'),
(77, 1, 'payment_approved', 'payment', 19, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '2001:4454:1b8:d900:fcf6:9447:a7ab:b273', '2026-05-31 17:39:43'),
(78, 1, 'bill_deleted', 'bill', 11, '{\"id\":11,\"tenant_id\":16,\"room_id\":2,\"billing_type\":\"individual\",\"charge_category\":\"rent\",\"bill_name\":\"try bill\",\"billing_period_start\":\"2026-05-01\",\"billing_period_end\":\"2026-05-31\",\"amount\":\"1000.00\",\"original_amount\":null,\"penalty_amount\":\"0.00\",\"penalty_applied_at\":null,\"missed_cycles\":0,\"last_penalty_applied_at\":null,\"last_penalty_notification_cycle\":null,\"overdue_notified_at\":null,\"last_penalty_notification_at\":null,\"amount_paid\":\"600.00\",\"partial_payment_status\":\"partial\",\"last_payment_date\":\"2026-05-31\",\"penalty_count\":0,\"due_date\":\"2026-06-30\",\"status\":\"partial\",\"created_by\":1,\"paid_at\":null,\"reminder_sent_1\":0,\"reminder_sent_2\":0,\"reminder_sent_3\":0,\"reminder_dates\":null,\"payment_plan_id\":null,\"notes\":null,\"created_at\":\"2026-05-31 17:11:34\",\"updated_at\":\"2026-05-31 17:39:43\"}', '{\"_description\":\"Bill deleted: try bill (with 2 payment(s))\"}', '2001:4454:1b8:d900:fcf6:9447:a7ab:b273', '2026-05-31 17:43:59'),
(79, 14, 'payment_submitted', 'payment', 20, NULL, '{\"amount_paid\":1000,\"payment_method\":\"cash\",\"proof_file_path\":\"7339a44f39e0bc05dd656aa7d53bc5e2.jpg\",\"proof_file_name\":\"1000058830.jpg\",\"notes\":null,\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b11,000.00 for \\\"bayad bala\\\"\"}', '49.145.180.247', '2026-05-31 20:21:38'),
(80, 1, 'payment_approved', 'payment', 20, '{\"status\":\"pending\"}', '{\"status\":\"approved\",\"_description\":\"Payment approved\"}', '49.145.180.247', '2026-05-31 20:22:10'),
(81, 14, 'password_changed', 'user', 14, NULL, '{\"_description\":\"User changed password via tenant portal\"}', '98.98.43.168', '2026-05-31 21:43:48'),
(82, 1, 'complaint_updated', 'complaint', 3, '{\"status\":\"pending\"}', '{\"status\":\"resolved\",\"_description\":\"Complaint status updated\"}', '49.145.180.247', '2026-05-31 23:43:31'),
(83, 1, 'bill_created', 'bill', 12, NULL, '{\"room_id\":2,\"tenant_id\":null,\"billing_type\":\"room_based\",\"charge_category\":\"rent\",\"bill_name\":\"dadadadd\",\"billing_period_start\":\"2026-06-01\",\"billing_period_end\":\"2026-06-30\",\"amount\":1000,\"due_date\":\"2026-06-11\",\"status\":\"unpaid\",\"created_by\":1,\"notes\":null,\"_description\":\"Room bill for 9: dadadadd\"}', '175.176.74.230', '2026-06-01 00:15:13'),
(84, 1, 'bill_created', 'bill', 13, NULL, '{\"room_id\":2,\"tenant_id\":null,\"billing_type\":\"room_based\",\"charge_category\":\"rent\",\"bill_name\":\"ddada\",\"billing_period_start\":\"2026-06-01\",\"billing_period_end\":\"2026-06-30\",\"amount\":1000,\"due_date\":\"2026-06-04\",\"status\":\"unpaid\",\"created_by\":1,\"notes\":\"aaasasas\",\"_description\":\"Room bill for 9: ddada\"}', '175.176.74.230', '2026-06-01 00:15:50'),
(85, 16, 'payment_submitted', 'payment', 21, NULL, '{\"amount_paid\":1000,\"payment_method\":\"gcash\",\"proof_file_path\":\"73b8ce6ad7a7ff9d5d9962504276ab19.png\",\"proof_file_name\":\"boardtrackLogo.png\",\"notes\":null,\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b11,000.00 for \\\"ddada\\\"\"}', '175.176.74.230', '2026-06-01 00:26:13'),
(86, 14, 'payment_submitted', 'payment', 22, NULL, '{\"amount_paid\":1000,\"payment_method\":\"cash\",\"proof_file_path\":\"e853bba54e67bd423e212bcda595a91d.jpg\",\"proof_file_name\":\"AHOF.jpg\",\"notes\":null,\"is_partial\":false,\"_description\":\"Tenant submitted a payment of \\u20b11,000.00 for \\\"dadadadd\\\"\"}', '175.176.74.230', '2026-06-01 00:32:58');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `room_id` int(11) NOT NULL,
  `billing_type` enum('individual','room_based') NOT NULL DEFAULT 'room_based',
  `charge_category` enum('rent','utility','maintenance','penalty','other') NOT NULL DEFAULT 'rent',
  `bill_name` varchar(100) NOT NULL,
  `billing_period_start` date NOT NULL,
  `billing_period_end` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `original_amount` decimal(10,2) DEFAULT NULL,
  `penalty_amount` decimal(10,2) DEFAULT 0.00,
  `penalty_applied_at` timestamp NULL DEFAULT NULL,
  `missed_cycles` int(11) DEFAULT 0,
  `last_penalty_applied_at` datetime DEFAULT NULL,
  `last_penalty_notification_cycle` int(11) DEFAULT NULL,
  `overdue_notified_at` timestamp NULL DEFAULT NULL,
  `last_penalty_notification_at` datetime DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `partial_payment_status` enum('none','partial','full') DEFAULT 'none',
  `last_payment_date` date DEFAULT NULL,
  `penalty_count` int(11) DEFAULT 0,
  `due_date` date NOT NULL,
  `status` enum('unpaid','pending_verification','partial','paid','overdue','cancelled','payment_plan') DEFAULT 'unpaid',
  `created_by` int(11) NOT NULL,
  `paid_at` datetime DEFAULT NULL,
  `reminder_sent_1` tinyint(1) DEFAULT 0,
  `reminder_sent_2` tinyint(1) DEFAULT 0,
  `reminder_sent_3` tinyint(1) DEFAULT 0,
  `reminder_dates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reminder_dates`)),
  `payment_plan_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `tenant_id`, `room_id`, `billing_type`, `charge_category`, `bill_name`, `billing_period_start`, `billing_period_end`, `amount`, `original_amount`, `penalty_amount`, `penalty_applied_at`, `missed_cycles`, `last_penalty_applied_at`, `last_penalty_notification_cycle`, `overdue_notified_at`, `last_penalty_notification_at`, `amount_paid`, `partial_payment_status`, `last_payment_date`, `penalty_count`, `due_date`, `status`, `created_by`, `paid_at`, `reminder_sent_1`, `reminder_sent_2`, `reminder_sent_3`, `reminder_dates`, `payment_plan_id`, `notes`, `created_at`, `updated_at`) VALUES
(1, 14, 2, 'individual', 'rent', 'bayad sak', '2026-05-01', '2026-05-31', 5000.00, 5000.00, 0.00, NULL, 0, NULL, NULL, NULL, NULL, 5000.00, 'full', '2026-05-30', 0, '2026-05-29', 'paid', 1, '2026-05-30 03:07:17', 0, 0, 0, NULL, NULL, 'haaialaansnsnskkskskak', '2026-05-29 03:30:40', '2026-05-31 04:03:35'),
(2, 14, 2, 'individual', 'rent', 'adsdsdsd', '2026-05-01', '2026-05-31', 99999999.99, 99999999.99, 0.00, NULL, 0, NULL, NULL, NULL, NULL, 99999999.99, 'full', '2026-05-30', 0, '2026-05-31', 'paid', 1, '2026-05-30 04:27:00', 0, 0, 0, NULL, NULL, 'sadsdsds', '2026-05-30 03:17:16', '2026-05-31 04:03:35'),
(3, 14, 2, 'individual', 'utility', 'assasasasas', '2026-05-01', '2026-05-31', 12121.00, 12121.00, 0.00, NULL, 0, NULL, NULL, NULL, NULL, 12121.00, 'full', '2026-05-30', 0, '2026-05-31', 'paid', 1, '2026-05-30 04:05:03', 0, 0, 0, NULL, NULL, 'sasasas', '2026-05-30 03:19:48', '2026-05-31 04:03:35'),
(4, 14, 2, 'individual', 'rent', 'dsdsdsdsdsd', '2026-05-30', '2026-05-31', 1000.00, 1000.00, 0.00, NULL, 0, NULL, NULL, NULL, NULL, 1000.00, 'full', '2026-05-31', 0, '2026-06-01', 'paid', 1, '2026-05-31 01:58:32', 0, 0, 0, NULL, NULL, 'dsdsdsdsdsdsddsdsd', '2026-05-30 12:29:30', '2026-05-31 04:03:35'),
(5, NULL, 2, 'room_based', 'rent', 'sasasasasasa', '2026-05-01', '2026-05-31', 1000.00, 1000.00, 0.00, NULL, 0, NULL, NULL, NULL, NULL, 1000.00, 'full', '2026-05-30', 0, '2026-06-29', 'paid', 1, '2026-05-30 15:53:44', 0, 0, 0, NULL, NULL, 'sasasa', '2026-05-30 12:36:14', '2026-05-31 04:03:35'),
(6, 14, 2, 'individual', 'rent', 'bayad po', '2026-05-01', '2026-05-31', 6676677.00, 6676677.00, 0.00, NULL, 0, NULL, NULL, NULL, NULL, 6676677.00, 'full', '2026-05-31', 0, '2026-06-01', 'paid', 1, '2026-05-31 02:16:51', 0, 0, 0, NULL, NULL, 'dadada', '2026-05-31 01:53:13', '2026-05-31 04:03:35'),
(7, NULL, 2, 'room_based', 'rent', 'adadadad', '2026-05-01', '2026-05-31', 1000.00, 1000.00, 0.00, NULL, 0, NULL, NULL, NULL, NULL, 1000.00, 'full', '2026-05-31', 0, '2026-06-04', 'paid', 1, '2026-05-31 02:30:35', 0, 0, 0, NULL, NULL, 'asasass', '2026-05-31 02:20:36', '2026-05-31 04:03:35'),
(8, NULL, 2, 'room_based', 'rent', 'bayad bala', '2026-05-01', '2026-05-31', 1500.00, 1500.00, 0.00, NULL, 0, NULL, NULL, NULL, NULL, 1500.00, 'full', '2026-05-31', 0, '2026-06-10', 'paid', 1, '2026-05-31 20:22:10', 0, 0, 0, NULL, NULL, 'heheehee', '2026-05-31 02:55:58', '2026-05-31 20:22:10'),
(9, NULL, 2, 'room_based', 'utility', 'bayad once again', '2026-05-01', '2026-05-31', 1000.00, 1000.00, 0.00, NULL, 0, NULL, NULL, NULL, NULL, 1000.00, 'full', '2026-05-31', 0, '2026-06-10', 'paid', 1, '2026-05-31 16:57:10', 0, 0, 0, NULL, NULL, 'sasajsnakass', '2026-05-31 03:19:18', '2026-05-31 16:57:10'),
(10, NULL, 2, 'room_based', 'rent', 'bayad oiii', '2026-05-01', '2026-05-31', 1000.00, NULL, 0.00, NULL, 0, NULL, NULL, NULL, NULL, 1000.00, 'full', '2026-05-31', 0, '2026-06-10', 'paid', 1, '2026-05-31 16:57:01', 0, 0, 0, NULL, NULL, 'adaasasas', '2026-05-31 15:28:16', '2026-05-31 16:57:01'),
(12, NULL, 2, 'room_based', 'rent', 'dadadadd', '2026-06-01', '2026-06-30', 1000.00, NULL, 0.00, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 'none', NULL, 0, '2026-06-11', 'pending_verification', 1, NULL, 0, 0, 0, NULL, NULL, NULL, '2026-06-01 00:15:13', '2026-06-01 00:32:58'),
(13, NULL, 2, 'room_based', 'rent', 'ddada', '2026-06-01', '2026-06-30', 1000.00, NULL, 0.00, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 'none', NULL, 0, '2026-06-04', 'pending_verification', 1, NULL, 0, 0, 0, NULL, NULL, 'aaasasas', '2026-06-01 00:15:50', '2026-06-01 00:26:13');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `category` enum('maintenance','roommate_conflict','billing','room_change','other') NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `is_anonymous` tinyint(1) DEFAULT 0,
  `status` enum('pending','in_progress','resolved','closed') DEFAULT 'pending',
  `landlord_response` text DEFAULT NULL,
  `tenant_response` text DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_message_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `tenant_id`, `category`, `title`, `description`, `is_anonymous`, `status`, `landlord_response`, `tenant_response`, `resolved_by`, `resolved_at`, `created_at`, `updated_at`, `last_message_at`) VALUES
(1, 14, 'other', 'other', 'other mani mani other', 0, 'resolved', 'ok', NULL, 1, '2026-05-30 15:21:32', '2026-05-30 03:25:44', '2026-05-31 14:08:11', '2026-05-30 03:25:44'),
(2, 16, 'billing', 'wala ga match ang sa billing sah', 'ang sa biling sak miski wala ko may gin bayad or miski pila lang naga zero gid na sa matic ya? hahaha ang sa due payment na 1000 pag submit ko ka payment ga 0 dayon sak', 0, 'resolved', '', NULL, 1, '2026-05-31 13:46:31', '2026-05-30 15:24:23', '2026-05-31 14:08:11', '2026-05-30 15:24:23'),
(3, 14, 'maintenance', 'Waay tubi', 'Waay gatubod tubig', 0, 'resolved', '', NULL, 1, '2026-05-31 23:43:31', '2026-05-31 23:43:07', '2026-05-31 23:43:31', NULL),
(4, 12, 'maintenance', 'Water shortage', 'No water flow', 0, 'pending', NULL, NULL, NULL, NULL, '2026-06-01 00:32:04', '2026-06-01 00:32:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `complaint_messages`
--

CREATE TABLE `complaint_messages` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('tenant','landlord') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `user_id`, `token`, `expires_at`, `verified_at`, `created_at`) VALUES
(1, 13, '112ff46a322f762bd41143181f02b0c21a5cc4c5cee999ad53dca5be5d57c6f7', '2026-05-29 03:14:10', NULL, '2026-05-28 01:14:10'),
(2, 14, 'ff6da3d2fed18ee1adaf4d48e8f09c4aeca16c9e843bb5f29f74737c3693eced', '2026-05-29 10:42:03', NULL, '2026-05-28 10:42:03'),
(3, 15, 'c681834441e0a13b8c4319dfca190037bcc62483b9b3d4a63790adb14a0af949', '2026-05-29 10:46:43', NULL, '2026-05-28 10:46:43'),
(4, 16, '29b24086190fcebb469b2149e6888ef28c9c42185af9d046c5d59dc3bb3b9c0c', '2026-05-30 03:22:02', NULL, '2026-05-29 03:22:02'),
(5, 17, 'a1538707941f089dbc4e556bbd3c8d3e82a2464f5d72bc358ca1ceb164fdd685', '2026-05-30 03:31:20', NULL, '2026-05-29 03:31:20'),
(6, 18, '8330c87f6690a632fcbc2a03158a06f18dfade848680589cb297df16049eda76', '2026-05-31 14:52:35', NULL, '2026-05-30 14:52:35');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` enum('plumbing','electrical','carpentry','painting','cleaning','appliance','other') NOT NULL DEFAULT 'other',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `assigned_to` varchar(100) DEFAULT NULL,
  `estimated_cost` decimal(10,2) DEFAULT NULL,
  `actual_cost` decimal(10,2) DEFAULT NULL,
  `bill_id` int(11) DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT current_timestamp(),
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('system','billing','complaint','announcement','room','payment') DEFAULT 'system',
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `is_read`, `read_at`, `link_url`, `created_at`) VALUES
(3, 16, 'room', 'Room Assigned', 'You have been approved and assigned to a room. Roommate compatibility: no current roommates yet. Air-conditioning preference: not matched.', 1, '2026-05-29 03:27:56', 'tenant/dashboard', '2026-05-29 03:26:07'),
(5, 1, 'payment', 'New Payment to Review', 'John Reniel Balsacao submitted a  GCash payment of ₱5,000.00 for \"bayad sak\".', 1, '2026-05-30 15:58:01', 'landlord/view-payment/1', '2026-05-30 03:06:31'),
(8, 1, 'payment', 'New Payment to Review', 'John Reniel Balsacao submitted a  GCash payment of ₱99,999,999.99 for \"adsdsdsd\".', 1, '2026-05-30 15:57:58', 'landlord/view-payment/2', '2026-05-30 03:18:00'),
(9, 16, 'payment', 'Payment Rejected', 'Your payment of ₱99,999,999.99 for \"adsdsdsd\" was rejected. Reason: asasasasas', 1, '2026-05-30 03:22:56', 'tenant/bills', '2026-05-30 03:18:56'),
(11, 1, 'payment', 'New Payment to Review', 'John Reniel Balsacao submitted a  GCash payment of ₱99,999,999.99 for \"adsdsdsd\".', 1, '2026-05-30 03:22:40', 'landlord/view-payment/3', '2026-05-30 03:20:16'),
(12, 1, 'payment', 'New Payment to Review', 'John Reniel Balsacao submitted a  GCash payment of ₱12,121.00 for \"assasasasas\".', 1, '2026-05-30 15:57:52', 'landlord/view-payment/4', '2026-05-30 03:23:13'),
(13, 16, 'payment', 'Payment Rejected', 'Your payment of ₱12,121.00 for \"assasasasas\" was rejected. Reason: sos oi', 1, '2026-05-30 03:23:56', 'tenant/bills', '2026-05-30 03:23:45'),
(14, 1, 'complaint', 'New Complaint', 'John Reniel Balsacao submitted: other', 1, '2026-05-30 03:25:55', 'landlord/view-complaint/1', '2026-05-30 03:25:44'),
(15, 16, 'complaint', 'Complaint Updated', 'Your complaint status updated to: In progress. Landlord response: wowowow', 1, '2026-05-30 03:26:17', 'tenant/view-complaint/1', '2026-05-30 03:26:09'),
(16, 1, 'payment', 'New Payment to Review', 'John Reniel Balsacao submitted a  GCash payment of ₱12,121.00 for \"assasasasas\".', 1, '2026-05-30 15:57:45', 'landlord/view-payment/5', '2026-05-30 03:27:56'),
(18, 16, 'payment', 'Payment Confirmed', 'Your payment of ₱99,999,999.99 for \"adsdsdsd\" was verified and confirmed by your landlord. (GCash)', 1, '2026-05-31 07:10:26', 'tenant/bills', '2026-05-30 04:27:00'),
(19, 1, '', 'Tenant Profile Updated', 'Tenant profile information has been updated.', 1, '2026-05-30 15:57:28', 'landlord/view-tenant/14', '2026-05-30 10:24:00'),
(20, 16, 'complaint', 'Complaint Updated', 'Your complaint status updated to: In progress. Landlord response: doldoldol', 1, '2026-05-30 11:25:26', 'tenant/view-complaint/1', '2026-05-30 11:18:11'),
(21, 1, '', 'New Review Submitted', 'John Reniel Balsacao has submitted a review for BoardTrack.', 1, '2026-05-30 15:19:54', 'https://boardtrack.bsit2a.com/index.php?url=landlord/dashboard', '2026-05-30 11:39:25'),
(22, 16, 'announcement', 'dsdsds', 'dsdsdsds', 1, '2026-05-31 07:10:25', 'tenant/notifications', '2026-05-30 12:26:59'),
(23, 9, 'announcement', 'dsdsds', 'dsdsdsds', 0, NULL, 'tenant/notifications', '2026-05-30 12:26:59'),
(24, 8, 'announcement', 'dsdsds', 'dsdsdsds', 0, NULL, 'tenant/notifications', '2026-05-30 12:26:59'),
(25, 16, 'billing', 'New Bill Assigned', 'A new bill \"dsdsdsdsdsd\" of ₱1,000.00 has been issued to you.', 1, '2026-05-31 07:10:24', 'tenant/bills', '2026-05-30 12:29:30'),
(26, 16, 'billing', 'New Bill for Room 9', 'A new room bill \"sasasasasasa\" of ₱1,000.00 has been issued.', 1, '2026-05-31 07:10:22', 'tenant/bills', '2026-05-30 12:36:14'),
(27, 18, 'room', 'Room Assigned', 'You have been approved and assigned to a room. Roommate compatibility: 75% Air-conditioning preference: not matched.', 1, '2026-05-30 15:12:11', 'tenant/dashboard', '2026-05-30 15:09:04'),
(28, 1, '', 'Review Updated', 'John Reniel Balsacao has updated their review for BoardTrack.', 1, '2026-05-30 15:19:49', 'https://boardtrack.bsit2a.com/index.php?url=landlord/dashboard', '2026-05-30 15:19:22'),
(29, 1, 'payment', 'New Payment to Review', 'Reancy Francois B. Langrio submitted a  GCash payment of ₱1,000.00 for \"sasasasasasa\".', 1, '2026-05-30 15:57:22', 'landlord/view-payment/6', '2026-05-30 15:20:31'),
(30, 16, 'complaint', 'Complaint Updated', 'Your complaint status updated to: Resolved. Landlord response: ok', 1, '2026-05-31 07:10:20', 'tenant/view-complaint/1', '2026-05-30 15:21:32'),
(31, 1, 'complaint', 'New Complaint', 'Reancy Francois B. Langrio submitted: wala ga match ang sa billing sah', 1, '2026-05-30 15:56:45', 'landlord/view-complaint/2', '2026-05-30 15:24:23'),
(32, 1, '', 'New Review Submitted', 'Reancy Francois B. Langrio has submitted a review for BoardTrack.', 1, '2026-05-30 15:56:43', 'https://boardtrack.bsit2a.com/index.php?url=landlord/dashboard', '2026-05-30 15:30:51'),
(33, 18, 'payment', 'Payment Confirmed', 'Your payment of ₱1,000.00 for \"sasasasasasa\" was verified and confirmed by your landlord. (GCash)', 1, '2026-05-30 16:22:52', 'tenant/bills', '2026-05-30 15:53:44'),
(34, 18, 'complaint', 'Complaint Updated', 'Your complaint status updated to: Pending. Landlord response: bokas sak ubrahon', 1, '2026-05-30 16:22:37', 'tenant/view-complaint/2', '2026-05-30 15:57:16'),
(35, 17, 'system', 'Application Rejected', 'Your registration was rejected. Reason: incomplete', 0, NULL, 'tenant/dashboard', '2026-05-30 16:07:13'),
(36, 18, 'announcement', 'bday', 'it\'s my bday', 1, '2026-05-30 16:22:36', 'tenant/notifications', '2026-05-30 16:21:08'),
(37, 16, 'announcement', 'bday', 'it\'s my bday', 1, '2026-05-31 00:59:16', 'tenant/notifications', '2026-05-30 16:21:08'),
(38, 9, 'announcement', 'bday', 'it\'s my bday', 0, NULL, 'tenant/notifications', '2026-05-30 16:21:08'),
(39, 8, 'announcement', 'bday', 'it\'s my bday', 0, NULL, 'tenant/notifications', '2026-05-30 16:21:08'),
(40, 16, 'billing', 'New Bill Assigned', 'A new bill \"bayad po\" of ₱6,676,677.00 has been issued to you.', 1, '2026-05-31 07:10:18', 'tenant/bills', '2026-05-31 01:53:13'),
(41, 1, 'payment', 'New Payment to Review', 'John Reniel Balsacao submitted a partial GCash payment of ₱4,999.99 for \"bayad po\".', 1, '2026-05-31 09:19:54', 'landlord/view-payment/7', '2026-05-31 01:54:48'),
(42, 16, 'payment', 'Payment Approved', 'Your payment of ₱4,999.99 for \"bayad po\" was approved. Remaining balance: ₱6,671,677.01 Landlord note: bayad nexttime ha', 1, '2026-05-31 02:04:15', 'tenant/bills', '2026-05-31 01:55:27'),
(43, 1, 'payment', 'New Payment to Review', 'John Reniel Balsacao submitted a  GCash payment of ₱1,000.00 for \"dsdsdsdsdsd\".', 1, '2026-05-31 09:19:52', 'landlord/view-payment/8', '2026-05-31 01:58:01'),
(44, 16, 'payment', 'Payment Approved', 'Your payment of ₱1,000.00 for \"dsdsdsdsdsd\" was approved. Bill is now fully paid!', 1, '2026-05-31 02:04:44', 'tenant/bills', '2026-05-31 01:58:32'),
(45, 1, 'payment', 'New Payment to Review', 'John Reniel Balsacao submitted a  GCash payment of ₱6,671,677.01 for \"bayad po\".', 1, '2026-05-31 02:24:53', 'landlord/view-payment/9', '2026-05-31 02:16:14'),
(46, 16, 'payment', 'Payment Approved', 'Your payment of ₱6,671,677.01 for \"bayad po\" was approved. Bill is now fully paid!', 1, '2026-05-31 07:09:54', 'tenant/bills', '2026-05-31 02:16:51'),
(47, 16, 'billing', 'New Bill for Room 9', 'A new room bill \"adadadad\" of ₱1,000.00 has been issued.', 1, '2026-05-31 07:09:52', 'tenant/bills', '2026-05-31 02:20:36'),
(48, 18, 'billing', 'New Bill for Room 9', 'A new room bill \"adadadad\" of ₱1,000.00 has been issued.', 1, '2026-05-31 13:23:31', 'tenant/bills', '2026-05-31 02:20:36'),
(49, 14, 'room', 'Account Approved', 'Your account is approved. You have been placed on the waiting list.', 1, '2026-05-31 02:28:55', 'tenant/dashboard', '2026-05-31 02:23:50'),
(50, 1, 'payment', 'New Payment to Review', 'John Reniel Balsacao submitted a partial GCash payment of ₱500.00 for \"adadadad\".', 1, '2026-05-31 02:24:52', 'landlord/view-payment/10', '2026-05-31 02:24:07'),
(51, 16, 'payment', 'Payment Approved', 'Your payment of ₱500.00 for \"adadadad\" was approved. Remaining balance: ₱500.00 Landlord note: bayad na nexttime ha', 1, '2026-05-31 07:09:50', 'tenant/bills', '2026-05-31 02:24:48'),
(52, 14, 'room', 'Room Assigned', 'You have been approved and assigned to a room. Roommate compatibility: 77.5% Air-conditioning preference: not matched.', 1, '2026-05-31 02:28:51', 'tenant/dashboard', '2026-05-31 02:25:35'),
(53, 1, 'payment', 'New Payment to Review', 'John Reniel Balsacao submitted a partial GCash payment of ₱250.00 for \"adadadad\".', 1, '2026-05-31 08:37:04', 'landlord/view-payment/11', '2026-05-31 02:26:05'),
(54, 16, 'payment', 'Payment Approved', 'Your payment of ₱250.00 for \"adadadad\" was approved. Remaining balance: ₱250.00 Landlord note: baw a', 1, '2026-05-31 02:38:35', 'tenant/bills', '2026-05-31 02:26:41'),
(55, 1, 'payment', 'New Payment to Review', 'Sheena May Espolon submitted a  Cash (in person) payment of ₱250.00 for \"adadadad\".', 1, '2026-05-31 02:30:29', 'landlord/view-payment/12', '2026-05-31 02:30:13'),
(56, 14, 'payment', 'Payment Approved', 'Your payment of ₱250.00 for \"adadadad\" was approved. Bill is now fully paid!', 1, '2026-05-31 02:30:58', 'tenant/bills', '2026-05-31 02:30:35'),
(57, 16, 'billing', 'New Bill for Room 9', 'A new room bill \"bayad bala\" of ₱1,500.00 has been issued.', 1, '2026-05-31 07:09:47', 'tenant/bills', '2026-05-31 02:55:58'),
(58, 18, 'billing', 'New Bill for Room 9', 'A new room bill \"bayad bala\" of ₱1,500.00 has been issued.', 1, '2026-05-31 13:23:11', 'tenant/bills', '2026-05-31 02:55:58'),
(59, 14, 'billing', 'New Bill for Room 9', 'A new room bill \"bayad bala\" of ₱1,500.00 has been issued.', 1, '2026-05-31 13:59:47', 'tenant/bills', '2026-05-31 02:55:58'),
(60, 16, 'billing', 'New Bill for Room 9', 'A new room bill \"bayad once again\" of ₱1,000.00 has been issued.', 1, '2026-05-31 03:20:38', 'tenant/bills', '2026-05-31 03:19:18'),
(61, 18, 'billing', 'New Bill for Room 9', 'A new room bill \"bayad once again\" of ₱1,000.00 has been issued.', 1, '2026-05-31 13:22:59', 'tenant/bills', '2026-05-31 03:19:18'),
(62, 14, 'billing', 'New Bill for Room 9', 'A new room bill \"bayad once again\" of ₱1,000.00 has been issued.', 1, '2026-05-31 14:00:08', 'tenant/bills', '2026-05-31 03:19:18'),
(63, 1, '', 'Review Updated', 'John Reniel Balsacao has updated their review for BoardTrack.', 1, '2026-05-31 06:30:50', 'https://boardtrack.bsit2a.com/index.php?url=landlord/dashboard', '2026-05-31 06:30:41'),
(64, 1, '', 'New Review Submitted', 'John Reniel Balsacao has submitted a 5-star review for BoardTrack.', 1, '2026-05-31 06:35:12', 'landlord/reviews', '2026-05-31 06:35:03'),
(65, 1, '', 'Review Updated', 'John Reniel Balsacao has updated their review for BoardTrack.', 1, '2026-05-31 06:38:07', 'landlord/reviews', '2026-05-31 06:38:00'),
(66, 1, 'payment', 'New Payment to Review', 'Reancy Francois B. Langrio submitted a partial GCash payment of ₱500.00 for \"bayad bala\".', 1, '2026-05-31 13:42:58', 'landlord/view-payment/13', '2026-05-31 13:26:16'),
(67, 1, 'payment', 'New Payment to Review', 'Reancy Francois B. Langrio submitted a partial GCash payment of ₱500.00 for \"bayad once again\".', 1, '2026-05-31 13:42:55', 'landlord/view-payment/14', '2026-05-31 13:29:31'),
(68, 18, 'complaint', 'Complaint Updated', 'Your complaint status updated to: Resolved', 1, '2026-05-31 16:53:28', 'tenant/view-complaint/2', '2026-05-31 13:45:12'),
(69, 18, 'complaint', 'Complaint Updated', 'Your complaint status updated to: Resolved', 1, '2026-05-31 16:53:18', 'tenant/view-complaint/2', '2026-05-31 13:46:31'),
(70, 16, 'billing', 'New Bill for Room 9', 'A new room bill \"bayad oiii\" of ₱1,000.00 has been issued.', 1, '2026-05-31 16:39:54', 'tenant/bills', '2026-05-31 15:28:16'),
(71, 18, 'billing', 'New Bill for Room 9', 'A new room bill \"bayad oiii\" of ₱1,000.00 has been issued.', 1, '2026-05-31 16:52:24', 'tenant/bills', '2026-05-31 15:28:16'),
(72, 14, 'billing', 'New Bill for Room 9', 'A new room bill \"bayad oiii\" of ₱1,000.00 has been issued.', 1, '2026-05-31 16:54:17', 'tenant/bills', '2026-05-31 15:28:16'),
(73, 1, 'payment', 'New Payment to Review', 'John Reniel Balsacao submitted a partial GCash payment of ₱500.00 for \"bayad oiii\".', 1, '2026-05-31 16:41:09', 'landlord/view-payment/15', '2026-05-31 15:28:56'),
(74, 16, 'payment', 'Payment Approved', 'Your payment of ₱500.00 for \"bayad oiii\" was approved. Remaining balance: ₱500.00', 1, '2026-05-31 18:08:18', 'tenant/bills', '2026-05-31 16:41:15'),
(75, 18, 'payment', 'Payment Approved', 'Your payment of ₱500.00 for \"bayad once again\" was approved. Remaining balance: ₱500.00', 1, '2026-05-31 16:53:14', 'tenant/bills', '2026-05-31 16:53:11'),
(76, 1, 'payment', 'New Payment to Review', 'Sheena May Espolon submitted a  Cash (in person) payment of ₱500.00 for \"bayad once again\".', 1, '2026-05-31 17:02:05', 'landlord/view-payment/16', '2026-05-31 16:54:57'),
(77, 1, 'payment', 'New Payment to Review', 'Sheena May Espolon submitted a  Cash (in person) payment of ₱500.00 for \"bayad oiii\".', 1, '2026-05-31 17:02:02', 'landlord/view-payment/17', '2026-05-31 16:55:31'),
(78, 14, 'payment', 'Payment Approved', 'Your payment of ₱500.00 for \"bayad oiii\" was approved. Bill is now fully paid!', 1, '2026-05-31 20:19:36', 'tenant/bills', '2026-05-31 16:57:01'),
(79, 14, 'payment', 'Payment Approved', 'Your payment of ₱500.00 for \"bayad once again\" was approved. Bill is now fully paid!', 1, '2026-05-31 20:19:32', 'tenant/bills', '2026-05-31 16:57:10'),
(80, 18, 'payment', 'Payment Approved', 'Your payment of ₱500.00 for \"bayad bala\" was approved. Remaining balance: ₱1,000.00 Landlord note: thanks', 1, '2026-05-31 17:13:46', 'tenant/bills', '2026-05-31 17:00:31'),
(81, 18, 'billing', 'New Bill Assigned', 'A new bill \"try bill\" of ₱1,000.00 has been issued to you.', 1, '2026-05-31 17:13:49', 'tenant/bills', '2026-05-31 17:11:34'),
(82, 1, 'payment', 'New Payment to Review', 'Reancy Francois B. Langrio submitted a partial GCash payment of ₱200.00 for \"try bill\".', 1, '2026-05-31 17:32:52', 'landlord/view-payment/18', '2026-05-31 17:18:07'),
(83, 18, 'payment', 'Payment Approved', 'Your payment of ₱200.00 for \"try bill\" was approved. Remaining balance: ₱800.00', 1, '2026-05-31 17:37:47', 'tenant/bills', '2026-05-31 17:34:10'),
(84, 1, 'payment', 'New Payment to Review', 'Reancy Francois B. Langrio submitted a partial GCash payment of ₱400.00 for \"try bill\".', 1, '2026-05-31 17:39:37', 'landlord/view-payment/19', '2026-05-31 17:39:03'),
(85, 18, 'payment', 'Payment Approved', 'Your payment of ₱400.00 for \"try bill\" was approved. Remaining balance: ₱400.00', 1, '2026-05-31 17:40:32', 'tenant/bills', '2026-05-31 17:39:43'),
(86, 1, 'payment', 'New Payment to Review', 'Sheena May Espolon submitted a  Cash (in person) payment of ₱1,000.00 for \"bayad bala\".', 1, '2026-05-31 20:22:03', 'landlord/view-payment/20', '2026-05-31 20:21:38'),
(87, 14, 'payment', 'Payment Approved', 'Your payment of ₱1,000.00 for \"bayad bala\" was approved. Bill is now fully paid!', 1, '2026-05-31 20:44:50', 'tenant/bills', '2026-05-31 20:22:10'),
(88, 1, 'complaint', 'New Complaint', 'John Reniel Balsacao submitted: Waay tubi', 1, '2026-05-31 23:43:48', 'landlord/view-complaint/3', '2026-05-31 23:43:07'),
(89, 16, 'complaint', 'Complaint Updated', 'Your complaint status updated to: Resolved', 1, '2026-05-31 23:45:33', 'tenant/view-complaint/3', '2026-05-31 23:43:31'),
(90, 16, 'billing', 'New Bill for Room 9', 'A new room bill \"dadadadd\" of ₱1,000.00 has been issued.', 0, NULL, 'tenant/bills', '2026-06-01 00:15:13'),
(91, 18, 'billing', 'New Bill for Room 9', 'A new room bill \"dadadadd\" of ₱1,000.00 has been issued.', 0, NULL, 'tenant/bills', '2026-06-01 00:15:13'),
(92, 14, 'billing', 'New Bill for Room 9', 'A new room bill \"dadadadd\" of ₱1,000.00 has been issued.', 1, '2026-06-01 00:20:11', 'tenant/bills', '2026-06-01 00:15:13'),
(93, 16, 'billing', 'New Bill for Room 9', 'A new room bill \"ddada\" of ₱1,000.00 has been issued.', 0, NULL, 'tenant/bills', '2026-06-01 00:15:50'),
(94, 18, 'billing', 'New Bill for Room 9', 'A new room bill \"ddada\" of ₱1,000.00 has been issued.', 0, NULL, 'tenant/bills', '2026-06-01 00:15:50'),
(95, 14, 'billing', 'New Bill for Room 9', 'A new room bill \"ddada\" of ₱1,000.00 has been issued.', 1, '2026-06-01 00:20:06', 'tenant/bills', '2026-06-01 00:15:50'),
(96, 1, 'payment', 'New Payment to Review', 'John Reniel Balsacao submitted a  GCash payment of ₱1,000.00 for \"ddada\".', 1, '2026-06-01 00:30:05', 'landlord/view-payment/21', '2026-06-01 00:26:13'),
(97, 1, 'complaint', 'New Complaint', 'Sheena May Espolon submitted: Water shortage', 0, NULL, 'landlord/view-complaint/4', '2026-06-01 00:32:04'),
(98, 1, 'payment', 'New Payment to Review', 'Sheena May Espolon submitted a  Cash (in person) payment of ₱1,000.00 for \"dadadadd\".', 0, NULL, 'landlord/view-payment/22', '2026-06-01 00:32:58');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 9, '59fc056c575da0e7331f6f0b7617233017b382f36b58faa3ba5d8733e0736919', '2026-05-27 15:17:25', '2026-05-27 20:17:29', '2026-05-27 12:17:25'),
(2, 9, 'a4cc434887cd01697216f677eaa4abb0808c313607260fbf22d92c9b18f24990', '2026-05-27 15:17:29', NULL, '2026-05-27 12:17:29'),
(3, 14, 'e8628070da74b894945c6389fb3f0ab83b0985a9d23b663a587d73e897971688', '2026-05-31 22:45:08', NULL, '2026-05-31 21:45:08');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` enum('bank_transfer','gcash','cash','other') DEFAULT 'other',
  `proof_file_path` varchar(255) NOT NULL,
  `proof_file_name` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_partial` tinyint(1) DEFAULT 0,
  `payment_plan_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `landlord_note` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `bill_id`, `tenant_id`, `amount_paid`, `payment_method`, `proof_file_path`, `proof_file_name`, `notes`, `is_partial`, `payment_plan_id`, `status`, `reviewed_by`, `review_notes`, `landlord_note`, `reviewed_at`, `uploaded_at`) VALUES
(1, 1, 14, 5000.00, 'gcash', '0604f55078b4efb4896b3bbd84b0ea6f.jpg', 'landingpagebg.jpg', 'sssssdsd', 0, NULL, 'approved', 1, NULL, NULL, '2026-05-30 03:07:17', '2026-05-30 03:06:31'),
(2, 2, 14, 99999999.99, 'gcash', '74e93be1e82c66ea94822bbac16492bc.jpg', 'landingpagebg.jpg', 'sdsdsdsdsd', 0, NULL, 'rejected', 1, 'asasasasas', NULL, '2026-05-30 03:18:56', '2026-05-30 03:18:00'),
(3, 2, 14, 99999999.99, 'gcash', '63b13beacd9026a5959469969eacd14a.jpg', 'landingpagebg.jpg', 'asasasas', 0, NULL, 'approved', 1, NULL, NULL, '2026-05-30 04:27:00', '2026-05-30 03:20:16'),
(4, 3, 14, 12121.00, 'gcash', 'fe3f90936e94c645de1516411909f2b2.jpg', 'landingpagebg.jpg', '5555', 0, NULL, 'rejected', 1, 'sos oi', NULL, '2026-05-30 03:23:45', '2026-05-30 03:23:13'),
(5, 3, 14, 12121.00, 'gcash', 'bf2493741f19e6b8b130902ed09c4a3a.jpg', 'landingpagebg.jpg', 'saddsdssd', 0, NULL, 'approved', 1, NULL, NULL, '2026-05-30 04:05:03', '2026-05-30 03:27:56'),
(6, 5, 16, 1000.00, 'gcash', 'b145780c892b4cd5b0b8abd9ebf166e9.jpg', '1000034355.jpg', '300 lng anay sir', 0, NULL, 'approved', 1, NULL, NULL, '2026-05-30 15:53:44', '2026-05-30 15:20:31'),
(7, 6, 14, 4999.99, 'gcash', '7c730388e6244101de83eda500e9c6ee.jpg', '1f886825-cd3a-4b16-91cd-0d01071a5793.jpg', 'asasasasas', 1, NULL, 'approved', 1, NULL, 'bayad nexttime ha', '2026-05-31 01:55:27', '2026-05-31 01:54:48'),
(8, 4, 14, 1000.00, 'gcash', 'f74234555e14e348f32db3894d83f8bd.jpg', 'landingpagebg.jpg', 'adadadad', 0, NULL, 'approved', 1, NULL, NULL, '2026-05-31 01:58:32', '2026-05-31 01:58:01'),
(9, 6, 14, 6671677.01, 'gcash', '78951f5a125b055b25138dfe04eeafd7.jpg', 'landingpagebg.jpg', 'sfsssdsds', 0, NULL, 'approved', 1, NULL, NULL, '2026-05-31 02:16:51', '2026-05-31 02:16:14'),
(10, 7, 14, 500.00, 'gcash', 'd2608a6dcf847161401631d350c44bf3.jpg', 'landingpagebg.jpg', 'ddsda', 1, NULL, 'approved', 1, NULL, 'bayad na nexttime ha', '2026-05-31 02:24:48', '2026-05-31 02:24:07'),
(11, 7, 14, 250.00, 'gcash', '8cbf25540196a7002808ee8f891ed63f.jpg', 'landingpagebg.jpg', NULL, 1, NULL, 'approved', 1, NULL, 'baw a', '2026-05-31 02:26:41', '2026-05-31 02:26:05'),
(12, 7, 12, 250.00, 'cash', 'bcf527124c931063f0906a1304446dc0.jpeg', 'upscalemedia-transformed (1).jpeg', NULL, 0, NULL, 'approved', 1, NULL, NULL, '2026-05-31 02:30:35', '2026-05-31 02:30:13'),
(13, 8, 16, 500.00, 'gcash', '0a94c91e6d999ec501084e6a915f7c78.jpg', '1000034353.jpg', NULL, 1, NULL, 'approved', 1, NULL, 'thanks', '2026-05-31 17:00:31', '2026-05-31 13:26:16'),
(14, 9, 16, 500.00, 'gcash', '06b56ca4e95994bd995f00ac3b4ad729.jpg', '1000034590.jpg', NULL, 1, NULL, 'approved', 1, NULL, NULL, '2026-05-31 16:53:11', '2026-05-31 13:29:31'),
(15, 10, 14, 500.00, 'gcash', '0d6e9367b3d76951141b3a76853b55dc.jpg', 'landingpagebg.jpg', 'ahhhh', 1, NULL, 'approved', 1, NULL, NULL, '2026-05-31 16:41:15', '2026-05-31 15:28:56'),
(16, 9, 12, 500.00, 'cash', 'dd428d8b6fae5c3e7c4b9c65a538f7ba.png', 'Screenshot 2026-06-01 004332.png', NULL, 0, NULL, 'approved', 1, NULL, NULL, '2026-05-31 16:57:10', '2026-05-31 16:54:57'),
(17, 10, 12, 500.00, 'cash', 'dd0cbcabc356f45e0d38beb5c187dced.png', 'Screenshot 2026-06-01 004332.png', NULL, 0, NULL, 'approved', 1, NULL, NULL, '2026-05-31 16:57:01', '2026-05-31 16:55:31'),
(20, 8, 12, 1000.00, 'cash', '7339a44f39e0bc05dd656aa7d53bc5e2.jpg', '1000058830.jpg', NULL, 0, NULL, 'approved', 1, NULL, NULL, '2026-05-31 20:22:10', '2026-05-31 20:21:38'),
(21, 13, 14, 1000.00, 'gcash', '73b8ce6ad7a7ff9d5d9962504276ab19.png', 'boardtrackLogo.png', NULL, 0, NULL, 'pending', NULL, NULL, NULL, NULL, '2026-06-01 00:26:13'),
(22, 12, 12, 1000.00, 'cash', 'e853bba54e67bd423e212bcda595a91d.jpg', 'AHOF.jpg', NULL, 0, NULL, 'pending', NULL, NULL, NULL, NULL, '2026-06-01 00:32:58');

-- --------------------------------------------------------

--
-- Table structure for table `payment_plans`
--

CREATE TABLE `payment_plans` (
  `id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `number_of_installments` int(11) NOT NULL DEFAULT 1,
  `installment_amount` decimal(10,2) NOT NULL,
  `status` enum('active','completed','cancelled','defaulted') DEFAULT 'active',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `next_payment_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_schedule`
--

CREATE TABLE `payment_schedule` (
  `id` int(11) NOT NULL,
  `payment_plan_id` int(11) NOT NULL,
  `installment_number` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','overdue','skipped') DEFAULT 'pending',
  `paid_date` date DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personality_answers`
--

CREATE TABLE `personality_answers` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_value` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `personality_answers`
--

INSERT INTO `personality_answers` (`id`, `tenant_id`, `question_id`, `answer_value`, `created_at`) VALUES
(1, 14, 1, 1, '2026-05-29 03:23:03'),
(2, 14, 2, 2, '2026-05-29 03:23:03'),
(3, 14, 3, 3, '2026-05-29 03:23:03'),
(4, 14, 4, 2, '2026-05-29 03:23:03'),
(5, 14, 5, 3, '2026-05-29 03:23:03'),
(6, 14, 6, 4, '2026-05-29 03:23:03'),
(7, 14, 7, 3, '2026-05-29 03:23:03'),
(8, 14, 8, 2, '2026-05-29 03:23:03'),
(9, 14, 9, 1, '2026-05-29 03:23:03'),
(10, 14, 10, 1, '2026-05-29 03:23:03'),
(11, 16, 1, 1, '2026-05-30 14:54:45'),
(12, 16, 2, 4, '2026-05-30 14:54:45'),
(13, 16, 3, 2, '2026-05-30 14:54:45'),
(14, 16, 4, 3, '2026-05-30 14:54:45'),
(15, 16, 5, 3, '2026-05-30 14:54:45'),
(16, 16, 6, 2, '2026-05-30 14:54:45'),
(17, 16, 7, 3, '2026-05-30 14:54:45'),
(18, 16, 8, 1, '2026-05-30 14:54:45'),
(19, 16, 9, 2, '2026-05-30 14:54:45'),
(20, 16, 10, 3, '2026-05-30 14:54:45'),
(21, 12, 1, 3, '2026-05-31 02:21:22'),
(22, 12, 2, 3, '2026-05-31 02:21:22'),
(23, 12, 3, 2, '2026-05-31 02:21:22'),
(24, 12, 4, 3, '2026-05-31 02:21:22'),
(25, 12, 5, 3, '2026-05-31 02:21:22'),
(26, 12, 6, 2, '2026-05-31 02:21:22'),
(27, 12, 7, 1, '2026-05-31 02:21:22'),
(28, 12, 8, 1, '2026-05-31 02:21:22'),
(29, 12, 9, 2, '2026-05-31 02:21:22'),
(30, 12, 10, 3, '2026-05-31 02:21:22');

-- --------------------------------------------------------

--
-- Table structure for table `personality_questions`
--

CREATE TABLE `personality_questions` (
  `id` int(11) NOT NULL,
  `category` enum('sleep_schedule','cleanliness','noise_tolerance','study_habits','social_preference') NOT NULL DEFAULT 'social_preference',
  `question_text` text NOT NULL,
  `weight` decimal(3,2) NOT NULL DEFAULT 1.00,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personality_questions`
--

INSERT INTO `personality_questions` (`id`, `category`, `question_text`, `weight`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'social_preference', 'After a long day, how do you usually recharge?', 1.00, 1, 1, '2026-05-29 02:51:40'),
(2, 'social_preference', 'How comfortable are you with starting conversations with new people?', 1.00, 2, 1, '2026-05-29 02:51:40'),
(3, 'social_preference', 'What type of room environment do you prefer most?', 1.00, 3, 1, '2026-05-29 02:51:40'),
(4, 'social_preference', 'When living with roommates, which best describes you?', 1.00, 4, 1, '2026-05-29 02:51:40'),
(5, 'social_preference', 'How often do you join social activities or gatherings?', 1.00, 5, 1, '2026-05-29 02:51:40'),
(6, 'social_preference', 'If your roommates invite friends over, how would you react?', 1.00, 6, 1, '2026-05-29 02:51:40'),
(7, 'social_preference', 'Which statement describes you best?', 1.00, 7, 1, '2026-05-29 02:51:40'),
(8, 'social_preference', 'How do you usually handle group discussions or teamwork?', 1.00, 8, 1, '2026-05-29 02:51:40'),
(9, 'social_preference', 'What kind of roommate would make you most comfortable?', 1.00, 9, 1, '2026-05-29 02:51:40'),
(10, 'social_preference', 'Which best describes your personality overall?', 1.00, 10, 1, '2026-05-29 02:51:40');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `floor` int(11) DEFAULT 1,
  `room_type` enum('single','shared') NOT NULL,
  `allowed_gender` enum('male','female','any','prefer_not_to_say') DEFAULT 'any',
  `max_occupants` int(11) DEFAULT 1,
  `current_occupants` int(11) DEFAULT 0,
  `monthly_rent` decimal(10,2) NOT NULL,
  `status` enum('available','occupied','maintenance') DEFAULT 'available',
  `description` text DEFAULT NULL,
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenities`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `air_conditioned` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_number`, `floor`, `room_type`, `allowed_gender`, `max_occupants`, `current_occupants`, `monthly_rent`, `status`, `description`, `amenities`, `created_at`, `updated_at`, `air_conditioned`) VALUES
(1, '69', 1, 'single', 'any', 1, 2, 500.00, 'occupied', NULL, NULL, '2026-03-26 18:22:47', '2026-05-31 12:46:51', 0),
(2, '9', 1, 'shared', 'any', 10, 3, 1000.00, 'occupied', '', NULL, '2026-05-05 01:37:36', '2026-05-31 12:46:51', 0),
(6, '1', 1, 'single', 'any', 1, 0, 3000.00, 'available', '', NULL, '2026-05-30 16:11:36', '2026-05-30 16:11:36', 1);

-- --------------------------------------------------------

--
-- Table structure for table `room_charges`
--

CREATE TABLE `room_charges` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_id` int(11) NOT NULL COMMENT 'FK → rooms.id — which room this charge applies to',
  `charge_name` varchar(100) NOT NULL COMMENT 'Human label, e.g. "July 2025 Electricity", "Water – Q3"',
  `charge_category` enum('rent','utility','maintenance','penalty','other') NOT NULL DEFAULT 'utility',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total room-level charge — divided per occupant by split_method',
  `split_method` enum('equal','manual') NOT NULL DEFAULT 'equal' COMMENT 'equal = divide evenly; manual = landlord sets per-tenant amounts in bills',
  `billing_period_start` date NOT NULL,
  `billing_period_end` date NOT NULL,
  `due_date` date NOT NULL,
  `occupant_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Snapshot of occupant count at time of charge creation — for audit',
  `status` enum('draft','issued','partially_paid','fully_paid','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL COMMENT 'FK → users.id — landlord who created this room charge',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Room-level grouped charges; individual tenant bills generated from these records';

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL COMMENT 'Dot-namespaced key, e.g. payment.gcash_qr_path, app.maintenance_mode',
  `setting_val` text DEFAULT NULL COMMENT 'String or JSON value — application parses type',
  `description` varchar(255) DEFAULT NULL COMMENT 'Human-readable explanation for the admin settings panel',
  `updated_by` int(11) DEFAULT NULL COMMENT 'FK → users.id — last admin to update this setting',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Global application settings managed by landlord admin panel';

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_val`, `description`, `updated_by`, `updated_at`) VALUES
(1, 'payment.gcash_qr_path', NULL, 'Path to GCash QR image', NULL, '2026-05-09 23:18:24'),
(2, 'payment.gcash_name', NULL, 'GCash account name', NULL, '2026-05-09 23:18:24'),
(3, 'payment.gcash_number', NULL, 'GCash mobile number', NULL, '2026-05-09 23:18:24'),
(4, 'app.maintenance_mode', '0', '1 = locked, 0 = normal', NULL, '2026-05-09 23:18:24'),
(5, 'app.allow_registration', '1', '1 = open, 0 = closed', NULL, '2026-05-09 23:18:24');

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `room_type_preference` enum('single','shared') DEFAULT NULL,
  `gender` enum('male','female','prefer_not_to_say') DEFAULT NULL,
  `id_document_path` varchar(255) DEFAULT NULL,
  `personality_completed` tinyint(4) DEFAULT 0,
  `tenant_status` enum('registered','quiz_done','approved','type_chosen','room_selected','complete') DEFAULT 'registered',
  `move_in_date` date DEFAULT NULL,
  `move_out_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tenant_type` enum('independent','dependent') NOT NULL DEFAULT 'independent' COMMENT 'independent = solo adult tenant; dependent = minor or guardian-supervised tenant',
  `guardian_name` varchar(100) DEFAULT NULL COMMENT 'Full legal name of guardian — REQUIRED in app layer when tenant_type = dependent',
  `guardian_email` varchar(150) DEFAULT NULL COMMENT 'Guardian email — REQUIRED in app layer when tenant_type = dependent; used for notifications',
  `guardian_phone` varchar(20) DEFAULT NULL COMMENT 'Guardian contact number — optional even for dependent tenants',
  `guardian_purpose` text DEFAULT NULL,
  `air_conditioned_preference` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `user_id`, `room_id`, `room_type_preference`, `gender`, `id_document_path`, `personality_completed`, `tenant_status`, `move_in_date`, `move_out_date`, `notes`, `created_at`, `updated_at`, `tenant_type`, `guardian_name`, `guardian_email`, `guardian_phone`, `guardian_purpose`, `air_conditioned_preference`) VALUES
(1, 2, NULL, 'single', NULL, '5e2546ae90655678cd898c2de0b16e5b.png', 0, 'registered', NULL, NULL, NULL, '2026-03-26 18:01:52', '2026-03-26 18:01:52', 'independent', NULL, NULL, NULL, NULL, 0),
(2, 3, NULL, 'shared', NULL, NULL, 0, 'registered', NULL, NULL, NULL, '2026-03-26 18:05:10', '2026-05-29 02:51:40', 'independent', NULL, NULL, NULL, NULL, 0),
(3, 4, NULL, 'shared', NULL, '2660f6013af9dc4637207a0aae6ddef7.jpg', 0, 'registered', NULL, NULL, NULL, '2026-04-05 07:45:41', '2026-04-05 07:45:41', 'independent', NULL, NULL, NULL, NULL, 0),
(4, 5, NULL, 'shared', NULL, 'b8e007aac05f37820a634f265f9996c2.jpg', 0, 'registered', NULL, NULL, NULL, '2026-04-05 08:02:18', '2026-04-05 08:02:18', 'independent', NULL, NULL, NULL, NULL, 0),
(5, 6, NULL, 'single', NULL, '229fb3916352b81c2bb39662aeec827e.jpg', 0, 'registered', NULL, NULL, NULL, '2026-04-24 06:10:22', '2026-04-24 06:10:22', 'independent', NULL, NULL, NULL, NULL, 0),
(6, 7, NULL, 'single', NULL, 'c7c719754146c85353034a6fdd6034b6.jpg', 0, 'registered', NULL, NULL, NULL, '2026-04-24 06:40:56', '2026-04-24 06:40:56', 'independent', NULL, NULL, NULL, NULL, 0),
(7, 8, 1, 'shared', NULL, '5e03043c925d64922c4bdc3bedda214e.png', 0, 'registered', '2026-05-05', NULL, 'PERSONALITY FLAGGED: Suspicious pattern: majority of answers are identical', '2026-05-04 23:57:28', '2026-05-29 02:51:40', 'independent', NULL, NULL, NULL, NULL, 0),
(8, 9, 1, 'single', NULL, NULL, 0, 'registered', '2026-05-05', NULL, NULL, '2026-05-05 01:28:34', '2026-05-29 02:51:40', 'independent', 'macky', 'marklobrigo64@gmail.com', NULL, 'brooooooooooooooooooooo', 0),
(11, 13, NULL, 'shared', NULL, '920d2bdc9cac907316d9e147d7fba0e7.jpg', 0, 'registered', NULL, NULL, NULL, '2026-05-28 01:14:09', '2026-05-28 01:14:09', 'independent', 'Inday sara', 'chisai12369@gmail.com', NULL, 'my bfffffff', 0),
(12, 14, 2, 'shared', 'female', '153bc47bfe59b1a0070a8763382762ba.jpg', 1, 'registered', '2026-05-31', NULL, NULL, '2026-05-28 10:42:03', '2026-05-31 02:25:35', 'independent', 'Ma. Suzette O. Espolon', 'zetteespolon@gmail.com', NULL, 'She is my mother', 1),
(13, 15, NULL, 'shared', NULL, 'e903ef15a1f178164797963b0b2dd8ee.jpg', 0, 'registered', NULL, NULL, NULL, '2026-05-28 10:46:43', '2026-05-28 10:46:43', 'independent', 'Cylnor Langrio', 'langrioreancy22@gmail.com', NULL, 'My mother - contact for emergencies', 0),
(14, 16, 2, 'single', 'male', '655f73f5706f9c9c3498c8dade4d4ff0.jpg', 1, 'registered', '2026-05-29', NULL, NULL, '2026-05-29 03:22:02', '2026-05-30 10:24:00', 'independent', 'Inday sara', 'jrbalsacao@gmail.com', NULL, 'my father but also my mother', 1),
(15, 17, NULL, 'shared', NULL, '91942604a522adcbdb816e362aebc2ba.jpg', 0, 'registered', NULL, NULL, NULL, '2026-05-29 03:31:20', '2026-05-29 03:31:20', 'independent', 'Inday sara', 'jrbalsacao@gmail.com', NULL, 'hahahahahahaahahahhahahahah ahaha', 0),
(16, 18, 2, 'shared', NULL, 'e91731fdedd3da54cb8e0584f9cfc79c.jpg', 1, 'registered', '2026-05-30', NULL, NULL, '2026-05-30 14:52:35', '2026-05-30 15:09:04', 'independent', 'Reanan Langrio', 'rensi2228@gmail.com', NULL, 'My Father - contact for emergencies', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tenant_compatibility_cache`
--

CREATE TABLE `tenant_compatibility_cache` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `compatibility_score` decimal(5,2) NOT NULL,
  `compatibility_status` varchar(50) NOT NULL,
  `reasons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reasons`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenant_compatibility_cache`
--

INSERT INTO `tenant_compatibility_cache` (`id`, `tenant_id`, `room_id`, `compatibility_score`, `compatibility_status`, `reasons`, `created_at`, `updated_at`) VALUES
(37, 12, 2, 77.50, 'Good Match', '[\"General lifestyle compatibility\",\"Compatible social energy\"]', '2026-05-31 02:25:35', '2026-05-31 02:25:35'),
(38, 14, 2, 71.25, 'Moderate Match', '[\"General lifestyle compatibility\"]', '2026-05-31 02:25:35', '2026-05-31 02:42:36'),
(39, 16, 2, 81.25, 'Good Match', '[\"Compatible social energy\",\"General lifestyle compatibility\"]', '2026-05-31 02:25:35', '2026-05-31 19:47:28');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text NOT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `user_id`, `tenant_id`, `rating`, `review_text`, `is_approved`, `created_at`, `updated_at`) VALUES
(1, 9, 8, 4, 'what a website', 1, '2026-05-27 08:02:08', '2026-05-27 08:19:53'),
(3, 18, 16, 5, 'maganda d2 mga sah', 1, '2026-05-30 15:30:51', '2026-05-30 15:30:51'),
(4, 16, 14, 5, 'WOWOOWOWOWOOWOWW', 1, '2026-05-31 06:35:03', '2026-05-31 06:38:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('landlord','tenant') DEFAULT 'tenant',
  `status` enum('unverified','pending','approved','rejected','moved_out') NOT NULL DEFAULT 'pending',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `two_fa_secret` varchar(64) CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL COMMENT 'Base32-encoded TOTP secret key — generate via GoogleAuthenticator::generateSecretKey()',
  `two_fa_enabled` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = 2FA off (default), 1 = Google Authenticator active',
  `two_fa_verified_at` timestamp NULL DEFAULT NULL COMMENT 'When the user first validated a live TOTP code (setup confirmation)',
  `two_fa_backup_codes` text DEFAULT NULL COMMENT 'JSON array of bcrypt-hashed single-use recovery codes — e.g. ["$2y$10$...", ...]',
  `totp_secret` varchar(64) DEFAULT NULL COMMENT 'Base32 Google Authenticator secret',
  `totp_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = 2FA active',
  `totp_verified_at` timestamp NULL DEFAULT NULL COMMENT 'When user first verified 2FA setup',
  `recovery_codes` text DEFAULT NULL COMMENT 'JSON-encoded bcrypt-hashed recovery codes',
  `gcash_qr_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `status`, `last_login`, `created_at`, `updated_at`, `two_fa_secret`, `two_fa_enabled`, `two_fa_verified_at`, `two_fa_backup_codes`, `totp_secret`, `totp_enabled`, `totp_verified_at`, `recovery_codes`, `gcash_qr_path`) VALUES
(1, 'Landlord', 'landlord@boardtrack.com', '$2y$10$uIpcuTbgazlZuqAzO2/A1ew89nJBUxAWvvgTnEWd8Ufdtfti2lB1e', 'landlord', 'approved', NULL, '2026-03-26 17:32:07', '2026-05-29 02:51:40', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, 'gcash_1_4d0e69d5f07599be.jpg'),
(2, 'Test Tenant', 'tenant@example.com', '$2y$10$57ovtTrMmdZzCoUKaqfVledPSU1gpURK4WPUupubmufbUMF17DIAS', 'tenant', 'pending', NULL, '2026-03-26 18:01:52', '2026-03-26 18:01:52', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(3, 'Test Tenant', 'tenant@boardtrack.com', '$2y$10$uIpcuTbgazlZuqAzO2/A1ew89nJBUxAWvvgTnEWd8Ufdtfti2lB1e', 'tenant', 'pending', NULL, '2026-03-26 18:05:10', '2026-05-29 02:51:40', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(4, 'John Reniel Balsacao', 'jrbalsacao@gmail.com', '$2y$10$nidLHJeaNb7pXTJSEut0s.FwgAOewPpL1xlPOSp1Fs6Q0ysgoLEXG', 'tenant', 'pending', NULL, '2026-04-05 07:45:41', '2026-04-05 07:45:41', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(5, 'John Reniel Balsacao', 'hipipipi@gmail.com', '$2y$10$5Gg2rTpqUXV8yu.y2/t4tO2l54WxKuJkWO/57RDDNxvxkseuVlG5K', 'tenant', 'pending', NULL, '2026-04-05 08:02:18', '2026-04-05 08:02:18', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(6, 'test one', 'testone@gmail.com', '$2y$10$7/IBvOBL0Gs0NYJr/ePtJOM8edLEf8Zc2dF/QnFRm8TzfdXsDccty', 'tenant', 'pending', NULL, '2026-04-24 06:10:22', '2026-04-24 06:10:22', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(7, 'juan jeje', 'juanjeje@gmail.com', '$2y$10$RQ7izBbYRal5lI6fPX/PBeR5aVP44YbNhlEjaLCm/pXzoN28D3A.a', 'tenant', 'pending', NULL, '2026-04-24 06:40:56', '2026-04-24 06:40:56', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(8, 'John Reniel Balsacao', 'chisai12369@gmail.com', '$2y$10$AAsfUaLOlaJ1UnXgDQDJWuxYdku/c5EuXrjTZHFVcDXIxdIU0na9S', 'tenant', 'approved', NULL, '2026-05-04 23:57:28', '2026-05-29 02:51:40', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(9, 'try tenant', 'johnreniel.balsacao@chmsu.edu.ph', '$2y$10$Gm9HkeubiLY5NrmRBbSKee0/ej0sThJAIzTTn8tK.5S0YrmZsuY2G', 'tenant', 'approved', NULL, '2026-05-05 01:28:34', '2026-05-28 06:14:15', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(13, 'dods try', 'haruichi.k031@gmail.com', '$2y$10$JGdQAZulhAGyqWnHv2deDeiF0.9q7Cvm0MPJ/0DMwLGDJo8uNACK6', 'tenant', 'pending', NULL, '2026-05-28 01:14:09', '2026-05-28 03:20:11', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(14, 'Sheena May Espolon', 'sheenamayespolon@gmail.com', '$2y$10$Sixdg8hlQmeo0d9niGooPu.x6IAc5ikbFd9lV.lEeZpczrz0WvL3.', 'tenant', 'approved', NULL, '2026-05-28 10:42:03', '2026-05-31 21:43:48', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(15, 'Reancy Francois B. Langrio', 'langrioreancy@gmail.com', '$2y$10$KdWL05BIea1nzm9WARStweeKNwvTMmO6mXC4y5LqpbkejPhpD5/o2', 'tenant', 'pending', NULL, '2026-05-28 10:46:43', '2026-05-28 10:46:43', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(16, 'John Reniel Balsacao', 'zeroespinosa@gmail.com', '$2y$10$xFV0.0OBSNP1gpI/QNecKuzIimLb/IhuM7mwmyiragi7aabpoKZ1a', 'tenant', 'approved', NULL, '2026-05-29 03:22:02', '2026-05-29 03:25:37', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(17, 'shadow eyan', 'langrioreancy22@gmail.com', '$2y$10$muvixvNu1FoBYHXUwqyslOcgWV4Y5QVSMkPWfCcI4Yz3V4x/mF1im', 'tenant', 'rejected', NULL, '2026-05-29 03:31:20', '2026-05-30 16:07:13', NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(18, 'Reancy Francois B. Langrio', 'reancylangrio22@gmail.com', '$2y$10$xxHeyu50JlSM2o3j3csA5uUniK3wE5FQVIIXK9qiDMgGfT.mTJKPW', 'tenant', 'approved', NULL, '2026-05-30 14:52:35', '2026-05-30 15:43:47', NULL, 0, NULL, NULL, 'QNP5DXKCIEEMKPCJHRQ5HGRG7N7LV4QF', 1, '2026-05-30 15:18:29', '[\"$2y$10$sHhY1zoae5.otSupuDo7Y.rfoxibVvw1DTDmJYSPLlSmJL2JSaE4m\",\"$2y$10$COcZ9Bkv3MXPM4XJ\\/.H8Q.4DWGCRUbU\\/6\\/66TicBBdS0Uj4MgHD\\/O\",\"$2y$10$IIiBRlJh0cQW13ph7YLEDOlp36KImUb\\/vGOt\\/f8lTckNkMg9I004K\",\"$2y$10$C\\/ZH5Qijalsn2JxL5t3DfuSD6FHUi.pYZ.lwgLqPVFIVBaeDc88WS\",\"$2y$10$E3YzO9M2Zbvj0hXp78f1leSKjViwowI0Oajl1aP0q0qh1Go5vJyx.\",\"$2y$10$au5zsuBPRubenAlf.uC\\/W.OWx4Xy35cuwFf108cxD8t\\/6\\/oedWK9G\",\"$2y$10$xlHVPG2L8DzWMlfKEY8a1eEUj\\/GtwKJEE4LBzGy\\/EYVCO6tjl5tm2\",\"$2y$10$.iDG4PRtZK\\/jQQmSKjyPmOQk9sNW6f8Mq99vR\\/caiJe.2OywZXR1m\"]', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `waiting_list`
--

CREATE TABLE `waiting_list` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `room_type_preference` enum('single','shared') NOT NULL DEFAULT 'shared',
  `priority_order` int(11) DEFAULT 0,
  `status` enum('waiting','notified','assigned','expired') DEFAULT 'waiting',
  `requested_at` timestamp NULL DEFAULT current_timestamp(),
  `notified_at` timestamp NULL DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `waiting_list`
--

INSERT INTO `waiting_list` (`id`, `tenant_id`, `room_type_preference`, `priority_order`, `status`, `requested_at`, `notified_at`, `assigned_at`, `notes`) VALUES
(1, 14, 'single', 0, 'waiting', '2026-05-29 03:25:37', NULL, NULL, NULL),
(2, 12, 'shared', 0, 'waiting', '2026-05-31 02:23:50', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_room_status` (`room_id`,`status`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_overdue_penalty` (`due_date`,`status`,`last_penalty_applied_at`),
  ADD KEY `idx_billing_period` (`billing_period_start`,`billing_period_end`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resolved_by` (`resolved_by`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_last_message` (`last_message_at`);

--
-- Indexes for table `complaint_messages`
--
ALTER TABLE `complaint_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_complaint` (`complaint_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `idx_tenant_id` (`tenant_id`),
  ADD KEY `idx_room_id` (`room_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_requested_at` (`requested_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_bill` (`bill_id`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_uploaded` (`uploaded_at`),
  ADD KEY `idx_reviewed_at` (`reviewed_at`);

--
-- Indexes for table `payment_plans`
--
ALTER TABLE `payment_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bill` (`bill_id`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_next_payment` (`next_payment_date`);

--
-- Indexes for table `payment_schedule`
--
ALTER TABLE `payment_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `idx_plan` (`payment_plan_id`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `personality_answers`
--
ALTER TABLE `personality_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tenant_question` (`tenant_id`,`question_id`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_question` (`question_id`);

--
-- Indexes for table `personality_questions`
--
ALTER TABLE `personality_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_order` (`display_order`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`),
  ADD KEY `idx_type` (`room_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_rooms_air_conditioned` (`air_conditioned`);

--
-- Indexes for table `room_charges`
--
ALTER TABLE `room_charges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rc_created_by` (`created_by`),
  ADD KEY `idx_rc_room_id` (`room_id`),
  ADD KEY `idx_rc_status` (`status`),
  ADD KEY `idx_rc_category` (`charge_category`),
  ADD KEY `idx_rc_due_date` (`due_date`),
  ADD KEY `idx_rc_period` (`billing_period_start`,`billing_period_end`),
  ADD KEY `idx_rc_room_period` (`room_id`,`billing_period_start`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_setting_key` (`setting_key`),
  ADD KEY `fk_sysset_updated_by` (`updated_by`),
  ADD KEY `idx_sysset_key` (`setting_key`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_room` (`room_id`),
  ADD KEY `idx_tenants_type` (`tenant_type`),
  ADD KEY `idx_personality_completed` (`personality_completed`),
  ADD KEY `idx_tenants_ac_preference` (`air_conditioned_preference`);

--
-- Indexes for table `tenant_compatibility_cache`
--
ALTER TABLE `tenant_compatibility_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tenant_room` (`tenant_id`,`room_id`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_room` (`room_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `idx_testimonials_approved` (`is_approved`),
  ADD KEY `idx_testimonials_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_users_2fa_enabled` (`two_fa_enabled`),
  ADD KEY `idx_totp_enabled` (`totp_enabled`),
  ADD KEY `idx_last_login` (`last_login`);

--
-- Indexes for table `waiting_list`
--
ALTER TABLE `waiting_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_room_type` (`room_type_preference`),
  ADD KEY `idx_priority` (`priority_order`,`requested_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `complaint_messages`
--
ALTER TABLE `complaint_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `payment_plans`
--
ALTER TABLE `payment_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_schedule`
--
ALTER TABLE `payment_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personality_answers`
--
ALTER TABLE `personality_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personality_questions`
--
ALTER TABLE `personality_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `room_charges`
--
ALTER TABLE `room_charges`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tenant_compatibility_cache`
--
ALTER TABLE `tenant_compatibility_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `waiting_list`
--
ALTER TABLE `waiting_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bills_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaint_messages`
--
ALTER TABLE `complaint_messages`
  ADD CONSTRAINT `complaint_messages_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `maintenance_requests_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_requests_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_requests_ibfk_3` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_plans`
--
ALTER TABLE `payment_plans`
  ADD CONSTRAINT `payment_plans_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_plans_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_schedule`
--
ALTER TABLE `payment_schedule`
  ADD CONSTRAINT `payment_schedule_ibfk_1` FOREIGN KEY (`payment_plan_id`) REFERENCES `payment_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_schedule_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `personality_answers`
--
ALTER TABLE `personality_answers`
  ADD CONSTRAINT `fk_pa_question` FOREIGN KEY (`question_id`) REFERENCES `personality_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pa_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_charges`
--
ALTER TABLE `room_charges`
  ADD CONSTRAINT `fk_rc_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rc_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `fk_sysset_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tenants`
--
ALTER TABLE `tenants`
  ADD CONSTRAINT `tenants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenants_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tenant_compatibility_cache`
--
ALTER TABLE `tenant_compatibility_cache`
  ADD CONSTRAINT `tenant_compatibility_cache_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenant_compatibility_cache_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `testimonials_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `waiting_list`
--
ALTER TABLE `waiting_list`
  ADD CONSTRAINT `waiting_list_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
