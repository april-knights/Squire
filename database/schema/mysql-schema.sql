-- phpMyAdmin SQL Dump
-- version 4.6.6deb5ubuntu0.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 04, 2022 at 04:51 PM
-- Server version: 5.7.38-0ubuntu0.18.04.1
-- PHP Version: 7.2.24-0ubuntu0.18.04.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

SET FOREIGN_KEY_CHECKS=0; -- There are some foreign key errors when importing...

--
-- Database: `squiretestone`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth`
--

CREATE TABLE `auth` (
  `pkey` int(11) NOT NULL,
  `knightkey` int(11) NOT NULL,
  `redditid` varchar(30) NOT NULL,
  `auth` varchar(32) NOT NULL,
  `refreshtoken` varchar(32) NOT NULL,
  `expirein` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



-- --------------------------------------------------------

--
-- Table structure for table `battalion`
--

CREATE TABLE `battalion` (
  `pkey` tinyint(4) NOT NULL,
  `name` varchar(30) NOT NULL,
  `battdescr` varchar(255) DEFAULT NULL,
  `battlead` int(11) DEFAULT NULL,
  `battsec1` int(11) DEFAULT NULL,
  `battsec2` int(11) DEFAULT NULL,
  `battalias` varchar(10) NOT NULL,
  `color` varchar(15) DEFAULT NULL,
  `motto` varchar(64) DEFAULT NULL,
  `crtsetdt` datetime NOT NULL,
  `crtsetid` int(11) NOT NULL,
  `lstmddt` datetime NOT NULL,
  `lstmdby` int(11) NOT NULL,
  `activeflg` bit(1) NOT NULL,
  `delflg` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `division`
--

CREATE TABLE `division` (
  `pkey` tinyint(4) NOT NULL,
  `name` varchar(30) NOT NULL,
  `divdescr` varchar(255) DEFAULT NULL,
  `divlead` int(11) DEFAULT NULL,
  `divsec1` int(11) DEFAULT NULL,
  `divsec2` int(11) DEFAULT NULL,
  `divalias` varchar(10) NOT NULL,
  `color` varchar(15) DEFAULT NULL,
  `motto` varchar(64) DEFAULT NULL,
  `crtsetdt` datetime NOT NULL,
  `crtsetid` int(11) NOT NULL,
  `lstmdby` int(11) NOT NULL,
  `lstmdts` datetime NOT NULL,
  `activeflg` bit(1) NOT NULL,
  `delflg` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `divknight`
--

CREATE TABLE `divknight` (
  `pkey` tinyint(4) NOT NULL,
  `fkeyknight` int(11) NOT NULL,
  `fkeydivision` int(11) NOT NULL,
  `crtsetdt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `crtsetid` int(11) NOT NULL,
  `lstmdby` int(11) NOT NULL,
  `lstmdts` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delflg` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `pkey` tinyint(4) NOT NULL,
  `title` varchar(30) NOT NULL,
  `livedate` date NOT NULL,
  `enddate` date NOT NULL,
  `redown` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`pkey`, `title`, `livedate`, `enddate`, `redown`) VALUES
(1, 'The Button', '2015-04-01', '2015-06-05', 'Powerlanguage'),
(2, 'Robin', '2016-04-01', '2016-04-08', 'Powerlanguage'),
(3, 'Place', '2017-04-01', '2017-04-03', 'Powerlanguage'),
(4, 'Circle of Trust', '2018-04-02', '2018-04-06', 'mjmayank'),
(5, 'Sequence', '2019-04-01', '2019-04-03', 'youngluck'),
(6, 'Impostor', '2020-04-01', '2020-04-03', 'Powerlanguage'),
(7, 'Second', '2021-04-01', '2021-04-05', 'Powerlanguage'),
(8, 'Place22', '2022-04-01', '2022-04-04', 'TBD');

-- --------------------------------------------------------

--
-- Table structure for table `knight`
--

CREATE TABLE `knight` (
  `pkey` int(11) NOT NULL,
  `knum` int(6) DEFAULT NULL,
  `rname` varchar(30) DEFAULT NULL,
  `dname` varchar(40) DEFAULT NULL,
  `discordid` bigint(25) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `inttrans` varchar(255) DEFAULT NULL,
  `bio` varchar(255) DEFAULT NULL,
  `firstevent` tinyint(4) DEFAULT NULL,
  `frenemy` bit(1) NOT NULL DEFAULT b'0',
  `rlimpact` varchar(255) DEFAULT NULL,
  `batt` tinyint(4) DEFAULT '99',
  `batt2` tinyint(4) DEFAULT NULL,
  `rnk` int(11) DEFAULT '99',
  `security` tinyint(4) DEFAULT NULL,
  `banned` tinyint(1) DEFAULT NULL,
  `crtsetid` int(11) NOT NULL,
  `crtsetdt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lstmdby` int(11) NOT NULL,
  `lstmdts` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activeflg` bit(1) NOT NULL DEFAULT b'1',
  `delflg` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `krank`
