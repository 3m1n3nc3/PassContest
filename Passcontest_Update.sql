SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


-- Drop the albums TABLE
DROP TABLE IF EXISTS albums;

-- Alter the users TABLE
ALTER TABLE users ADD online int(11) NOT NULL;
ALTER TABLE users ADD gender varchar(128) NULL DEFAULT NULL AFTER `lname`,

-- Alter the settings TABLE
ALTER TABLE settings ADD email_reply_temp text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE settings ADD online_time int(11) NOT NULL DEFAULT '600';
ALTER TABLE settings ADD per_messenger int(11) NOT NULL DEFAULT '5';
ALTER TABLE settings ADD email_social enum('0','1') NOT NULL DEFAULT '1';
ALTER TABLE settings ADD twilio_sid varchar(128) DEFAULT NULL;
ALTER TABLE settings ADD twilio_token varchar(128) DEFAULT NULL;
ALTER TABLE settings ADD site_phone varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE settings ADD ads_6 text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE settings ADD skin varchar(128) NOT NULL DEFAULT 'mdb-skin';
ALTER TABLE settings ADD landing enum('1','2') NOT NULL DEFAULT '1';
ALTER TABLE settings ADD sms enum('0','1') NOT NULL DEFAULT '0';
ALTER TABLE settings ADD sms_premium enum('0','1') NOT NULL DEFAULT '1';

-- Alter the comment TABLE
ALTER TABLE comments ADD master enum('post','gallery','poll','') NOT NULL DEFAULT 'post';

-- Alter the contest TABLE
ALTER TABLE contest ADD cid int(11) NOT NULL;
ALTER TABLE contest ADD tags varchar(255) DEFAULT NULL;
ALTER TABLE contest ADD allow_free enum('0','1') NOT NULL DEFAULT '1'; 

-- Create the admin TABLE
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(24) NOT NULL,
  `password` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Update admin TABLE
UPDATE `admin` SET `id` = 1, `username` = '<USER_NAME>', `password` = '<PASSWORD>';

-- Create the gallery TABLE
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `description` text,
  `rank` smallint(6) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create the timelines TABLE
CREATE TABLE IF NOT EXISTS `timelines` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `share_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `post_photo` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `privacy` enum('0','1','2') NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create the relationships TABLE
CREATE TABLE IF NOT EXISTS `relationships` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `leader_id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create the likes TABLE
CREATE TABLE IF NOT EXISTS `likes` (
  `lid` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `content_type` enum('post','comment','gallery') NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create the blocked TABLE
CREATE TABLE IF NOT EXISTS `blocked_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`id`,`user_id`),
  KEY `by` (`id`,`by`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 
-- Create the welcome TABLE
CREATE TABLE IF NOT EXISTS `welcome` (
  `carousel_one` text,
  `carousel_one_sub` text,
  `carousel_one_desc` text,
  `carousel_two` text,
  `carousel_two_sub` text,
  `carousel_two_desc` text,
  `carousel_three` text,
  `carousel_three_sub` text,
  `carousel_three_desc` text,
  `intro` text,
  `intro_desc` text,
  `uses_one` text,
  `uses_one_desc` text,
  `uses_two` text,
  `uses_two_desc` text,
  `uses_three` text,
  `uses_three_desc` text,
  `uses_four` text,
  `uses_four_desc` text,
  `cover` varchar(128) NOT NULL DEFAULT 'profile-city.jpg',
  `favicon` varchar(128) NOT NULL DEFAULT 'favicon.png',
  `logo` varchar(128) NOT NULL DEFAULT 'logo.png',
  `slide_1` varchar(128) NOT NULL DEFAULT 'slide-1.jpg',
  `slide_2` varchar(128) NOT NULL DEFAULT 'slide-2.jpg',
  `slide_3` varchar(128) NOT NULL DEFAULT 'slide-3.jpg',
  `time`  int(11) NOT NULL DEFAULT '6000'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `welcome` (`carousel_one`, `carousel_one_sub`, `carousel_one_desc`, `carousel_two`, `carousel_two_sub`, `carousel_two_desc`, `carousel_three`, `carousel_three_sub`, `carousel_three_desc`, `intro`, `intro_desc`, `uses_one`, `uses_one_desc`, `uses_two`, `uses_two_desc`, `uses_three`, `uses_three_desc`, `uses_four`, `uses_four_desc`, `cover`, `favicon`, `logo`, `slide_1`, `slide_2`, `slide_3`) VALUES
('Encrypted at heart!', 'Designed with your security in mind', 'With strict implementation of latest security encryption and validation standards we made a system that ensures that every counted vote deserved a count, why? Because we want to promote a society controlled and managed by honest people for honest people.', 'No hassle, yet your votes count!', 'No registration strain! No robotic threats!', 'We made the signup process as straight forward as we possibly could, then we set up measures to combat robots and duplicate accounts, not withstanding our awesome management and admin team, always on the lookout for suspicious processes and proceedings, so you can be sure, that vote came from human, from one human... We made the process simple but hard to cheat.', 'We make your contest transparent', 'Let every one see through the process', 'Because we hate the practices of state and real life democracy, we asked \"What if we make a change where we can, in our little possible way?\" So we built a system where your votes really count, and you can be sure of the results you get.', 'Why Passcontest', 'With over 5 years of web development experience and working with several contests and voting processes, we discovered the reasons why systems failed to inprove and why agencies stuck to old methods. First there is the need to gain followers during contests while still ensuring the security and safety of both voters and contestants, then there is the need to secure vote and avoid rigging, but the old methods no longer work. So we built a system that efficiently fulfills these need, without straining you or your voters, no fake or foul comments, no autoliker, while at the same time providing a platform for every one (Agency, Contestants and Voters) to make money.', 'Contests and Voting', 'Stage online contests, Pageants, Elections, Popularity Contests and perfectly anything that requires people to vote, we have everything required to fulfill you contests requirements.', 'Make Money', 'Using Passcontest you can make money, from selling Gift Cards and Contest cards. Our Bounty Commission program allows you to refer users and earn when they earn and when they spend.', 'Meetup', 'Find an Agency, Model or Voter, get contact and address information, chat and exchange information with other users, our aim is to build a network of fans, Hobbyists and people who run contests as business, future updates will make this possible.', 'What we have', 'We have everything required to fulfill you contests requirements, Stage online contests, Pageants, Elections, Popularity Contests and perfectly anything that requires people to vote.', 'profile_city.jpg', 'favicon.png', 'logo.png', 'slide-1.jpg', 'slide-2.jpg', 'slide-3.jpg');

-- Create the api TABLE
CREATE TABLE IF NOT EXISTS `api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `token` int(11) NOT NULL,
  `server` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create the countries TABLE
CREATE TABLE IF NOT EXISTS `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sortname` varchar(3) NOT NULL,
  `name` varchar(150) NOT NULL,
  `phonecode` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Create the states TABLE
CREATE TABLE IF NOT EXISTS `states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `country_id` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
