ALTER TABLE  `projects_projects` ADD  `short_title` VARCHAR( 32 ) NOT NULL AFTER  `title`;
UPDATE  `projects_projects` SET  `short_title` =  `title` WHERE 1;