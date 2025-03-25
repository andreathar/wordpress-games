-- Fix register endpoint
UPDATE tfsysur_apis 
SET data2 = '{"canRead":false,"canWrite":true,"canUpdate":false,"canDelete":false}'
WHERE id = 4;

-- Fix login endpoint
UPDATE tfsysur_apis 
SET data2 = '{"canRead":true,"canWrite":false,"canUpdate":false,"canDelete":false}'
WHERE id = 5;

-- Fix validate endpoint
UPDATE tfsysur_apis 
SET data2 = '{"canRead":true,"canWrite":false,"canUpdate":false,"canDelete":false}'
WHERE id = 6;

-- Fix logout endpoint
UPDATE tfsysur_apis 
SET data2 = '{"canRead":false,"canWrite":false,"canUpdate":false,"canDelete":true}'
WHERE id = 7;

-- Fix profile endpoint
UPDATE tfsysur_apis 
SET data2 = '{"canRead":true,"canWrite":false,"canUpdate":false,"canDelete":false}'
WHERE id = 8;

-- Fix update endpoint
UPDATE tfsysur_apis 
SET data2 = '{"canRead":false,"canWrite":false,"canUpdate":true,"canDelete":false}'
WHERE id = 9;

-- Fix sessiontest endpoint
UPDATE tfsysur_apis 
SET data2 = '{"canRead":true,"canWrite":false,"canUpdate":false,"canDelete":false}'
WHERE id = 10; 