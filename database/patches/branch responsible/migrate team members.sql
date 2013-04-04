INSERT INTO resources_responsibles (project, action, value, status, institution, contact, type)
SELECT t.project, t.action, t.value, t.status, l.institution, l.contact, 100
FROM `resources_team_members` t
INNER JOIN `register_linkages` l ON t.linkage = l.id
WHERE 1;

UPDATE resources_responsibles SET type = 100 WHERE 1;