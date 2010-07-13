-- MySQL dump 10.11
--
-- Host: localhost    Database: ycppquiz
-- ------------------------------------------------------
-- Server version	5.0.77-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `33e75ff09dd601bbe69f351039152189`
--

DROP TABLE IF EXISTS `33e75ff09dd601bbe69f351039152189`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `33e75ff09dd601bbe69f351039152189` (
  `id` int(10) unsigned NOT NULL,
  `misdn` varchar(40) default NULL,
  `names` varchar(80) default NULL,
  `hits` int(10) unsigned NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `34173cb38f07f89ddbebc2ac9128303f`
--

DROP TABLE IF EXISTS `34173cb38f07f89ddbebc2ac9128303f`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `34173cb38f07f89ddbebc2ac9128303f` (
  `id` int(10) unsigned NOT NULL,
  `misdn` varchar(40) default NULL,
  `names` varchar(80) default NULL,
  `hits` int(10) unsigned NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `6ea9ab1baa0efb9e19094440c317e21b`
--

DROP TABLE IF EXISTS `6ea9ab1baa0efb9e19094440c317e21b`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `6ea9ab1baa0efb9e19094440c317e21b` (
  `id` int(10) unsigned NOT NULL,
  `misdn` varchar(40) default NULL,
  `names` varchar(80) default NULL,
  `hits` int(10) unsigned NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `6f4922f45568161a8cdf4ad2299f6d23`
--

DROP TABLE IF EXISTS `6f4922f45568161a8cdf4ad2299f6d23`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `6f4922f45568161a8cdf4ad2299f6d23` (
  `id` int(10) unsigned NOT NULL,
  `misdn` varchar(40) default NULL,
  `names` varchar(80) default NULL,
  `hits` int(10) unsigned NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `8e296a067a37563370ded05f5a3bf3ec`
--

DROP TABLE IF EXISTS `8e296a067a37563370ded05f5a3bf3ec`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `8e296a067a37563370ded05f5a3bf3ec` (
  `id` int(10) unsigned NOT NULL,
  `misdn` varchar(40) default NULL,
  `names` varchar(80) default NULL,
  `hits` int(10) unsigned NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `9bf31c7ff062936a96d3c8bd1f8f2ff3`
--

DROP TABLE IF EXISTS `9bf31c7ff062936a96d3c8bd1f8f2ff3`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `9bf31c7ff062936a96d3c8bd1f8f2ff3` (
  `id` int(10) unsigned NOT NULL,
  `misdn` varchar(40) default NULL,
  `names` varchar(80) default NULL,
  `hits` int(10) unsigned NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `GlobalSettings`
--

DROP TABLE IF EXISTS `GlobalSettings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `GlobalSettings` (
  `attribution` tinyint(3) unsigned NOT NULL default '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `GoogleSMS6001`
--

DROP TABLE IF EXISTS `GoogleSMS6001`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `GoogleSMS6001` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `logdate` datetime NOT NULL,
  `misdn` varchar(30) NOT NULL,
  `site` varchar(128) default NULL,
  `location` varchar(128) default NULL,
  `importID` varchar(128) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `HealthIVR`
--

DROP TABLE IF EXISTS `HealthIVR`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `HealthIVR` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `logdate` datetime NOT NULL,
  `misdn` varchar(30) NOT NULL,
  `duration` int(10) unsigned default NULL,
  `importID` varchar(128) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `OktopusSearchLog`
--

DROP TABLE IF EXISTS `OktopusSearchLog`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `OktopusSearchLog` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `server_entry_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `handset_submit_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `handset_id` varchar(255) NOT NULL default '',
  `interviewee_id` varchar(255) NOT NULL default '',
  `location` varchar(255) NOT NULL default '',
  `status` enum('SUCCEEDED','FAILED') NOT NULL default 'SUCCEEDED',
  `query` varchar(255) NOT NULL default '',
  `content` text NOT NULL,
  `interviewer_id` varchar(255) NOT NULL default '',
  `interviewer_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2169 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `aab3238922bcc25a6f606eb525ffdc56`
--

DROP TABLE IF EXISTS `aab3238922bcc25a6f606eb525ffdc56`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `aab3238922bcc25a6f606eb525ffdc56` (
  `id` int(10) unsigned NOT NULL,
  `misdn` varchar(40) default NULL,
  `names` varchar(80) default NULL,
  `hits` int(10) unsigned NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `admin` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `email` varchar(128) NOT NULL,
  `type` enum('ADMINISTRATOR','LIMITED_USER') NOT NULL default 'ADMINISTRATOR',
  `active` tinyint(3) unsigned NOT NULL default '1',
  `createDate` datetime NOT NULL,
  `capability` varchar(255) NOT NULL,
  `su` varchar(50) default NULL,
  `sustr` varchar(100) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `aliases`
--

DROP TABLE IF EXISTS `aliases`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `aliases` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `word_id` int(10) unsigned NOT NULL,
  `alias` varchar(100) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `word_id` (`word_id`,`alias`),
  UNIQUE KEY `alias` (`alias`)
) ENGINE=InnoDB AUTO_INCREMENT=326 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `answer`
--

DROP TABLE IF EXISTS `answer`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `answer` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `questionId` int(10) unsigned NOT NULL,
  `answer` text,
  `no` int(11) NOT NULL,
  `correct` tinyint(3) unsigned NOT NULL default '0',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=latin1 COMMENT='Stores Answeres to the Quiz questions';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `c4ca4238a0b923820dcc509a6f75849b`
--

DROP TABLE IF EXISTS `c4ca4238a0b923820dcc509a6f75849b`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `c4ca4238a0b923820dcc509a6f75849b` (
  `id` int(10) unsigned NOT NULL,
  `misdn` varchar(40) default NULL,
  `names` varchar(80) default NULL,
  `hits` int(10) unsigned NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `c51ce410c124a10e0db5e4b97fc2af39`
--

DROP TABLE IF EXISTS `c51ce410c124a10e0db5e4b97fc2af39`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `c51ce410c124a10e0db5e4b97fc2af39` (
  `id` int(10) unsigned NOT NULL,
  `misdn` varchar(40) default NULL,
  `names` varchar(80) default NULL,
  `hits` int(10) unsigned NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `ckwsearch` int(10) unsigned NOT NULL default '0',
  `description` varchar(255) default NULL,
  `created` datetime default NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `dictionary`
--

DROP TABLE IF EXISTS `dictionary`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `dictionary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `word` varchar(50) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `word` (`word`)
) ENGINE=InnoDB AUTO_INCREMENT=1123 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `district`
--

DROP TABLE IF EXISTS `district`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `district` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `name` varchar(100) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `dlr`
--

DROP TABLE IF EXISTS `dlr`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `dlr` (
  `log` varchar(100) default NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `e4da3b7fbbce2345d7772b0674a318d5`
--

DROP TABLE IF EXISTS `e4da3b7fbbce2345d7772b0674a318d5`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `e4da3b7fbbce2345d7772b0674a318d5` (
  `id` int(10) unsigned NOT NULL,
  `misdn` varchar(40) default NULL,
  `names` varchar(80) default NULL,
  `hits` int(10) unsigned NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `eresult`
--

DROP TABLE IF EXISTS `eresult`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `eresult` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `surveyId` bigint(20) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `fieldcode` varchar(100) NOT NULL,
  `fieldtype` varchar(50) NOT NULL,
  `fieldvalue` varchar(100) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `surveyId` (`surveyId`),
  CONSTRAINT `eresult_ibfk_1` FOREIGN KEY (`surveyId`) REFERENCES `msurvey` (`id`),
  CONSTRAINT `eresult_ibfk_2` FOREIGN KEY (`surveyId`) REFERENCES `msurvey` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=291111 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `hit`
--

DROP TABLE IF EXISTS `hit`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `hit` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `keyword` varchar(80) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `request` varchar(200) default NULL,
  `reply` varchar(200) default NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `phone` (`phone`),
  KEY `id_key` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4682 DEFAULT CHARSET=latin1 COMMENT='Logs all forms of replies sent';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `initiative`
--

DROP TABLE IF EXISTS `initiative`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `initiative` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `keyword`
--

DROP TABLE IF EXISTS `keyword`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `keyword` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `keyword` text,
  `aliases` text,
  `keywordAlias` text,
  `optionalWords` text,
  `categoryId` int(10) unsigned default NULL,
  `languageId` int(10) unsigned default NULL,
  `createDate` datetime NOT NULL,
  `content` text,
  `otrigger` int(10) unsigned NOT NULL default '0',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `quizAction_action` varchar(128) NOT NULL default '',
  `quizAction_quizId` int(10) unsigned NOT NULL default '0',
  `attribution` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `keyword` (`keyword`(767)),
  KEY `categoryId` (`categoryId`),
  KEY `languageId` (`languageId`),
  CONSTRAINT `keyword_ibfk_1` FOREIGN KEY (`categoryId`) REFERENCES `category` (`id`),
  CONSTRAINT `keyword_ibfk_2` FOREIGN KEY (`languageId`) REFERENCES `language` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42403 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

/*!50003 SET @SAVE_SQL_MODE=@@SQL_MODE*/;

DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `DELETE_KEYWORD` BEFORE DELETE ON `keyword` FOR EACH ROW DELETE FROM subkeyword WHERE keywordId=OLD.id */;;

DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@SAVE_SQL_MODE*/;

--
-- Table structure for table `language`
--

DROP TABLE IF EXISTS `language`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `language` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) default NULL,
  `created` datetime default NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `logic`
--

DROP TABLE IF EXISTS `logic`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `logic` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `createdate` datetime NOT NULL,
  `surveyId` bigint(20) unsigned NOT NULL,
  `logic` text,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `surveyId` (`surveyId`),
  CONSTRAINT `logic_ibfk_1` FOREIGN KEY (`surveyId`) REFERENCES `msurvey` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `logs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(80) NOT NULL,
  `action` text,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38442 DEFAULT CHARSET=latin1 COMMENT='Logs Actions of administrators';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `message` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `message` text,
  `sent` tinyint(3) unsigned NOT NULL default '0',
  `sendTime` datetime NOT NULL,
  `createDate` datetime NOT NULL,
  `admin` varchar(50) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `senderPhoneNumber` varchar(128) NOT NULL default '',
  `messageStatus` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `mresult`
--

DROP TABLE IF EXISTS `mresult`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `mresult` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `surveyId` bigint(20) unsigned default NULL,
  `phoneId` varchar(50) default NULL,
  `form` text,
  `surveySignature` varchar(128) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `surveyId` (`surveyId`),
  KEY `phoneId` (`phoneId`),
  KEY `id_key` (`id`),
  CONSTRAINT `mresult_ibfk_1` FOREIGN KEY (`surveyId`) REFERENCES `msurvey` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6190 DEFAULT CHARSET=latin1 COMMENT='Stores Mobile survey results';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `msglog`
--

DROP TABLE IF EXISTS `msglog`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `msglog` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `type` enum('INCOMING','OUTGOING') default NULL,
  `sender` varchar(15) default NULL,
  `request` varchar(160) default NULL,
  `message` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `msgq`
--

DROP TABLE IF EXISTS `msgq`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `msgq` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `recipient` varchar(20) NOT NULL,
  `message` text,
  `sent` tinyint(3) unsigned NOT NULL default '0',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2144 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `msurvey`
--

DROP TABLE IF EXISTS `msurvey`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `msurvey` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `createdate` datetime NOT NULL,
  `name` varchar(100) NOT NULL,
  `active` tinyint(3) unsigned NOT NULL default '0',
  `useraccess` tinyint(3) unsigned NOT NULL default '0',
  `fif` text,
  `logic` text,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updatelogic` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `normalizedKeywords`
--

DROP TABLE IF EXISTS `normalizedKeywords`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `normalizedKeywords` (
  `id` bigint(20) NOT NULL auto_increment,
  `keywordId` int(10) unsigned NOT NULL default '0',
  `normalizedKeyword` varchar(128) NOT NULL default '',
  `wordCount` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ensureNoDup` (`keywordId`,`normalizedKeyword`),
  CONSTRAINT `normalizedKeywords_ibfk_1` FOREIGN KEY (`keywordId`) REFERENCES `keyword` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47889 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `occupation`
--

DROP TABLE IF EXISTS `occupation`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `occupation` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `oldFormSurveys`
--

DROP TABLE IF EXISTS `oldFormSurveys`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `oldFormSurveys` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `logdate` datetime NOT NULL,
  `msisdn` varchar(30) NOT NULL,
  `otherInfo` text,
  `importID` varchar(128) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `otrigger`
--

DROP TABLE IF EXISTS `otrigger`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `otrigger` (
  `createdate` datetime NOT NULL,
  `keywordId` int(10) unsigned NOT NULL,
  `options` text,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  UNIQUE KEY `keywordId` (`keywordId`),
  CONSTRAINT `otrigger_ibfk_1` FOREIGN KEY (`keywordId`) REFERENCES `keyword` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `phoneno`
--

DROP TABLE IF EXISTS `phoneno`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `phoneno` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `phone` varchar(30) NOT NULL,
  `createDate` datetime NOT NULL,
  `hits` int(10) unsigned NOT NULL default '1',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=5258 DEFAULT CHARSET=latin1 COMMENT='Stores unique phone numbers in the system';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `qndelivery`
--

DROP TABLE IF EXISTS `qndelivery`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `qndelivery` (
  `questionId` int(10) unsigned NOT NULL,
  `status` varchar(100) default NULL,
  `phoneId` int(10) unsigned NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `question`
--

DROP TABLE IF EXISTS `question`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `question` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `quizId` int(10) unsigned NOT NULL,
  `sendTime` datetime NOT NULL,
  `createDate` date NOT NULL,
  `createTime` datetime NOT NULL,
  `keyword` varchar(50) default NULL,
  `question` varchar(255) NOT NULL,
  `correctReply` varchar(200) default NULL,
  `wrongReply` varchar(200) default NULL,
  `no` int(10) unsigned default NULL,
  `sent` tinyint(3) unsigned NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `quizId` (`quizId`,`question`),
  UNIQUE KEY `keyword` (`keyword`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=latin1 COMMENT='Stores Quiz Questions';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `quiz`
--

DROP TABLE IF EXISTS `quiz`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `quiz` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `sendall` tinyint(3) unsigned NOT NULL default '0',
  `reply` varchar(200) default NULL,
  `singleKeyword` tinyint(3) unsigned NOT NULL default '0',
  `keyword` varchar(50) default NULL,
  `createDate` datetime NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `keyword` (`keyword`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=latin1 COMMENT='The Quiz';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `quizphoneno`
--

DROP TABLE IF EXISTS `quizphoneno`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `quizphoneno` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `quizId` int(10) unsigned NOT NULL,
  `createDate` datetime NOT NULL,
  `phone` varchar(30) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `quizId` (`quizId`,`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=251 DEFAULT CHARSET=latin1 COMMENT='Stores Phone numbers associated with a quiz';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `quizreply`
--

DROP TABLE IF EXISTS `quizreply`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `quizreply` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `questionId` int(10) unsigned NOT NULL,
  `phone` varchar(30) NOT NULL,
  `reply` text,
  `correct` tinyint(3) unsigned NOT NULL default '0',
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `phone` (`phone`),
  KEY `questionId` (`questionId`),
  KEY `id_key` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2478 DEFAULT CHARSET=latin1 COMMENT='A reply to a quiz qusetion';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `recipient`
--

DROP TABLE IF EXISTS `recipient`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `recipient` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `messageId` int(10) unsigned NOT NULL,
  `phone` varchar(30) NOT NULL,
  `createDate` datetime NOT NULL,
  `deliveryStatus` tinyint(3) unsigned NOT NULL default '0',
  `status` varchar(100) default NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `messageId` (`messageId`,`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=29087 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sessions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `session_id` varchar(255) default NULL,
  `expiry_time` int(10) unsigned default NULL,
  `userid` int(10) unsigned default NULL,
  `type` enum('ADMINISTRATOR','LIMITED_USER') NOT NULL default 'ADMINISTRATOR',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1453 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `simlog`
--

DROP TABLE IF EXISTS `simlog`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `simlog` (
  `date` datetime NOT NULL,
  `indx` int(10) unsigned NOT NULL,
  `time` varchar(30) NOT NULL,
  `sender` varchar(20) NOT NULL,
  `message` varchar(255) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `smslog`
--

DROP TABLE IF EXISTS `smslog`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `smslog` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `direction` enum('OUTGOING','INCOMING') default NULL,
  `processed` tinyint(3) unsigned NOT NULL default '0',
  `sender` varchar(20) default NULL,
  `recipient` varchar(20) default NULL,
  `message` text,
  `userId` int(10) unsigned NOT NULL,
  `status` varchar(100) default NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=70039 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `sresult`
--

DROP TABLE IF EXISTS `sresult`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sresult` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `surveyId` int(10) unsigned NOT NULL,
  `phone` varchar(30) NOT NULL,
  `request` varchar(255) default NULL,
  `reply` text,
  `analysis` text,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `phone` (`phone`),
  KEY `id_key` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2109 DEFAULT CHARSET=latin1 COMMENT='Replies to a multiple question survey';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `subcounty`
--

DROP TABLE IF EXISTS `subcounty`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `subcounty` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `districtId` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `districtId` (`districtId`,`name`),
  CONSTRAINT `subcounty_ibfk_1` FOREIGN KEY (`districtId`) REFERENCES `district` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `subkeyword`
--

DROP TABLE IF EXISTS `subkeyword`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `subkeyword` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `keywordId` int(10) unsigned NOT NULL,
  `keyword` varchar(80) NOT NULL,
  `createdate` datetime NOT NULL,
  `content` text,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `keywordId` (`keywordId`,`keyword`),
  CONSTRAINT `subkeyword_ibfk_1` FOREIGN KEY (`keywordId`) REFERENCES `keyword` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `survey`
--

DROP TABLE IF EXISTS `survey`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `survey` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `createdate` datetime NOT NULL,
  `name` varchar(100) NOT NULL,
  `keyword` varchar(50) default NULL,
  `reply` varchar(200) default NULL,
  `questions` text,
  `sendtime` datetime NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `keyword` (`keyword`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

/*!50003 SET @SAVE_SQL_MODE=@@SQL_MODE*/;

DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `DELETE_SURVEY` BEFORE DELETE ON `survey` FOR EACH ROW DELETE FROM surveyno WHERE surveyId=OLD.id */;;

DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@SAVE_SQL_MODE*/;

--
-- Table structure for table `surveyno`
--

DROP TABLE IF EXISTS `surveyno`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `surveyno` (
  `createdate` datetime NOT NULL,
  `phone` varchar(20) NOT NULL,
  `surveyId` bigint(20) unsigned NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  UNIQUE KEY `phone` (`phone`,`surveyId`),
  KEY `surveyId` (`surveyId`),
  CONSTRAINT `surveyno_ibfk_1` FOREIGN KEY (`surveyId`) REFERENCES `survey` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tmp`
--

DROP TABLE IF EXISTS `tmp`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tmp` (
  `phone` varchar(30) NOT NULL,
  `keywordId` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  KEY `keywordId` (`keywordId`),
  CONSTRAINT `tmp_ibfk_1` FOREIGN KEY (`keywordId`) REFERENCES `keyword` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `createdate` datetime NOT NULL,
  `names` varchar(100) default NULL,
  `misdn` varchar(30) NOT NULL,
  `phones` varchar(100) default NULL COMMENT 'Other phone number for this user',
  `gender` enum('Male','Female') default NULL,
  `dob` date NOT NULL default '0000-00-00',
  `subcountyId` int(10) unsigned default NULL,
  `groups` varchar(255) default NULL,
  `occupation` varchar(100) default NULL,
  `occupationId` int(10) unsigned default NULL,
  `location` varchar(50) default NULL,
  `initiativeInfo` varchar(255) default NULL,
  `initiativeId` int(10) unsigned default NULL,
  `deviceInfo` varchar(100) default NULL,
  `gpscordinates` varchar(80) default NULL,
  `notes` varchar(255) default NULL,
  `hits` int(10) unsigned NOT NULL default '0',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `misdn` (`misdn`),
  KEY `initiativeId` (`initiativeId`),
  KEY `occupationId` (`occupationId`),
  KEY `subcountyId` (`subcountyId`),
  CONSTRAINT `user_ibfk_1` FOREIGN KEY (`initiativeId`) REFERENCES `initiative` (`id`),
  CONSTRAINT `user_ibfk_2` FOREIGN KEY (`occupationId`) REFERENCES `occupation` (`id`),
  CONSTRAINT `user_ibfk_3` FOREIGN KEY (`subcountyId`) REFERENCES `subcounty` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6361 DEFAULT CHARSET=latin1 COMMENT='Associates Phone numbers with user information';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `vAppUploadLog`
--

DROP TABLE IF EXISTS `vAppUploadLog`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `vAppUploadLog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `filename` varchar(255) NOT NULL,
  `application` varchar(128) NOT NULL,
  `fileSignature` varchar(128) NOT NULL,
  `importID` varchar(128) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `workinglist`
--

DROP TABLE IF EXISTS `workinglist`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `workinglist` (
  `phone` varchar(20) NOT NULL,
  `owner` int(10) unsigned NOT NULL,
  `createdate` datetime NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  UNIQUE KEY `owner` (`owner`,`phone`),
  CONSTRAINT `workinglist_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `admin` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-07-13 12:47:41
