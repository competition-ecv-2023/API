<?php
include $_SERVER['DOCUMENT_ROOT'].'/v1/src/Database/SQLManager.php';
use \Database\SQLManager;

// Print adverts
if (!isset($_GET['advert_id'])) {
    echo json_encode(SQLManager::findAll('*','adverts'));
} else {
    echo json_encode(SQLManager::findBy('*','adverts','id = :id', array(':id' => $_GET['advert_id'])));
}