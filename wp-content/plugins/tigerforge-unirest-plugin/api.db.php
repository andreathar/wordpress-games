<?php

// ------------------------------------------------------------------------------------------------
$UR_F_NAME = "unirest_api_db_table_create";
add_action("wp_ajax_$UR_F_NAME", $UR_F_NAME);
function unirest_api_db_table_create()
{
    $DB = URTools::getDB();
    $data = URTools::getJSONdata();

    $tableName = "tfur_" . $data->name;
    $row = $data->row;

    $columns = [];

    $columns['id'] = [
        'INT',
        'NOT NULL',
        'AUTO_INCREMENT',
        'PRIMARY KEY',
    ];

    foreach ($row as $column) {
        if ($column->name === 'id') {
            continue;
        }

        $columnDef = [$column->type];

        if (in_array($column->type, ['VARCHAR']) && isset($column->size) && $column->size > 0) {
            $columnDef[0] .= "({$column->size})";
        }

        if (!$column->canBeNull) {
            $columnDef[] = 'NOT NULL';
        } else {
            $columnDef[] = 'NULL';
        }

        $columns[$column->name] = $columnDef;
    }

    $DB->create($tableName, $columns);

    if ($DB->error) {
        $results['result'] = "ERROR";
        $results["error"] = $DB->error;
        $results["error_info"] = $DB->errorInfo;
    } else {
        $results['result'] = "SUCCESS";
    }

    // Restituisce la risposta JSON
    URTools::send($results);
}

// ------------------------------------------------------------------------------------------------
$UR_F_NAME = "unirest_api_db_table_list";
add_action("wp_ajax_$UR_F_NAME", $UR_F_NAME);
function unirest_api_db_table_list()
{
    $DB = URTools::getDB();

    $tables = $DB->query("SHOW TABLES LIKE 'tfur_%'")->fetchAll(PDO::FETCH_COLUMN);

    if ($DB->error) {
        $results['result'] = "ERROR";
        $results["error"] = $DB->error;
        $results["error_info"] = $DB->errorInfo;
        $results['list'] = [];
    } else {
        $results['result'] = "SUCCESS";
        $results['list'] = $tables;
    }

    URTools::send($results);
}

// ------------------------------------------------------------------------------------------------
$UR_F_NAME = "unirest_api_db_table_structure";
add_action("wp_ajax_$UR_F_NAME", $UR_F_NAME);
function unirest_api_db_table_structure()
{
    $DB = URTools::getDB();
    $data = URTools::getData();

    $columns = $DB->query("SHOW COLUMNS FROM `tfur_$data`")->fetchAll(PDO::FETCH_ASSOC);

    if ($DB->error) {
        $results['result'] = "ERROR";
        $results["error"] = $DB->error;
        $results["error_info"] = $DB->errorInfo;
        $results['list'] = [];
    } else {
        $results['result'] = "SUCCESS";
        $results['list'] = [];
        foreach ($columns as $column) {
            $results['list'][] = $column;
        }

    }

    URTools::send($results);
}

