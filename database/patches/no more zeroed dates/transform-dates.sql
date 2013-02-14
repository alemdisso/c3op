UPDATE projects_actions_dates SET predicted_begin_date = NULL WHERE predicted_begin_date = '0000-00-00';
UPDATE projects_actions_dates SET predicted_finish_date = NULL WHERE predicted_finish_date = '0000-00-00';
UPDATE projects_actions_dates SET real_begin_date = NULL WHERE real_begin_date = '0000-00-00';
UPDATE projects_actions_dates SET real_finish_date = NULL WHERE real_finish_date = '0000-00-00';