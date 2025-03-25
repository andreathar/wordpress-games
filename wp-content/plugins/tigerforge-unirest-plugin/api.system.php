<?php

function unirest_api_systemcheck()
{

    $results = array(
        'php_version' => '',
        'getallheaders_available' => false,
        'permalinks_structure' => '',
        'folder_creation_1' => false,
        'folder_deletion_1' => false,
        'folder_creation_2' => false,
        'file_creation' => false,
        'file_deletion' => false,
        'folder_deletion_2' => false,
        'database' => true,
    );

    // Verifica della versione di PHP
    if (version_compare(PHP_VERSION, '7.4', '>')) {
        $results['php_version'] = PHP_VERSION;
    } else {
        $results['php_version'] = "";
    }

    // Verifica della disponibilità di getallheaders()
    if (function_exists('getallheaders')) {
        $results['getallheaders_available'] = true;
    }

    // Verifica della struttura dei permalinks
    global $wp_rewrite;
    if ($wp_rewrite->permalink_structure) {
        $results['permalinks_structure'] = $wp_rewrite->permalink_structure;
    } else {
        $results['permalinks_structure'] = "";
    }

    // Directory di upload
    $upload_dir = wp_upload_dir();
    $test_dir = $upload_dir['basedir'] . '/test-folder';

    // Creazione della prima cartella
    if (wp_mkdir_p($test_dir)) {
        $results['folder_creation_1'] = true;
    }

    // Cancellazione della prima cartella
    if (is_dir($test_dir) && rmdir($test_dir)) {
        $results['folder_deletion_1'] = true;
    }

    // Creazione della seconda cartella
    if (wp_mkdir_p($test_dir)) {
        $results['folder_creation_2'] = true;
    }

    // Creazione di un file dentro la nuova cartella
    $test_file = $test_dir . '/test-file.txt';
    if (file_put_contents($test_file, 'Test content')) {
        $results['file_creation'] = true;
    }

    // Cancellazione del file
    if (is_file($test_file) && unlink($test_file)) {
        $results['file_deletion'] = true;
    }

    // Cancellazione della seconda cartella
    if (is_dir($test_dir) && rmdir($test_dir)) {
        $results['folder_deletion_2'] = true;
    }

    // Verifica Database
    $DB = URTools::getDB();
    $tableExists = $DB->query("SHOW TABLES LIKE 'tfur_database_test'")->fetch();
    if ($tableExists) {
        $DB->query("DROP TABLE tfur_database_test");
    }

    $DB->create("tfur_database_test", [
        "id" => ["BIGINT", "UNSIGNED", "NOT NULL", "AUTO_INCREMENT", "PRIMARY KEY"],
    ]);
    if ($DB->error) {$results['database'] = false;}

    $DB->query("DROP TABLE tfur_database_test");
    if ($DB->error) {$results['database'] = false;}

    // Rispondi con un messaggio di successo
    $results['result'] = "SUCCESS";
    URTools::send($results);
}
add_action('wp_ajax_unirest_api_systemcheck', 'unirest_api_systemcheck');

function unirest_api_key()
{
    $data = URTools::getJSONdata();

    if ($data->action == "READ") {

        $results['result'] = "SUCCESS";
        $results['data'] = get_option($data->key);
        URTools::send($results);

    } else if ($data->action == "WRITE") {

        $results['result'] = "SUCCESS";
        update_option($data->key, $data->value);
        $results['data'] = "OK";
        URTools::send($results);

    } else if ($data->action == "DELETE") {

        $results['result'] = "SUCCESS";
        delete_option($data->key);
        $results['data'] = "OK";
        URTools::send($results);

    } else if ($data->action == "EXISTS") {

        // 4.1 Modificata per migliore affidabilità di rilevamento chiave esistente.
        $option_value = get_option($data->key);
        $results['result'] = "SUCCESS";
        $results['data'] = !($option_value === false || $option_value === null || $option_value === '');
        URTools::send($results);
        

    } else if ($data->action == "WRITE_DECODED") {

        $results['result'] = "SUCCESS";

        update_option(
            $data->key, 
            base64_encode(openssl_encrypt($data->value, 'aes-256-cbc', get_option("UniREST_Secret_Key1"), OPENSSL_RAW_DATA, get_option("UniREST_Secret_Key2")))
        );
        $results['data'] = "OK";
        URTools::send($results);

    } 

    $results['result'] = "ERROR";
    URTools::send($results);

}
add_action('wp_ajax_unirest_api_key', 'unirest_api_key');

