ALTER TABLE  `projects_actions_dates` ADD  `baseline_begin_date` DATE NULL DEFAULT NULL AFTER  `action` ,
ADD  `baseline_finish_date` DATE NULL DEFAULT NULL AFTER  `baseline_begin_date`;

