<?php

    require_once("../../../../../../wp-load.php");
    include_once "../../../../../plugins/tigerforge-unirest-plugin/plugins/medoo/Medoo.php";
    require_once("../urapitools.php");
    use Medoo\Medoo;

    class URBUILTINUSER {

        public $DB;
        public $DATA;

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

            if ($action == "USER_LOGIN") {
                $this->user_login();
            } else if ($action == "USER_REGISTRATION") {
                $this->user_registration();
            } else if ($action == "USER_UPDATE") {
                $this->user_update();
            } else if ($action == "USER_OTP_GET" || $action == "USER_OTP_SET") {
                $this->user_OTP($action);
            } else if ($action == "USER_LIST") {
                $this->user_list();
            } else {
                URAPITools::sendError("UNKNOWN_ACTION");
            }

        }

        function user_login() {

            $creds = array(
                'user_login'    => $this->DATA["user_login"],
                'user_password' => $this->DATA["user_pass"],
            );
        
            $user = wp_signon($creds, false);
        
            if (is_wp_error($user)) {
                URAPITools::sendError($user->get_error_message());
            } else {
    
                $updateToken = URAPITools::Decrypt(URAPITools::getHeader("x-token-extra"));

                $microtime = microtime(true);
                $token_login = URAPITools::randString(32) . round($microtime * 1000);

                $token_read = URAPITools::randString(8);
                $token_write = URAPITools::randString(8);
                $token_update = URAPITools::randString(8);
                $token_delete = URAPITools::randString(8);
    
                $DB = $this->DB;
                $token_exists = $DB->get('tfsysur_tokens', '*', [
                    'user_id' => $user->ID
                ]);

                // TOKENS
                // - Se i Token non esistono, vengono creati in tfsysur_tokens
                // - Se i Token esistono: il Login Token viene cambiato SOLO se richiesto. Altrimenti, NESSUN Token cambia.
        
                if ($token_exists) {
                    if ($updateToken == "UPDATE_LOGIN_TOKEN") {
                        $DB->update("tfsysur_tokens", ['token_login' => $token_login], ['user_id' => $user->ID]);
                    } else {
                        $token_login = $token_exists["token_login"];
                    }
                    $token_read = $token_exists["token_read"];
                    $token_write = $token_exists["token_write"];
                    $token_update = $token_exists["token_update"];
                    $token_delete = $token_exists["token_delete"];
                } else {
                    $DB->insert("tfsysur_tokens", [
                        'user_id'     => $user->ID,
                        'token_login' => $token_login,
                        'token_read'  => $token_read,
                        'token_write' => $token_write,
                        'token_update'=> $token_update,
                        'token_delete'=> $token_delete
                    ]);
                }
    
                $user_info = array(
                    'ID'            => $user->ID ?? 0,
                    'user_pass'     => '',
                    'user_login'    => $user->user_login ?? '',
                    'user_nicename' => $user->user_nicename ?? '',
                    'user_url'      => $user->user_url ?? '',
                    'user_email'    => $user->user_email ?? '',
                    'display_name'  => $user->display_name ?? '',
                    'nickname'      => $user->nickname ?? '',
                    'first_name'    => get_user_meta($user->ID, 'first_name', true) ?? '',
                    'last_name'     => get_user_meta($user->ID, 'last_name', true) ?? '',
                    'description'   => get_user_meta($user->ID, 'description', true) ?? '',
                    'role'          => implode(', ', $user->roles ?? []),
                    'token_login'   => $token_login,
                    'token_read'    => $token_read,
                    'token_write'   => $token_write,
                    'token_update'  => $token_update,
                    'token_delete'  => $token_delete
                );
        
                URAPITools::sendReply(json_encode($user_info));
            }
        }

        function user_registration() {

            $data = $this->DATA;
            $user_data = array(
                'user_pass'       => $data['user_pass'] ?? 'pass123',
                'user_login'      => $data['user_login'] ?? '',
                'user_nicename'   => $data['user_nicename'] ?? '',
                'user_url'        => $data['user_url'] ?? '',
                'user_email'      => $data['user_email'] ?? '',
                'display_name'    => $data['display_name'] ?? '',
                'nickname'        => $data['nickname'] ?? '',
                'first_name'      => $data['first_name'] ?? '',
                'last_name'       => $data['last_name'] ?? '',
                'description'     => $data['description'] ?? '',
                'role'            => $data['role'] ?? 'subscriber',
            );
            
            $user_id = wp_insert_user($user_data);
            
            if (is_wp_error($user_id)) {
                URAPITools::sendError($user_id->get_error_message());
            } else {
                URAPITools::sendReply("$user_id");
            }
        }

        function user_update() {

            $data = $this->DATA;
        
            if (!isset($data['ID']) || empty($data['ID'])) {
                URAPITools::sendError("User ID is mandatory.");
            }
        
            $user_data = array('ID' => $data['ID']);
        
            if ($data['user_login'] !== '') {
                $user_data['user_login'] = $data['user_login'] === '<EMPTY>' ? '' : $data['user_login'];
            }
            if ($data['user_nicename'] !== '') {
                $user_data['user_nicename'] = $data['user_nicename'] === '<EMPTY>' ? '' : $data['user_nicename'];
            }
            if ($data['user_url'] !== '') {
                $user_data['user_url'] = $data['user_url'] === '<EMPTY>' ? '' : $data['user_url'];
            }
            if ($data['user_email'] !== '') {
                $user_data['user_email'] = $data['user_email'] === '<EMPTY>' ? '' : $data['user_email'];
            }
            if ($data['display_name'] !== '') {
                $user_data['display_name'] = $data['display_name'] === '<EMPTY>' ? '' : $data['display_name'];
            }
            if ($data['nickname'] !== '') {
                $user_data['nickname'] = $data['nickname'] === '<EMPTY>' ? '' : $data['nickname'];
            }
            if ($data['first_name'] !== '') {
                $user_data['first_name'] = $data['first_name'] === '<EMPTY>' ? '' : $data['first_name'];
            }
            if ($data['last_name'] !== '') {
                $user_data['last_name'] = $data['last_name'] === '<EMPTY>' ? '' : $data['last_name'];
            }
            if ($data['description'] !== '') {
                $user_data['description'] = $data['description'] === '<EMPTY>' ? '' : $data['description'];
            }
            if ($data['role'] !== '') {
                $user_data['role'] = $data['role'] === '<EMPTY>' ? 'subscriber' : $data['role'];
            }
        
            if ($data['user_pass'] !== '' && $data['user_pass'] !== '<EMPTY>') {
                $user_data['user_pass'] = $data['user_pass'];
            }

            $user_id = wp_update_user($user_data);
        
            if (is_wp_error($user_id)) {
                URAPITools::sendError($user_id->get_error_message());
            } else {
                URAPITools::sendReply("$user_id");
            }
        }

        function user_OTP($action) {
            global $wpdb;
            $data = $this->DATA;
        
            $username = $data['user_login'] ?? null;
            $email = $data['user_email'] ?? null;
            $newPassword = $data['user_pass'] ?? null;
        
            $users_table = $wpdb->prefix . 'users'; // Tabella utenti di WordPress
        
            if ($action == "USER_OTP_GET") {
                
                // CREAZIONE OTP
    
                $OTP = wp_generate_password(32, false, false); // Token alfanumerico casuale
        
                // Costruisci la query di ricerca
                $where = [];
                if (!empty($username)) {
                    $where[] = $wpdb->prepare("user_login = %s", $username);
                }
                if (!empty($email)) {
                    $where[] = $wpdb->prepare("user_email = %s", $email);
                }
        
                if (empty($where)) {
                    URAPITools::sendError("NO_USER_DATA");
                    return;
                }
        
                // Trova l'utente
                $where_sql = implode(" AND ", $where);
                $user = $wpdb->get_row("SELECT ID FROM $users_table WHERE $where_sql");
        
                if (!$user) {
                    URAPITools::sendError("NO_USER_FOUND");
                    return;
                }
        
                // Salva il token nella colonna user_pass
                $update_result = $wpdb->update(
                    $users_table,
                    ['user_pass' => $OTP], // Scrive il token in user_pass
                    ['ID' => $user->ID]      // Condizione: ID dell'utente trovato
                );
        
                if ($update_result === false) {
                    URAPITools::sendError("OTP_ERROR");
                } else {
                    URAPITools::sendReply(json_encode(["ID" => $user->ID, "OTP" => $OTP]));
                }
    
            } else if ($action == "USER_OTP_SET") {
    
                // CAMBIO PASSWORD VIA OTP

                $OTP = URAPITools::Decrypt(URAPITools::getHeader("x-token-extra"));
    
                if (empty($newPassword) || empty($OTP)) {
                    URAPITools::sendError("NO_REQUIRED_DATA");
                    return;
                }

                $decoded_json = base64_decode($OTP);
                $data = json_decode($decoded_json, true);

                if (!isset($data['ID']) || !isset($data['OTP'])) {
                    URAPITools::sendError("INVALID_OTP_DATA");
                    return;
                }

                $user_id = $data['ID'];
                $otp_code = $data['OTP'];
               
                // Verifica che l'OPT esista
                $user = $wpdb->get_row($wpdb->prepare("SELECT ID FROM $users_table WHERE ID = %d AND user_pass = %s", $user_id, $otp_code));
        
                if (!$user) {
                    URAPITools::sendError("INVALID_OTP");
                    return;
                }
        
                // Aggiorna la password dell'utente
                $hashed_password = wp_hash_password($newPassword); // Hash della nuova password
                $update_result = $wpdb->update(
                    $users_table,
                    ['user_pass' => $hashed_password],
                    ['ID' => $user->ID]        
                );
        
                if ($update_result === false) {
                    URAPITools::sendError("UPDATE_ERROR");
                } else {
                    URAPITools::sendReply("OK");
                }
            } else {
                URAPITools::sendError("UNKNOWN_ACTION");
            }
        }

        function user_list() {
            global $wpdb;
        
            // Recupera tutti gli utenti
            $users = get_users();
        
            // Struttura dei dati utente
            $user_list = [];
        
            foreach ($users as $user) {
                $user_list[] = [
                    'ID'            => $user->ID,
                    'user_pass'     => '',
                    'user_login'    => $user->user_login ?? '',
                    'user_nicename' => $user->user_nicename ?? '',
                    'user_url'      => $user->user_url ?? '',
                    'user_email'    => $user->user_email ?? '',
                    'display_name'  => $user->display_name ?? '',
                    'nickname'      => $user->nickname ?? '',
                    'first_name'    => get_user_meta($user->ID, 'first_name', true) ?? '',
                    'last_name'     => get_user_meta($user->ID, 'last_name', true) ?? '',
                    'description'   => get_user_meta($user->ID, 'description', true) ?? '',
                    'role'          => implode(', ', $user->roles ?? [])
                ];
            }
        
            // Restituisci l'elenco degli utenti
            URAPITools::sendReply(json_encode($user_list));
        }
        


        
    }


    $urBuiltinUser = new URBUILTINUSER();
    $urBuiltinUser->Start();
    
?>