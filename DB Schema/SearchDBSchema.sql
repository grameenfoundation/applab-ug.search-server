CREATE TABLE IF NOT EXISTS `category` (

	`id`          int(10) unsigned NOT NULL      auto_increment,
	`name`        varchar(100)     NOT NULL,
	`description` varchar(255),
	`ckwsearch`   int(10) unsigned NOT NULL default 0,      -- Should be replaced with and enum(1,0) to represent the bool
	`created`     datetime,                                 -- Consider replace with timestamp NOW()
	`updated`     timestamp        NOT NULL default NOW(),
	PRIMARY KEY (`id`),
	UNIQUE ID `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- TABLE below is not used. Consider getting rid of it. It would be useful for the future
-- as xlation of searches would be a great feature to implement
CREATE TABLE IF NOT EXISTS `language` (

	`id`          int(10) unsigned NOT NULL auto_increment,
	`name`        varchar(100)     NOT NULL,
	`description` varchar(255),
	`created`     datetime,
	`updated`     timestamp        NOT NULL default NOW(),
	PRIMARY KEY (`id`),
	UNIQUE ID `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `keyword` (

	`id`                int(10) unsigned NOT NULL      auto_increment,
	`keyword`           text,
	`aliases`           text,
	`keywordAlias`      text,
	`optionalWords`     text,
	`categoryId`        int(10) unsigned,
	`languageId`        int(10) unsigned,  -- Foreign key to language. Not used at moment
	`createDate`        datetime         NOT NULL,
	`content`           text,
	`otrigger`          int(10) unsigned NOT NULL,
	`updated`           timestamp        NOT NULL default NOW(),
	`quizAction_action` varchar(128)     NOT NULL,
	`quizAction_quizId` int(10) unsigned NOT NULL,
	`attribution`       text,
	PRIMARY KEY (`id`),
	UNIQUE ID `keyword` (`keyword`),
	FOREIGN KEY (`categoryID`) REFERENCES category(id),
	FOREIGN KEY (`languageID`) REFERENCES language(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `normalizedKeywords` (

	`id`                bigint(10) unsigned NOT NULL auto_increment,
	`keywordId`         int(10)    unsigned NOT NULL default 0,
	`normalizedKeyword` varchar(128)        NOT NULL,
	`wordCount`         int(10)             NOT NULL default 0,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`keywordId`) REFERENCES keyword(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- This table will be removed and put into Saleforce as part of 2.8
CREATE TABLE IF NOT EXISTS `OktopusSearchLog` (

	`id`                   bigint(10) unsigned       NOT NULL auto_increment,
	`server_entry_time`    datetime                  NOT NULL default '0000-00-00 00:00:00',
	`handset_submit_time`  datetime                  NOT NULL default '0000-00-00 00:00:00',
	`handset_id`           varchar(255)              NOT NULL,
	`interviewee_id`       varchar(255)              NOT NULL,
	`location`             varchar(255)              NOT NULL,
	`status`               enum('SUCEEDED','FAILED') NOT NULL default 'SUCEEDED',
	`query`                varchar(255)              NOT NULL,
	`content`              trxt                      NOT NULL,
	`interviewer_id`       varchar(255)              NOT NULL,
	`interviewer_name`     varchar(255)              NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;