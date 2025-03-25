<?php

// ------------------------------------------------------------------------------------------------
$UR_F_NAME = "unirest_api_api_group_list";
add_action("wp_ajax_$UR_F_NAME", $UR_F_NAME);
function unirest_api_api_group_list()
{
    $DB = URTools::getDB();

    $records = $DB->select("tfsysur_apis", ["id", "name"], [
        "type" => "GRP",
    ]);

    if ($DB->error) {
        $results['result'] = "ERROR";
        $results["error"] = $DB->error;
        $results["error_info"] = $DB->errorInfo;
        $results['list'] = [];
    } else {
        $results['result'] = "SUCCESS";
        $results['list'] = $records;
    }

    URTools::send($results);
}

// ------------------------------------------------------------------------------------------------
$UR_F_NAME = "unirest_api_api_group_action";
add_action("wp_ajax_$UR_F_NAME", $UR_F_NAME);
function unirest_api_api_group_action()
{
    $DB = URTools::getDB();
    $data = URTools::getJSONdata();

    $action = isset($data->action) ? $data->action : null;
    $name = isset($data->name) ? $data->name : null;
    $extra = isset($data->extra) ? $data->extra : null;

    switch ($action) {
        case "GROUP_ADD":

            $existingRecord = $DB->get("tfsysur_apis", "*", [
                "name" => $name,
                "type" => "GRP",
            ]);
            if ($existingRecord) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => "GROUP_ALREADY_EXISTS",
                ]);
                return;
            }

            $insertResult = $DB->insert("tfsysur_apis", [
                "name" => $name,
                "type" => "GRP",
            ]);

            if ($DB->error) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => $DB->error,
                    'error_info' => $DB->errorInfo,
                ]);
            } else {
                URTools::send([
                    'result' => "SUCCESS",
                ]);
            }
            break;

        // Altri possibili casi di azioni
        case "GROUP_DELETE":
            $deleteResult = $DB->delete("tfsysur_apis", [
                "id" => $extra->id,
                "type" => "GRP",
            ]);

            if ($DB->error) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => $DB->error,
                    'error_info' => $DB->errorInfo,
                ]);
            } else {
                URTools::send([
                    'result' => "SUCCESS",
                ]);
            }
            break;

        case "GROUP_EMPTY":
            $deleteResult = $DB->delete("tfsysur_apis", [
                "group_id" => $extra->id,
                "type" => "API",
            ]);

            if ($DB->error) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => $DB->error,
                    'error_info' => $DB->errorInfo,
                ]);
            } else {
                URTools::send([
                    'result' => "SUCCESS",
                ]);
            }

        case "GROUP_RENAME":

            $existingRecord = $DB->get("tfsysur_apis", "*", [
                "name" => $name,
                "type" => "GRP",
            ]);

            if ($existingRecord) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => "GROUP_ALREADY_EXISTS",
                ]);
                return;
            }

            $updateResult = $DB->update("tfsysur_apis", [
                "name" => $name,
            ], [
                "id" => $extra->id,
                "type" => "GRP",
            ]);

            if ($DB->error) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => $DB->error,
                    'error_info' => $DB->errorInfo,
                ]);
            } else {
                URTools::send([
                    'result' => "SUCCESS",
                ]);
            }
            break;

        // Default nel caso di azione non riconosciuta
        default:
            URTools::send([
                'result' => "ERROR",
                'error' => "No action '{$action}'",
            ]);
            break;
    }
}

