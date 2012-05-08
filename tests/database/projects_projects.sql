CREATE TABLE IF NOT EXISTS `projects_projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `client` int(10) unsigned DEFAULT NULL,
  `our_responsible` int(10) unsigned DEFAULT NULL,
  `responsible_at_client` int(10) unsigned DEFAULT NULL,
  `date_begin` date DEFAULT NULL,
  `date_finish` date DEFAULT NULL,
  `value` float DEFAULT NULL,
  `nature_of_contract` int(10) unsigned DEFAULT NULL,
  `area_activity` int(10) unsigned DEFAULT NULL,
  `overhead` float DEFAULT NULL,
  `management_fee` float DEFAULT NULL,
  `object` text COLLATE latin1_general_ci,
  `summary` text COLLATE latin1_general_ci,
  `observation` text COLLATE latin1_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=498 ;

