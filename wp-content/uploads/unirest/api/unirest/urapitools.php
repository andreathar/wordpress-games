<?php

class URAPITools
{

    public static function getJSONdata()
    {
        $data = isset($_POST['data_to_send']) ? stripslashes($_POST['data_to_send']) : '';
        if ($data == "") {
            $results['result'] = "ERROR";
            $results['error'] = "NO_DATA";
            wp_send_json_success(json_encode($results));
            die();
        }

        return json_decode($data);
    }

    public static function getData()
    {
        $data = isset($_POST['data_to_send']) ? stripslashes($_POST['data_to_send']) : '';
        if ($data == "") {
            $results['result'] = "ERROR";
            $results['error'] = "NO_DATA";
            wp_send_json_success(json_encode($results));
            die();
        }

        return $data;
    }

    public static function getDB() {
        global $UNIREST_DB;
        return $UNIREST_DB;
    }

    public static function send($results) {
        wp_send_json_success(json_encode($results));
    }

    public static function mkdir($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public static function getHeader($key = null) {
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        return ($key != null) ? $headers[$key] : $headers;
    }

    public static function Encrypt($plaintext) {
        if ($plaintext == "") return ""; 
        global $KEYS;
        $key1 = "4njTUyUrn4vqAR0sjyKHSzMmbNKB1qWJ";
        $key2 = "vW2iRGdbrmNpm84A";
        return base64_encode(openssl_encrypt($plaintext, 'aes-256-cbc', $key1, OPENSSL_RAW_DATA, $key2));
    }
    
    public static function Decrypt($encrypted) {
        if ($encrypted == "") return ""; 
        global $KEYS;
        $key1 = "4njTUyUrn4vqAR0sjyKHSzMmbNKB1qWJ";
        $key2 = "vW2iRGdbrmNpm84A";
        return openssl_decrypt(base64_decode($encrypted), 'aes-256-cbc', $key1, OPENSSL_RAW_DATA, $key2);
    }

    public static function sha256TokenCheck($hashedText) {
        $hashedPlainText = hash('sha256', "4njTUyUrn4vqAR0sjyKHSzMmbNKB1qWJ");
        return hash_equals($hashedPlainText, $hashedText);
    }

    public static function sha256Check($plainText, $hashedText) {
        $hashedPlainText = hash('sha256', $plainText);
        return hash_equals($hashedPlainText, $hashedText);
    }

    public static function getPostDecryptedJSON() {
        $encodedPostData = file_get_contents('php://input');
        $decryptedData = self::Decrypt($encodedPostData);
        $jsonObject = json_decode($decryptedData, true);
        return $jsonObject;
    }

    public static function sendReply($data, $message = "") {
        die(json_encode(["status" => "SUCCESS", "message" => self::Encrypt($message), "data" => self::Encrypt($data)]));
    }

    public static function sendError($message, $data = "") {
        die(json_encode(["status" => "ERROR", "message" => self::Encrypt($message), "data" => self::Encrypt($data)]));
    }

    public static function randString($l) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters) - 1;
        $key1 = "";
        for ($i = 0; $i < $l; $i++) {
            $key1 .= $characters[rand(0, $charactersLength)];
        }
        return $key1;
    }

    public static function templateFile($fileName, $replaceData, $destinationFile) {

        $fileContent = file_get_contents($fileName);
        foreach ($replaceData as $marker => $value) {
            if (strpos($marker, '*') === 0) {
                $cleanMarker = ltrim($marker, '*');
                $fileContent = str_replace($cleanMarker, $value, $fileContent);
            } else {
                $fileContent = str_replace('{{' . $marker . '}}', $value, $fileContent);
            }
        }
    
        if ($destinationFile !== null) {
            file_put_contents($destinationFile, $fileContent);
        }
    
        return $fileContent;
    }
    
    public static function copyFile($fileName, $destinationFolder, $newFileName = null, $createDestinationFolder = false) {

        if ($newFileName === null) {
            $newFileName = basename($fileName);
        }

        if ($createDestinationFolder) {
            if (!is_dir($destinationFolder)) {
                mkdir($destinationFolder, 0755, true);
            }
        }        
    
        $destinationPath = rtrim($destinationFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newFileName;
    
        copy($fileName, $destinationPath);

        return $destinationPath;
    }

}

