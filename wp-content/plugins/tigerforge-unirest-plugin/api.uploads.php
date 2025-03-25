<?php

// ------------------------------------------------------------------------------------------------
$UR_F_NAME = "unirest_api_uploads";
add_action("wp_ajax_$UR_F_NAME", $UR_F_NAME);
function unirest_api_uploads()
{
    // Ottieni i dati inviati via POST
    $data = URTools::getJSONdata();
    $action = $data->action;
    $user = $data->user;

    switch ($action) {
        case 'LIST':

            if (is_numeric($user)) {
                $user_id = intval($user);
            } else {
                $user_obj = get_user_by('login', $user) ?: get_user_by('email', $user);
                if (!$user_obj) {
                    URTools::send([
                        'result' => "ERROR",
                        'message' => "User not found.",
                    ]);
                    return;
                }
                $user_id = $user_obj->ID;
            }

            $base_directory = wp_upload_dir()['basedir'] . "/unirest/assets/" . $user_id;

            if (!file_exists($base_directory)) {
                URTools::send([
                    'result' => "ERROR",
                    'message' => "User folder not found.",
                ]);
                return;
            }

            // Funzione ricorsiva per scansionare la struttura della cartella
            function scan_directory($dir)
        {
                $result = [
                    "folderName" => basename($dir),
                    "files" => [],
                    "folders" => [],
                ];

                // Ottieni la lista dei file e delle sottocartelle
                $items = scandir($dir);

                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }

                    $full_path = $dir . '/' . $item;

                    if (is_dir($full_path)) {
                        // Se è una cartella, la scansiona ricorsivamente
                        $result["folders"][] = scan_directory($full_path);
                    } elseif (is_file($full_path)) {
                        // Se è un file, aggiungi il nome e la dimensione
                        $result["files"][] = [
                            "fileName" => $item,
                            "size" => filesize($full_path),
                        ];
                    }
                }

                return $result;
            }

            // Inizia la scansione dalla cartella principale dell'utente
            $directory_structure = scan_directory($base_directory);

            // Invia il risultato in formato JSON
            URTools::send([
                'result' => "SUCCESS",
                'structure' => $directory_structure,
            ]);

            break;

        default:
            URTools::send([
                'result' => "ERROR",
                'message' => "Invalid action.",
            ]);
            break;
    }

    
}
