<?php
// Get the data
$data = $UniREST->data;

if (empty($data->token)) {
    $UniREST->sendError("No token provided");
    return;
}

$token = $data->token;
$player_data = $data->player;

// Find the session
global $wpdb;
$sessions_table = $wpdb->prefix . "tfur_player_sessions";
$session = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table WHERE session_token = %s AND expires_at > %s",
        $token,
        current_time('mysql')
    ),
    ARRAY_A
);

if (!$session) {
    $UniREST->sendError("Invalid or expired token");
    return;
}

// Prepare update data
$update_data = [];
if (isset($player_data->display_name)) {
    $update_data['display_name'] = $player_data->display_name;
}

// Add other fields you want to allow updating
// For security, don't allow updating username, email or password here
// Create separate endpoints for those if needed

if (empty($update_data)) {
    $UniREST->sendError("No data to update");
    return;
}

// Update the player
$players_table = $wpdb->prefix . "tfur_players";
$wpdb->update(
    $players_table,
    $update_data,
    ['id' => $session['player_id']]
);

// Get updated player data
$player = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM $players_table WHERE id = %d",
        $session['player_id']
    ),
    ARRAY_A
);

// Remove sensitive data
unset($player['password_hash']);

// Return updated player data
$UniREST->sendReply($player, "Profile updated");
?> 