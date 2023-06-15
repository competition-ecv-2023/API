<?php
include $_SERVER['DOCUMENT_ROOT'].'/v1/src/Database/Users.php';
use \Database\Users;

// Create users
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['passwordToVerify']) && isset($_POST['email'])) {
    switch(Users::register($_POST['username'], $_POST['password'], $_POST['passwordToVerify'], $_POST['email'])) {
        case 0:
            // Register done
            header("HTTP/1.1 200 OK");
            header("X-Message: Compte cree");
            break;
        case 1:
            // Exception
            header("HTTP/1.1 500 Internal Server Error");
            header("X-Error-Message: Une erreur s'est produite [Re_1eIn]");
            break;
        case 2:
            // SQL error
            header("HTTP/1.1 500 Internal Server Error");
            header("X-Error-Message: Une erreur s'est produite [Re_2eIn]");
            break;
        case 3:
            // Username don't match the regex
            header("HTTP/1.1 400 Bad Request");
            header("X-Error-Message: Le pseudo choisit n'est pas valide");
            header("X-Error-Field: username");
            break;
        case 4:
            // Password don't match the regex
            header("HTTP/1.1 400 Bad Request");
            header("X-Error-Message: Les mots de passe ne sont pas conformes");
            header("X-Error-Field: password");
            break;
        case 5:
            // Passwords don't match each other
            header("HTTP/1.1 400 Bad Request");
            header("X-Error-Message: Les mots de passe ne correspondent pas");
            header("X-Error-Field: passwordToVerify");
            break;
        case 6:
            // Email not valid
            header("HTTP/1.1 400 Bad Request");
            header("X-Error-Message: L'adresse email n'est pas valide");
            header("X-Error-Field: email");
            break;
        case 7:
            // Email already used
            header("HTTP/1.1 409 Conflict");
            header("X-Error-Message: L'adresse email est déjà utilisée par un autre compte");
            header("X-Error-Field: email");
            break;
        case 8:
            // Username already used
            header("HTTP/1.1 409 Conflict");
            header("X-Error-Message: Le pseudo est déjà utilisé par un autre compte");
            header("X-Error-Field: username");
            break;
        default;
            // error handler, something weird had occurred
            header("HTTP/1.1 500 Internal Server Error");
            header("X-Error-Message: Une erreur s'est produite [Re_?Def]");
            break;
    }        
} else {
    // missings data
    header("HTTP/1.1 400 Bad Request");
    header("X-Error-Message: Des champs sont manquants");
}