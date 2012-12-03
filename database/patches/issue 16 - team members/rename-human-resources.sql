RENAME TABLE  `projects_human_resources` TO  `projects_team_members` ;

ALTER TABLE  `projects_outlays` CHANGE  `human_resource`  `team_member` INT( 10 ) UNSIGNED NULL DEFAULT NULL;
