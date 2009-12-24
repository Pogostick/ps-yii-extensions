/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * DDL for example blog application
 * 
 * @package 	psYiiExtensions.examples
 * @subpackage 	blog
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 */
/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`pye_blog` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `pye_blog`;

/*Table structure for table `comment_t` */

DROP TABLE IF EXISTS `comment_t`;

CREATE TABLE `comment_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `content_text` text NOT NULL,
  `content_display_text` text,
  `status_nbr` int(11) NOT NULL,
  `author_name_text` varchar(255) NOT NULL,
  `email_addr_text` varchar(255) NOT NULL,
  `url_text` varchar(255) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_comment_post` (`post_id`),
  CONSTRAINT `fk_comment_post` FOREIGN KEY (`post_id`) REFERENCES `post_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `comment_t` */

/*Table structure for table `post_t` */

DROP TABLE IF EXISTS `post_t`;

CREATE TABLE `post_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `title_text` varchar(128) NOT NULL,
  `content_text` text NOT NULL,
  `content_display_text` text,
  `tags_text` text,
  `status_nbr` int(11) NOT NULL,
  `comment_count_nbr` int(11) DEFAULT '0',
  `create_date` datetime DEFAULT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_post_author` (`author_id`),
  CONSTRAINT `fk_post_author` FOREIGN KEY (`author_id`) REFERENCES `user_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `post_t` */

insert  into `post_t`(`id`,`author_id`,`title_text`,`content_text`,`content_display_text`,`tags_text`,`status_nbr`,`comment_count_nbr`,`create_date`,`lmod_date`) values (1,1,'Welcome to Yii Blog (featuring Pogostick Yii Extensions!','This blog system is developed using Yii and the Pogostick Yii Extension library. It is meant to demonstrate how to use Yii to build a complete real-world application. Complete source code may be found in the Yii releases.\nFeel free to try this system by writing new posts and posting comments.','<p>This blog system is developed using Yii and the <a href=\"http://code.googlecode.com/p/ps-yii-extensions/\">Pogostick Yii Extension</a> library. It is meant to demonstrate how to use Yii to build a complete real-world application. Complete source code may be found in the Yii releases.</p>\n<p>Feel free to try this system by writing new posts and posting comments.</p>','yii, blog',1,0,'2009-12-23 21:59:45','2009-12-23 21:59:45');

/*Table structure for table `post_tag_asgn_t` */

DROP TABLE IF EXISTS `post_tag_asgn_t`;

CREATE TABLE `post_tag_asgn_t` (
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`,`tag_id`),
  KEY `fk_pta_tag_id` (`tag_id`),
  CONSTRAINT `fk_pta_post_id` FOREIGN KEY (`post_id`) REFERENCES `post_t` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pta_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tag_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `post_tag_asgn_t` */

insert  into `post_tag_asgn_t`(`post_id`,`tag_id`,`create_date`) values (1,1,'2009-12-23 22:01:07');
insert  into `post_tag_asgn_t`(`post_id`,`tag_id`,`create_date`) values (1,2,'2009-12-23 22:01:07');

/*Table structure for table `tag_t` */

DROP TABLE IF EXISTS `tag_t`;

CREATE TABLE `tag_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_name_text` varchar(255) NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `tag_t` */

insert  into `tag_t`(`id`,`tag_name_text`,`create_date`,`lmod_date`) values (1,'yii','2009-12-23 22:04:43','2009-12-23 22:01:07');
insert  into `tag_t`(`id`,`tag_name_text`,`create_date`,`lmod_date`) values (2,'blog','2009-12-23 22:04:44','2009-12-23 22:01:07');

/*Table structure for table `user_t` */

DROP TABLE IF EXISTS `user_t`;

CREATE TABLE `user_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name_text` varchar(30) NOT NULL,
  `password_text` varchar(30) NOT NULL,
  `email_addr_text` varchar(255) NOT NULL,
  `profile_text` text,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `user_t` */
/* user demo, pass demo */
insert  into `user_t`(`id`,`user_name_text`,`password_text`,`email_addr_text`,`profile_text`,`create_date`,`lmod_date`) values (1,'demo','fe01ce2a7fbac8fafaed7c982a04e229','webmaster@example.com',NULL,'2009-12-23 22:05:30','2009-12-23 22:05:30');

/* Create a database user for Yii */
grant all privileges on psYiiExtensions.* to 'pye_user'@'localhost' identified by 'pye_user';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
