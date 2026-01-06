-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 06, 2026 at 05:06 AM
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
-- Database: `cinesync_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `blocked_users`
--

CREATE TABLE `blocked_users` (
  `block_id` int(11) NOT NULL,
  `blocker_id` int(11) NOT NULL,
  `blocked_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `post_id`, `user_id`, `comment_text`, `created_at`) VALUES
(15, 37, 36, '😊', '2025-12-28 20:03:48'),
(16, 52, 32, '❤️💜', '2025-12-28 20:09:52'),
(17, 53, 32, '🎥', '2025-12-28 20:16:36'),
(18, 37, 37, 'good 💯', '2025-12-29 05:13:55'),
(19, 37, 36, '👍🏻', '2025-12-29 06:08:51'),
(20, 53, 37, '😊', '2025-12-29 07:28:31'),
(21, 49, 37, '💯', '2025-12-29 14:30:26'),
(22, 55, 36, 'good', '2025-12-29 15:54:51'),
(23, 37, 36, 'good', '2025-12-29 16:40:31');

-- --------------------------------------------------------

--
-- Table structure for table `connections`
--

CREATE TABLE `connections` (
  `id` int(11) NOT NULL,
  `user_id_1` int(11) NOT NULL,
  `user_id_2` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected','blocked') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `connections`
--

INSERT INTO `connections` (`id`, `user_id_1`, `user_id_2`, `status`, `created_at`) VALUES
(25, 36, 37, 'accepted', '2025-12-31 04:00:03'),
(26, 36, 32, 'pending', '2025-12-31 05:10:57');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `group_name` varchar(255) DEFAULT NULL,
  `group_photo` varchar(255) DEFAULT NULL,
  `is_group` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `group_name`, `group_photo`, `is_group`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 0, '2025-12-30 18:25:50', '2025-12-30 19:38:18'),
(2, NULL, NULL, 0, '2025-12-30 18:44:21', '2025-12-30 18:44:26');

-- --------------------------------------------------------

--
-- Table structure for table `conversation_favorites`
--

CREATE TABLE `conversation_favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_participants`
--

CREATE TABLE `conversation_participants` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_favorite` tinyint(1) DEFAULT 0,
  `is_blocked` tinyint(1) DEFAULT 0,
  `unread_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversation_participants`
--

INSERT INTO `conversation_participants` (`id`, `conversation_id`, `user_id`, `is_admin`, `joined_at`, `is_favorite`, `is_blocked`, `unread_count`) VALUES
(1, 1, 36, 0, '2025-12-30 18:25:50', 0, 0, 0),
(2, 1, 37, 0, '2025-12-30 18:25:50', 0, 0, 0),
(3, 2, 37, 0, '2025-12-30 18:44:21', 0, 0, 0),
(4, 2, 32, 0, '2025-12-30 18:44:21', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `like_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`like_id`, `user_id`, `post_id`, `created_at`) VALUES
(29, 32, 37, '2025-12-28 13:06:11'),
(38, 36, 48, '2025-12-28 17:44:49'),
(40, 36, 49, '2025-12-28 18:36:22'),
(47, 36, 51, '2025-12-28 19:45:47'),
(52, 32, 48, '2025-12-28 19:50:16'),
(53, 32, 49, '2025-12-28 19:50:18'),
(54, 32, 51, '2025-12-28 19:50:20'),
(57, 32, 53, '2025-12-28 20:09:33'),
(59, 32, 52, '2025-12-28 20:09:56'),
(62, 37, 52, '2025-12-29 05:01:39'),
(63, 37, 37, '2025-12-29 05:13:48'),
(70, 37, 48, '2025-12-29 07:24:01'),
(74, 37, 53, '2025-12-29 07:28:13'),
(76, 36, 54, '2025-12-29 07:34:10'),
(78, 37, 55, '2025-12-29 07:39:49'),
(80, 36, 37, '2025-12-29 13:48:05'),
(82, 37, 51, '2025-12-29 14:30:18'),
(83, 37, 49, '2025-12-29 14:30:20'),
(94, 37, 54, '2025-12-30 20:04:08'),
(95, 36, 55, '2025-12-31 05:11:46'),
(96, 36, 53, '2025-12-31 08:43:34');

-- --------------------------------------------------------

--
-- Table structure for table `login_activities`
--

