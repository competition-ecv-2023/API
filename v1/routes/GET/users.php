<?php
include $_SERVER['DOCUMENT_ROOT'].'/v1/src/Database/SQLManager.php';
use \Database\SQLManager;

if (!isset($_GET['user_id'])) {
    echo SQLManager::findAll('*','users');
}