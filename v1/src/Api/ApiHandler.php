<?php
namespace Api;
include $_SERVER['DOCUMENT_ROOT'].'/src/Api/GetHandler.php';
include $_SERVER['DOCUMENT_ROOT'].'/src/Api/PostHandler.php';
include $_SERVER['DOCUMENT_ROOT'].'/src/Api/PutHandler.php';
include $_SERVER['DOCUMENT_ROOT'].'/src/Api/DeleteHandler.php';
use \Api\GetHandler;
use \Api\PostHandler;
use \Api\PutHandler;
use \Api\DeleteHandler;

class ApiHandler {
    public static function handler(
        $request_method,
        $action
    ) {
        switch($request_method) {
            case "GET":

                break;
            case "POST":
                break;
            case "DELETE":
                break;
            case "PUT":
                break;
            default:
                header("HTTP/1.1 404 Not Found");
                break;                
        }
    }
}