-- MySQL dump 10.11
--
-- Host: localhost    Database: ycppquiz
-- ------------------------------------------------------
-- Server version	5.0.45

DROP TABLE IF EXISTS `GoogleSMS6001`;
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

--
-- Table structure for table `HealthIVR`
--

DROP TABLE IF EXISTS `HealthIVR`;
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

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;

--
-- Table structure for table `aliases`
--

DROP TABLE IF EXISTS `aliases`;
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

--
-- Table structure for table `answer`
--

DROP TABLE IF EXISTS `answer`;
CREATE TABLE `answer` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `questionId` int(10) unsigned NOT NULL,
  `answer` text,
  `no` int(11) NOT NULL,
  `correct` tinyint(3) unsigned NOT NULL default '0',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=latin1 COMMENT='Stores Answeres to the Quiz questions';

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `ckwsearch` int(10) unsigned NOT NULL default '0',
  `description` varchar(255) default NULL,
  `created` datetime default NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Table structure for table `dictionary`
--

DROP TABLE IF EXISTS `dictionary`;
CREATE TABLE `dictionary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `word` varchar(50) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `word` (`word`)
) ENGINE=InnoDB AUTO_INCREMENT=566 DEFAULT CHARSET=latin1;

--
-- Table structure for table `district`
--

DROP TABLE IF EXISTS `district`;
CREATE TABLE `district` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `name` varchar(100) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Table structure for table `dlr`
--

DROP TABLE IF EXISTS `dlr`;
CREATE TABLE `dlr` (
  `log` varchar(100) default NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `eresult`
--

DROP TABLE IF EXISTS `eresult`;
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
) ENGINE=InnoDB AUTO_INCREMENT=287526 DEFAULT CHARSET=latin1;

--
-- Table structure for table `hit`
--

DROP TABLE IF EXISTS `hit`;
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
) ENGINE=InnoDB AUTO_INCREMENT=3737 DEFAULT CHARSET=latin1 COMMENT='Logs all forms of replies sent';

--
-- Table structure for table `initiative`
--

DROP TABLE IF EXISTS `initiative`;
CREATE TABLE `initiative` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Table structure for table `keyword`
--

DROP TABLE IF EXISTS `keyword`;
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
  PRIMARY KEY  (`id`),
  UNIQUE KEY `keyword` (`keyword`(767)),
  KEY `categoryId` (`categoryId`),
  KEY `languageId` (`languageId`),
  CONSTRAINT `keyword_ibfk_1` FOREIGN KEY (`categoryId`) REFERENCES `category` (`id`),
  CONSTRAINT `keyword_ibfk_2` FOREIGN KEY (`languageId`) REFERENCES `language` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27592 DEFAULT CHARSET=latin1;

DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `DELETE_KEYWORD` BEFORE DELETE ON `keyword` FOR EACH ROW DELETE FROM subkeyword WHERE keywordId=OLD.id */;;

DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;

--
-- Table structure for table `language`
--

DROP TABLE IF EXISTS `language`;
CREATE TABLE `language` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) default NULL,
  `created` datetime default NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Table structure for table `logic`
--

DROP TABLE IF EXISTS `logic`;
CREATE TABLE `logic` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `createdate` datetime NOT NULL,
  `surveyId` bigint(20) unsigned NOT NULL,
  `logic` text,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `surveyId` (`surveyId`),
  CONSTRAINT `logic_ibfk_1` FOREIGN KEY (`surveyId`) REFERENCES `msurvey` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(80) NOT NULL,
  `action` text,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27175 DEFAULT CHARSET=latin1 COMMENT='Logs Actions of administrators';

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
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

--
-- Table structure for table `mresult`
--

DROP TABLE IF EXISTS `mresult`;
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
) ENGINE=InnoDB AUTO_INCREMENT=6126 DEFAULT CHARSET=latin1 COMMENT='Stores Mobile survey results';

--
-- Table structure for table `msglog`
--

DROP TABLE IF EXISTS `msglog`;
CREATE TABLE `msglog` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `type` enum('INCOMING','OUTGOING') default NULL,
  `sender` varchar(15) default NULL,
  `request` varchar(160) default NULL,
  `message` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `msgq`
--

DROP TABLE IF EXISTS `msgq`;
CREATE TABLE `msgq` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `recipient` varchar(20) NOT NULL,
  `message` text,
  `sent` tinyint(3) unsigned NOT NULL default '0',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2144 DEFAULT CHARSET=latin1;

--
-- Table structure for table `msurvey`
--

DROP TABLE IF EXISTS `msurvey`;
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;

--
-- Table structure for table `normalizedKeywords`
--

DROP TABLE IF EXISTS `normalizedKeywords`;
CREATE TABLE `normalizedKeywords` (
  `id` bigint(20) NOT NULL auto_increment,
  `keywordId` int(10) unsigned NOT NULL default '0',
  `normalizedKeyword` varchar(128) NOT NULL default '',
  `wordCount` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ensureNoDup` (`keywordId`,`normalizedKeyword`),
  CONSTRAINT `normalizedKeywords_ibfk_1` FOREIGN KEY (`keywordId`) REFERENCES `keyword` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22912 DEFAULT CHARSET=latin1;

--
-- Table structure for table `occupation`
--

