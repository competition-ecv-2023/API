<?php
require 'vendor/autoload.php';
require 'v1/config/settings.php';

use Api\ApiHandler;

$request_method = $_SERVER["REQUEST_METHOD"]; //GET, POST, PUT, DELETE, etc...
$headerToken = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
$allowed_methods = ["GET", "POST", "PUT", "DELETE"];

if ($apiToken === $headerToken) {
    if (isset($_GET) && !empty($_GET)) {
        if (isset($_GET["page"]) && $_GET['page'] == "swagger") {
            if($_GET['page'] == "swagger") {
            }
        } else if (isset($_GET["api"]) && in_array($request_method, $allowed_methods)) {
            if(file_exists($_SERVER['DOCUMENT_ROOT'].'/v1/routes/'.$request_method.'/'.$_GET['api'].'.php')) {
                require $_SERVER['DOCUMENT_ROOT'].'/v1/routes/'.$request_method.'/'.$_GET['api'].'.php';
            }
        }
    }
}
header("HTTP/1.1 404 Not Found");