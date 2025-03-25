<?php

include_once "../../../../../plugins/tigerforge-unirest-plugin/plugins/medoo/Medoo.php";
include_once "{{APICONFIG_PHP}}";
use Medoo\Medoo;

class URAPIENGINE
{

    public $DB;
    public $APIConfig;
    public $HEADER;
    public $TOKENS;

    public function __construct()
    {

        global $DBCONF, $APIConfig;

        $this->DB = new Medoo([
            'type' => 'mysql',
            'host' => $DBCONF['DB_HOST'],
            'database' => $DBCONF['DB_NAME'],
            'username' => $DBCONF['DB_USER'],
            'password' => $DBCONF['DB_PASSWORD'],
            'charset' => $DBCONF['DB_CHARSET'],
        ]);

        $this->APIConfig = $APIConfig;
    }

    public function Start()
    {
        $this->HEADER = array_change_key_case(getallheaders(), CASE_LOWER);

        $encodedPostData = file_get_contents('php://input');
        $decryptedData = Decrypt($encodedPostData);
        $jsonObject = json_decode($decryptedData, true);

        $action = $jsonObject['action'];
        $condition = json_decode($jsonObject['condition'], true);
        $data = json_decode($jsonObject['data'], true);

        if (!$this->APIConfig['isSQL']) {
            $this->sendError("This API can't work with SQL.");
            exit;
        }

        if (($action == 'READ' && !$this->APIConfig['canRead']) ||
            ($action == 'WRITE' && !$this->APIConfig['canWrite']) ||
            (($action == 'UPDATE' || $action == 'UPDATEMATH') && !$this->APIConfig['canUpdate']) ||
            ($action == 'DELETE' && !$this->APIConfig['canDelete'])) {
            $this->sendError("This API doesn't support the $action operation.");
            exit;
        }

        if ($action == 'READ' && empty($this->APIConfig['read_custom_query']) && $this->APIConfig['read_is_existcheck']) {
            $action = "EXISTS";
        }

        if ($action == 'UPDATE' && empty($this->APIConfig['update_custom_query']) && $this->APIConfig['update_can_write']) {
            $action = "UPDATEWRITE";
        }

        $this->Authorize($action);

        switch ($action) {
            case 'READ':
                $this->Read($condition, $data);
                break;

            case 'EXISTS':
                $this->Exists($condition);
                break;

            case 'WRITE':
                $this->Write($data);
                break;

            case 'UPDATE':
                $this->Update($condition, $data);
                break;

            case 'UPDATEWRITE':
                $this->UpdateWrite($condition, $data);
                break;

            case 'UPDATEMATH':
                $this->UpdateMath($condition, $data);
                break;

            case 'UPDATEJSON':
                $this->UpdateJSON($condition, $data);
                break;

            case 'DELETE':
                $this->Delete($condition);
                break;

            default:
                $this->sendError("Not valid action.");
                break;
        }

    }

    public function Authorize($action)
    {
        $token_login  = $this->HEADER["x-token-login"];
        $token_read   = $this->HEADER["x-token-read"];
        $token_write  = $this->HEADER["x-token-write"];
        $token_update = $this->HEADER["x-token-update"];
        $token_delete = $this->HEADER["x-token-delete"];

        $checkToken = $this->DB->get('tfsysur_tokens', '*', ['token_login' => $token_login]);
        if (!$checkToken) {
            $this->sendError("NO_AUTH_LOGIN");
        }

        $user_id = $checkToken["user_id"];

        if ($action == "READ" || $action == "EXISTS") {
            $checkToken = $this->DB->get('tfsysur_tokens', '*', [
                'user_id'    => $user_id,
                'token_read' => $token_read
            ]);
            if (!$checkToken) {
                $this->sendError("NO_AUTH_READ");
            }
        }

        if ($action == "WRITE") {
            $checkToken = $this->DB->get('tfsysur_tokens', '*', [
                'user_id'     => $user_id,
                'token_write' => $token_write
            ]);
            if (!$checkToken) {
                $this->sendError("NO_AUTH_WRITE");
            }
        }

        if ($action == "UPDATE" || $action == "UPDATEWRITE" || $action == "UPDATEMATH" || $action == "UPDATEJSON") {
            $checkToken = $this->DB->get('tfsysur_tokens', '*', [
                'user_id'     => $user_id,
                'token_update' => $token_update
            ]);
            if (!$checkToken) {
                $this->sendError("NO_AUTH_UPDATE");
            }
        }

        if ($action == "DELETE") {
            $checkToken = $this->DB->get('tfsysur_tokens', '*', [
                'user_id'     => $user_id,
                'token_delete' => $token_delete
            ]);
            if (!$checkToken) {
                $this->sendError("NO_AUTH_DELETE");
            }
        }

        $updateTokenRequest = Decrypt($this->HEADER["x-token-extra"]);
        switch ($updateTokenRequest) {
            case 'UPDATE_READ_TOKEN':
                $token_read = $this->randString(8);
                $this->DB->update('tfsysur_tokens', ['token_read' => $token_read], ['user_id' => $user_id]);
                break;

            case 'UPDATE_WRITE_TOKEN':
                $token_write =$this->randString(8);
                $this->DB->update('tfsysur_tokens', ['token_write' => $token_write], ['user_id' => $user_id]);
                break;

            case 'UPDATE_UPDATE_TOKEN':
                $token_update = $this->randString(8);
                $this->DB->update('tfsysur_tokens', ['token_update' => $token_update], ['user_id' => $user_id]);
                break;

            case 'UPDATE_DELETE_TOKEN':
                $token_delete = $this->randString(8);
                $this->DB->update('tfsysur_tokens', ['token_delete' => $token_delete], ['user_id' => $user_id]);
                break;

            default:
                break;
        }

        $this->TOKENS = array(
            'token_login'  => $token_login,
            'token_read'   => $token_read,
            'token_write'  => $token_write,
            'token_update' => $token_update,
            'token_delete' => $token_delete,
        );        

        return true;
    }

