<?php
// Get the login data
$data = $UniREST->data;

// Validate inputs
if (empty($data->login) || empty($data->password)) {
    $UniREST->sendError("Missing login credentials");
    return;
}

$login = $data->login; // Can be username or email
$password = $data->password;

// Find the user
global $wpdb;
$table_name = $wpdb->prefix . "tfur_players";
$player = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM $table_name WHERE username = %s OR email = %s",
        $login,
        $login
    ),
    ARRAY_A
);

if (!$player) {
    $UniREST->sendError("User not found");
    return;
}

// Verify password
if (!password_verify($password, $player['password_hash'])) {
    $UniREST->sendError("Invalid password");
    return;
}

// Generate session token
$session_token = bin2hex(random_bytes(32));
$expires_at = date("Y-m-d H:i:s", strtotime("+7 days"));

// Store session
$sessions_table = $wpdb->prefix . "tfur_player_sessions";
$wpdb->insert(
    $sessions_table,
    [
        'player_id' => $player['id'],
        'session_token' => $session_token,
        'expires_at' => $expires_at,
        'last_activity' => current_time('mysql')
    ]
);

// Update last login
$wpdb->update(
    $table_name,
    ['last_login' => current_time('mysql')],
    ['id' => $player['id']]
);

// Return player data and token
$UniREST->sendReply(
    [
        'id' => $player['id'],
        'username' => $player['username'],
        'email' => $player['email'],
        'display_name' => $player['display_name'],
        'session_token' => $session_token
    ],
    "Login successful"
);
?> 