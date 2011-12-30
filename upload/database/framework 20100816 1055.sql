-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	4.1.16-nt


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema framework
--

CREATE DATABASE IF NOT EXISTS framework;
USE framework;

--
-- Definition of table `gui`
--

DROP TABLE IF EXISTS `gui`;
CREATE TABLE `gui` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `domain_id` int(10) unsigned NOT NULL default '0',
  `layout_id` int(10) unsigned NOT NULL default '0',
  `page_id` int(10) unsigned NOT NULL default '0',
  `region_id` int(10) unsigned NOT NULL default '0',
  `element_id` int(10) unsigned NOT NULL default '0',
  `rank` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;

--
-- Dumping data for table `gui`
--

/*!40000 ALTER TABLE `gui` DISABLE KEYS */;
INSERT INTO `gui` (`id`,`domain_id`,`layout_id`,`page_id`,`region_id`,`element_id`,`rank`) VALUES 
 (1,0,0,1,3,1,1);
/*!40000 ALTER TABLE `gui` ENABLE KEYS */;


--
-- Definition of table `gui_configuration`
--

DROP TABLE IF EXISTS `gui_configuration`;
CREATE TABLE `gui_configuration` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `domain_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(45) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `evaluate` tinyint(1) NOT NULL default '0',
  `rank` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gui_configuration`
--

/*!40000 ALTER TABLE `gui_configuration` DISABLE KEYS */;
INSERT INTO `gui_configuration` (`id`,`domain_id`,`group_id`,`name`,`value`,`evaluate`,`rank`) VALUES 
 (1,0,1,'','putenv(\'GDFONTPATH=\' . realpath(ROOT . \'/assets/fonts/\'));',1,1),
 (2,0,2,'assets','/assets/',0,1),
 (3,0,3,'version','2.1.0',0,1);
/*!40000 ALTER TABLE `gui_configuration` ENABLE KEYS */;


--
-- Definition of table `gui_configuration_groups`
--

DROP TABLE IF EXISTS `gui_configuration_groups`;
CREATE TABLE `gui_configuration_groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(45) NOT NULL default '',
  `description` varchar(45) NOT NULL default '',
  `rank` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gui_configuration_groups`
--

/*!40000 ALTER TABLE `gui_configuration_groups` DISABLE KEYS */;
INSERT INTO `gui_configuration_groups` (`id`,`name`,`description`,`rank`) VALUES 
 (1,'','Commands',1),
 (2,'paths','File/Folder Paths',2),
 (3,'app','Application',3),
 (4,'ssn','Session',4);
/*!40000 ALTER TABLE `gui_configuration_groups` ENABLE KEYS */;


--
-- Definition of table `gui_domains`
--

DROP TABLE IF EXISTS `gui_domains`;
CREATE TABLE `gui_domains` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(45) NOT NULL default '',
  `title` varchar(45) NOT NULL default '',
  `regexp` varchar(45) NOT NULL default '',
  `http` varchar(45) NOT NULL default '',
  `https` varchar(45) NOT NULL default '',
  `root` varchar(45) NOT NULL default '',
  `def` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;

--
-- Dumping data for table `gui_domains`
--

/*!40000 ALTER TABLE `gui_domains` DISABLE KEYS */;
INSERT INTO `gui_domains` (`id`,`name`,`title`,`regexp`,`http`,`https`,`root`,`def`) VALUES 
 (1,'localhost','Local Machine','localhost','http://localhost','https://localhost','/',1);
/*!40000 ALTER TABLE `gui_domains` ENABLE KEYS */;


--
-- Definition of table `gui_elements`
--

DROP TABLE IF EXISTS `gui_elements`;
CREATE TABLE `gui_elements` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `alias` varchar(45) NOT NULL default '',
  `uses` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;

--
-- Dumping data for table `gui_elements`
--

