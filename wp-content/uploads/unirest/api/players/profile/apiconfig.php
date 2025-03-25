<?php

// API Configuration
$APIConfig = array (
  'id' => 8,
  'name' => 'profile',
  'type' => 'SQL',
  'tableName' => 'tfur_players',
  'description' => 'Get player profile',
  'isSQL' => true,
  'isPHP' => true,
  'canRead' => true,
  'canWrite' => false,
  'canUpdate' => false,
  'canDelete' => false,
  'read_custom_query' => '',
  'write_custom_query' => '',
  'update_custom_query' => '',
  'delete_custom_query' => '',
  'php_script' => '',
  'tableColumns' => 
  array (
    0 => 'id',
    1 => 'username',
    2 => 'email',
    3 => 'password_hash',
    4 => 'display_name',
    5 => 'registration_date',
    6 => 'last_login',
    7 => 'status',
  ),
  'read_columns' => 
  array (
    0 => 'id',
    1 => 'username',
    2 => 'email',
    3 => 'display_name',
    4 => 'registration_date',
    5 => 'last_login',
    6 => 'status',
  ),
  'readConditions' => 
  array (
    0 => 
    array (
      'column' => 'id',
      'operator' => '=',
    ),
  ),
  'read_logical_operator' => 'AND',
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