--

CREATE TABLE `krank` (
  `pkey` tinyint(4) NOT NULL,
  `name` varchar(20) NOT NULL,
  `rankdescr` varchar(255) DEFAULT NULL,
  `rval` tinyint(4) NOT NULL,
  `uniqe` bit(1) NOT NULL,
  `crtsetdt` datetime NOT NULL,
  `crtsetid` int(11) NOT NULL,
  `lstmdby` int(11) NOT NULL,
  `lstmdts` datetime NOT NULL,
  `activeflg` bit(1) NOT NULL,
  `delflg` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `krank`
--

INSERT INTO `krank` (`pkey`, `name`, `rankdescr`, `rval`, `uniqe`, `crtsetdt`, `crtsetid`, `lstmdby`, `lstmdts`, `activeflg`, `delflg`) VALUES
(1, 'Grandmaster', 'Head of the Order', 1, b'1', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(2, 'First Steward', 'Triad Member, Arts', 2, b'1', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(3, 'First Ranger', 'Triad Member, Diplomacy', 3, b'1', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(4, 'First Builder', 'Triad Member, Internal', 3, b'1', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(5, 'Council Advisor', 'Special Appointment', 4, b'0', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(6, 'Archmage', 'Head of Development', 4, b'1', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(7, 'Commander', 'Battalion Leader', 5, b'0', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(8, 'Captain', 'Captain', 6, b'0', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(9, 'Lieutenant', 'TBD', 7, b'0', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(10, 'First Sergeant', 'Battalion Second', 8, b'0', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(11, 'Sergeant', 'Veteran Knight of the Order', 8, b'0', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(12, 'Knight', 'Member of the Order', 10, b'0', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(13, 'Initiate', 'New Member of the Order', 20, b'0', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(14, 'Applicant', 'TBD', 99, b'0', '2020-02-16 11:28:58', 1, 1, '2020-02-16 11:28:58', b'1', b'0'),
(15, 'Grand Inquisitor', 'Head of Order Intel and OpSec', 4, b'1', '2020-03-17 00:00:00', 1, 1, '2020-03-17 00:00:00', b'1', b'0'),
(16, 'Corporal', 'Recognition of Merit', 9, b'0', '2022-05-30 00:00:00', 1, 1, '2022-05-30 00:00:00', b'1', b'0');

-- --------------------------------------------------------

--
-- Table structure for table `link`
--

CREATE TABLE `link` (
  `pkey` tinyint(4) NOT NULL,
  `typcd` enum('subreddit','event','discord','document') NOT NULL,
  `linknm` varchar(50) NOT NULL,
  `linkdesc` varchar(255) NOT NULL,
  `linkurl` varchar(150) DEFAULT NULL,
  `imgurl` varchar(150) DEFAULT NULL,
  `crtsetdt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `crtsetid` int(11) NOT NULL DEFAULT '1',
  `lstmdby` int(11) NOT NULL DEFAULT '1',
  `lstmdts` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activeflg` bit(1) NOT NULL DEFAULT b'1',
  `delflg` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `link`
--

INSERT INTO `link` (`pkey`, `typcd`, `linknm`, `linkdesc`, `linkurl`, `imgurl`, `crtsetdt`, `crtsetid`, `lstmdby`, `lstmdts`, `activeflg`, `delflg`) VALUES
(1, 'document', 'April Knights Constitution', 'Most current version of the Order\'s Constitution', 'https://bit.ly/AKConstitution2dot1', '/static/img/akdoc.png', '2020-03-18 06:35:09', 1, 1, '2020-03-18 06:35:09', b'1', b'0'),
(2, 'document', 'Vidicaris Maximus Act', 'By which the Order may deal with Heresy, Treachery, or Both.', 'https://goo.gl/4FN4ZT', '/static/img/akdoc.png', '2020-03-18 06:37:09', 1, 1, '2020-03-18 06:37:09', b'1', b'0'),
(3, 'discord', 'April Knights Discord', 'Invite link for AK', ' https://discord.gg/AprilKnights', '/static/img/BackgroundLogo.png', '2020-03-18 06:38:18', 1, 1, '2020-03-18 06:38:18', b'1', b'0'),
(4, 'discord', 'Narrators Nexus Discord', 'Invite link for the Narrators Nexus', 'https://discord.gg/DeqR5MA', '/static/img/nndiscord.gif', '2020-03-18 06:39:44', 1, 1, '2020-03-18 06:39:44', b'1', b'0'),
(5, 'event', 'The Button', 'The Reddit event for 2015', 'https://www.reddit.com/r/thebutton', '/static/img/the_button.jpg', '2020-03-18 06:45:04', 1, 1, '2020-03-18 06:45:04', b'1', b'0'),
(6, 'event', 'The Robin', 'The Reddit event for 2016', 'https://www.reddit.com/r/joinrobin/', '/static/img/robin.png', '2020-03-18 06:47:29', 1, 1, '2020-03-18 06:47:29', b'1', b'0'),
(7, 'event', 'Place', 'The Reddit event for 2017', 'https://www.reddit.com/r/place', '/static/img/place.png', '2020-03-18 06:51:12', 1, 1, '2020-03-18 06:51:12', b'1', b'0'),
(8, 'event', 'Circle of Trust', 'The Reddit event for 2018', 'https://www.reddit.com/r/circleoftrust', '/static/img/CoT.png', '2020-03-18 06:51:12', 1, 1, '2020-03-18 06:51:12', b'1', b'0'),
(9, 'event', 'Sequence', 'The Reddit event for 2019', 'https://www.reddit.com/r/sequence', '/static/img/sequence.png', '2020-03-18 06:53:53', 1, 1, '2020-03-18 06:53:53', b'1', b'0'),
(10, 'discord', 'ccKufi Warbirds', 'Cross functional discord server acting as ccKufi\'s main base of operation.', 'https://discord.gg/QSd2ydQ ', '/static/img/cckufidiscord.png', '2020-03-18 06:55:10', 1, 1, '2020-03-18 06:55:10', b'1', b'0'),
(11, 'subreddit', 'April Knights', 'Primary sub for the AK', 'https://www.reddit.com/r/AprilKnights', '/static/img/BackgroundLogo.png', '2020-03-19 15:20:59', 1, 1, '2020-03-19 15:20:59', b'1', b'0'),
(12, 'subreddit', 'Redguard Knights', 'Primary sub for Redguard', 'https://www.reddit.com/r/RedguardKnights/', '/static/img/RedguardSub.png', '2020-03-19 15:26:27', 1, 1, '2020-03-19 15:26:27', b'1', b'0'),
(13, 'event', 'Imposter', 'The Reddit event for 2020', 'https://www.reddit.com/r/Imposter', '/static/img/imposter.png', '2020-04-06 20:12:43', 1, 1, '2020-04-06 20:12:43', b'1', b'0'),
(14, 'event', 'Second', 'The Reddit event for 2021', 'https://www.reddit.com/r/Second', '/static/img/second.png', '2022-05-30 19:38:05', 1, 1, '2022-05-30 19:38:05', b'1', b'0'),
(15, 'event', 'Place 2022', 'The Reddit event for 2022', 'https://www.reddit.com/r/place', '/static/img/2022Place.png', '2022-05-30 19:41:15', 1, 1, '2022-05-30 19:41:15', b'1', b'0');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `pkey` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `authorid` int(11) NOT NULL DEFAULT '1',
  `level` int(11) NOT NULL,
  `crtsetdt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `crtsetid` int(11) NOT NULL DEFAULT '1',
  `lstmdby` int(11) NOT NULL DEFAULT '1',
  `lstmdts` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activeflg` bit(1) NOT NULL DEFAULT b'1',
  `delflg` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`pkey`, `title`, `body`, `authorid`, `level`, `crtsetdt`, `crtsetid`, `lstmdby`, `lstmdts`, `activeflg`, `delflg`) VALUES
(1, 'Sample Order', 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;\r\n\r\nPraesent sem urna, mollis vel venenatis vel, rhoncus nec ligula. Curabitur sit amet lacus vitae tellus commodo.', 1337, 10, '2020-03-19 15:24:31', 1, 1, '2020-03-19 15:24:31', b'1', b'1'),
(2, 'Review of the Squire', 'Click around and observe the different pages, becoming familiar with the design and how the data relates to itself. Make a note of stale data, when we want to go live, we\'ll want to make sure the data is fresh.', 1, 10, '2020-03-17 03:48:55', 1, 1, '2020-03-17 03:48:55', b'1', b'0'),
(3, 'Be Prepared', 'Get everyone rounded up and their info input into the system. You all should have the ability to edit your own folks, Triad and I can edit everyone.', 1, 5, '2020-03-17 12:44:15', 1, 1, '2020-03-17 12:44:15', b'1', b'0'),
(4, 'Deleted order', 'This order has been deleted.', 1337, 10, '2020-03-17 12:45:27', 1, 1, '2020-03-17 12:45:27', b'1', b'1');

-- --------------------------------------------------------

--
-- Table structure for table `security`
--

CREATE TABLE `security` (
  `pkey` tinyint(4) NOT NULL,
  `secname` varchar(30) NOT NULL,
  `secdescr` varchar(255) DEFAULT NULL,
  `cvuser` bit(1) NOT NULL,
  `cmuser` bit(1) NOT NULL,
  `cduser` bit(1) NOT NULL,
  `cvskill` bit(1) NOT NULL,
  `cmskill` bit(1) NOT NULL,
  `cdskill` bit(1) NOT NULL,
  `cmsskill` bit(1) NOT NULL,
  `cmoskill` bit(1) NOT NULL,
  `cvrank` bit(1) NOT NULL,
  `cmrank` bit(1) NOT NULL,
  `cdrank` bit(1) NOT NULL,
  `cvbatt` bit(1) NOT NULL,
  `cmbatt` bit(1) NOT NULL,
  `cdbatt` bit(1) NOT NULL,
  `cvevent` bit(1) NOT NULL,
  `cmevent` bit(1) NOT NULL,
  `cdevent` bit(1) NOT NULL,
  `cvsec` bit(1) NOT NULL,
  `cmsec` bit(1) NOT NULL,
  `cdsec` bit(1) NOT NULL,
  `cmbattuser` bit(1) NOT NULL,
  `crtsetdt` datetime NOT NULL,
  `crtsetid` int(11) NOT NULL,
  `lstmdby` int(11) NOT NULL,
  `lstmdts` datetime NOT NULL,
  `activeflg` bit(1) NOT NULL,
  `delflg` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `security`
--

INSERT INTO `security` (`pkey`, `secname`, `secdescr`, `cvuser`, `cmuser`, `cduser`, `cvskill`, `cmskill`, `cdskill`, `cmsskill`, `cmoskill`, `cvrank`, `cmrank`, `cdrank`, `cvbatt`, `cmbatt`, `cdbatt`, `cvevent`, `cmevent`, `cdevent`, `cvsec`, `cmsec`, `cdsec`, `cmbattuser`, `crtsetdt`, `crtsetid`, `lstmdby`, `lstmdts`, `activeflg`, `delflg`) VALUES
(1, 'Admin', 'Full Access Admin', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', '2020-02-16 11:32:19', 1, 1, '2020-02-16 11:32:19', b'1', b'0'),
(2, 'Grandmaster', 'Admin', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'1', b'0', b'1', '2020-02-16 11:32:19', 1, 1, '2020-02-16 11:32:19', b'1', b'0'),
(3, 'Councilor', 'Council Member', b'1', b'1', b'0', b'1', b'1', b'0', b'1', b'1', b'1', b'0', b'0', b'1', b'1', b'0', b'1', b'0', b'0', b'0', b'0', b'0', b'1', '2020-02-16 11:32:19', 1, 1, '2020-02-16 11:32:19', b'1', b'0'),
(4, 'Commander', 'Battalion Commander', b'1', b'0', b'0', b'1', b'1', b'0', b'1', b'1', b'1', b'0', b'0', b'1', b'1', b'0', b'1', b'0', b'0', b'0', b'0', b'0', b'1', '2020-02-16 11:32:19', 1, 1, '2020-02-16 11:32:19', b'1', b'0'),
(5, 'Lt', 'Battalion Second', b'1', b'0', b'0', b'1', b'0', b'0', b'1', b'1', b'1', b'0', b'0', b'1', b'0', b'0', b'1', b'0', b'0', b'0', b'0', b'0', b'1', '2020-02-16 11:32:19', 1, 1, '2020-02-16 11:32:19', b'1', b'0'),
(6, 'First Sergeant', 'Battalion Second', b'1', b'0', b'0', b'1', b'0', b'0', b'1', b'1', b'1', b'0', b'0', b'1', b'0', b'0', b'1', b'0', b'0', b'0', b'0', b'0', b'1', '2020-02-16 11:32:19', 1, 1, '2020-02-16 11:32:19', b'1', b'0'),
(7, 'Sergeant', 'Veteran Knight of the Realm', b'1', b'0', b'0', b'1', b'0', b'0', b'1', b'0', b'1', b'0', b'0', b'1', b'0', b'0', b'1', b'0', b'0', b'0', b'0', b'0', b'0', '2020-02-16 11:32:19', 1, 1, '2020-02-16 11:32:19', b'1', b'0'),
(8, 'Knight', 'Knight of the Realm', b'1', b'0', b'0', b'1', b'0', b'0', b'1', b'0', b'1', b'0', b'0', b'1', b'0', b'0', b'1', b'0', b'0', b'0', b'0', b'0', b'0', '2020-02-16 11:32:19', 1, 1, '2020-02-16 11:32:19', b'1', b'0'),
(9, 'Initiate', 'Aspirant to the Blade', b'1', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', '2020-02-16 11:32:19', 1, 1, '2020-02-16 11:32:19', b'1', b'0'),
(50, 'Applicant', 'Newcomer', b'1', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', b'0', '2020-02-16 11:32:19', 1, 1, '2020-02-16 11:32:19', b'1', b'0');

-- --------------------------------------------------------

--
-- Table structure for table `skill`
--

CREATE TABLE `skill` (
  `pkey` tinyint(4) NOT NULL,
  `parentid` int(4) DEFAULT NULL,
  `skillname` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `skilldescr` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `public` bit(1) NOT NULL,
  `crtsetdt` datetime NOT NULL,
  `crtsetid` int(11) NOT NULL,
  `lstmdby` int(11) NOT NULL,
  `lstmdts` datetime NOT NULL,
  `activeflg` bit(1) NOT NULL,
  `delflg` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `skill`
--

INSERT INTO `skill` (`pkey`, `parentid`, `skillname`, `skilldescr`, `public`, `crtsetdt`, `crtsetid`, `lstmdby`, `lstmdts`, `activeflg`, `delflg`) VALUES
(1, NULL, 'Photoshop', 'Image editing/manipulation', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(2, 1, 'Basic Photo Editing', 'Capable of editing images to add text, creation of memes, basic photo manipulation', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(3, 1, 'Advanced Photoshop', 'Capable of advanced photo editing techniques, such as merging multiple images, or placing objects in hands or backgrounds.', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(4, 1, 'Gif Maker', 'Can create custom gifs. Doesn’t necessarily include coming up with the concept of the gif, just being able to create one.', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(5, NULL, 'Artist', 'Crafting of original works', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(6, 5, 'Doodlist', 'Quick sketches, comics, illustrations, etc', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(7, 5, 'Musician', 'Can create music', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(8, NULL, 'Memecraft', 'Comedic timing, punmanship, tasteful trolling, wurst-o-shire, morale boosting', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(9, NULL, 'Management', '', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(10, 9, 'Project Manager ', 'Manages larger projects, especially ones across multiple batallions. Includes checking on individual progress and summarizing progress to leadership.', b'0', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(11, 9, 'Builder', 'Organizes and runs events (giveaways, easter egg hunts, whatever they can come up with). Regular events can increase activity and morale within the Knights and attract new recruits.', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(12, NULL, 'Information Technology', '', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(13, 13, 'DBA', 'Able to manage simple to moderate database set ups', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(14, 13, 'QA', 'The ability to review and debug other people’s code', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(15, 13, 'SysAdmin', 'General systems manager', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(16, 13, 'WebDev', 'Able to review requirements documents and produce code for website enhancement/deployment', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(17, 13, 'Developer', 'Coder', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(18, 13, 'Discord Bot', 'Capable of creating or editing Discord bot code', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(19, 13, 'Google Chrome Extension', 'Familiar with how to create an extension and some of the basic applications it can be used for', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(20, 13, 'Reddit Bot', 'Has experience in reddit bot programming, such as listening for updates on a subreddit, auto-responding to certain messages, or reminder after time frame messages.', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(21, NULL, 'Communication', 'Written/Verbal communications skills, either real time or delayed.', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(22, 21, 'Influencer', 'Posts propaganda, batallion info, hype, copy, etc.  Some people are great at posting to reddit +other platforms.  Allows the people who create content to not worry about getting said content out there. ', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(23, 21, 'Interviewer', 'This person is capable of conducting Knight entry interviews. They know what kind of questions to ask, are personable enough to draw easy conversation, have a good understanding of each battalion’s personality, and are watchful for suspicious characters.', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(24, 21, 'Multi-Lingual', 'Conversant in language other than English', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(25, NULL, 'Strategy', '', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(26, 25, 'Strategic Thinking', 'Able to develop actions and responses in a big picture methodology', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(27, 25, 'Logical Thinking', 'Able to think around corners, addressing/solving out of box challenges', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(28, 25, 'Tactical Thinking', 'Able to devlop transactional plans to respond to circumstances in an agile fashion while maintaining the idea of the overall goal.', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(29, NULL, 'Documentation', '', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(30, 29, 'Research', 'Search and sourcing of topical information based on provided requirements. Essentially Google-Fu.', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(31, 29, 'Scribe ', 'Writes copy, propaganda, stories, etc relating to knights and events - helps to increase recruitment and communication between knights ', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(32, 29, 'Historian ', 'Annual update of our history, success and lessons learned.  Doesn’t have to write it all, but responsible for compiling and proofreading.  Could also be batallion specific.  Improved communication, recognition of knights and great recruitment tool ', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(33, 29, 'Proofreading', 'Reviewing another’s work for consistency, and insuring high quality writing.', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(34, 29, 'Presentation', 'Development of effective slide presentations', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(35, 29, 'Document creation/design', 'Familiarity with Microsoft Word, Google Docs, and pdf creation, such that they can make the content look official, professional, and readily readable. Tasteful use of themes, headers, proper fonts, and colors are part of this skill.', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0'),
(36, 29, 'Spreadsheet', 'Visualization of concrete or abstract concepts into data models utilizing common spreadsheet products', b'1', '2019-12-03 00:00:00', 1, 1, '2019-12-03 16:21:17', b'1', b'0');

-- --------------------------------------------------------

--
-- Table structure for table `userskill`
--

CREATE TABLE `userskill` (
  `usid` int(11) NOT NULL,
  `fkeyuser` int(11) NOT NULL,
  `fkeyskill` int(11) NOT NULL,
  `crtsetdt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `crtsetid` int(11) NOT NULL,
  `lstmdby` int(11) NOT NULL,
  `lstmdts` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delflg` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth`
--
ALTER TABLE `auth`
  ADD PRIMARY KEY (`pkey`),
  ADD KEY `knightkey` (`knightkey`);

--
-- Indexes for table `battalion`
--
ALTER TABLE `battalion`
  ADD PRIMARY KEY (`pkey`),
  ADD KEY `battlead` (`battlead`),
  ADD KEY `battsec1` (`battsec1`),
  ADD KEY `battsec2` (`battsec2`),
  ADD KEY `crtsetid` (`crtsetid`),
  ADD KEY `lstmdby` (`lstmdby`);

--
-- Indexes for table `division`
--
ALTER TABLE `division`
  ADD PRIMARY KEY (`pkey`),
  ADD UNIQUE KEY `divalias` (`divalias`),
  ADD UNIQUE KEY `pkey` (`pkey`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `divsec1` (`divsec1`),
  ADD KEY `division_ibfk_4` (`divlead`),
  ADD KEY `division_ibfk_5` (`divsec2`),
  ADD KEY `division_ibfk_6` (`crtsetid`),
  ADD KEY `division_ibfk_7` (`lstmdby`);

--
-- Indexes for table `divknight`
--
ALTER TABLE `divknight`
  ADD PRIMARY KEY (`pkey`),
  ADD UNIQUE KEY `pkey` (`pkey`),
  ADD KEY `fkeyknight` (`fkeyknight`),
  ADD KEY `crtsetid` (`crtsetid`),
  ADD KEY `lstmdby` (`lstmdby`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`pkey`);

--
-- Indexes for table `knight`
--
ALTER TABLE `knight`
  ADD PRIMARY KEY (`pkey`),
  ADD UNIQUE KEY `knum` (`knum`),
  ADD UNIQUE KEY `rname` (`rname`),
  ADD UNIQUE KEY `dname` (`dname`),
  ADD KEY `firstevent` (`firstevent`),
  ADD KEY `security` (`security`),
  ADD KEY `crtsetid` (`crtsetid`),
  ADD KEY `lstmdby` (`lstmdby`);

--
-- Indexes for table `krank`
--
ALTER TABLE `krank`
  ADD PRIMARY KEY (`pkey`),
  ADD KEY `crtsetid` (`crtsetid`),
  ADD KEY `lstmdby` (`lstmdby`);

--
-- Indexes for table `link`
--
ALTER TABLE `link`
  ADD PRIMARY KEY (`pkey`),
  ADD KEY `crtsetid` (`crtsetid`),
  ADD KEY `lstmdby` (`lstmdby`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`pkey`);

--
-- Indexes for table `security`
--
ALTER TABLE `security`
  ADD PRIMARY KEY (`pkey`),
  ADD KEY `crtsetid` (`crtsetid`),
  ADD KEY `lstmdby` (`lstmdby`);

--
-- Indexes for table `skill`
--
ALTER TABLE `skill`
  ADD PRIMARY KEY (`pkey`),
  ADD KEY `crtsetid` (`crtsetid`),
  ADD KEY `lstmdby` (`lstmdby`);

--
-- Indexes for table `userskill`
--
ALTER TABLE `userskill`
  ADD PRIMARY KEY (`usid`),
  ADD KEY `fkeyuser` (`fkeyuser`),
  ADD KEY `crtsetid` (`crtsetid`),
  ADD KEY `lstmdby` (`lstmdby`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `divknight`
--
ALTER TABLE `divknight`
  MODIFY `pkey` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT for table `knight`
--
ALTER TABLE `knight`
  MODIFY `pkey` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1369;
--
-- AUTO_INCREMENT for table `link`
--
ALTER TABLE `link`
  MODIFY `pkey` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `pkey` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `skill`
--
ALTER TABLE `skill`
  MODIFY `pkey` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
--
-- AUTO_INCREMENT for table `userskill`
--
ALTER TABLE `userskill`
  MODIFY `usid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=341;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `auth`
--
ALTER TABLE `auth`
  ADD CONSTRAINT `auth_ibfk_1` FOREIGN KEY (`knightkey`) REFERENCES `knight` (`pkey`);

--
-- Constraints for table `battalion`
--
ALTER TABLE `battalion`
  ADD CONSTRAINT `battalion_ibfk_1` FOREIGN KEY (`battlead`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `battalion_ibfk_2` FOREIGN KEY (`battsec1`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `battalion_ibfk_3` FOREIGN KEY (`battsec2`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `battalion_ibfk_4` FOREIGN KEY (`crtsetid`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `battalion_ibfk_5` FOREIGN KEY (`lstmdby`) REFERENCES `knight` (`pkey`);

--
-- Constraints for table `division`
--
ALTER TABLE `division`
  ADD CONSTRAINT `division_ibfk_1` FOREIGN KEY (`divlead`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `division_ibfk_2` FOREIGN KEY (`divsec1`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `division_ibfk_3` FOREIGN KEY (`lstmdby`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `division_ibfk_4` FOREIGN KEY (`divlead`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `division_ibfk_5` FOREIGN KEY (`divsec2`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `division_ibfk_6` FOREIGN KEY (`crtsetid`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `division_ibfk_7` FOREIGN KEY (`lstmdby`) REFERENCES `knight` (`pkey`);

--
-- Constraints for table `divknight`
--
ALTER TABLE `divknight`
  ADD CONSTRAINT `divknight_ibfk_1` FOREIGN KEY (`fkeyknight`) REFERENCES `knight` (`pkey`);

--
-- Constraints for table `knight`
--
ALTER TABLE `knight`
  ADD CONSTRAINT `knight_ibfk_1` FOREIGN KEY (`firstevent`) REFERENCES `event` (`pkey`),
  ADD CONSTRAINT `knight_ibfk_2` FOREIGN KEY (`security`) REFERENCES `security` (`pkey`),
  ADD CONSTRAINT `knight_ibfk_3` FOREIGN KEY (`crtsetid`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `knight_ibfk_4` FOREIGN KEY (`lstmdby`) REFERENCES `knight` (`pkey`);

--
-- Constraints for table `krank`
--
ALTER TABLE `krank`
  ADD CONSTRAINT `krank_ibfk_1` FOREIGN KEY (`crtsetid`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `krank_ibfk_2` FOREIGN KEY (`lstmdby`) REFERENCES `knight` (`pkey`);

--
-- Constraints for table `link`
--
ALTER TABLE `link`
  ADD CONSTRAINT `link_ibfk_1` FOREIGN KEY (`crtsetid`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `link_ibfk_2` FOREIGN KEY (`lstmdby`) REFERENCES `knight` (`pkey`);

--
-- Constraints for table `security`
--
ALTER TABLE `security`
  ADD CONSTRAINT `security_ibfk_1` FOREIGN KEY (`crtsetid`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `security_ibfk_2` FOREIGN KEY (`lstmdby`) REFERENCES `knight` (`pkey`);

--
-- Constraints for table `skill`
--
ALTER TABLE `skill`
  ADD CONSTRAINT `skill_ibfk_1` FOREIGN KEY (`crtsetid`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `skill_ibfk_2` FOREIGN KEY (`lstmdby`) REFERENCES `knight` (`pkey`);

--
-- Constraints for table `userskill`
--
ALTER TABLE `userskill`
  ADD CONSTRAINT `userskill_ibfk_1` FOREIGN KEY (`fkeyuser`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `userskill_ibfk_2` FOREIGN KEY (`crtsetid`) REFERENCES `knight` (`pkey`),
  ADD CONSTRAINT `userskill_ibfk_3` FOREIGN KEY (`lstmdby`) REFERENCES `knight` (`pkey`);

-- Create Laravel migrations table as having this file as a Laravel schema requires this table to be here too
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
                              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                              `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                              `batch` int(11) NOT NULL,
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
