<?php

// ------------------------------------------------------------------------------------------------
$UR_F_NAME = "unirest_api_unity_script";
add_action("wp_ajax_$UR_F_NAME", $UR_F_NAME);
function unirest_api_unity_script()
{
    $DB = URTools::getDB();

    // Step 1: Ottenere la lista delle tabelle che iniziano per 'tfur_' e le loro colonne
    $tablesWithColumns = [];
    $tables = $DB->query("SHOW TABLES LIKE 'tfur_%'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $columns = $DB->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $tablesWithColumns[$table] = $columns;
    }

    // Step 2: Ottenere la lista delle API create dall'utente dalla tabella 'tfsysur_apis'
    $apiList = [];
    $groups = $DB->select("tfsysur_apis", ["id", "name", "type", "info"], [
        "type" => "GRP",
    ]);

    foreach ($groups as $group) {
        $apis = $DB->select("tfsysur_apis", ["name", "type", "info"], [
            "type[!]" => "GRP",
            "group_id" => $group['id'],
        ]);

        $apiList[$group["name"]] = $apis;
    }

    // Step 3: dati di sistema
    $secretKey1 = get_option('UniREST_Secret_Key1');
    $secretKey2 = get_option('UniREST_Secret_Key2');
    $webGLPath = get_option('UniREST_WebGL_Path');
    $appAccount = get_option('UniREST_Application_Account');

    $upload_dir = wp_upload_dir();
    $api_url = $upload_dir['baseurl'] . '/unirest/api';
    $assets_url = $upload_dir['baseurl'] . '/unirest/assets';

    // Step 5: Generare il codice C# per le API e i gruppi (e la struttura di cartelle in Uploads)
    unirest_api_create_builtinapi($secretKey1, $secretKey2);
    $baseDir = $upload_dir['basedir'] . '/unirest/api/';
    $apiClassCode = "";
    foreach ($apiList as $groupName => $apis) {

        $className = "Class" . $groupName;
        $apiClassCode .= "        public class {$className}\n        {\n";

        $groupDir = $baseDir . $groupName;
        URTools::mkdir($groupDir);

        foreach ($apis as $api) {
            $propertyName = $api['name'];
            $api["info"] = json_decode($api['info'], true);
            $description = $api['info']['description'] ?? '';
            $apiData = "{$groupName}/{$propertyName}";

            $apiDir = $groupDir . '/' . $api['name'];
            URTools::mkdir($apiDir);
            if ($api["info"]["isSQL"]) {
                unirest_api_create_SQL_restapi($apiDir, $api["info"], $secretKey1, $secretKey2);
            } else if ($api["info"]["isPHP"]) {
                unirest_api_create_PHP_restapi($apiDir, $api["info"], $secretKey1, $secretKey2);
            }

            // Controlla le operazioni disponibili
            $operations = [];
            if ($api['info']['canRead']) {
                $operations[] = "Read";
            }

            if ($api['info']['canWrite']) {
                $operations[] = "Write";
            }

            if ($api['info']['canUpdate']) {
                $operations[] = "Update" . ($api['info']['update_can_write'] ? " (can write)" : "");
            }

            if ($api['info']['canDelete']) {
                $operations[] = "Delete";
            }

            $operationSummary = implode(", ", $operations);

            // Crea il <summary> con le operazioni
            if ($description) {
                $apiClassCode .= "            /// <summary>\n";
                $apiClassCode .= "            /// {$description}\n";
                $apiClassCode .= "            /// <para>API: {$apiData}</para>\n";
                $apiClassCode .= "            /// <para>Operations: {$operationSummary}</para>\n";
                $apiClassCode .= "            /// </summary>\n";
            } else {
                $apiClassCode .= "            /// <summary>\n";
                $apiClassCode .= "            /// API: {$apiData}\n";
                $apiClassCode .= "            /// <para>Operations: {$operationSummary}</para>\n";
                $apiClassCode .= "            /// </summary>\n";
            }

            // Crea la proprietà stringa
            $apiClassCode .= "            public string {$propertyName} = \"{$apiData}\";\n";
        }

        $apiClassCode .= "        }\n\n";
        $apiClassCode .= "        /// <summary>\n";
        $apiClassCode .= "        /// API Group: {$groupName}\n";
        $apiClassCode .= "        /// </summary>\n";
        $apiClassCode .= "        public static {$className} {$groupName} = new();\n\n";
    }
    
    // Step 6: Generare il codice C# per le classi delle tabelle
    $tableClassCode = "";
    foreach ($tablesWithColumns as $tableName => $columns) {

        $simpleName = str_replace('tfur_', '', $tableName);
        $className = ucfirst($simpleName);

        $tableClassCode .= "        /// <summary>\n";
        $tableClassCode .= "        /// MySQL Table: " . $simpleName . "\n";
        $tableClassCode .= "        /// </summary>\n";
        $tableClassCode .= "        public class " . $className . "\n        {\n";

        $enumData = "";

        foreach ($columns as $columnDetails) {
            $column = $columnDetails['Field'];
            $dataType = $columnDetails['Type'];
            $isNullable = $columnDetails['Null'] === 'YES';

            preg_match('/^(\w+)/', $dataType, $matches);
            $baseType = $matches[1];

            // Conversione del tipo MySQL al tipo C#
            switch ($baseType) {
                case 'int':
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                    $csharpType = $isNullable ? 'int?' : 'int';
                    break;
                case 'bigint':
                    $csharpType = $isNullable ? 'long?' : 'long';
                    break;
                case 'varchar':
                case 'text':
                case 'char':
                    $csharpType = $isNullable ? 'string?' : 'string';
                    break;
                case 'float':
                    $csharpType = $isNullable ? 'float?' : 'float';
                    break;
                case 'double':
                    $csharpType = $isNullable ? 'double?' : 'double';
                    break;
                case 'decimal':
                    $csharpType = $isNullable ? 'decimal?' : 'decimal';
                    break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                case 'year':
                    $csharpType = $isNullable ? 'DateTime?' : 'DateTime';
                    break;
                case 'time':
                    $csharpType = $isNullable ? 'TimeSpan?' : 'TimeSpan';
                    break;
                case 'boolean':
                case 'bit':
                    $csharpType = $isNullable ? 'bool?' : 'bool';
                    break;
                case 'blob':
                case 'tinyblob':
                case 'mediumblob':
                case 'longblob':
                    $csharpType = $isNullable ? 'byte[]?' : 'byte[]';
                    break;
                default:
                    $csharpType = 'object';
                    break;
            }

            // Aggiungere il summary alla proprietà
            $nullableText = $isNullable ? " - Nullable" : "";
            $dataType = strtoupper($dataType);
            $tableClassCode .= "            /// <summary>\n";
            $tableClassCode .= "            /// Column: {$column} ({$dataType}{$nullableText})\n";
            $tableClassCode .= "            /// </summary>\n";

            // Aggiungi la proprietà alla classe
            $tableClassCode .= "            public {$csharpType} {$column}" . ($isNullable ? " = null" : "") . ";\n";

            // Dati per l'enumeratore delle Colonne
            $enumData .= "            /// <summary>\n";
            $enumData .= "            /// Column: {$column} ({$dataType}{$nullableText})\n";
            $enumData .= "            /// </summary>\n";
            $enumData .= "            $column,\n";
        }

        $tableClassCode .= "        }\n\n";

        // Enumeratore delle colonne
        $tableClassCode .= "        public enum $className" . "Col {" . "\n";
        $tableClassCode .= $enumData;
        $tableClassCode .= "        }" . "\n";
    }

    // Step 4: Leggere il file "assets/unity_api_script.txt" e sostituire i marcatori
    $templatePath = UNIREST_PLUGIN_URL . 'assets/templates/unity_api_script.txt';
    $templateContent = URTools::templateFile($templatePath, array(
        'V'          => UNIREST_PLUGIN_VERSION,
        'KEY1'       => $secretKey1,
        'KEY2'       => $secretKey2,
        'APP_ACCOUNT'=> $appAccount,
        'URL_API'    => $api_url,
        'URL_ASSETS' => $assets_url,
        'WEBGL_URL_API'     => rtrim($webGLPath, "/") . "/unirest/api",
        'WEBGL_URL_ASSETS'  => rtrim($webGLPath, "/") . "/unirest/assets",
        'APIS'       => $apiClassCode,
        'TABLES'     => $tableClassCode
    ), null);

    URTools::send(['result' => "SUCCESS", 'data' => "", 'script' => $templateContent]);
}

