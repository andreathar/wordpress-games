-- Update register endpoint with conditions
UPDATE tfsysur_apis 
SET info = JSON_SET(
    info, 
    '$.writeConditions', JSON_ARRAY(
        JSON_OBJECT('column', 'username', 'operator', '='),
        JSON_OBJECT('column', 'email', 'operator', '='),
        JSON_OBJECT('column', 'password', 'operator', '=')
    ),
    '$.write_logical_operator', 'AND'
)
WHERE id = 4;

-- Update login endpoint with conditions
UPDATE tfsysur_apis 
SET info = JSON_SET(
    info, 
    '$.readConditions', JSON_ARRAY(
        JSON_OBJECT('column', 'username', 'operator', '='),
        JSON_OBJECT('column', 'email', 'operator', '=')
    ),
    '$.read_logical_operator', 'OR'
)
WHERE id = 5;

-- Update validate endpoint with conditions
UPDATE tfsysur_apis 
SET info = JSON_SET(
    info, 
    '$.readConditions', JSON_ARRAY(
        JSON_OBJECT('column', 'session_token', 'operator', '=')
    ),
    '$.read_logical_operator', 'AND'
)
WHERE id = 6;

-- Update logout endpoint with conditions
UPDATE tfsysur_apis 
SET info = JSON_SET(
    info, 
    '$.deleteConditions', JSON_ARRAY(
        JSON_OBJECT('column', 'session_token', 'operator', '=')
    ),
    '$.delete_logical_operator', 'AND'
)
WHERE id = 7;

-- Update profile endpoint with conditions
UPDATE tfsysur_apis 
SET info = JSON_SET(
    info, 
    '$.readConditions', JSON_ARRAY(
        JSON_OBJECT('column', 'id', 'operator', '=')
    ),
    '$.read_logical_operator', 'AND'
)
WHERE id = 8;

-- Update update endpoint with conditions
UPDATE tfsysur_apis 
SET info = JSON_SET(
    info, 
    '$.updateConditions', JSON_ARRAY(
        JSON_OBJECT('column', 'id', 'operator', '=')
    ),
    '$.update_logical_operator', 'AND'
)
WHERE id = 9;

-- Update sessiontest endpoint with conditions
UPDATE tfsysur_apis 
SET info = JSON_SET(
    info, 
    '$.readConditions', JSON_ARRAY(
        JSON_OBJECT('column', 'player_id', 'operator', '=')
    ),
    '$.read_logical_operator', 'AND'
)
WHERE id = 10; 