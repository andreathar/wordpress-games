<?php

    require_once("../../../../../../wp-load.php");
    include_once "../../../../../plugins/tigerforge-unirest-plugin/plugins/medoo/Medoo.php";
    require_once("../urapitools.php");
    use Medoo\Medoo;

    class URBUILTINTOKENS {

        public $DB;

        public function __construct() {

            $this->DB = new Medoo([
                'type' => 'mysql',
                'host' => '{{DB_HOST}}',
                'database' => '{{DB_NAME}}',
                'username' => '{{DB_USER}}',
                'password' => '{{DB_PASSWORD}}',
                'charset' => '{{DB_CHARSET}}',
            ]);

        }

        public function Start() { 

            $this->DATA = URAPITools::getPostDecryptedJSON();
            $action = URAPITools::Decrypt(URAPITools::getHeader("x-token-action"));

            if ($action == "TOKENS_UPDATE") {
                $this->tokens_update();
            } else {
                URAPITools::sendError("Invalid Action.");
            }

        }

        function tokens_update() {

            $tokensData = URAPITools::Decrypt(URAPITools::getHeader("x-token-extra"));
            $tokensData = explode("|", $tokensData);
            $token_login = $tokensData[0];
            $tokens = "__" . $tokensData[1];
        
            // Generare i nuovi token
            $token_read = URAPITools::randString(8);
            $token_write = URAPITools::randString(8);
            $token_update = URAPITools::randString(8);
            $token_delete = URAPITools::randString(8);
        
            // Preparare la risposta
            $response = [
                "token_read_updated" => false,
                "token_read" => null,
                "token_write_updated" => false,
                "token_write" => null,
                "token_update_updated" => false,
                "token_update" => null,
                "token_delete_updated" => false,
                "token_delete" => null,
            ];
        
            // Aggiornare i token e costruire la risposta
            if (strpos($tokens, "[R]") > 0) {
                $this->db_token("token_read", $token_read, $token_login);
                $response["token_read_updated"] = true;
                $response["token_read"] = $token_read;
            }
            if (strpos($tokens, "[W]") > 0) { 
                $this->db_token("token_write", $token_write, $token_login);
                $response["token_write_updated"] = true;
                $response["token_write"] = $token_write;
            }
            if (strpos($tokens, "[U]") > 0) {
                $this->db_token("token_update", $token_update, $token_login);
                $response["token_update_updated"] = true;
                $response["token_update"] = $token_update;
            }
            if (strpos($tokens, "[D]") > 0) {
                $this->db_token("token_delete", $token_delete, $token_login);
                $response["token_delete_updated"] = true;
                $response["token_delete"] = $token_delete;
            }
            
            // Inviare la risposta
            URAPITools::sendReply(json_encode($response));
        }
        

        function db_token($column, $value, $token_login) {
            $this->DB->update('tfsysur_tokens', [$column => $value], ['token_login' => $token_login]);
        }
        
    }

    $urBuiltinTokens = new URBUILTINTOKENS();
    $urBuiltinTokens->Start();
    
?>