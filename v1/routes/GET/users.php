<?php
include $_SERVER['DOCUMENT_ROOT'].'/v1/src/Database/SQLManager.php';
use \Database\SQLManager;

// to get users :
//      api.ext/v1/users

// to get a user :
//      api.ext/v1/users/$id

// Print users
if (!isset($_GET['user_id'])) {
    echo json_encode(SQLManager::findAll('id,username,email,is_verified,token','users'));
} else {
    echo json_encode(SQLManager::findBy('id,username,email,is_verified,token','users','id = :id', array(':id' => $_GET['user_id'])));
}