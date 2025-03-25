-- Update register endpoint with columns
UPDATE tfsysur_apis 
SET info = JSON_SET(
    info, 
    '$.tableColumns', JSON_ARRAY('id', 'username', 'email', 'password_hash', 'display_name', 'registration_date', 'last_login', 'status'),
    '$.write_columns', JSON_ARRAY('username', 'email', 'password_hash', 'display_name', 'registration_date', 'status')
)
WHERE id = 4;

-- Update login endpoint with columns
UPDATE tfsysur_apis 
SET info = JSON_SET(
    info, 
    '$.tableColumns', JSON_ARRAY('id', 'username', 'email', 'password_hash', 'display_name', 'registration_date', 'last_login', 'status'),
    '$.read_columns', JSON_ARRAY('id', 'username', 'email', 'password_hash', 'display_name', 'status')
)
WHERE id = 5;

-- Update validate endpoint with columns
UPDATE tfsysur_apis 
SET info = JSON_SET(
    info, 
    '$.tableColumns', JSON_ARRAY('id', 'player_id', 'session_token', 'created_at', 'expires_at', 'last_activity'),
    '$.read_columns', JSON_ARRAY('id', 'player_id', 'session_token', 'expires_at')
)
WHERE id = 6;

-- Update profile endpoint with columns
UPDATE tfsysur_apis 
SET info = JSON_SET(
    info, 
    '$.tableColumns', JSON_ARRAY('id', 'username', 'email', 'password_hash', 'display_name', 'registration_date', 'last_login', 'status'),
    '$.read_columns', JSON_ARRAY('id', 'username', 'email', 'display_name', 'registration_date', 'last_login', 'status')
)
WHERE id = 8;

-- Update update endpoint with columns
UPDATE tfsysur_apis 
SET info = JSON_SET(
    info, 
    '$.tableColumns', JSON_ARRAY('id', 'username', 'email', 'password_hash', 'display_name', 'registration_date', 'last_login', 'status'),
    '$.update_columns', JSON_ARRAY('display_name')
)
WHERE id = 9;

-- Update sessiontest endpoint with columns
UPDATE tfsysur_apis 
SET info = JSON_SET(
    info, 
    '$.tableColumns', JSON_ARRAY('id', 'player_id', 'session_token', 'created_at', 'expires_at', 'last_activity'),
    '$.read_columns', JSON_ARRAY('id', 'player_id', 'session_token', 'created_at', 'expires_at', 'last_activity')
)
WHERE id = 10; 