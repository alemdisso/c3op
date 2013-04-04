
ALTER TABLE  `finances_outlays` CHANGE  `team_member`  `responsible` INT( 10 ) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE  `finances_outlays` ADD INDEX  `responsible` (  `responsible` );

CREATE TABLE IF NOT EXISTS `resources_responsibles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(10) unsigned NOT NULL,
  `action` int(10) unsigned NOT NULL,
  `type` int(10) unsigned NOT NULL,
  `institution` int(10) unsigned NOT NULL,
  `contact` int(10) unsigned NOT NULL,
  `value` decimal(10,2) unsigned NOT NULL,
  `status` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project` (`project`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
