<?php
// Get the token
$data = $UniREST->data;

if (empty($data->player_id)) {
    $UniREST->sendError("No player ID provided");
    return;
}

$player_id = $data->player_id;

// Find the sessions
global $wpdb;
$sessions_table = $wpdb->prefix . "tfur_player_sessions";
$sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table WHERE player_id = %d",
        $player_id
    ),
    ARRAY_A
);

if (!$sessions) {
    $UniREST->sendError("No sessions found for this player");
    return;
}

// Return sessions data
$UniREST->sendReply($sessions, "Sessions retrieved");
?> 