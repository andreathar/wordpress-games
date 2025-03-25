-- Create the players table
CREATE TABLE IF NOT EXISTS tfur_players (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(100),
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    PRIMARY KEY (id),
    UNIQUE KEY (username),
    UNIQUE KEY (email)
);

-- Create the player sessions table
CREATE TABLE IF NOT EXISTS tfur_player_sessions (
    id INT(11) NOT NULL AUTO_INCREMENT,
    player_id INT(11) NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    last_activity DATETIME,
    PRIMARY KEY (id),
    FOREIGN KEY (player_id) REFERENCES tfur_players(id) ON DELETE CASCADE
);

-- Insert the API group
INSERT INTO tfsysur_apis (type, group_id, name, info, data2, data3, data4) 
VALUES ('GRP', NULL, 'players', NULL, NULL, NULL, NULL);

-- Get the API group ID
SET @api_group_id = LAST_INSERT_ID();

-- Insert the API endpoints
INSERT INTO tfsysur_apis (type, group_id, name, info, data2, data3, data4) 
VALUES 
('SQL', @api_group_id, 'register', '{"id":1,"name":"register","type":"SQL","tableName":"players"}', 
'{"canRead":false,"canWrite":true,"canUpdate":false,"canDelete":false}', NULL, NULL),

('SQL', @api_group_id, 'login', '{"id":2,"name":"login","type":"SQL","tableName":"players"}', 
'{"canRead":true,"canWrite":false,"canUpdate":false,"canDelete":false}', NULL, NULL),

('SQL', @api_group_id, 'validate', '{"id":3,"name":"validate","type":"SQL","tableName":"player_sessions"}', 
'{"canRead":true,"canWrite":false,"canUpdate":false,"canDelete":false}', NULL, NULL),

('SQL', @api_group_id, 'logout', '{"id":4,"name":"logout","type":"SQL","tableName":"player_sessions"}', 
'{"canRead":false,"canWrite":false,"canUpdate":false,"canDelete":true}', NULL, NULL),

('SQL', @api_group_id, 'profile', '{"id":5,"name":"profile","type":"SQL","tableName":"players"}', 
'{"canRead":true,"canWrite":false,"canUpdate":false,"canDelete":false}', NULL, NULL),

('SQL', @api_group_id, 'update', '{"id":6,"name":"update","type":"SQL","tableName":"players"}', 
'{"canRead":false,"canWrite":false,"canUpdate":true,"canDelete":false}', NULL, NULL); 