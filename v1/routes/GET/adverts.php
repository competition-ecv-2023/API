<?php
include $_SERVER['DOCUMENT_ROOT'].'/v1/src/Database/SQLManager.php';
use \Database\SQLManager;

// to get adverts :
//      api.ext/v1/adverts
//      api.ext/v1/adverts&page=1

// to get an advert :
//      api.ext/v1/adverts/$id

// Print adverts
if (isset($_GET['advert_id'])) {
    echo json_encode(SQLManager::findBy('*','adverts','id = :id', array(':id' => $_GET['advert_id'])));
} else if (!isset($_GET['page'])) {
    // Récupère les 10 derniers ID
    echo json_encode(SQLManager::findAll('*','adverts ORDER BY id DESC LIMIT 10'));
} else if (intval($_GET['page'])) {
    // Récupère les 10 x $_GET['page'] sans prendre ceux des pages précédentes
    $page_number = ($_GET['page'] - 1) * 10;
    echo json_encode(SQLManager::findAll('*','adverts ORDER BY id DESC LIMIT '.$page_number.', 10'));
} else {
    // le numéro de page n'est pas un nombre entier
    header("HTTP/1.1 409 Conflict");
    header("X-Error-Message: Le numero de page n'est pas un nombre entier");
}