<?php

require_once("../../../../../../wp-load.php");
require_once("../urapitools.php");
include_once "../../../../../plugins/tigerforge-unirest-plugin/plugins/medoo/Medoo.php";
use Medoo\Medoo;

class URBUILTINUPLOAD {

    public $DB;

    public function __construct() {

        $this->DB = new Medoo([
            'type' => 'mysql',
            'host' => 'localhost',
            'database' => 'wordpress',
            'username' => 'wpuser',
            'password' => 'wppassword',
            'charset' => 'utf8',
        ]);

    }

    public function Start() {

        if (isset($_POST['action'])) {

            if ($_POST['action'] == "UPLOAD") {
                if (isset($_FILES['file']) && isset($_POST['remotePath']) && isset($_POST['X-Token-Login'])  && isset($_POST['X-Token-ID'])) $this->upload();
            } else if ($_POST['action'] == "DELETE") {
                $this->delete();
            } else if ($_POST['action'] == "FILESLIST") {
                $this->filesList();
            } else if ($_POST['action'] == "EMPTY") {
                $this->emptyUserFolder();
            } else if ($_POST['action'] == "RENAME") {
                $this->fileRename();
            }              
    
        } else {
            $this->reply("NO_DATA");
        }

    }

    private function upload() {

        $uploadDirectory = "../../../../../uploads/unirest/assets/";
        
        $file = $_FILES['file'];

        $token_login = URAPITools::Decrypt($_POST['X-Token-Login']);
        $user_id = intval(URAPITools::Decrypt($_POST['X-Token-ID']));
        if (strlen($token_login) < 45 || $user_id <= 0) $this->reply("NO_AUTH");
        $newFileName = $_POST['newFileName'];
        $attachToUser = ($_POST['attachToUser'] == "YES") ? true : false;

        $remotePath = rtrim($_POST['remotePath'], '/');
        $remotePath = ltrim($_POST['remotePath'], '/');
        $remotePath = $user_id . "/" . $remotePath;
        
        $fileName = $newFileName;
        $uploadPath = $uploadDirectory . $remotePath . '/' . $fileName;
        $userFolder = $uploadDirectory . $remotePath;

        if (!file_exists($userFolder)) {
            mkdir($userFolder, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            if ($attachToUser) {
                $this->attachFileToUser($uploadPath, $fileName, $user_id);
            }
            $this->reply($remotePath . '/' . $fileName);
        } else {
            $this->reply("NO_UPLOAD");
        }

    }

    private function attachFileToUser($filePath, $fileName, $user_id) {

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/post.php');
    
        $filetype = wp_check_filetype(basename($fileName), null);
    
        $attachment = array(
            'guid'           => wp_upload_dir()['url'] . '/' . basename($fileName),  // URL del file
            'post_mime_type' => $filetype['type'],  // Tipo MIME
            'post_title'     => sanitize_file_name(pathinfo($fileName, PATHINFO_FILENAME)),  // Nome del file senza estensione
            'post_content'   => '',  // Nessun contenuto specifico
            'post_status'    => 'inherit',  // Stato dell'allegato
        );
    
        // Inserisci il file nella libreria multimediale
        $attachment_id = wp_insert_attachment($attachment, $filePath);
    
        // Se il file è un'immagine o richiede metadati, rigenera i metadati del file
        if (!is_wp_error($attachment_id)) {
            $attach_data = wp_generate_attachment_metadata($attachment_id, $filePath);
            wp_update_attachment_metadata($attachment_id, $attach_data);
    
            // Associa l'allegato all'utente
            update_post_meta($attachment_id, '_wp_attachment_user', $user_id);
        } else {
            $this->reply("NO_ATTACH");
        }
    }

    private function delete() {

        $user_id = $this->securityCheck();
    
        // Recupera i parametri per la cancellazione
        $fileName = $_POST['fileName'];
        $remotePath = rtrim($_POST['remotePath'], '/');
        $remotePath = ltrim($remotePath, '/');
        $remotePath = $user_id . "/" . $remotePath;
    
        $uploadDirectory = "../../../../../uploads/unirest/assets/";
        $filePath = $uploadDirectory . $remotePath . '/' . $fileName;
    
        // Verifica che il file esista
        if (!file_exists($filePath)) {
            $this->reply("FILE_NOT_FOUND");
        }
    
        // Cancella il file dal filesystem
        if (unlink($filePath)) {
            $this->deleteAttachmentFromUser($fileName, $user_id);
            $this->reply("");
        } else {
            $this->reply("DELETE_FAILED");
        }
    }
    
    private function deleteAttachmentFromUser($fileName, $user_id) {

        global $wpdb;
        $attachment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE guid LIKE %s AND post_type = 'attachment'",
            '%' . $wpdb->esc_like($fileName)
        ));
    
