-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Apr 10, 2026 at 08:07 AM
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
-- Database: `nexo`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `last_message_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `user1_id`, `user2_id`, `last_message_at`, `created_at`) VALUES
(2, 8, 9, '2026-04-09 06:31:37', '2026-04-06 05:31:37'),
(3, 8, 10, '2026-04-08 13:46:16', '2026-04-08 04:41:40');

-- --------------------------------------------------------

--
-- Table structure for table `friendships`
--

CREATE TABLE `friendships` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `action_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `friendships`
--

INSERT INTO `friendships` (`id`, `user_id`, `friend_id`, `status`, `action_user_id`, `created_at`, `updated_at`) VALUES
(69, 10, 8, 'accepted', 10, '2026-04-07 18:24:19', '2026-04-07 18:55:45'),
(70, 8, 10, 'accepted', 10, '2026-04-07 18:24:19', '2026-04-07 18:55:45'),
(73, 10, 9, 'pending', 10, '2026-04-07 18:41:32', '2026-04-07 18:41:32'),
(74, 9, 10, 'pending', 10, '2026-04-07 18:41:32', '2026-04-07 18:41:32'),
(75, 8, 9, 'pending', 8, '2026-04-08 10:35:11', '2026-04-08 10:35:11'),
(76, 9, 8, 'pending', 8, '2026-04-08 10:35:11', '2026-04-08 10:35:11');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `message`, `is_read`, `created_at`) VALUES
(17, 2, 8, 'Hi', 1, '2026-04-06 05:31:42'),
(18, 2, 9, 'hello', 1, '2026-04-06 05:32:21'),
(19, 2, 8, '😀', 1, '2026-04-07 18:23:00'),
(20, 3, 8, 'yo', 1, '2026-04-08 04:41:45'),
(21, 3, 8, 'hello po', 1, '2026-04-08 08:53:32'),
(22, 3, 10, 'yes?', 1, '2026-04-08 08:56:34'),
(23, 3, 8, 'asa ka?', 1, '2026-04-08 08:56:58'),
(24, 3, 8, 'balay', 1, '2026-04-08 08:59:47'),
(25, 3, 10, 'ayy', 1, '2026-04-08 09:19:48'),
(26, 2, 8, 'hellows', 1, '2026-04-08 09:46:41'),
(27, 2, 8, 'huy', 1, '2026-04-08 13:01:59'),
(28, 3, 8, '😀', 0, '2026-04-08 13:46:16'),
(29, 2, 8, '✋', 0, '2026-04-09 06:31:37');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `actor_id` int(11) NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `actor_id`, `related_id`, `message`, `is_read`, `created_at`) VALUES
(24, 9, 'friend_accept', 8, 8, 'jun rainer accepted your friend request', 1, '2026-04-06 05:31:30'),
(25, 9, 'friend_request', 8, 8, 'jun rainer sent you a friend request', 0, '2026-04-07 15:38:09'),
(26, 10, 'friend_request', 8, 8, 'jun rainer sent you a friend request', 0, '2026-04-07 15:38:13'),
(27, 10, 'friend_request', 8, 8, 'jun rainer sent you a friend request', 0, '2026-04-07 15:44:52'),
(28, 10, 'friend_request', 8, 8, 'jun rainer sent you a friend request', 0, '2026-04-07 15:53:56'),
(29, 9, 'friend_request', 8, 8, 'jun rainer sent you a friend request', 0, '2026-04-07 16:59:16'),
(30, 9, 'friend_request', 8, 8, 'jun rainer sent you a friend request', 0, '2026-04-07 16:59:43'),
(31, 10, 'friend_request', 8, 8, 'jun rainer sent you a friend request', 0, '2026-04-07 17:00:12'),
(32, 10, 'friend_request', 8, 8, 'jun rainer sent you a friend request', 0, '2026-04-07 17:21:06'),
(34, 9, 'friend_request', 8, 8, 'jun rainer sent you a friend request', 0, '2026-04-07 18:08:46'),
(36, 9, 'friend_request', 10, 10, 'jun rainer secorin sent you a friend request', 0, '2026-04-07 18:25:23'),
(37, 9, 'friend_request', 10, 10, 'jun rainer secorin sent you a friend request', 0, '2026-04-07 18:41:32'),
(39, 9, 'friend_request', 8, 8, 'jun rainer sent you a friend request', 0, '2026-04-08 10:35:11');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `code` char(6) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `created_at`, `code`) VALUES
(8, 'junrainer3@gmail.com', '37a830dc478b3302a3ba3edc4cd61f181fdf4d1562e5c98bd2c644190356b771', '2026-04-07 11:58:14', ''),
(20, 'junrainer2@gmail.com', '72de2f250faab08b7fb13e85332155cc76ee55d58fdfddb3de27552d932532e9', '2026-04-08 13:51:49', '475095'),
(26, 'junrainer4@gmail.com', '65b9c8ad737e24430509c01d0b74f7638c557b6a529b9703212e4ca23fa1877d', '2026-04-09 14:14:48', '218790');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `visibility` enum('public','friends','only_me') NOT NULL DEFAULT 'public',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content`, `image`, `visibility`, `created_at`, `updated_at`) VALUES
(32, 8, 'wawe', NULL, 'public', '2026-04-08 13:01:37', '2026-04-08 13:01:37');

-- --------------------------------------------------------

--
-- Table structure for table `post_media`
--

CREATE TABLE `post_media` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_posts`
--

