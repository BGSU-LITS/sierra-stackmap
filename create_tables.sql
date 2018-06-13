CREATE TABLE `current` (
    `location_id` varchar(100) NOT NULL
);

CREATE TABLE `iconassign` (
    `iconid` int(100) default NULL,
    `mapid` int(100) NOT NULL
);

CREATE TABLE `iconimgs` (
    `icoid` int(100) NOT NULL auto_increment,
    `name` varchar(100) NOT NULL,
    `filename` varchar(100) NOT NULL,
    PRIMARY KEY (`icoid`)
);

CREATE TABLE `mapimgs` (
    `mapid` int(100) NOT NULL auto_increment,
    `name` varchar(100) NOT NULL,
    `filename` varchar(100) NOT NULL,
    PRIMARY KEY (`mapid`)
);

CREATE TABLE `maps` (
    `location_id` int(10) unsigned NOT NULL auto_increment,
    `location` varchar(100) NOT NULL,
    `text_link` varchar(100) NOT NULL,
    `is_mapfile` tinyint(1) NOT NULL,
    `mapid` int(100) default NULL,
    PRIMARY KEY (`location_id`)
);