    public function Read($condition, $data)
    {
        try {
            if (!empty($this->APIConfig['read_custom_query'])) {
                $this->executeCustomQuery('READ', $this->DB, $this->APIConfig['read_custom_query'], $data);
            } else {
                $columns = empty($this->APIConfig['read_columns']) ? '*' : $this->APIConfig['read_columns'];
                $conditions = $this->createConditions($this->APIConfig['readConditions'], $condition, $this->APIConfig['read_logical_operator']);

                if ($conditions == "" || $conditions == null) {
                    $results = $this->DB->select($this->APIConfig['tableName'], $columns);
                } else if ($conditions == "MANDATORY_DATA") {
                    $this->sendError("This API has been set to work with a Condition, which requires data. No data has been provided.");
                } else {
                    $results = $this->DB->select($this->APIConfig['tableName'], $columns, $conditions);
                }

                $results = json_encode($results);
                $this->sendReply($results);
            }
        } catch (Exception $e) {
            $this->sendSystemError($e, 'READ', $condition, $data, $this->APIConfig['read_custom_query']);
        }
    }

    public function Write($data)
    {
        try {
            if (!empty($this->APIConfig['write_custom_query'])) {
                $this->executeCustomQuery('WRITE', $this->DB, $this->APIConfig['write_custom_query'], $data);
            } else {
                $insertData = $this->getColumnsData('write_columns', $data, true);
                $this->DB->insert($this->APIConfig['tableName'], $insertData);
                $newId = $this->DB->id();
                $results = ($newId > 0) ? "WT,$newId" : "WF,$newId";
            }

            $this->sendReply($results);

        } catch (Exception $e) {
            $this->sendSystemError($e, 'WRITE', [], $data, $this->APIConfig['write_custom_query']);
        }
    }

    public function Update($condition, $data)
    {
        try {
            if (!empty($this->APIConfig['update_custom_query'])) {
                $this->executeCustomQuery('UPDATE', $this->DB, $this->APIConfig['update_custom_query'], $data);
            } else {
                $updateData = $this->getColumnsData('update_columns', $data, true);
                $conditions = $this->createConditions($this->APIConfig['updateConditions'], $condition, $this->APIConfig['update_logical_operator']);

                if (!is_array($conditions)) {
                    $this->sendError("Conditions are mandatory in the UPDATE operation.");
                }

                $update = $this->DB->update($this->APIConfig['tableName'], $updateData, $conditions);
                $updatedCount = $update->rowCount();
                $results = ($updatedCount > 0) ? "UT,$updatedCount" : "UF,$updatedCount";
            }

            $this->sendReply($results);

        } catch (Exception $e) {
            $this->sendSystemError($e, 'UPDATE', $conditions ?? [], $data, $this->APIConfig['update_custom_query']);
        }
    }

