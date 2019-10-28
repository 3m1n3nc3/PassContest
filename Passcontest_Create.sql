SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(24) NOT NULL,
  `password` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1; 

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, '<USER_NAME>', '<PASSWORD>');
 
CREATE TABLE IF NOT EXISTS `application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `gender` varchar(128) NULL DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `zip` int(11) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `address1` text,
  `address2` text,
  `dob` varchar(255) DEFAULT NULL,
  `pob` varchar(255) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `swim` int(11) DEFAULT NULL,
  `dress` int(11) DEFAULT NULL,
  `shoe` int(11) DEFAULT NULL,
  `work` varchar(255) DEFAULT NULL,
  `certificate` text,
  `hobbies` varchar(255) DEFAULT NULL,
  `activities` text,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `food` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `sport` text,
  `ambition` text,
  `performing` text,
  `awards` text,
  `training` text,
  `family` text,
  `languages` text,
  `liketomeet` text,
  `unusual` text,
  `moment` text,
  `traveled` text,
  `statement` text,
  `headshot` varchar(255) DEFAULT NULL,
  `fullbody` varchar(255) DEFAULT NULL,
  `agree` enum('0','1') NOT NULL DEFAULT '0',
  `agree2` enum('0','1') NOT NULL DEFAULT '0',
  `agree3` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1; 

CREATE TABLE IF NOT EXISTS `bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `paypal` varchar(128) NOT NULL,
  `bank_name` varchar(128) NOT NULL,
  `bank_address` varchar(128) NOT NULL,
  `sort_code` varchar(128) NOT NULL,
  `account_name` varchar(128) NOT NULL,
  `account_number` varchar(128) NOT NULL,
  `aba` varchar(128) NOT NULL,
  `cashout` decimal(6,2) NOT NULL,
  `approved` enum('0','1','2') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `blocked_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`id`,`user_id`),
  KEY `by` (`id`,`by`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(255) DEFAULT NULL,
  `requirements` text,
  `description` text,
  `contest` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contest` (`contest`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `state_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `support` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reply` int(11) NOT NULL,
  `subject` varchar(128) NOT NULL,
  `message` text NOT NULL,
  `priority` enum('0','1','2') NOT NULL,
  `type` varchar(128) NOT NULL,
  `solved` enum('0','1') NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `fname` varchar(255) DEFAULT NULL,
  `lname` varchar(255) DEFAULT NULL,
  `gender` varchar(128) NULL DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `cover` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `intro` text,
  `profession` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `lovesto` varchar(255) DEFAULT NULL,
  `creator` int(11) NOT NULL DEFAULT '0',
  `token` varchar(255) NOT NULL,
  `status` enum('0','1','2') NOT NULL DEFAULT '0',
  `role` enum('contestant','agency','voter') NOT NULL DEFAULT 'voter',
  `claimed` enum('0','1') NOT NULL DEFAULT '0',
  `featured` enum('0','1') NOT NULL DEFAULT '0',
  `new_notification` enum('0','1') NOT NULL DEFAULT '0',
  `site_notifications` enum('0','1') NOT NULL DEFAULT '1',
  `email_notifications` enum('0','1') NOT NULL DEFAULT '1',
  `online` int(11) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `voters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voter_id` int(11) DEFAULT NULL,
  `contest_id` int(11) DEFAULT NULL,
  `contestant_id` int(11) DEFAULT NULL,
  `voted` enum('0','1') NOT NULL DEFAULT '0',
  `count` int(11) NOT NULL,
  `social` enum('0','1') NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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

CREATE TABLE IF NOT EXISTS `api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `token` int(11) NOT NULL,
  `server` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1; 
COMMIT;