DROP TABLE IF EXISTS `occupation`;
CREATE TABLE `occupation` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `oldFormSurveys`
--

DROP TABLE IF EXISTS `oldFormSurveys`;
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

--
-- Table structure for table `otrigger`
--

DROP TABLE IF EXISTS `otrigger`;
CREATE TABLE `otrigger` (
  `createdate` datetime NOT NULL,
  `keywordId` int(10) unsigned NOT NULL,
  `options` text,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  UNIQUE KEY `keywordId` (`keywordId`),
  CONSTRAINT `otrigger_ibfk_1` FOREIGN KEY (`keywordId`) REFERENCES `keyword` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `phoneno`
--

DROP TABLE IF EXISTS `phoneno`;
CREATE TABLE `phoneno` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `phone` varchar(30) NOT NULL,
  `createDate` datetime NOT NULL,
  `hits` int(10) unsigned NOT NULL default '1',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=5258 DEFAULT CHARSET=latin1 COMMENT='Stores unique phone numbers in the system';

--
-- Table structure for table `qndelivery`
--

DROP TABLE IF EXISTS `qndelivery`;
CREATE TABLE `qndelivery` (
  `questionId` int(10) unsigned NOT NULL,
  `status` varchar(100) default NULL,
  `phoneId` int(10) unsigned NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `question`
--

DROP TABLE IF EXISTS `question`;
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
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=latin1 COMMENT='Stores Quiz Questions';

--
-- Table structure for table `quiz`
--

DROP TABLE IF EXISTS `quiz`;
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1 COMMENT='The Quiz';

--
-- Table structure for table `quizphoneno`
--

DROP TABLE IF EXISTS `quizphoneno`;
CREATE TABLE `quizphoneno` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `quizId` int(10) unsigned NOT NULL,
  `createDate` datetime NOT NULL,
  `phone` varchar(30) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `quizId` (`quizId`,`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=242 DEFAULT CHARSET=latin1 COMMENT='Stores Phone numbers associated with a quiz';

--
-- Table structure for table `quizreply`
--

DROP TABLE IF EXISTS `quizreply`;
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

--
-- Table structure for table `recipient`
--

DROP TABLE IF EXISTS `recipient`;
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

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `session_id` varchar(255) default NULL,
  `expiry_time` int(10) unsigned default NULL,
  `userid` int(10) unsigned default NULL,
  `type` enum('ADMINISTRATOR','LIMITED_USER') NOT NULL default 'ADMINISTRATOR',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1265 DEFAULT CHARSET=latin1;

--
-- Table structure for table `simlog`
--

DROP TABLE IF EXISTS `simlog`;
CREATE TABLE `simlog` (
  `date` datetime NOT NULL,
  `indx` int(10) unsigned NOT NULL,
  `time` varchar(30) NOT NULL,
  `sender` varchar(20) NOT NULL,
  `message` varchar(255) NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `smslog`
--

DROP TABLE IF EXISTS `smslog`;
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
) ENGINE=InnoDB AUTO_INCREMENT=67789 DEFAULT CHARSET=latin1;

--
-- Table structure for table `sresult`
--

DROP TABLE IF EXISTS `sresult`;
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

--
-- Table structure for table `subcounty`
--

DROP TABLE IF EXISTS `subcounty`;
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

--
-- Table structure for table `subkeyword`
--

DROP TABLE IF EXISTS `subkeyword`;
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

--
-- Table structure for table `survey`
--

DROP TABLE IF EXISTS `survey`;
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `DELETE_SURVEY` BEFORE DELETE ON `survey` FOR EACH ROW DELETE FROM surveyno WHERE surveyId=OLD.id */;;

DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;

--
-- Table structure for table `surveyno`
--

DROP TABLE IF EXISTS `surveyno`;
CREATE TABLE `surveyno` (
  `createdate` datetime NOT NULL,
  `phone` varchar(20) NOT NULL,
  `surveyId` bigint(20) unsigned NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  UNIQUE KEY `phone` (`phone`,`surveyId`),
  KEY `surveyId` (`surveyId`),
  CONSTRAINT `surveyno_ibfk_1` FOREIGN KEY (`surveyId`) REFERENCES `survey` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `tmp`
--

DROP TABLE IF EXISTS `tmp`;
CREATE TABLE `tmp` (
  `phone` varchar(30) NOT NULL,
  `keywordId` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  KEY `keywordId` (`keywordId`),
  CONSTRAINT `tmp_ibfk_1` FOREIGN KEY (`keywordId`) REFERENCES `keyword` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
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
) ENGINE=InnoDB AUTO_INCREMENT=5533 DEFAULT CHARSET=latin1 COMMENT='Associates Phone numbers with user information';

--
-- Table structure for table `vAppUploadLog`
--

DROP TABLE IF EXISTS `vAppUploadLog`;
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

--
-- Table structure for table `workinglist`
--

DROP TABLE IF EXISTS `workinglist`;
CREATE TABLE `workinglist` (
  `phone` varchar(20) NOT NULL,
  `owner` int(10) unsigned NOT NULL,
  `createdate` datetime NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  UNIQUE KEY `owner` (`owner`,`phone`),
  CONSTRAINT `workinglist_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `admin` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dump completed on 2010-02-08 12:58:53