CREATE TABLE `login_activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_name` varchar(100) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `last_active` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_activities`
--

INSERT INTO `login_activities` (`id`, `user_id`, `device_name`, `ip_address`, `location`, `last_active`) VALUES
(1, 36, 'Android Device', '10.36.249.193', NULL, '2025-12-31 14:13:25'),
(2, 36, 'Android Device', '10.36.249.193', NULL, '2025-12-31 14:38:19'),
(3, 36, 'Android Device', '10.36.249.193', NULL, '2025-12-31 14:53:18'),
(4, 36, 'Android Device', '10.36.249.193', NULL, '2025-12-31 14:53:56'),
(5, 36, 'Android Device', '10.36.249.193', NULL, '2026-01-02 21:50:55'),
(6, 36, 'Android Device', '10.36.249.193', NULL, '2026-01-02 21:59:30');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `conversation_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `audio_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`message_id`, `sender_id`, `receiver_id`, `message_text`, `is_read`, `created_at`, `conversation_id`, `image_url`, `audio_url`) VALUES
(1, 1, 2, 'Did you see the new casting call?', 0, '2025-12-04 14:21:02', NULL, NULL, NULL),
(2, 1, 2, 'Hello, I am interested in your casting call.', 0, '2025-12-05 04:38:28', NULL, NULL, NULL),
(3, 1, 2, 'Hello, I am interested in your casting call.', 0, '2025-12-11 08:44:12', NULL, NULL, NULL),
(4, 36, 0, 'hii', 0, '2025-12-30 18:25:55', 1, NULL, NULL),
(5, 36, 0, 'hii', 0, '2025-12-30 18:40:35', 1, NULL, NULL),
(6, 37, 0, 'hii', 0, '2025-12-30 18:42:46', 1, NULL, NULL),
(7, 37, 0, 'hii', 0, '2025-12-30 18:44:26', 2, NULL, NULL),
(8, 36, 0, 'hii', 0, '2025-12-30 18:53:02', 1, NULL, NULL),
(9, 36, 0, 'hii', 0, '2025-12-30 19:37:59', 1, NULL, NULL),
(10, 36, 0, 'Voice Message', 0, '2025-12-30 19:38:08', 1, NULL, 'http://10.36.249.194:8012/cinesync/uploads/1767123488_audio.mp3'),
(11, 36, 0, 'Image', 0, '2025-12-30 19:38:18', 1, 'http://10.36.249.194:8012/cinesync/uploads/1767123498_1000283427.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `message_reactions`
--

CREATE TABLE `message_reactions` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reaction_type` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `type` enum('like','comment','request','accept','system') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `user_id`, `sender_id`, `type`, `reference_id`, `is_read`, `created_at`) VALUES
(22, 32, 37, 'request', 37, 1, '2025-12-29 07:40:46'),
(23, 37, 36, 'like', 55, 1, '2025-12-29 08:50:56'),
(24, 32, 36, 'request', 36, 1, '2025-12-29 13:48:02'),
(25, 32, 36, 'like', 37, 1, '2025-12-29 13:48:05'),
(26, 37, 36, 'like', 55, 1, '2025-12-29 14:24:11'),
(28, 36, 37, 'like', 51, 1, '2025-12-29 14:30:18'),
(29, 36, 37, 'like', 49, 1, '2025-12-29 14:30:20'),
(30, 36, 37, 'comment', 49, 1, '2025-12-29 14:30:26'),
(31, 37, 36, 'like', 55, 1, '2025-12-29 15:40:00'),
(32, 37, 36, 'like', 55, 1, '2025-12-29 15:49:54'),
(33, 37, 36, 'like', 55, 1, '2025-12-29 15:50:11'),
(34, 37, 36, 'comment', 55, 1, '2025-12-29 15:54:51'),
(36, 32, 36, 'comment', 37, 1, '2025-12-29 16:40:31'),
(37, 37, 36, 'like', 55, 1, '2025-12-29 16:43:03'),
(38, 37, 36, 'like', 55, 1, '2025-12-29 16:55:40'),
(39, 37, 36, 'like', 55, 1, '2025-12-29 17:00:43'),
(40, 37, 36, 'like', 55, 1, '2025-12-29 17:00:46'),
(41, 32, 36, 'request', 36, 1, '2025-12-29 17:10:17'),
(46, 36, 37, 'accept', 37, 1, '2025-12-29 17:58:11'),
(47, 36, 37, 'accept', 37, 1, '2025-12-29 17:58:19'),
(48, 36, 37, 'accept', 37, 1, '2025-12-29 17:58:20'),
(49, 36, 37, 'accept', 37, 1, '2025-12-29 17:58:21'),
(50, 36, 37, 'accept', 37, 1, '2025-12-29 18:00:08'),
(51, 32, 36, 'request', 36, 1, '2025-12-29 18:02:48'),
(59, 37, 32, 'accept', 32, 1, '2025-12-29 18:10:29'),
(60, 37, 32, 'request', 32, 1, '2025-12-29 18:10:38'),
(62, 36, 32, 'accept', 32, 1, '2025-12-29 18:10:45'),
(66, 32, 37, 'accept', 37, 1, '2025-12-29 19:13:36'),
(68, 37, 36, 'accept', 36, 1, '2025-12-29 19:15:41'),
(70, 32, 36, 'request', 36, 0, '2025-12-30 07:40:49'),
(72, 32, 36, 'request', 36, 0, '2025-12-30 08:33:25'),
(76, 32, 36, 'request', 36, 0, '2025-12-30 08:42:29'),
(80, 32, 36, 'request', 36, 0, '2025-12-30 10:57:43'),
(82, 32, 36, 'request', 36, 0, '2025-12-30 11:24:55'),
(84, 32, 36, 'request', 36, 0, '2025-12-30 11:48:13'),
(86, 32, 36, 'request', 36, 0, '2025-12-30 20:02:02'),
(88, 32, 36, 'request', 36, 0, '2025-12-31 05:10:57');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`) VALUES
(11, 'vimalpriyan2004@gmail.com', '$2y$10$D5fVK.z0y/4UFMqVkX5vKuQRrg4EpJjPofAeCHrQl3irztQdtVyEG', '2025-12-26 14:56:24'),
(12, 'vimalpriyanmd1147.sse@saveetha.com', '$2y$10$7afzkD/GF1rTFmVUNxnK8OCfHp.0N94Z9pvirSeuwtl6vvASc8Ou.', '2025-12-30 21:02:24');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `caption` text NOT NULL,
  `media_url` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `video_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `user_id`, `caption`, `media_url`, `location`, `created_at`, `video_url`) VALUES
