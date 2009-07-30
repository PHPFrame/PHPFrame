-- phpMyAdmin SQL Dump
-- version 3.1.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 23, 2009 at 02:25 AM
-- Server version: 5.1.32
-- PHP Version: 5.2.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `phpframe-cli`
--

-- --------------------------------------------------------

--
-- Table structure for table `phpframe_acl_groups`
--

CREATE TABLE IF NOT EXISTS `phpframe_acl_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupid` int(11) NOT NULL,
  `component` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `value` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `phpframe_acl_groups`
--

INSERT INTO `phpframe_acl_groups` (`id`, `groupid`, `component`, `action`, `value`) VALUES
(1, 1, 'admin', '*', 'all'),
(2, 1, 'com_dashboard', '*', 'own'),
(3, 2, 'com_dashboard', '*', 'own'),
(4, 0, 'com_login', '*', 'own'),
(5, 1, 'com_login', '*', 'all'),
(6, 2, 'com_login', '*', 'all'),
(7, 0, 'com_users', 'reset_password', 'all'),
(8, 1, 'com_users', '*', 'all'),
(9, 2, 'com_users', '*', 'own'),
(10, 3, 'com_users', 'get_user', 'all');

-- --------------------------------------------------------

--
-- Table structure for table `phpframe_api_clients`
--

CREATE TABLE IF NOT EXISTS `phpframe_api_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `phpframe_api_clients`
--


-- --------------------------------------------------------

--
-- Table structure for table `phpframe_components`
--

CREATE TABLE IF NOT EXISTS `phpframe_components` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `menu_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `author` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `version` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` enum('0','1') COLLATE utf8_unicode_ci NOT NULL,
  `system` enum('0','1') COLLATE utf8_unicode_ci NOT NULL COMMENT 'system components are required',
  `ordering` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `phpframe_components`
--

INSERT INTO `phpframe_components` (`id`, `name`, `menu_name`, `author`, `version`, `enabled`, `system`, `ordering`) VALUES
(1, 'login', 'Logout', 'Luis Montero', '1.0.0', '1', '1', 99),
(2, 'users', 'Users', 'Luis Montero', '1.0.0', '1', '1', 99),
(3, 'admin', 'Admin', 'Luis Montero', '1.0.0', '1', '1', 99),
(4, 'dashboard', 'Dashboard', 'Luis Montero', '1.0.0', '1', '1', 1);

-- --------------------------------------------------------

--
-- Table structure for table `phpframe_groups`
--

CREATE TABLE IF NOT EXISTS `phpframe_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `phpframe_groups`
--

INSERT INTO `phpframe_groups` (`id`, `name`) VALUES
(1, 'admin'),
(2, 'staff'),
(3, 'api');

-- --------------------------------------------------------

--
-- Table structure for table `phpframe_modules`
--

CREATE TABLE IF NOT EXISTS `phpframe_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `author` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `version` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` enum('0','1') COLLATE utf8_unicode_ci NOT NULL,
  `system` enum('0','1') COLLATE utf8_unicode_ci NOT NULL,
  `position` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `ordering` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `phpframe_modules`
--

INSERT INTO `phpframe_modules` (`id`, `name`, `author`, `version`, `enabled`, `system`, `position`, `ordering`) VALUES
(1, 'menu', 'Luis Montero [e-noise.com]', '1.0.0', '1', '1', 'mainmenu', 1),
(2, 'topmenu', 'Luis Montero [e-noise.com]', '1.0.0', '1', '1', 'topmenu', 1),
(3, 'sysevents', 'Sven Lito [e-noise.com]', '1.0.0', '1', '1', 'sysevents', 1);

-- --------------------------------------------------------

--
-- Table structure for table `phpframe_modules_options`
--

CREATE TABLE IF NOT EXISTS `phpframe_modules_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moduleid` int(11) NOT NULL,
  `option` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `phpframe_modules_options`
--

INSERT INTO `phpframe_modules_options` (`id`, `moduleid`, `option`) VALUES
(1, 1, '*'),
(2, 2, '*'),
(3, 3, '*');

-- --------------------------------------------------------

--
-- Table structure for table `phpframe_organisations`
--

CREATE TABLE IF NOT EXISTS `phpframe_organisations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `phpframe_organisations`
--


-- --------------------------------------------------------

--
-- Table structure for table `phpframe_users`
--

CREATE TABLE IF NOT EXISTS `phpframe_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupid` tinyint(4) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `photo` varchar(128) COLLATE utf8_unicode_ci DEFAULT 'default.png',
  `notifications` enum('0','1') COLLATE utf8_unicode_ci DEFAULT '1',
  `show_email` enum('0','1') COLLATE utf8_unicode_ci DEFAULT '1',
  `block` enum('0','1') COLLATE utf8_unicode_ci DEFAULT '0',
  `created` datetime NOT NULL,
  `last_visit` datetime DEFAULT NULL,
  `activation` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `params` text COLLATE utf8_unicode_ci,
  `ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=63 ;

--
-- Dumping data for table `phpframe_users`
--

INSERT INTO `phpframe_users` (`id`, `groupid`, `username`, `password`, `email`, `firstname`, `lastname`, `photo`, `notifications`, `show_email`, `block`, `created`, `last_visit`, `activation`, `params`, `ts`, `deleted`) VALUES
(62, 1, 'admin', '59d0d3a4baecc0fe31a46fb5bd879cd1:kEuXamI4LBOIR405xh5tvq5vBmsr8mNp', 'admin@example.com', 'Administrator', 'ChangeMe', 'default.png', '1', '1', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `phpframe_users_organisations`
--

CREATE TABLE IF NOT EXISTS `phpframe_users_organisations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `organisationid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `phpframe_users_organisations`
--


-- --------------------------------------------------------

--
-- Table structure for table `phpframe_user_openids`
--

CREATE TABLE IF NOT EXISTS `phpframe_user_openids` (
  `openid_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`openid_url`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `phpframe_user_openids`
--

