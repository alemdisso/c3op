ALTER TABLE  `resources_responsibles` ADD  `predicted_value` DECIMAL( 10, 2 ) UNSIGNED NULL AFTER  `contact`;

ALTER TABLE  `resources_responsibles` CHANGE  `value`  `contracted_value` DECIMAL( 10, 2 ) UNSIGNED NULL;

UPDATE `resources_responsibles` SET predicted_value = contracted_value WHERE 1;

UPDATE `resources_responsibles` SET contracted_value = null WHERE status < 300;