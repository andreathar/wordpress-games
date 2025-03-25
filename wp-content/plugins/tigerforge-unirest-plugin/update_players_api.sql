-- Update the API endpoints to have the correct type and info
UPDATE tfsysur_apis 
SET type = 'SQL', 
    info = '{"id":4,"name":"register","type":"SQL","tableName":"tfur_players"}', 
    data2 = '{"canRead":false,"canWrite":true,"canUpdate":false,"canDelete":false}'
WHERE name = 'register' AND group_id = (SELECT id FROM (SELECT id FROM tfsysur_apis WHERE name = 'players' AND type = 'GRP') AS temp);

UPDATE tfsysur_apis 
SET type = 'SQL', 
    info = '{"id":5,"name":"login","type":"SQL","tableName":"tfur_players"}', 
    data2 = '{"canRead":true,"canWrite":false,"canUpdate":false,"canDelete":false}'
WHERE name = 'login' AND group_id = (SELECT id FROM (SELECT id FROM tfsysur_apis WHERE name = 'players' AND type = 'GRP') AS temp);

UPDATE tfsysur_apis 
SET type = 'SQL', 
    info = '{"id":6,"name":"validate","type":"SQL","tableName":"tfur_player_sessions"}', 
    data2 = '{"canRead":true,"canWrite":false,"canUpdate":false,"canDelete":false}'
WHERE name = 'validate' AND group_id = (SELECT id FROM (SELECT id FROM tfsysur_apis WHERE name = 'players' AND type = 'GRP') AS temp);

UPDATE tfsysur_apis 
SET type = 'SQL', 
    info = '{"id":7,"name":"logout","type":"SQL","tableName":"tfur_player_sessions"}', 
    data2 = '{"canRead":false,"canWrite":false,"canUpdate":false,"canDelete":true}'
WHERE name = 'logout' AND group_id = (SELECT id FROM (SELECT id FROM tfsysur_apis WHERE name = 'players' AND type = 'GRP') AS temp);

UPDATE tfsysur_apis 
SET type = 'SQL', 
    info = '{"id":8,"name":"profile","type":"SQL","tableName":"tfur_players"}', 
    data2 = '{"canRead":true,"canWrite":false,"canUpdate":false,"canDelete":false}'
WHERE name = 'profile' AND group_id = (SELECT id FROM (SELECT id FROM tfsysur_apis WHERE name = 'players' AND type = 'GRP') AS temp);

UPDATE tfsysur_apis 
SET type = 'SQL', 
    info = '{"id":9,"name":"update","type":"SQL","tableName":"tfur_players"}', 
    data2 = '{"canRead":false,"canWrite":false,"canUpdate":true,"canDelete":false}'
WHERE name = 'update' AND group_id = (SELECT id FROM (SELECT id FROM tfsysur_apis WHERE name = 'players' AND type = 'GRP') AS temp); 