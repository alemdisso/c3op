ALTER TABLE  `projects_team_members` CHANGE  `value`  `value` DECIMAL( 10, 2 ) UNSIGNED NOT NULL;

ALTER TABLE  `projects_outlays` CHANGE  `predicted_value`  `predicted_value` DECIMAL( 10, 2 ) NULL DEFAULT NULL;

ALTER TABLE  `projects_projects` CHANGE  `value`  `value` DECIMAL( 10, 2 ) NULL DEFAULT NULL ,
CHANGE  `overhead`  `overhead` DECIMAL( 10, 2 ) NULL DEFAULT NULL ,
CHANGE  `management_fee`  `management_fee` DECIMAL( 10, 2 ) NULL DEFAULT NULL;

ALTER TABLE  `projects_receivables` CHANGE  `predicted_value`  `predicted_value` DECIMAL( 10, 2 ) NULL DEFAULT NULL ,
CHANGE  `real_value`  `real_value` DECIMAL( 10, 2 ) NULL DEFAULT NULL;