// ------------------------------------------------------------------------------------------------
$UR_F_NAME = "unirest_api_db_table_structure_update";
add_action("wp_ajax_$UR_F_NAME", $UR_F_NAME);
function unirest_api_db_table_structure_update()
{
    $DB = URTools::getDB();
    $data = URTools::getJSONdata();

    $tableName = "tfur_" . $data->table; // Nome della tabella attuale

    try {
        // 1. Cambia il nome della tabella se necessario
        if ($data->name !== "") {
            $newTableName = "tfur_" . $data->name;
            $sqlRenameTable = "ALTER TABLE `$tableName` RENAME TO `$newTableName`;";
            $DB->query($sqlRenameTable);
            $tableName = $newTableName; // Aggiorna il nome della tabella per i prossimi passi
        }

        // 2. Elimina le colonne elencate in "delcolumns"
        foreach ($data->delcolumns as $column) {
            $columnName = $column->name;
            $sqlDropColumn = "ALTER TABLE `$tableName` DROP COLUMN `$columnName`;";
            $DB->query($sqlDropColumn);
        }

        // 3. Modifica le colonne elencate in "modcolumns"
        foreach ($data->modcolumns as $column) {
            $columnName = $column->name;
            $columnType = $column->type;
            $columnCanBeNull = $column->canBeNull ? "NULL" : "NOT NULL";
            $newColumnName = isset($column->newName) ? $column->newName : $columnName;

            // Creiamo la query per modificare il nome della colonna o il tipo
            if ($columnType === 'VARCHAR') {
                $columnSize = $column->size;
                $sqlModifyColumn = "ALTER TABLE `$tableName` CHANGE `$columnName` `$newColumnName` $columnType($columnSize) $columnCanBeNull;";
            } else {
                $sqlModifyColumn = "ALTER TABLE `$tableName` CHANGE `$columnName` `$newColumnName` $columnType $columnCanBeNull;";
            }
            $DB->query($sqlModifyColumn);
        }

        // 4. Aggiungi le nuove colonne elencate in "newcolumns"
        foreach ($data->newcolumns as $column) {
            $columnName = $column->name;
            $columnType = $column->type;
            $columnCanBeNull = $column->canBeNull ? "NULL" : "NOT NULL";

            if ($columnType === 'VARCHAR') {
                $columnSize = $column->size;
                $sqlAddColumn = "ALTER TABLE `$tableName` ADD `$columnName` $columnType($columnSize) $columnCanBeNull;";
            } else {
                $sqlAddColumn = "ALTER TABLE `$tableName` ADD `$columnName` $columnType $columnCanBeNull;";
            }
            $DB->query($sqlAddColumn);
        }

        // 5. Cambia l'ordine delle colonne secondo l'array "columnNames"
        // La prima colonna è sempre "ID" e non può essere modificata
        $previousColumn = 'ID'; // Inizializziamo con la colonna 'ID' che è sempre la prima

        // Itera dall'indice 1 (ignorando la prima colonna, che è "ID")
        for ($i = 1; $i < count($data->columnNames); $i++) {
            $updatedColumn = $data->columnNames[$i];
            $columnName = $updatedColumn->name;
            $columnType = strtoupper($updatedColumn->type); // Assicurati che il tipo sia maiuscolo

            // Verifica se il tipo di colonna richiede una dimensione
            if ($columnType === 'VARCHAR') {
                $columnSize = $updatedColumn->size;
                $sqlAlterOrder = "ALTER TABLE `$tableName` MODIFY COLUMN `$columnName` $columnType($columnSize) AFTER `$previousColumn`;";
            } else {
                $sqlAlterOrder = "ALTER TABLE `$tableName` MODIFY COLUMN `$columnName` $columnType AFTER `$previousColumn`;";
            }

            // Esegui la query
            $DB->query($sqlAlterOrder);

            // $results["query"][] = $sqlAlterOrder;
            // $results["query_error"][] = $DB->error;
            // $results["query_error_info"][] = $DB->errorInfo;

            $previousColumn = $columnName; // Aggiorna la colonna precedente per il prossimo ciclo
        }

        // Successo
        $results['result'] = "SUCCESS";

    } catch (Exception $e) {
        // Errore nel processo
        $results['result'] = "ERROR";
        $results['error'] = $e->getMessage();
        $results["query"] = $DB->last();
    }

    URTools::send($results);
}