    public function Delete($condition)
    {
        try {
            if (!empty($this->APIConfig['delete_custom_query'])) {
                $this->executeCustomQuery('DELETE', $this->DB, $this->APIConfig['delete_custom_query'], []);
            } else {
                $conditions = $this->createConditions($this->APIConfig['deleteConditions'], $condition, $this->APIConfig['delete_logical_operator']);
                if (!is_array($conditions)) {
                    $this->sendError("Conditions are mandatory in the DELETE operation.");
                }

                $delete = $this->DB->delete($this->APIConfig['tableName'], $conditions);
                $deletedCount = $delete->rowCount();
                $results = ($deletedCount > 0) ? "DT,$deletedCount" : "DF,$deletedCount";
            }

            $this->sendReply($results);
        } catch (Exception $e) {
            $this->sendSystemError($e, 'DELETE', $conditions ?? [], [], $this->APIConfig['delete_custom_query']);
        }
    }

    public function Exists($condition)
    {
        try {
            $conditions = $this->createConditions($this->APIConfig['readConditions'], $condition, $this->APIConfig['read_logical_operator']);
            if (!is_array($conditions)) {
                $this->sendError("The READ operation is set to check if a record exists. In this API, conditions are mandatory.");
            }

            $count = $this->DB->count($this->APIConfig['tableName'], $conditions);
            $results = ($count > 0) ? "ET,$count" : "EF,$count";

            $this->sendReply($results);
        } catch (Exception $e) {
            $this->sendSystemError($e, 'EXISTS', $conditions ?? [], [], null);
        }
    }

    public function UpdateWrite($condition, $data)
    {
        try {
            $updateData = $this->getColumnsData('update_columns', $data, true);

            $conditions = $this->createConditions($this->APIConfig['updateConditions'], $condition, $this->APIConfig['update_logical_operator']);
            if (!is_array($conditions)) {
                $this->sendError("Conditions are mandatory in the UPDATE operation.");
            }

            $existingRecord = $this->DB->get($this->APIConfig['tableName'], "id", $conditions);

            if ($existingRecord) {
                $update = $this->DB->update($this->APIConfig['tableName'], $updateData, $conditions);
                $updatedCount = $update->rowCount();
                $results = ($updatedCount > 0) ? "UT,$updatedCount" : "UF,$updatedCount";

                $this->sendReply($results);
            } else {
                $insert = $this->DB->insert($this->APIConfig['tableName'], $updateData);
                $newId = $this->DB->id();
                $results = ($newId > 0) ? "WT,$newId" : "WF,$newId";

                $this->sendReply($results);
            }
        } catch (Exception $e) {
            $this->sendSystemError($e, 'UPDATE_WRITE', $conditions ?? [], $data);
        }
    }

    public function UpdateMath($condition, $data)
    {
        try {
            $column = $data['column'];
            $formula = $data['value'];

            $conditions = $this->createConditions($this->APIConfig['updateConditions'], $condition, $this->APIConfig['update_logical_operator']);
            if (!is_array($conditions)) {
                $this->sendError("Conditions are mandatory in the UPDATE operation.");
            }

            $originalValue = $this->DB->get($this->APIConfig['tableName'], $column, $conditions);

            if ($originalValue === null) {
                $this->sendError("No record found matching the conditions.");
            }

            $formulaToEvaluate = str_replace('X', $originalValue, $formula);

            try {
                $calculatedValue = eval('return ' . $formulaToEvaluate . ';');
            } catch (ParseError $e) {
                $this->sendError("Invalid mathematical expression.");
            }

            $updateData = [$column => $calculatedValue];

            $update = $this->DB->update($this->APIConfig['tableName'], $updateData, $conditions);
            $updatedCount = $update->rowCount();
            $results = ($updatedCount > 0) ? "MT,$calculatedValue" : "MF,$updatedCount";

            $this->sendReply($results);
        } catch (Exception $e) {
            $this->sendSystemError($e, 'UPDATE_MATH', $conditions ?? [], $data);
        }
    }

    public function UpdateJSON($condition, $data)
    {
        try {
            $column = $data['column'];
            $jsonUpdate = $data['value'];

            $conditions = $this->createConditions($this->APIConfig['updateConditions'], $condition, $this->APIConfig['update_logical_operator']);
            if (!is_array($conditions)) {
                $this->sendError("Conditions are mandatory in the UPDATE operation.");
            }

            $originalJson = $this->DB->get($this->APIConfig['tableName'], $column, $conditions);

            if ($originalJson === null) {
                $this->sendError("No record found matching the conditions.");
            }

            $decodedJson = json_decode($originalJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendError("Invalid JSON format in the original data.");
            }

            $updateParts = explode(':', $jsonUpdate, 2);
            if (count($updateParts) !== 2) {
                $this->sendError("Invalid format for value. Use 'key:value' format.");
            }

            $jsonKey = trim($updateParts[0]);
            $jsonValue = trim($updateParts[1]);
            $decodedJson[$jsonKey] = is_numeric($jsonValue) ? (float) $jsonValue : $jsonValue;

            $updatedJson = json_encode($decodedJson);
            if ($updatedJson === false) {
                $this->sendError("Failed to encode updated JSON.");
            }

            $updateData = [$column => $updatedJson];
            $update = $this->DB->update($this->APIConfig['tableName'], $updateData, $conditions);
            $updatedCount = $update->rowCount();
            $results = ($updatedCount > 0) ? "JT,OK" : "JF,KO";

            $this->sendReply($results);
        } catch (Exception $e) {
            $this->sendSystemError($e, 'UPDATE_JSON', $conditions ?? [], $data);
        }
    }

