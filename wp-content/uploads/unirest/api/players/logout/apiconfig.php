<?php

// API Configuration
$APIConfig = array (
  'id' => 7,
  'name' => 'logout',
  'type' => 'SQL',
  'tableName' => 'tfur_player_sessions',
  'description' => 'Logout player',
  'isSQL' => true,
  'isPHP' => true,
  'canRead' => false,
  'canWrite' => false,
  'canUpdate' => false,
  'canDelete' => true,
  'read_custom_query' => '',
  'write_custom_query' => '',
  'update_custom_query' => '',
  'delete_custom_query' => '',
  'php_script' => '',
  'tableColumns' => 
  array (
    0 => 'id',
    1 => 'player_id',
    2 => 'session_token',
    3 => 'created_at',
    4 => 'expires_at',
    5 => 'last_activity',
  ),
  'deleteConditions' => 
  array (
    0 => 
    array (
      'column' => 'session_token',
      'operator' => '=',
    ),
  ),
  'delete_logical_operator' => 'AND',
);

// DB Configuration
$DBCONF = array (
  'DB_NAME' => 'wordpress',
  'DB_USER' => 'wpuser',
  'DB_PASSWORD' => 'wppassword',
  'DB_HOST' => 'localhost',
  'DB_CHARSET' => 'utf8',
  'DB_COLLATE' => '',
  'DB_PREFIX' => 'wp_',
);

function Encrypt($plaintext) {
    if ($plaintext == "") return ""; 
    global $KEYS;
    $key1 = "4njTUyUrn4vqAR0sjyKHSzMmbNKB1qWJ";
    $key2 = "vW2iRGdbrmNpm84A";
    return base64_encode(openssl_encrypt($plaintext, 'aes-256-cbc', $key1, OPENSSL_RAW_DATA, $key2));
}

function Decrypt($encrypted) {
    if ($encrypted == "") return ""; 
    global $KEYS;
    $key1 = "4njTUyUrn4vqAR0sjyKHSzMmbNKB1qWJ";
    $key2 = "vW2iRGdbrmNpm84A";
    return openssl_decrypt(base64_decode($encrypted), 'aes-256-cbc', $key1, OPENSSL_RAW_DATA, $key2);
} 