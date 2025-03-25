<?php
// Get the token
$data = $UniREST->data;

if (empty($data->token)) {
    $UniREST->sendError("No token provided");
    return;
}

$token = $data->token;

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

// Get the player
$players_table = $wpdb->prefix . "tfur_players";
$player = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM $players_table WHERE id = %d",
        $session['player_id']
    ),
    ARRAY_A
);

if (!$player) {
    $UniREST->sendError("Player not found");
    return;
}

// Remove sensitive data
unset($player['password_hash']);

// Return player data
$UniREST->sendReply($player, "Profile retrieved");
?> 