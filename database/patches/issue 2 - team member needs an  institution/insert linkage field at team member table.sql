ALTER TABLE  `projects_team_members` ADD  `linkage` INT( 10 ) UNSIGNED NOT NULL AFTER  `contact`;

ALTER TABLE `projects_team_members`
  DROP `contact`,
  DROP `institution`;
