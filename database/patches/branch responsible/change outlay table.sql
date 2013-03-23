
ALTER TABLE  `finances_outlays` CHANGE  `team_member`  `responsible` INT( 10 ) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE  `testing_c3op`.`finances_outlays` ADD INDEX  `responsible` (  `responsible` );
