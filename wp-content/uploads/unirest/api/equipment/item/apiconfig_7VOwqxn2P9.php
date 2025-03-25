<?php

// API Configuration
$APIConfig = array (
  'id' => 2,
  'name' => 'item',
  'type' => 'SQL',
  'tableName' => 'tfur_equipment',
  'description' => 'items of users',
  'isSQL' => true,
  'isPHP' => false,
  'canRead' => true,
  'canWrite' => true,
  'canUpdate' => true,
  'canDelete' => true,
  'read_custom_query' => '',
  'write_custom_query' => '',
  'update_custom_query' => '',
  'delete_custom_query' => '',
  'php_script' => '<?php

// Your PHP code here!
',
  'tableColumns' => 
  array (
    0 => 'id',
    1 => 'code',
    2 => 'name',
    3 => 'price',
    4 => 'quantity',
  ),
  'read_columns' => 
  array (
    0 => 'id',
    1 => 'code',
    2 => 'name',
    3 => 'price',
    4 => 'quantity',
  ),
  'write_columns' => 
  array (
    0 => 'id',
    1 => 'code',
    2 => 'name',
    3 => 'price',
    4 => 'quantity',
  ),
  'update_columns' => 
  array (
    0 => 'id',
    1 => 'code',
    2 => 'name',
    3 => 'price',
    4 => 'quantity',
  ),
  'readConditions' => 
  array (
    0 => 
    array (
      'column' => 'code',
      'operator' => '=',
    ),
  ),
  'updateConditions' => 
  array (
    0 => 
    array (
      'column' => 'code',
      'operator' => '=',
    ),
  ),
  'deleteConditions' => 
  array (
    0 => 
    array (
      'column' => 'code',
      'operator' => '=',
    ),
  ),
  'read_logical_operator' => 'AND',
  'update_logical_operator' => 'AND',
  'delete_logical_operator' => 'AND',
  'update_can_write' => true,
  'read_is_existcheck' => false,
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