CREATE TABLE `saved_posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_posts`
--

INSERT INTO `saved_posts` (`id`, `user_id`, `post_id`, `created_at`) VALUES
(1, 1, 2, '2026-04-04 09:55:01'),
(2, 2, 1, '2026-04-04 09:55:01'),
(9, 8, 15, '2026-04-07 18:08:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `profile_image` varchar(255) NOT NULL DEFAULT 'default.png',
  `cover_image` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_active_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `bio`, `profile_image`, `cover_image`, `mobile`, `birthday`, `gender`, `created_at`, `last_active_at`) VALUES
(8, 'junrainer1', 'junrainer4@gmail.com', '$2y$10$UyBLIoXSLjMb0RhMohR50uXsd7GrFQBV49gammdpYEtmfDEwZzIWi', 'jun rainer', 'im new here', 'avatar_69d36a88275433.08953953.jpg', 'cover_69d7ac42415d54.20633715.jpg', NULL, '2006-09-04', 'Male', '2026-04-06 05:18:23', '2026-04-10 06:00:19'),
(9, 'rosemarie1', 'junrainer2@gmail.com', '$2y$10$0wOaIDKJ3roa31wOO3aEhe6D8hc/ZRK5tHUQI.R5TsojhORXIk6P6', 'Rosemarie Buhisan', NULL, 'default.png', NULL, NULL, NULL, NULL, '2026-04-06 05:29:14', '2026-04-08 13:27:43'),
(10, 'junrainer2', 'junrainer3@gmail.com', '$2y$10$/EXqjhcEkx3ulggRirKJi.tx0c2gIqmYQc/EUklNpXAhhPUCBX/RC', 'jun rainer secorin', NULL, 'default.png', NULL, NULL, NULL, NULL, '2026-04-07 11:57:46', '2026-04-08 09:19:50');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `user_id` int(11) NOT NULL,
  `dark_mode` tinyint(1) DEFAULT 1,
  `email_notifications` tinyint(1) DEFAULT 1,
  `push_notifications` tinyint(1) DEFAULT 1,
  `friend_requests_privacy` varchar(20) DEFAULT 'everyone',
  `post_privacy` varchar(20) DEFAULT 'public',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`user_id`, `dark_mode`, `email_notifications`, `push_notifications`, `friend_requests_privacy`, `post_privacy`, `updated_at`) VALUES
(8, 1, 1, 1, 'everyone', 'public', '2026-04-09 13:39:55'),
(9, 1, 1, 1, 'everyone', 'public', '2026-04-06 05:40:21'),
(10, 1, 1, 1, 'everyone', 'public', '2026-04-07 11:57:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comments_post_id` (`post_id`),
  ADD KEY `idx_comments_user_id` (`user_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conversation` (`user1_id`,`user2_id`),
  ADD KEY `idx_last_message` (`last_message_at`),
  ADD KEY `idx_user1_id` (`user1_id`),
  ADD KEY `idx_user2_id` (`user2_id`);

--
-- Indexes for table `friendships`
--
ALTER TABLE `friendships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_friendship` (`user_id`,`friend_id`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_friend_status` (`friend_id`,`status`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_friend_id` (`friend_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_likes_post_user` (`post_id`,`user_id`),
  ADD KEY `idx_likes_post_id` (`post_id`),
  ADD KEY `idx_likes_user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversation` (`conversation_id`,`created_at`),
  ADD KEY `idx_unread` (`conversation_id`,`is_read`),
  ADD KEY `idx_conversation_id` (`conversation_id`),
  ADD KEY `idx_sender_id` (`sender_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_actor_id` (`actor_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_posts_user_id` (`user_id`);

--
-- Indexes for table `post_media`
--
ALTER TABLE `post_media`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saved_posts`
--
ALTER TABLE `saved_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_save` (`user_id`,`post_id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_post_id` (`post_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `friendships`
--
ALTER TABLE `friendships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `post_media`
--
ALTER TABLE `post_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `saved_posts`
--
ALTER TABLE `saved_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `fk_likes_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
