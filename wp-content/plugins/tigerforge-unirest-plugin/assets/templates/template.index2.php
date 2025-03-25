<?php
    include_once "{{APICONFIG_PHP}}";

    class UniREST {

        public $data;
        public $HEADER;
        public $TOKENS;

        public function __construct() {
            $this->HEADER = array_change_key_case(getallheaders(), CASE_LOWER);

            $encodedPostData = file_get_contents('php://input');
            $decryptedData = Decrypt($encodedPostData);

            $this->data = json_decode($decryptedData, true);
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

            $checkToken = $this->DB->get('tfsysur_tokens', '*', [
                'user_id'    => $user_id,
                'token_read' => $token_read
            ]);
            if (!$checkToken) {
                $this->sendError("NO_AUTH_PHP");
            }

            $updateTokenRequest = Decrypt($this->HEADER["x-token-extra"]);
            switch ($updateTokenRequest) {
                case 'UPDATE_READ_TOKEN':
                    $token_read = $this->randString(8);
                    $this->DB->update('tfsysur_tokens', ['token_read' => $token_read], ['user_id' => $user_id]);
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

        function sendReply($data, $message = "") {
            die(json_encode(["status" => "SUCCESS", "message" => Encrypt($message), "data" => Encrypt($data), "tokens" => $this->TOKENS]));
        }
    
        function sendError($message, $data = "") {
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

    $UniREST = new UniREST();
    
?>

//{{PHP_CODE}}