/*!40000 ALTER TABLE `gui_elements` DISABLE KEYS */;
INSERT INTO `gui_elements` (`id`,`name`,`alias`,`uses`) VALUES 
 (1,'example.htm','','');
/*!40000 ALTER TABLE `gui_elements` ENABLE KEYS */;


--
-- Definition of table `gui_includes`
--

DROP TABLE IF EXISTS `gui_includes`;
CREATE TABLE `gui_includes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(45) NOT NULL default '',
  `alias` varchar(45) NOT NULL default '',
  `uses` varchar(255) NOT NULL default '',
  `rank` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;

--
-- Dumping data for table `gui_includes`
--

/*!40000 ALTER TABLE `gui_includes` DISABLE KEYS */;
INSERT INTO `gui_includes` (`id`,`name`,`alias`,`uses`,`rank`) VALUES 
 (1,'global','','',1),
 (2,'common','','',2),
 (3,'mail','class.mail','',3),
 (4,'structure','class.structure','',4),
 (5,'image','class.image','',5),
 (6,'xml','class.xml','',6),
 (7,'javascript/core','javascript.core','',1),
 (8,'javascript/ajax','javascript.ajax','javascript.core',2),
 (9,'javascript/canvas','javascript.canvas','javascript.core',2),
 (10,'javascript/debug','javascript.debug','javascript.core',2),
 (11,'javascript/elements','javascript.elements','javascript.core',2),
 (12,'javascript/events','javascript.events','javascript.core',2),
 (13,'javascript/structure','javascript.structure','javascript.core',2),
 (14,'javascript/styles','javascript.styles','javascript.core',2);
/*!40000 ALTER TABLE `gui_includes` ENABLE KEYS */;


--
-- Definition of table `gui_layout_regions`
--

DROP TABLE IF EXISTS `gui_layout_regions`;
CREATE TABLE `gui_layout_regions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned NOT NULL default '0',
  `layout_id` int(10) unsigned NOT NULL default '0',
  `region_id` int(10) unsigned NOT NULL default '0',
  `class` varchar(255) NOT NULL default '',
  `rank` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gui_layout_regions`
--

/*!40000 ALTER TABLE `gui_layout_regions` DISABLE KEYS */;
INSERT INTO `gui_layout_regions` (`id`,`parent_id`,`layout_id`,`region_id`,`class`,`rank`) VALUES 
 (1,0,2,0,'GUILayout-Body',0),
 (2,1,2,0,'GUILayout-ContentWrapper',0),
 (3,2,2,0,'GUILayout-Content',0),
 (4,3,2,2,'GUILayout-ContentLeft',1),
 (5,3,2,3,'GUILayout-ContentRight',2),
 (6,0,3,0,'GUILayout-Body',0),
 (7,6,3,0,'GUILayout-HeaderWrapper',1),
 (8,7,3,1,'GUILayout-Header',0),
 (9,6,3,0,'GUILayout-ContentWrapper',2),
 (10,9,3,3,'GUILayout-Content',0),
 (11,6,3,0,'GUILayout-FooterWrapper',3),
 (12,11,3,5,'GUILayout-Footer',0),
 (13,0,4,0,'GUILayout-Body',0),
 (14,13,4,0,'GUILayout-HeaderWrapper',1),
 (15,14,4,1,'GUILayout-Header',0),
 (16,13,4,0,'GUILayout-ContentWrapper',2),
 (17,16,4,0,'GUILayout-Content',0),
 (18,17,4,2,'GUILayout-ContentLeft',1),
 (19,17,4,3,'GUILayout-ContentRight',2),
 (20,13,4,0,'GUILayout-FooterWrapper',3),
 (21,20,4,5,'GUILayout-Footer',0),
 (22,0,5,0,'GUILayout-Body',0),
 (23,22,5,0,'GUILayout-HeaderWrapper',1),
 (24,23,5,1,'GUILayout-Header',0),
 (25,22,5,0,'GUILayout-ContentWrapper',2),
 (26,25,5,0,'GUILayout-Content',0),
 (27,26,5,2,'GUILayout-ContentLeft',1),
 (28,26,5,3,'GUILayout-ContentCenter',2),
 (29,26,5,4,'GUILayout-ContentRight',3),
 (30,22,5,0,'GUILayout-FooterWrapper',3),
 (31,30,5,5,'GUILayout-Footer',0);
/*!40000 ALTER TABLE `gui_layout_regions` ENABLE KEYS */;


--
-- Definition of table `gui_layouts`
--

DROP TABLE IF EXISTS `gui_layouts`;
CREATE TABLE `gui_layouts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `layout_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `framework` varchar(16) NOT NULL default '',
  `layout` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gui_layouts`
