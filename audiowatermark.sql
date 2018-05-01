-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 01, 2018 at 08:54 AM
-- Server version: 10.1.31-MariaDB
-- PHP Version: 7.2.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `audiowatermark`
--

-- --------------------------------------------------------

--
-- Table structure for table `multimedia`
--

CREATE TABLE `multimedia` (
  `stt` int(11) NOT NULL,
  `id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `parentid` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `song` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `singer` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `owner` varchar(32) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `multimedia`
--

INSERT INTO `multimedia` (`stt`, `id`, `parentid`, `song`, `singer`, `url`, `owner`) VALUES
(34, '138B4iroe5S_ro3PqtdkTqhBqEzbx6uvP', '138B4iroe5S_ro3PqtdkTqhBqEzbx6uvP', 'Just A Dream', 'Nelly', 'https://drive.google.com/file/d/138B4iroe5S_ro3PqtdkTqhBqEzbx6uvP/view?usp=sharing', 'admin'),
(35, '1MoVkhS5E_Y4kKdwbAekHj4vMGOSZoD40', '1MoVkhS5E_Y4kKdwbAekHj4vMGOSZoD40', 'Show Me What I\'m Looking For', 'Carolina Liar', 'https://drive.google.com/file/d/1MoVkhS5E_Y4kKdwbAekHj4vMGOSZoD40/view?usp=sharing', 'admin'),
(36, '19YX2i4YNZUFptopf5iPxFWHuQMuA9PPS', '19YX2i4YNZUFptopf5iPxFWHuQMuA9PPS', 'You Can Be King Again', 'Lauren Aquilina', 'https://drive.google.com/file/d/19YX2i4YNZUFptopf5iPxFWHuQMuA9PPS/view?usp=sharing', 'admin'),
(37, '165FjSbme7-BnH7yQzsKadZY08VXReRsb', '165FjSbme7-BnH7yQzsKadZY08VXReRsb', 'Người Lạ Ơi', 'Karik - Orange', 'https://drive.google.com/file/d/165FjSbme7-BnH7yQzsKadZY08VXReRsb/view?usp=sharing', 'admin'),
(39, '1O-7E902k5sjjUYxe69_qfai-JVfFRxrO', '1O-7E902k5sjjUYxe69_qfai-JVfFRxrO', 'Uchiage Hanabi', 'Daoko', 'https://drive.google.com/file/d/1O-7E902k5sjjUYxe69_qfai-JVfFRxrO/view?usp=sharing', 'admin'),
(40, '1Fn7ul8bQkSUYZl3vTlPW8j8t_K_tWGAw', '1Fn7ul8bQkSUYZl3vTlPW8j8t_K_tWGAw', 'Sign', 'DEAMN', 'https://drive.google.com/file/d/1Fn7ul8bQkSUYZl3vTlPW8j8t_K_tWGAw/view?usp=sharing', 'admin'),
(41, '1rBc_6IqAnb7MtyR6GMU0YMnYbVj1r67g', '1rBc_6IqAnb7MtyR6GMU0YMnYbVj1r67g', 'Fly Away', 'Anjulie', 'https://drive.google.com/file/d/1rBc_6IqAnb7MtyR6GMU0YMnYbVj1r67g/view?usp=sharing', 'admin'),
(42, '1VEPp4YiOwFsl49xwucyB25mpghCH7c4a', '1VEPp4YiOwFsl49xwucyB25mpghCH7c4a', 'The River', 'Axel Johansson', 'https://drive.google.com/file/d/1VEPp4YiOwFsl49xwucyB25mpghCH7c4a/view?usp=sharing', 'admin'),
(43, '12pABOPdD9cajAvhcw3sofXWQTNGYwguO', '12pABOPdD9cajAvhcw3sofXWQTNGYwguO', 'Save Me', 'DEAMN', 'https://drive.google.com/file/d/12pABOPdD9cajAvhcw3sofXWQTNGYwguO/view?usp=sharing', 'admin'),
(44, '1E0GJTeyuwLHMnqgro4usDtugZ2_ORdF5', '1O-7E902k5sjjUYxe69_qfai-JVfFRxrO', 'Uchiage Hanabi', 'Daoko', 'https://drive.google.com/file/d/1E0GJTeyuwLHMnqgro4usDtugZ2_ORdF5/view?usp=sharing', 'tester'),
(45, '1moS2I0Tz9RABzMBXs8jmAmOwJ96yYMiz', '12pABOPdD9cajAvhcw3sofXWQTNGYwguO', 'Save Me', 'DEAMN', 'https://drive.google.com/file/d/1moS2I0Tz9RABzMBXs8jmAmOwJ96yYMiz/view?usp=sharing', 'tester'),
(46, '1kHqo4FA8lwZFA5yLu5GYumUZXzeyuHuQ', '1VEPp4YiOwFsl49xwucyB25mpghCH7c4a', 'The River', 'Axel Johansson', 'https://drive.google.com/file/d/1kHqo4FA8lwZFA5yLu5GYumUZXzeyuHuQ/view?usp=sharing', 'tester'),
(47, '1lu3q-wuhOAC-4Mcn4fe0SEhU_qhoIz0h', '1rBc_6IqAnb7MtyR6GMU0YMnYbVj1r67g', 'Fly Away', 'Anjulie', 'https://drive.google.com/file/d/1lu3q-wuhOAC-4Mcn4fe0SEhU_qhoIz0h/view?usp=sharing', 'tester');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `permission` varchar(16) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user`, `password`, `permission`) VALUES
('admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', 'admin'),
('tester', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `multimedia`
--
ALTER TABLE `multimedia`
  ADD PRIMARY KEY (`stt`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `multimedia`
--
ALTER TABLE `multimedia`
  MODIFY `stt` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