    public function createConditions($conditions, $data, $logicalOperator)
    {
        if (empty($conditions)) {
            return "";
        }

        if (empty($data)) {
            return "MANDATORY_DATA";
        }

        $finalConditions = [];
        $currentOperator = strtoupper($logicalOperator);

        $operatorMapping = [
            '=' => '',
            '!=' => '[!]',
            '>' => '[>]',
            '<' => '[<]',
            '>=' => '[>=]',
            '<=' => '[<=]',
            'IN' => '',
            'NOTIN' => '[!]',
            'LIKE' => '[~]',
            'NOTLIKE' => '[!~]',
        ];

        foreach ($conditions as $condition) {
            $column = $condition['column'];
            $operator = $condition['operator'];
            $value = $condition['value'] ?? '';

            if ($value === '') {
                $value = $data[$column] ?? null;
            }

            if ($value !== null) {
                if ($operator === 'IN' || $operator === 'NOTIN') {
                    $value = array_map('trim', explode(',', $value));
                }

                $key = $operator === 'IN' ? $column : $column . $operatorMapping[$operator];

                $finalConditions[$currentOperator][$key] = $value;
            }
        }

        return !empty($finalConditions) ? $finalConditions : null;
    }

    public function executeCustomQuery($action, $DB, $query, $data)
    {
        if (empty($query)) {
            $this->sendError("Query string cannot be empty.");
        }
        
        // Cerca i placeholder nella query
        preg_match_all('/:\w+/', $query, $placeholders);
        $placeholders = $placeholders[0];
        
        // Se ci sono placeholder ma manca $data, restituisci un errore
        if (!empty($placeholders) && empty($data)) {
            $this->sendError("Data must be provided as the Custom Query requires values from Unity.");
        }

        $filteredData = [];
        foreach ($data as $key => $value) {
            if (in_array(":" . $key, $placeholders)) {
                $filteredData[":" . $key] = $value;
            }
        }

        try {
            $results = $DB->query($query, $filteredData)->fetchAll();
            if (is_array($results)) {
                $results = json_encode($results);
            }
            $this->sendReply($results);
        } catch (Exception $e) {
            $this->sendSystemError($e, $DB, $action, $filteredData, $data, $query);
        }
    }

    public function getColumnsData($id, $data, $removeID = false)
    {
        $insertData = [];

        if (empty($this->APIConfig[$id])) {
            return $data;
        }

        foreach ($this->APIConfig[$id] as $column) {
            if (array_key_exists($column, $data)) {
                $insertData[$column] = $data[$column];
            }
        }

        if ($removeID && isset($insertData['id'])) {
            unset($insertData['id']);
        }

        return $insertData;
    }

    public function sendSystemError($e, $DB, $action, $conditions, $data, $custom_query = "")
    {

        $results = "\n";
        $results .= "exception: " . $e->getMessage() . "\n\n";
        $results .= "action: " . $action . "\n\n";
        $results .= "conditions: " . json_encode($conditions) . "\n\n";
        $results .= "data: " . json_encode($data) . "\n\n";
        $results .= "db: " . $DB->last() . "\n\n";
        $results .= "custom query: " . $custom_query . "\n\n";
        $results .= "db error: " . $DB->error . "\n\n";
        $results .= "db error info: " . $DB->errorInfo . "\n\n";

        $this->sendError($results);

    }

    public function sendReply($data, $message = "")
    {
        die(json_encode(["status" => "SUCCESS", "message" => Encrypt($message), "data" => Encrypt($data), "tokens" => $this->TOKENS]));
    }

    public function sendError($message, $data = "")
    {
        die(json_encode(["status" => "ERROR", "message" => Encrypt($message), "data" => Encrypt($data)]));
    }

    public function randString($l) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters) - 1;
        $key1 = "";
        for ($i = 0; $i < $l; $i++) {
            $key1 .= $characters[rand(0, $charactersLength)];
        }
        return $key1;
    }

}

$apiEngine = new URAPIENGINE();
$apiEngine->Start();
