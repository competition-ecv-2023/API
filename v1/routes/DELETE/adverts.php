<?php
include $_SERVER['DOCUMENT_ROOT'].'/v1/src/Database/Adverts.php';
use \Database\Adverts;

// Delete adverts
if (isset($_GET['advert_id'])) {
    /** 
     * - 0 si l'annonce a été supprimée avec succès
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors de l'exécution de la requête SQL
     * - 3 si l'annonce n'existe pas
     */
    switch(Adverts::delete($_GET['advert_id'])) {
        case 0:
            // Register done
            header("HTTP/1.1 200 OK");
            header("X-Message: Annonce supprimee");
            break;
        case 1:
            // Exception
            header("HTTP/1.1 500 Internal Server Error");
            header("X-Error-Message: Une erreur s'est produite [AdD_1eIn]");
            break;
        case 2:
            // SQL error
            header("HTTP/1.1 500 Internal Server Error");
            header("X-Error-Message: Une erreur s'est produite [AdD_2eIn]");
            break;
        case 3:
            // Username don't match the regex
            header("HTTP/1.1 400 Bad Request");
            header("X-Error-Message: L'annonc n'existe pas");
            break;
        default;
            // error handler, something weird had occurred
            header("HTTP/1.1 500 Internal Server Error");
            header("X-Error-Message: Une erreur s'est produite [AdD_?Def]");
            break;
    }        
} else {
    // missings data
    header("HTTP/1.1 400 Bad Request");
    header("X-Error-Message: Des champs sont manquants");
}