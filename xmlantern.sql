-- phpMyAdmin SQL Dump
-- version 4.5.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2016-03-04 16:56:43
-- 服务器版本： 5.7.10
-- PHP Version: 7.0.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xmlantern`
--

-- --------------------------------------------------------

--
-- 表的结构 `Answer`
--

CREATE TABLE `Answer` (
  `openid` char(30) NOT NULL,
  `riddleid` int(11) NOT NULL,
  `YesOrNot` tinyint(1) DEFAULT NULL,
  `AnswerTime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `Contact`
--

CREATE TABLE `Contact` (
  `openid` char(30) CHARACTER SET utf8 NOT NULL,
  `nickname` char(30) NOT NULL,
  `phone` char(12) CHARACTER SET utf8 NOT NULL,
  `postcard` int(11) NOT NULL DEFAULT '0',
  `jointime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `PostCard`
--

CREATE TABLE `PostCard` (
  `id` int(11) NOT NULL,
  `total` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `Riddle`
--

CREATE TABLE `Riddle` (
  `id` int(11) NOT NULL,
  `question` char(200) COLLATE utf8_bin NOT NULL,
  `answer` char(50) COLLATE utf8_bin NOT NULL,
  `answer2` char(50) COLLATE utf8_bin NOT NULL,
  `difficult` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 表的结构 `User`
--

CREATE TABLE `User` (
  `openid` char(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `status` char(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `finalquestion` int(11) DEFAULT NULL,
  `grade` int(11) DEFAULT '0',
  `finalanswer` datetime DEFAULT NULL,
  `foultime` int(10) NOT NULL DEFAULT '0',
  `joinnum` int(3) DEFAULT '1',
  `sendtime` int(10) NOT NULL DEFAULT '0',
  `receivetime` int(10) DEFAULT '0',
  `starttime` datetime DEFAULT NULL,
  `firstend` datetime DEFAULT NULL,
  `secondstart` datetime DEFAULT NULL,
  `secondend` datetime DEFAULT NULL,
  `threestart` datetime DEFAULT NULL,
  `threeend` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Answer`
--
ALTER TABLE `Answer`
  ADD PRIMARY KEY (`openid`,`riddleid`);

--
-- Indexes for table `Contact`
--
ALTER TABLE `Contact`
  ADD PRIMARY KEY (`openid`),
  ADD UNIQUE KEY `openid` (`openid`),
  ADD KEY `openid_2` (`openid`);

--
-- Indexes for table `PostCard`
--
ALTER TABLE `PostCard`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id_2` (`id`);

--
-- Indexes for table `Riddle`
--
ALTER TABLE `Riddle`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id_2` (`id`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`openid`),
  ADD UNIQUE KEY `id` (`openid`),
  ADD KEY `id_2` (`openid`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
