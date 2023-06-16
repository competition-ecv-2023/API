<?php
// to create user :
//      api.ext/v1/users

// to get login user :
//      api.ext/v1/users/login

if (!isset($_GET['user_id'])) {
    if (isset($_GET['login'])) {
        require $_SERVER['DOCUMENT_ROOT'].'/v1/routes/POST/users/login.php';
    } else {
        require $_SERVER['DOCUMENT_ROOT'].'/v1/routes/POST/users/create.php';
    }
} else {
    header("HTTP/1.1 404 Not Found");
}