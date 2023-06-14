<?php
require 'vendor/autoload.php';
require 'v1/config/settings.php';

use Api\ApiHandler;

$request_method = $_SERVER["REQUEST_METHOD"]; //GET, POST, PUT, DELETE, etc...

$headerToken = '';
// Vérification du bearer token
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'];
    if (strpos($authorizationHeader, 'Bearer') === 0) {
        // Pour obtenir seulement le token
        $headerToken = substr($authorizationHeader, 7);
    }
}

$allowed_methods = ["GET", "POST", "PUT", "DELETE"];

if ($apiToken === $headerToken) {
    if (isset($_GET) && !empty($_GET)) {
        if (isset($_GET["page"]) && $_GET['page'] == "swagger") {
            if($_GET['page'] == "swagger") {
            } else {
                header("HTTP/1.1 404 Not Found");
            }
        } else if (isset($_GET["api"]) && in_array($request_method, $allowed_methods)) {
            if(file_exists($_SERVER['DOCUMENT_ROOT'].'/v1/routes/'.$request_method.'/'.$_GET['api'].'.php')) {
                require $_SERVER['DOCUMENT_ROOT'].'/v1/routes/'.$request_method.'/'.$_GET['api'].'.php';
            } else {
                header("HTTP/1.1 404 Not Found");
            }
        } else {
            header("HTTP/1.1 404 Not Found");
        }
    } else {
        header("HTTP/1.1 404 Not Found");
    }
} else {
    header("HTTP/1.1 403 Forbidden");
}