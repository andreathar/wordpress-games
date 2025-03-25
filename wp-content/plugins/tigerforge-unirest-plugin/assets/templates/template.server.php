<?php

    require_once("../../../../../../wp-load.php");
    require_once("../urapitools.php");
    
    $data = URAPITools::getPostDecryptedJSON();

    $sha256Key1 = $data["code"];
    $result = URAPITools::sha256TokenCheck($sha256Key1);

    if ($result) {
        URAPITools::sendReply("OK");
    }
    else {
        URAPITools::sendError("NO_AUTH");
    }
    

?>