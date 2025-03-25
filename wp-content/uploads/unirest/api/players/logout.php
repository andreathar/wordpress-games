<?php
// Get the token
$data = $UniREST->data;

if (empty($data->token)) {
    $UniREST->sendError("No token provided");
    return;
}

$token = $data->token;

// Delete the session
global $wpdb;
$sessions_table = $wpdb->prefix . "tfur_player_sessions";
$wpdb->delete(
    $sessions_table,
    ['session_token' => $token]
);

$UniREST->sendReply([], "Logged out successfully");
?> 