--

/*!40000 ALTER TABLE `gui_layouts` DISABLE KEYS */;
INSERT INTO `gui_layouts` (`id`,`layout_id`,`name`,`framework`,`layout`) VALUES 
 (1,0,'none','',''),
 (2,2,'2 region, {2,3}','xhtml','layout2'),
 (3,3,'3 region, {1},{3},{5}','xhtml','layout3'),
 (4,4,'4 region, {1},{2,3},{5}','xhtml','layout4'),
 (5,5,'5 region, {1},{2,3,4},{5}','xhtml','layout5');
/*!40000 ALTER TABLE `gui_layouts` ENABLE KEYS */;


--
-- Definition of table `gui_pages`
--

DROP TABLE IF EXISTS `gui_pages`;
CREATE TABLE `gui_pages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `domain_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(45) NOT NULL default '',
  `regexp` varchar(255) NOT NULL default '',
  `alias` varchar(45) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `layout_id` int(10) unsigned NOT NULL default '0',
  `mobile` tinyint(1) unsigned NOT NULL default '0',
  `def` tinyint(1) unsigned NOT NULL default '0',
  `disabled` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;

--
-- Dumping data for table `gui_pages`
--

/*!40000 ALTER TABLE `gui_pages` DISABLE KEYS */;
INSERT INTO `gui_pages` (`id`,`domain_id`,`name`,`regexp`,`alias`,`title`,`description`,`layout_id`,`mobile`,`def`,`disabled`) VALUES 
 (1,0,'index.php','','home','Home','Default Global Framework Page',2,0,1,0);
/*!40000 ALTER TABLE `gui_pages` ENABLE KEYS */;


--
-- Definition of table `gui_user_agents`
--

DROP TABLE IF EXISTS `gui_user_agents`;
CREATE TABLE `gui_user_agents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `bot` tinyint(1) unsigned NOT NULL default '0',
  `downloader` tinyint(1) unsigned NOT NULL default '0',
  `mobile` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gui_user_agents`
--

/*!40000 ALTER TABLE `gui_user_agents` DISABLE KEYS */;
/*!40000 ALTER TABLE `gui_user_agents` ENABLE KEYS */;


--
-- Definition of table `image_groups`
--

DROP TABLE IF EXISTS `image_groups`;
CREATE TABLE `image_groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(45) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;

--
-- Dumping data for table `image_groups`
--

/*!40000 ALTER TABLE `image_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `image_groups` ENABLE KEYS */;


--
-- Definition of table `images`
--

DROP TABLE IF EXISTS `images`;
CREATE TABLE `images` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned NOT NULL default '0',
  `alias` varchar(45) NOT NULL default '',
  `image` varchar(45) NOT NULL default '',
  `tooltip` varchar(45) NOT NULL default '',
  `width` int(10) unsigned NOT NULL default '0',
  `height` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `images`
--

/*!40000 ALTER TABLE `images` DISABLE KEYS */;
/*!40000 ALTER TABLE `images` ENABLE KEYS */;


--
-- Definition of table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(32) NOT NULL default '',
  `timestamp` int(10) unsigned NOT NULL default '0',
  `data` text NOT NULL,
  `active` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sessions`
--

/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
