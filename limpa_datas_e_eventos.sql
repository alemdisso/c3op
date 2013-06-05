TRUNCATE `projects_actions_dates`;
TRUNCATE `projects_actions_events`;
UPDATE `projects_actions` SET status = 100 WHERE status > 100;