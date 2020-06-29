-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: waveustransit.com.mysql.service.one.com:3306
-- Generation Time: Jun 26, 2020 at 02:51 PM
-- Server version: 10.3.23-MariaDB-1:10.3.23+maria~bionic
-- PHP Version: 7.2.24-0ubuntu0.18.04.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `waveustransit_com`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` varchar(200) COLLATE utf8_bin NOT NULL,
  `fullname` varchar(225) COLLATE utf8_bin NOT NULL,
  `email` varchar(200) COLLATE utf8_bin NOT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL,
  `phone` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `activation_link` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `new_info` varchar(225) COLLATE utf8_bin DEFAULT NULL COMMENT 'Useful when you wanna change something that needs to be confirmed first, e.g email, this is the only use case for now shaa ',
  `admin_type` bigint(20) NOT NULL,
  `date_time` datetime NOT NULL,
  `date_time_updated` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `dispatchers`
--

CREATE TABLE `dispatchers` (
  `id` varchar(200) COLLATE utf8_bin NOT NULL,
  `login_id` varchar(200) COLLATE utf8_bin NOT NULL,
  `fullname` varchar(225) COLLATE utf8_bin NOT NULL,
  `email` varchar(200) COLLATE utf8_bin NOT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL,
  `phone` varchar(100) COLLATE utf8_bin NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `activation_link` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `new_info` varchar(225) COLLATE utf8_bin DEFAULT NULL COMMENT 'Useful when you wanna change something that needs to be confirmed first, e.g email, this is the only use case for now shaa ',
  `type` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `date_time` datetime NOT NULL,
  `date_time_updated` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `mapping`
--

CREATE TABLE `mapping` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `coordinates` tinytext COLLATE utf8_bin NOT NULL COMMENT 'coordinates format long x|lat y',
  `date_time` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) COLLATE utf8_bin NOT NULL,
  `dispatcher_id` varchar(255) COLLATE utf8_bin NOT NULL,
  `title` varchar(225) COLLATE utf8_bin NOT NULL,
  `message` tinytext COLLATE utf8_bin NOT NULL,
  `device` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `viewed` tinyint(1) NOT NULL DEFAULT 0,
  `date_time` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` varchar(200) COLLATE utf8_bin NOT NULL,
  `status` int(11) NOT NULL,
  `dispatcher_id` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `price` decimal(10,0) DEFAULT NULL,
  `payment_type` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `order_type` varchar(200) COLLATE utf8_bin NOT NULL DEFAULT 'bike',
  `date_time` datetime NOT NULL,
  `date_time_updated` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `order_history`
--

CREATE TABLE `order_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_status` varchar(100) COLLATE utf8_bin NOT NULL,
  `user_id` longtext COLLATE utf8_bin NOT NULL COMMENT 'this is used to determine who made this update:format user_id,user_type e.g. 23,2',
  `date_time` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `order_meta`
--

CREATE TABLE `order_meta` (
  `meta_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `meta_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `meta_value` longtext CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_options`
--

CREATE TABLE `site_options` (
  `id` int(11) NOT NULL,
  `option_name` varchar(225) COLLATE utf8_bin NOT NULL,
  `option_value` longtext COLLATE utf8_bin NOT NULL,
  `date_time` datetime NOT NULL,
  `date_time_updated` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='stores general option of the site e.g order_admin_id';

-- --------------------------------------------------------

--
-- Table structure for table `temp_users`
--

CREATE TABLE `temp_users` (
  `id` varchar(200) COLLATE utf8_bin NOT NULL,
  `fullname` varchar(225) COLLATE utf8_bin NOT NULL,
  `email` varchar(100) COLLATE utf8_bin NOT NULL,
  `password` varchar(225) COLLATE utf8_bin NOT NULL,
  `activation_link` varchar(225) COLLATE utf8_bin NOT NULL,
  `phone` varchar(100) COLLATE utf8_bin NOT NULL,
  `address` tinytext COLLATE utf8_bin NOT NULL,
  `date_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(200) COLLATE utf8_bin NOT NULL,
  `fullname` varchar(225) COLLATE utf8_bin NOT NULL,
  `email` varchar(200) COLLATE utf8_bin NOT NULL,
  `password` varchar(225) COLLATE utf8_bin NOT NULL,
  `phone` varchar(100) COLLATE utf8_bin NOT NULL,
  `address` tinytext COLLATE utf8_bin NOT NULL,
  `coordinates` varchar(150) COLLATE utf8_bin DEFAULT NULL COMMENT 'format long x|lat y',
  `temp_coordinates` varchar(150) COLLATE utf8_bin NOT NULL DEFAULT '0|0' COMMENT 'this is the non default coordinate of the user that changes,same format as coordinate',
  `firebase_id` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `state` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `country` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT 'nigeria',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `activation_link` varchar(100) COLLATE utf8_bin DEFAULT NULL COMMENT 'may come in handy when you need a forgot password reset link',
  `new_info` varchar(225) COLLATE utf8_bin DEFAULT NULL COMMENT 'Useful when you wanna change something that needs to be confirmed first, e.g email, this is the only use case for now shaa',
  `date_time` datetime NOT NULL COMMENT 'time of registeration',
  `date_time_updated` datetime NOT NULL COMMENT 'last time user details was edited',
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

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
-- Indexes for table `dispatchers`
--
ALTER TABLE `dispatchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login_id` (`login_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `mapping`
--
ALTER TABLE `mapping`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_history`
--
ALTER TABLE `order_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_meta`
--
ALTER TABLE `order_meta`
  ADD PRIMARY KEY (`meta_id`),
  ADD KEY `comment_id` (`order_id`),
  ADD KEY `meta_key` (`meta_key`(191));

--
-- Indexes for table `site_options`
--
ALTER TABLE `site_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `option_name_2` (`option_name`),
  ADD KEY `option_name` (`option_name`);

--
-- Indexes for table `temp_users`
--
ALTER TABLE `temp_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mapping`
--
ALTER TABLE `mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_history`
--
ALTER TABLE `order_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_meta`
--
ALTER TABLE `order_meta`
  MODIFY `meta_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_options`
--
ALTER TABLE `site_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