// ------------------------------------------------------------------------------------------------
$UR_F_NAME = "unirest_api_apis_action";
add_action("wp_ajax_$UR_F_NAME", $UR_F_NAME);
function unirest_api_apis_action()
{
    $DB = URTools::getDB();
    $data = URTools::getJSONdata();

    $action = isset($data->action) ? $data->action : null;
    $name = isset($data->name) ? $data->name : null;
    $extra = isset($data->extra) ? $data->extra : null;

    switch ($action) {
        case "GRUP_INFO":

            $existingRecord = $DB->get("tfsysur_apis", "*", [
                "id" => $name,
                "type" => "GRP",
            ]);
            if ($existingRecord) {
                URTools::send([
                    'result' => "SUCCESS",
                    'data' => $existingRecord,
                ]);
                return;
            } else {
                URTools::send([
                    'result' => "ERROR",
                ]);
                return;
            }
            break;

        case "APIS_LIST":
            $apisList = $DB->select("tfsysur_apis", ["id", "name", "type", "info"], [
                "group_id" => $name,
            ]);

            $tables = $DB->query("SHOW TABLES LIKE 'tfur_%'")->fetchAll(PDO::FETCH_COLUMN);

            $tablesWithColumns = [];
            foreach ($tables as $table) {
                $columns = $DB->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
                $tablesWithColumns[substr($table, 5)] = $columns;
            }

            if ($DB->error) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => $DB->error,
                    'error_info' => $DB->errorInfo,
                ]);
            } else {
                URTools::send([
                    'result' => "SUCCESS",
                    'data' => $apisList,
                    'db' => $tablesWithColumns,
                ]);
            }
            break;

        case "API_GET":

            $apiRecord = $DB->get("tfsysur_apis", "*", [
                "id" => $name,
            ]);

            if ($DB->error) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => $DB->error,
                    'error_info' => $DB->errorInfo,
                ]);
            } else {
                URTools::send([
                    'result' => "SUCCESS",
                    'data' => $apiRecord,
                ]);
            }
            break;

        case "API_CREATE":
            $type = isset($extra->type) ? $extra->type : null;
            $groupId = isset($extra->groupId) ? $extra->groupId : null;
            $name = isset($extra->name) ? $extra->name : null;
            $description = isset($extra->description) ? $extra->description : null;

            $existingRecord = $DB->get("tfsysur_apis", "*", [
                "name" => $name,
                "type[!]" => "GRP",
            ]);

            if ($existingRecord) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => "API_ALREADY_EXISTS",
                ]);
                return;
            }

            $info = [
                "description" => $description,
                "tableName" => "",
                "isSQL" => ($type === "SQL"),
                "isPHP" => ($type === "PHP"),
                "canRead" => false,
                "canWrite" => false,
                "canUpdate" => false,
                "canDelete" => false,
            ];
            $infoJson = json_encode($info);

            $DB->insert("tfsysur_apis", [
                "type" => $type,
                "group_id" => $groupId,
                "name" => $name,
                "info" => $infoJson,
            ]);

            if ($DB->error) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => $DB->error,
                    'error_info' => $DB->errorInfo,
                ]);
            } else {
                URTools::send([
                    'result' => "SUCCESS",
                    'id' => $DB->id(),
                ]);
            }
            break;

        case "API_DELETE":

            $deleteResult = $DB->delete("tfsysur_apis", [
                "id" => $extra->id,
                "group_id" => $extra->groupId,
            ]);

            if ($DB->error) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => $DB->error,
                    'error_info' => $DB->errorInfo,
                ]);
            } else {
                URTools::send([
                    'result' => "SUCCESS",
                    'data' => $extra,
                ]);
            }
            break;

        case "API_UPDATE":
            $type = isset($extra->type) ? $extra->type : null;
            $groupId = isset($extra->groupId) ? $extra->groupId : null;
            $description = isset($extra->description) ? $extra->description : null;
            $apiname = isset($extra->name) ? $extra->name : null;

            $existingRecordWithSameName = $DB->get("tfsysur_apis", "*", [
                "name" => $apiname,
                "type[!]" => "GRP",
                "id[!]" => $name,
            ]);

            if ($existingRecordWithSameName) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => "API_ALREADY_EXISTS",
                ]);
                return;
            }

            $existingRecord = $DB->get("tfsysur_apis", "*", [
                "id" => $name,
            ]);

            // Aggiorna il campo "info"
            $info = json_decode($existingRecord['info'], true);
            $info['description'] = $description;
            $info['isSQL'] = ($type === "SQL");
            $info['isPHP'] = ($type === "PHP");
            $infoJson = json_encode($info);

            // Esegui l'update nel database
            $DB->update("tfsysur_apis", [
                "name" => $apiname,
                "type" => $type,
                "group_id" => $groupId,
                "info" => $infoJson,
            ], [
                "id" => $name,
            ]);

            // Verifica errori e invia la risposta
            if ($DB->error) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => $DB->error,
                    'error_info' => $DB->errorInfo,
                ]);
            } else {
                URTools::send([
                    'result' => "SUCCESS",
                ]);
            }
            break;

        case "API_SAVE":

            $id = isset($extra->id) ? $extra->id : null;
            $isSQL = isset($extra->isSQL) ? $extra->isSQL : false;
            $groupId = isset($name) ? $name : null;
            $name = isset($extra->name) ? $extra->name : null;
            $type = $isSQL ? "SQL" : "PHP";
            $info = json_encode($extra); // Serializza l'intero JSON

            // Crea un JSON contenente solo i booleani e le colonne richieste
            $data2 = json_encode([
                "canRead" => $extra->canRead,
                "canWrite" => $extra->canWrite,
                "canUpdate" => $extra->canUpdate,
                "canDelete" => $extra->canDelete,
                "tableName" => $extra->tableName,
                "tableColumns" => $extra->tableColumns,
            ]);

            $DB->update("tfsysur_apis", [
                "type" => $type,
                "group_id" => $groupId,
                "name" => $name,
                "info" => $info,
                "data2" => $data2,
            ], [
                "id" => $id,
            ]);

            if ($DB->error) {
                URTools::send([
                    'result' => "ERROR",
                    'error' => $DB->error,
                    'error_info' => $DB->errorInfo,
                ]);
            } else {
                URTools::send([
                    'result' => "SUCCESS",
                    'id' => $DB->id(),
                    'group' => $groupId,
                ]);
            }
            break;

        default:
            URTools::send([
                'result' => "ERROR",
                'error' => "No action '{$action}'",
            ]);
            break;
    }
}