        // Se esiste un allegato, controlla se è collegato all'utente e, in caso positivo, eliminalo
        if ($attachment_id) {
            $attached_user_id = get_post_meta($attachment_id, '_wp_attachment_user', true);
    
            // Verifica se l'allegato è associato a questo utente
            if ($attached_user_id == $user_id) {
                wp_delete_attachment($attachment_id, true);
            }
        }
    }

    private function filesList() {

        $user_id = $this->securityCheck();
    
        // Ottieni le informazioni sulla directory di upload da WordPress
        $wpUploadDir = wp_upload_dir();
        $uploadDirectory = $wpUploadDir['basedir'] . "/unirest/assets/";
        $baseURL = $wpUploadDir['baseurl'] . "/unirest/assets/";
    
        $userDirectory = $uploadDirectory . $user_id;
    
        // Verifica se la directory utente esiste
        if (!is_dir($userDirectory)) {
            $this->reply("NO_USER_FOLDER");
            return;
        }
    
        // Funzione ricorsiva per esplorare le directory
        function exploreDirectory($directory, $urlPath) {
            $result = [
                "name" => basename($directory),
                "path" => $urlPath,
                "files" => [],
                "folders" => []
            ];
    
            $items = scandir($directory);
            foreach ($items as $item) {
                if ($item === "." || $item === "..") continue;
                $itemPath = $directory . DIRECTORY_SEPARATOR . $item;
                $itemURL = $urlPath . "/" . $item;
    
                if (is_file($itemPath)) {
                    // Aggiunge il file con nome e dimensione
                    $result["files"][] = [
                        "name" => $item,
                        "size" => filesize($itemPath)
                    ];
                } elseif (is_dir($itemPath)) {
                    // Esplora la sottodirectory
                    $result["folders"][] = exploreDirectory($itemPath, $itemURL);
                }
            }
    
            return $result;
        }
    
        // Esplora la directory utente
        $rootData = exploreDirectory($userDirectory, $baseURL . $user_id);
    
        // Risposta JSON
        $this->reply(json_encode(["root" => $rootData]));
    }

    private function emptyUserFolder() {
        
        $user_id = $this->securityCheck();
    
        // Ottieni le informazioni sulla directory di upload da WordPress
        $wpUploadDir = wp_upload_dir();
        $uploadDirectory = $wpUploadDir['basedir'] . "/unirest/assets/";
    
        $userDirectory = $uploadDirectory . $user_id;
    
        // Verifica se la directory utente esiste
        if (!is_dir($userDirectory)) {
            $this->reply("NO_USER_FOLDER");
            return;
        }
    
        // Funzione ricorsiva per cancellare il contenuto della directory
        function deleteContents($directory) {
            $items = scandir($directory);
            foreach ($items as $item) {
                if ($item === "." || $item === "..") continue;
                $itemPath = $directory . DIRECTORY_SEPARATOR . $item;
    
                if (is_file($itemPath)) {
                    // Cancella il file
                    unlink($itemPath);
                } elseif (is_dir($itemPath)) {
                    // Cancella il contenuto della sottodirectory ricorsivamente
                    deleteContents($itemPath);
                    // Cancella la sottodirectory vuota
                    rmdir($itemPath);
                }
            }
        }
    
        // Cancella il contenuto della directory utente
        deleteContents($userDirectory);
    
        // Risposta di conferma
        $this->reply("");
    }
    
    private function fileRename() {
 
        $user_id = $this->securityCheck();
    
        $uploadDirectory = "../../../../../uploads/unirest/assets/";
        $userDirectory = $uploadDirectory . $user_id;
    
        if (!is_dir($userDirectory)) {
            $this->reply("NO_USER_FOLDER");
            return;
        }
    
        // Recupera i parametri dal POST
        $originalName = $_POST['originalName'] ?? '';
        $newName = $_POST['newName'] ?? '';
    
        // Validazione di base
        if (empty($originalName) || empty($newName)) {
            $this->reply("NO_VALID_DATA");
            return;
        }
    
        // Costruisce i percorsi completi
        $originalFilePath = $userDirectory . '/' . $originalName;
        $newFilePath = $userDirectory . '/' . $newName;
    
        rename($originalFilePath, $newFilePath);
    
        $this->reply($user_id . '/' . $newName);
    }
    
    

    private function reply($message) {
        die(URAPITools::Encrypt($message));
    }

    private function securityCheck() {
        $token_login = URAPITools::Decrypt($_POST['X-Token-Login']);
        $user_id = intval(URAPITools::Decrypt($_POST['X-Token-ID']));
        if (strlen($token_login) < 45 || $user_id <= 0) $this->reply("NO_AUTH");

        return $user_id;
    }

}

$urBuiltinUpload = new URBUILTINUPLOAD();
$urBuiltinUpload->Start();

?>
