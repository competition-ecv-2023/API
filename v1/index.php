<?php
require 'vendor/autoload.php';
require 'v1/config/settings.php';

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
            } else if(file_exists($_SERVER['DOCUMENT_ROOT'].'/v1/routes/'.$request_method.'/'.$_GET['API'].'.php')) {
                require $_SERVER['DOCUMENT_ROOT'].'/v1/routes/'.$request_method.'/'.$_GET['API'].'.php';
            }
            break;
    }
} else {
    header("HTTP/1.1 404 Not Found");
}