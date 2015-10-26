

USE `fitbitsync`;

/*Table structure for table `fitbit_tokens` */

DROP TABLE IF EXISTS `fitbit_tokens`;

CREATE TABLE `fitbit_tokens` (
  `encoded_id` varchar(16) NOT NULL,
  `token` varchar(32) DEFAULT NULL,
  `secret` varchar(32) DEFAULT NULL,
  `bbuser` varchar(64) DEFAULT NULL,
  `bbpass` varchar(64) DEFAULT NULL,
  `bbtoken` varchar(32) DEFAULT NULL,
  `bbuser_id` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`encoded_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
