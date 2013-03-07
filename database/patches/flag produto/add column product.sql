ALTER TABLE  `projects_actions` ADD  `product` TINYINT( 1 ) UNSIGNED NULL AFTER  `subordinated_to`;
UPDATE `projects_actions` SET product = 1 WHERE subordinated_to = 0;
UPDATE `projects_actions` SET product = 0 WHERE subordinated_to > 0;