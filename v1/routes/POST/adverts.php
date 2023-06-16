<?php
include $_SERVER['DOCUMENT_ROOT'].'/v1/src/Database/Adverts.php';
use \Database\Adverts;

// Create users
if (isset($_POST['user_id']) && isset($_POST['title']) && isset($_POST['description']) && isset($_POST['is_premium']) && isset($_POST['is_google_ads']) && isset($_POST['latitude']) && isset($_POST['longitude']) && isset($_POST['city']) && isset($_POST['images'])) {
    /**
     * - 0 si l'annonce a été créée avec succès
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors de l'exécution de la requête SQL ou de la base de données
     * - 3 si le titre de l'annonce n'est pas conforme
     * - 4 si la description de l'annonce n'est pas conforme
     * - 5 si une erreur s'est produite lors de l'enregistrement d'une image associée à l'annonce
     */
    switch(Adverts::create($_POST['user_id'], $_POST['title'], $_POST['description'], $_POST['is_premium'], $_POST['is_google_ads'], $_POST['latitude'], $_POST['longitude'], $_POST['city'], $_POST['images'])) {
        case 0:
            // Register done
            header("HTTP/1.1 200 OK");
            header("X-Message: Annonce creee");
            break;
        case 1:
            // Exception
            header("HTTP/1.1 500 Internal Server Error");
            header("X-Error-Message: Une erreur s'est produite [Ad_1eIn]");
            break;
        case 2:
            // SQL error
            header("HTTP/1.1 500 Internal Server Error");
            header("X-Error-Message: Une erreur s'est produite [Ad_2eIn]");
            break;
        case 3:
            // Username don't match the regex
            header("HTTP/1.1 400 Bad Request");
            header("X-Error-Message: Titre de l'annonce non conforme");
            break;
        case 4:
            // Password don't match the regex
            header("HTTP/1.1 400 Bad Request");
            header("X-Error-Message: Description de l'annonce non conforme");
            break;
        case 5:
            // Passwords don't match each other
            header("HTTP/1.1 400 Bad Request");
            header("X-Error-Message: Un probleme lors de l'enregistrement des images est survenu");
            break;
        default;
            // error handler, something weird had occurred
            header("HTTP/1.1 500 Internal Server Error");
            header("X-Error-Message: Une erreur s'est produite [Ad_?Def]");
            break;
    }        
} else {
    // missings data
    header("HTTP/1.1 400 Bad Request");
    header("X-Error-Message: Des champs sont manquants");
}