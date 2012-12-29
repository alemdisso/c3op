ALTER TABLE  `projects_outlays` ADD  `real_value` DECIMAL( 10.2 ) NULL AFTER  `predicted_date` ,
ADD  `real_date` DATE NULL AFTER  `real_value`