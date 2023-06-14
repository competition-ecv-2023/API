<?php
include $_SERVER['DOCUMENT_ROOT'].'/v1/src/Database/SQLManager.php';
use \Database\SQLManager;

// Print users
if (!isset($_GET['user_id'])) {
    echo json_encode(SQLManager::findAll('*','users'));
} else {
    echo json_encode(SQLManager::findBy('*','users','id = :id', array(':id' => $_GET['user_id'])));
}