function unirest_api_create_SQL_restapi($path, $info, $secretKey1, $secretKey2)
{
    $fileName = unirest_api_getFileName($path);
    $filePath = rtrim($path, '/') . '/' . $fileName;

    $DBConfig = array(
        'DB_NAME' => DB_NAME,
        'DB_USER' => DB_USER,
        'DB_PASSWORD' => DB_PASSWORD,
        'DB_HOST' => DB_HOST,
        'DB_CHARSET' => DB_CHARSET,
        'DB_COLLATE' => DB_COLLATE,
        'DB_PREFIX' => $GLOBALS['table_prefix'],
    );

    $DB = var_export($DBConfig, true);

    $info["tableName"] = "tfur_" . $info["tableName"];
    unirest_api_removeUnityLabel($info);
    $APIConfig = var_export($info, true);

    $phpContent = "<?php\n\n";
    $phpContent .= "// API Configuration\n";
    $phpContent .= "\$APIConfig = " . $APIConfig . ";\n\n";
    $phpContent .= "// DB Configuration\n";
    $phpContent .= "\$DBCONF = " . $DB . ";\n\n";

    $phpContent .= "function Encrypt(\$plaintext) {\n";
    $phpContent .= "    if (\$plaintext == \"\") return \"\"; \n";
    $phpContent .= "    global \$KEYS;\n";
    $phpContent .= "    \$key1 = \"" . $secretKey1 . "\";\n";
    $phpContent .= "    \$key2 = \"" . $secretKey2 . "\";\n";
    $phpContent .= "    return base64_encode(openssl_encrypt(\$plaintext, 'aes-256-cbc', \$key1, OPENSSL_RAW_DATA, \$key2));\n";
    $phpContent .= "}\n\n";

    $phpContent .= "function Decrypt(\$encrypted) {\n";
    $phpContent .= "    if (\$encrypted == \"\") return \"\"; \n";
    $phpContent .= "    global \$KEYS;\n";
    $phpContent .= "    \$key1 = \"" . $secretKey1 . "\";\n";
    $phpContent .= "    \$key2 = \"" . $secretKey2 . "\";\n";
    $phpContent .= "    return openssl_decrypt(base64_decode(\$encrypted), 'aes-256-cbc', \$key1, OPENSSL_RAW_DATA, \$key2);\n";
    $phpContent .= "}\n\n";

    file_put_contents($filePath, $phpContent);

    $indexSourcePath = __DIR__ . '/assets/templates/template.index.php';
    $indexDestinationPath = URTools::copyFile($indexSourcePath, $path, "index.php");
    URTools::templateFile($indexDestinationPath, array('APICONFIG_PHP' => $fileName), $indexDestinationPath);

}

