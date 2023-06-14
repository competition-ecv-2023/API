<?php
require 'vendor/autoload.php';
require 'v1/config/settings.php';
require 'v1/src/Api/ApiHandler.php';

use Api\ApiHandler;

$request_method = $_SERVER["REQUEST_METHOD"]; //GET, POST, PUT, DELETE, etc...
$allowed_methods = ["GET", "POST", "PUT", "DELETE"];

if (isset($_GET) && !empty($_GET)) {
    switch($_GET) {
        case "page":
                if($_GET['page'] == "swagger") {

                } else {
                    header("HTTP/1.1 404 Not Found");
                }
            break;
        case "api":
            if (!in_array($request_method, $allowed_methods)) {
                header("HTTP/1.1 404 Not Found");
            } else {
                ApiHandler::handler($request_method, $_GET['api']);
            }
            break;
    }
}