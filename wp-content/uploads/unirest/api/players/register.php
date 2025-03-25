<?php
// Get the registration data
$data = $UniREST->data;

// Validate inputs
if (empty($data->username) || empty($data->email) || empty($data->password)) {
    $UniREST->sendError("Missing required fields");
    return;
}

$username = $data->username;
$email = $data->email;
$password = $data->password;
$display_name = isset($data->display_name) ? $data->display_name : $username;

// Check if username or email already exists
global $wpdb;
$table_name = $wpdb->prefix . "tfur_players";
$existing_user = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM $table_name WHERE username = %s OR email = %s",
        $username,
        $email
    )
);

if ($existing_user) {
    $UniREST->sendError("Username or email already exists");
    return;
}

// Hash the password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert the new player
$result = $wpdb->insert(
    $table_name,
    [
        'username' => $username,
        'email' => $email,
        'password_hash' => $password_hash,
        'display_name' => $display_name,
        'registration_date' => current_time('mysql'),
        'status' => 'active'
    ]
);

if ($result) {
    $player_id = $wpdb->insert_id;
    $UniREST->sendReply(
        [
            'id' => $player_id,
            'username' => $username,
            'display_name' => $display_name
        ],
        "Registration successful"
    );
} else {
    $UniREST->sendError("Registration failed: " . $wpdb->last_error);
}
?> 