// ------------------------------------------------------------------------------------------------
$UR_F_NAME = "unirest_api_db_table_actions";
add_action("wp_ajax_$UR_F_NAME", $UR_F_NAME);
function unirest_api_db_table_actions()
{
    $DB = URTools::getDB();
    $data = URTools::getJSONdata();

    $action = $data->action;
    $tableName = "tfur_" . $data->table;
    $extra = $data->extra;

    // Array per i risultati
    $results = [];

    // Controlla l'azione richiesta
    switch ($action) {
        case 'EMPTY':
            // Svuota la tabella
            $sqlEmpty = "TRUNCATE TABLE `$tableName`;";
            $DB->query($sqlEmpty);

            // Verifica se ci sono stati errori
            if ($DB->error) {
                $results['result'] = "ERROR";
                $results['error'] = $DB->error;
            } else {
                $results['result'] = "SUCCESS";
            }
            break;

        case 'DELETE':
            // Cancella la tabella
            $sqlDelete = "DROP TABLE IF EXISTS `$tableName`;";
            $DB->query($sqlDelete);

            // Verifica se ci sono stati errori
            if ($DB->error) {
                $results['result'] = "ERROR";
                $results['error'] = $DB->error;
            } else {
                $results['result'] = "SUCCESS";
            }
            break;

        case 'COUNT':
            $countResult = $DB->count($tableName);

            // Ottieni i nomi delle colonne della tabella
            $columnsResult = $DB->query("SHOW COLUMNS FROM `$tableName`")->fetchAll(PDO::FETCH_ASSOC);

            if ($countResult === false || $columnsResult === false) {
                // Gestione dell'errore
                $results['result'] = "ERROR";
                $results['error'] = $DB->error();
            } else {
                // Se tutto va bene, restituisci il numero di record e i nomi delle colonne
                $results['result'] = "SUCCESS";
                $results['count'] = $countResult;

                // Crea un array con i nomi delle colonne
                $columns = [];
                foreach ($columnsResult as $column) {
                    $c["name"] = $column['Field'];
                    $c["type"] = $column['Type'];
                    $columns[] = $c;
                }

                // Aggiungi l'array delle colonne ai risultati
                $results['columns'] = $columns;
            }
            break;

        case 'RECORDS':
            list($limit, $page) = explode(";", $extra);
            $limit = (int) $limit;
            $offset = ($page - 1) * $limit;

            // Recupera la struttura della tabella per ottenere i nomi delle colonne
            $columnsResult = $DB->query("SHOW COLUMNS FROM `$tableName`")->fetchAll(PDO::FETCH_ASSOC);

            // Filtra le colonne da escludere
            $includedColumns = [];
            foreach ($columnsResult as $columnInfo) {
                $columnType = $columnInfo['Type'];
                if (!in_array($columnType, ['blob', 'binary'])) {
                    $includedColumns[] = $columnInfo['Field']; // Solo colonne da includere
                }
            }

            // Seleziona i record solo dalle colonne incluse
            $recordsResult = $DB->select($tableName, $includedColumns, [
                "LIMIT" => [$offset, $limit],
            ]);

            if ($recordsResult === false) {
                $results['result'] = "ERROR";
                $results['error'] = $DB->error();
            } else {
                $simpleRecords = [];

                foreach ($recordsResult as $record) {
                    $simpleRecord = [];
                    foreach ($columnsResult as $columnInfo) {
                        $columnName = $columnInfo['Field'];
                        if (in_array($columnInfo['Type'], ['blob', 'binary'])) {
                            $simpleRecord[$columnName] = '[...]'; // Sostituisci con stringa vuota
                        } else {
                            $simpleRecord[$columnName] = $record[$columnName]; // Mantieni il valore originale
                        }
                    }
                    $simpleRecords[] = array_values($simpleRecord); // Solo i valori del record
                }

                $results['result'] = "SUCCESS";
                $results['records'] = $simpleRecords;
                $results['limits'] = "$offset, $limit";
            }
            break;

        case 'DELETE_RECORD':
            // Cancella uno specifico record tramite ID
            $recordId = (int) $extra;
            $deleteResult = $DB->delete($tableName, ["id" => $recordId]);

            if ($deleteResult->rowCount() === 0) {
                $results['result'] = "ERROR";
                $results['error'] = "Record not found or not deleted";
            } else {
                $results['result'] = "SUCCESS";
            }
            break;

        case 'WRITE_RECORD':
            // Scrive un nuovo record
            $recordData = get_object_vars($extra);
            $insertResult = $DB->insert($tableName, $recordData);

            if ($insertResult->rowCount() === 0) {
                $results['result'] = "ERROR";
                $results['error'] = $DB->error();
            } else {
                $results['result'] = "SUCCESS";
            }
            break;

        case 'UPDATE_RECORD':
            // Aggiorna un record tramite ID
            $extraData = $extra;
            $recordId = (int) $extraData->id;
            $updateData = $extraData->data;

            $updateResult = $DB->update($tableName, $updateData, ["id" => $recordId]);

            if ($updateResult->rowCount() === 0) {
                $results['result'] = "ERROR";
                $results['error'] = "Record not found or not updated";
            } else {
                $results['result'] = "SUCCESS";
            }
            break;

        default:
            $results['result'] = "ERROR";
            $results["error"] = $DB->error;
            $results["error_info"] = $DB->errorInfo;
            break;
    }

    URTools::send($results);
}