function unirest_api_create_PHP_restapi($path, $info, $secretKey1, $secretKey2) 
{
    $fileName = unirest_api_getFileName($path);
    $filePath = rtrim($path, '/') . '/' . $fileName;

    $phpContent = "<?php\n\n";

    $phpContent .= "require_once(\"" . UNIREST_WPLOAD_PATH . "\");\n\n";

    $phpContent .= "function Encrypt(\$plaintext) {\n";
    $phpContent .= "    if (\$plaintext == \"\") return \"\"; \n";
    $phpContent .= "    global \$KEYS;\n";
    $phpContent .= "    \$key1 = \"" . $secretKey1 . "\";\n";
    $phpContent .= "    \$key2 = \"" . $secretKey2 . "\";\n";
    $phpContent .= "    return base64_encode(openssl_encrypt(\$plaintext, 'aes-256-cbc', \$key1, OPENSSL_RAW_DATA, \$key2));\n";
    $phpContent .= "}\n\n";

    $phpContent .= "function Decrypt(\$encrypted) {\n";
    $phpContent .= "    if (\$encrypted == \"\") return \"\"; \n";
    $phpContent .= "    global \$KEYS;\n";
    $phpContent .= "    \$key1 = \"" . $secretKey1 . "\";\n";
    $phpContent .= "    \$key2 = \"" . $secretKey2 . "\";\n";
    $phpContent .= "    return openssl_decrypt(base64_decode(\$encrypted), 'aes-256-cbc', \$key1, OPENSSL_RAW_DATA, \$key2);\n";
    $phpContent .= "}\n\n";

    file_put_contents($filePath, $phpContent);

    $indexSourcePath = __DIR__ . '/assets/templates/template.index2.php';
    $indexDestinationPath = URTools::copyFile($indexSourcePath, $path, "index.php");
    
    URTools::templateFile($indexDestinationPath, array(
        'APICONFIG_PHP' => $fileName, 
        '*//{{PHP_CODE}}' => $info["php_script"]
    ), $indexDestinationPath);
}