function unirest_api_install()
{
    $key1 = URTools::randString(32);
    $key2 = URTools::randString(16);
    
    $results['key1'] = update_option("UniREST_Secret_Key1", $key1);
    $results['key2'] = update_option("UniREST_Secret_Key2", $key2);

    $upload_dir = wp_upload_dir();
    $folder = $upload_dir['basedir'];
    $results['f1'] = wp_mkdir_p("$folder/unirest");
    $results['f2'] = wp_mkdir_p("$folder/unirest/api");
    $results['f3'] = wp_mkdir_p("$folder/unirest/assets");

    $DB = URTools::getDB();

    // Creazione tabella tfsysur_apis
    $tableExists = $DB->query("SHOW TABLES LIKE 'tfsysur_apis'")->fetch();

    if (!$tableExists) {
        $DB->create("tfsysur_apis", [
            "id" => [
                "BIGINT",
                "UNSIGNED",
                "NOT NULL",
                "AUTO_INCREMENT",
                "PRIMARY KEY",
            ],
            "type" => [
                "VARCHAR(3)",
                "NOT NULL",
            ],
            "group_id" => [
                "BIGINT",
            ],
            "name" => [
                "TEXT",
            ],
            "info" => [
                "TEXT",
            ],
            "data2" => [
                "TEXT",
            ],
            "data3" => [
                "TEXT",
            ],
            "data4" => [
                "TEXT",
            ],
        ]);

        if ($DB->error) {
            $results['DB'] = $DB->error;
        } else {
            $results['DB'] = "OK";
        }
    } else {
        $results['DB'] = "OK";
    }

    // Creazione tabella tfsysur_tokens
    $tableTokensExists = $DB->query("SHOW TABLES LIKE 'tfsysur_tokens'")->fetch();

    if (!$tableTokensExists) {
        $DB->create("tfsysur_tokens", [
            "user_id" => [
                "BIGINT",
                "UNSIGNED",
                "NOT NULL",
                "PRIMARY KEY",
            ],
            "token_login" => [
                "TEXT"
            ],
            "token_read" => [
                "TEXT"
            ],
            "token_write" => [
                "TEXT"
            ],
            "token_update" => [
                "TEXT"
            ],
            "token_delete" => [
                "TEXT"
            ],
        ]);

        if ($DB->error) {
            $results['DB2'] = $DB->error;
        } else {
            $results['DB2'] = "OK";
        }
    } else {
        $results['DB2'] = "OK";
    }

    $results['result'] = "SUCCESS";
    URTools::send($results);
}
add_action('wp_ajax_unirest_api_install', 'unirest_api_install');

function unirest_api_systemconfig()
{
    $results['result'] = "SUCCESS";

    $results['settings'] = [
        'post_max_size' => ini_get('post_max_size'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'max_file_uploads' => ini_get('max_file_uploads'),
        'max_input_time' => ini_get('max_input_time'),
        'memory_limit' => ini_get('memory_limit'),
        'file_uploads' => ini_get('file_uploads') ? 'enabled' : 'disabled',
        'key1' => get_option("UniREST_Secret_Key1"),
        'key2' => get_option("UniREST_Secret_Key2"),
        'webglpath' => get_option("UniREST_WebGL_Path"),
    ];

    URTools::send($results);
}
add_action('wp_ajax_unirest_api_systemconfig', 'unirest_api_systemconfig');