(37, 32, 'new post', 'http://10.36.249.194:8012/cinesync/uploads/1766852771_post_image3989257098265265592.jpg', 'chennai', '2025-12-27 16:26:11', NULL),
(48, 36, '..', 'http://10.36.249.194:8012/cinesync/uploads/1766942447_post_image7920815319339358550.jpg', '', '2025-12-28 17:20:47', NULL),
(49, 36, '....', 'http://10.36.249.194:8012/cinesync/uploads/1766942463_post_image8683749819147979695.jpg', '', '2025-12-28 17:21:03', NULL),
(51, 36, '🎥🎥🎥', 'http://10.36.249.194:8012/cinesync/uploads/1766950221_post_image8535584553028654023.jpg', '', '2025-12-28 19:30:21', NULL),
(52, 36, '#Assistant Director ', 'http://10.36.249.194:8012/cinesync/uploads/1766950839_post_image1305771238514992534.jpg', 'Thandalam', '2025-12-28 19:40:39', NULL),
(53, 36, '🎥🎥🎥', 'http://10.36.249.194:8012/cinesync/uploads/1766950911_post_image8190319116059845120.jpg', 'Thandalam', '2025-12-28 19:41:51', NULL),
(54, 37, 'new story  🎥', 'http://10.36.249.194:8012/cinesync/uploads/1766985078_post_image814008638164512467.jpg', '', '2025-12-29 05:11:18', NULL),
(55, 37, '#comment you opinion 🤔', 'http://10.36.249.194:8012/cinesync/uploads/1766985128_post_image6689656564192466048.jpg', 'Chennai', '2025-12-29 05:12:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `registration_otps`
--

CREATE TABLE `registration_otps` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `otp_code` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `report_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `target_type` enum('user','post','message') NOT NULL,
  `target_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `report_status` enum('pending','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`report_id`, `reporter_id`, `target_type`, `target_id`, `reason`, `report_status`, `created_at`) VALUES
(1, 1, 'post', 5, 'Contains copyrighted material without attribution.', 'pending', '2025-12-04 14:50:53'),
(2, 1, 'post', 5, 'Contains copyrighted material without attribution.', 'pending', '2025-12-05 03:48:50'),
(3, 1, 'post', 5, 'Contains copyrighted material without attribution.', 'pending', '2025-12-17 13:14:34');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'Viewer',
  `is_verified` tinyint(1) DEFAULT 0,
  `profile_pic_url` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notifications_enabled` tinyint(1) DEFAULT 1,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `language` varchar(20) DEFAULT 'English',
  `personalized_ads` tinyint(1) DEFAULT 1,
  `ad_partners` tinyint(1) DEFAULT 1,
  `data_sharing` tinyint(1) DEFAULT 0,
  `is_private` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `mobile`, `password`, `full_name`, `role`, `is_verified`, `profile_pic_url`, `bio`, `created_at`, `notifications_enabled`, `two_factor_enabled`, `language`, `personalized_ads`, `ad_partners`, `data_sharing`, `is_private`) VALUES
(31, 'testuser', 'finalte@gmail.com', '123456789', '$2y$10$kSdZ67oezVXuUFg4Lc74veMliqjNCe/YQfxuya8xO2RSlUm8kq89C', 'Test User', 'user', 0, NULL, NULL, '2025-12-24 15:20:12', 1, 0, 'English', 1, 1, 0, 0),
(32, 'vimalpriyan_vs', 'vimalpriyan2004@gmail.com', '9150826616', '$2y$10$ghmO.MB7iiSsPVsEzHENDOIPZmuXbIEQsPl0qJ/vkJ1fWfTzyKGCO', 'vimal', 'Artist', 0, 'http://10.36.249.194:8012/cinesync/uploads/1766852719_profile.jpg', 'artist |chennai.....', '2025-12-24 15:26:47', 1, 0, 'English', 1, 1, 0, 0),
(35, 'te', 'e@gmail.com', '123', '$2y$10$FDAtn6i/CidIa0p6OlxY5ehTMZZLNBp6grgAb/kC01Ml3XNP2Qirq', 'Tes', 'artist', 0, NULL, NULL, '2025-12-27 12:43:35', 1, 0, 'English', 1, 1, 0, 0),
(36, 'shankar', 'vimalpriyanmd1147.sse@saveetha.com', '915874556', '$2y$10$M9f/6YzIjCMHoHEtFo/g3uTVHhL8O2.UCEM4iS8wM5FTpFbbooXEe', 'shankar', 'Director', 0, 'http://10.36.249.194:8012/cinesync/uploads/1766942418_profile.jpg', '', '2025-12-28 14:25:05', 1, 0, 'English', 1, 1, 0, 0),
(37, 'Barath', 'pubg007for@gmail.com', '8564789587', '$2y$10$4kgW5dxI7A896tFwgTQ3CudrN4n75LgWs98XKIcl3Y7yM5aXMygW2', 'Barath', 'Writer', 0, 'http://10.36.249.194:8012/cinesync/uploads/1766984868_profile.jpg', 'Storyteller & Content Strategist 💡\nSimplifying digital marketing, one blog post at a time.\nJoin 10k+ newsletter readers!..', '2025-12-29 05:01:22', 1, 0, 'English', 1, 1, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blocked_users`
--
ALTER TABLE `blocked_users`
  ADD PRIMARY KEY (`block_id`),
  ADD KEY `blocker_id` (`blocker_id`),
  ADD KEY `blocked_id` (`blocked_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `connections`
--
ALTER TABLE `connections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_connection` (`user_id_1`,`user_id_2`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversation_favorites`
--
ALTER TABLE `conversation_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fav` (`user_id`,`conversation_id`),
  ADD KEY `conversation_id` (`conversation_id`);

--
-- Indexes for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_convo` (`conversation_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `conversation_id` (`conversation_id`,`user_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `login_activities`
--
ALTER TABLE `login_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `conversation_id` (`conversation_id`);

--
-- Indexes for table `message_reactions`
--
ALTER TABLE `message_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_msg_reaction` (`message_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `registration_otps`
--
ALTER TABLE `registration_otps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blocked_users`
--
ALTER TABLE `blocked_users`
  MODIFY `block_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `connections`
--
ALTER TABLE `connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `conversation_favorites`
--
ALTER TABLE `conversation_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `login_activities`
--
ALTER TABLE `login_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `message_reactions`
--
ALTER TABLE `message_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `registration_otps`
--
ALTER TABLE `registration_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blocked_users`
--
ALTER TABLE `blocked_users`
  ADD CONSTRAINT `blocked_users_ibfk_1` FOREIGN KEY (`blocker_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blocked_users_ibfk_2` FOREIGN KEY (`blocked_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_favorites`
--
ALTER TABLE `conversation_favorites`
  ADD CONSTRAINT `conversation_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_favorites_ibfk_2` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD CONSTRAINT `conversation_participants_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE;

--
-- Constraints for table `login_activities`
--
ALTER TABLE `login_activities`
  ADD CONSTRAINT `login_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `fk_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_reactions`
--
ALTER TABLE `message_reactions`
  ADD CONSTRAINT `message_reactions_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `message` (`message_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