function unirest_api_create_builtinapi($key1, $key2) {

    $builtin = ["server", "user", "tokens", "upload", "test", "filemanager"];
    $upload_dir = wp_upload_dir();
    $api_dir = $upload_dir['basedir'] . '/unirest/api/unirest';
    $api_url = $upload_dir['baseurl'] . '/unirest/api/unirest';

    URTools::mkdir($api_dir);

    foreach($builtin as $name) {

        $templatePath = __DIR__ . '/assets/templates/template.' . $name . '.php';
        $subfolder = $api_dir . '/' . $name;
        URTools::copyFile($templatePath, $subfolder, "index.php", true);

    }

    $templatePath = __DIR__ . '/urtools.php';
    $destinationFile = URTools::copyFile($templatePath, $api_dir, "urapitools.php");

    URTools::templateFile($destinationFile, array(
        'KEY1' => $key1, 
        'KEY2' => $key2, 
        '*URTools' => "URAPITools"
    ), $destinationFile);

    unirest_api_finalizeTemplate($api_dir . '/user/index.php');
    unirest_api_finalizeTemplate($api_dir . '/tokens/index.php');
    unirest_api_finalizeTemplate($api_dir . '/upload/index.php');
}

function unirest_api_finalizeTemplate($fileName) {
    URTools::templateFile($fileName, array(
        'DB_NAME' => DB_NAME,
        'DB_USER' => DB_USER,
        'DB_PASSWORD' => DB_PASSWORD,
        'DB_HOST' => DB_HOST,
        'DB_CHARSET' => DB_CHARSET,
        'DB_COLLATE' => DB_COLLATE,
        'DB_PREFIX' => $GLOBALS['table_prefix'],
    ), $fileName);
}

function unirest_api_getFileName($path) {
    $existingFile = glob(rtrim($path, '/') . '/apiconfig_*.php');
    if (!empty($existingFile)) {
        $fileName = basename($existingFile[0]);
    } else {
        $fileName = "apiconfig_" . substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10) . ".php";
    }
    return $fileName;
}

function unirest_api_removeUnityLabel(&$array) {
    foreach ($array as $key => &$value) {
        if (is_array($value)) {
            unirest_api_removeUnityLabel($value);
        } elseif ($key === 'unityLabel' || $key === 'showValueInput') {
            unset($array[$key]);
        }
    }
}
