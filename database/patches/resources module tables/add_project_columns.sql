ALTER TABLE  `resources_material_supplies` ADD  `project` INT UNSIGNED NOT NULL AFTER  `id` ,
ADD INDEX (  `project` );
ALTER TABLE  `resources_team_members` ADD  `project` INT UNSIGNED NOT NULL AFTER  `id` ,
ADD INDEX (  `project` );
ALTER TABLE  `resources_outside_services` ADD  `project` INT UNSIGNED NOT NULL AFTER  `id` ,
ADD INDEX (  `project` );

UPDATE resources_material_supplies m
INNER JOIN projects_actions a ON m.action = a.id
 SET m.project = a.project;

UPDATE resources_team_members t
INNER JOIN projects_actions a ON t.action = a.id
 SET t.project = a.project;

UPDATE resources_outside_services o
INNER JOIN projects_actions a ON o.action = a.id
 SET o.project = a.project;