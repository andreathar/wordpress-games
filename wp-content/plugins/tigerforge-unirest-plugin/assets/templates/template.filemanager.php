<?php

require_once("../../../../../../wp-load.php");
require_once("../urapitools.php");

class URFILEMANAGER {

    public function Start() {
        if (isset($_POST['action'])) {
            $user_id = $this->securityCheck(); // Controllo di sicurezza e recupero dell'ID utente

            switch ($_POST['action']) {
                case "SAVE":
                    $this->save($user_id);
                    break;
                case "OPEN":
                    $this->open($user_id);
                    break;
                case "DELETE":
                    $this->delete($user_id);
                    break;
                case "RENAME":
                    $this->rename($user_id);
                    break;
                case "EXISTS":
                    $this->exists($user_id);
                    break;
                case "SIZE":
                    $this->size($user_id);
                    break;
                case "LIST":
                    $this->listFiles($user_id);
                    break;
                default:
                    URAPITools::sendError("INVALID_ACTION");
            }
        } else {
            URAPITools::sendError("NO_ACTION_PROVIDED");
        }
    }

    private function save($user_id) {
        $remotePath = $this->getUserPath($user_id);
        $fileName = $_POST['fileName'] ?? '';
        $content = $_POST['content'] ?? '';

        if (empty($fileName) || empty($content)) {
            URAPITools::sendError("NO_VALID_DATA");
        }

        $filePath = $remotePath . '/' . $fileName;

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        file_put_contents($filePath, $content);
        URAPITools::sendReply("");
    }

    private function open($user_id) {
        $remotePath = $this->getUserPath($user_id);
        $fileName = $_POST['fileName'] ?? '';

        if (empty($fileName)) {
            URAPITools::sendError("NO_FILE_PROVIDED");
        }

        $filePath = $remotePath . '/' . $fileName;

        if (!file_exists($filePath)) {
            URAPITools::sendError("FILE_NOT_FOUND");
        }

        $content = file_get_contents($filePath);
        URAPITools::sendReply($content);
    }

    private function delete($user_id) {
        $remotePath = $this->getUserPath($user_id);
        $fileName = $_POST['fileName'] ?? '';

        if (empty($fileName)) {
            URAPITools::sendError("NO_FILE_PROVIDED");
        }

        $filePath = $remotePath . '/' . $fileName;

        if (file_exists($filePath)) {
            unlink($filePath);
            URAPITools::sendReply("");
        } else {
            URAPITools::sendError("FILE_NOT_FOUND");
        }
    }

    private function rename($user_id) {
        $remotePath = $this->getUserPath($user_id);
        $oldName = $_POST['oldName'] ?? '';
        $newName = $_POST['newName'] ?? '';

        if (empty($oldName) || empty($newName)) {
            URAPITools::sendError("NO_VALID_DATA");
        }

        $oldFilePath = $remotePath . '/' . $oldName;
        $newFilePath = $remotePath . '/' . $newName;

        if (!file_exists($oldFilePath)) {
            URAPITools::sendError("FILE_NOT_FOUND");
        }

        rename($oldFilePath, $newFilePath);
        URAPITools::sendReply("");
    }

    private function exists($user_id) {
        $remotePath = $this->getUserPath($user_id);
        $fileName = $_POST['fileName'] ?? '';

        if (empty($fileName)) {
            URAPITools::sendError("NO_FILE_PROVIDED");
        }

        $filePath = $remotePath . '/' . $fileName;
        $exists = file_exists($filePath) ? "TRUE" : "FALSE";

        URAPITools::sendReply($exists);
    }

    private function size($user_id) {
        $remotePath = $this->getUserPath($user_id);
        $fileName = $_POST['fileName'] ?? '';

        if (empty($fileName)) {
            URAPITools::sendError("NO_FILE_PROVIDED");
        }

        $filePath = $remotePath . '/' . $fileName;

        if (!file_exists($filePath)) {
            URAPITools::sendError("FILE_NOT_FOUND");
        }

        $size = filesize($filePath);
        URAPITools::sendReply((string)$size);
    }

    private function listFiles($user_id) {
        $remotePath = $this->getUserPath($user_id);

        if (!is_dir($remotePath)) {
            URAPITools::sendError("NO_USER_FOLDER");
        }

        $files = scandir($remotePath);
        $fileList = [];

        foreach ($files as $file) {
            if ($file === "." || $file === "..") continue;

            $filePath = $remotePath . '/' . $file;

            if (is_file($filePath)) {
                $fileList[] = [
                    "name" => $file,
                    "size" => filesize($filePath)
                ];
            }
        }

        URAPITools::sendReply(json_encode($fileList));
    }

    private function getUserPath($user_id) {
        $wpUploadDir = wp_upload_dir();
        $baseDirectory = $wpUploadDir['basedir'] . "/unirest/assets/" . $user_id;
        $remotePath = rtrim($_POST['remotePath'] ?? '', '/');
        return $baseDirectory . '/' . ltrim($remotePath, '/');
    }

    private function securityCheck() {
        $token_login = URAPITools::Decrypt($_POST['X-Token-Login']);
        $user_id = intval(URAPITools::Decrypt($_POST['X-Token-ID']));
        if (strlen($token_login) < 45 || $user_id <= 0) URAPITools::sendError("NO_AUTH");

        return $user_id;
    }
}

$urFileManager = new URFILEMANAGER();
$urFileManager->Start();

?>
