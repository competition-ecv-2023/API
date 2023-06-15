<?php
include $_SERVER['DOCUMENT_ROOT'].'/v1/src/Database/Users.php';
use \Database\Users;
use \Database\SQLManager;

// Login user
if (isset($_POST['email']) && isset($_POST['passwordToVerify'])) {
    // Récupère l'identifiant de l'utilisateur à partir de son adresse email
    $columns = 'id';
    $from = 'users';
    $where = 'email = :email';
    $params = array(
        ':email' => $_POST['email']
    );
    $error = 0;
    $result = 0;
    $data = SQLManager::findBy($columns, $from, $where, $params);
    if ($data == false && !isset($data['empty'])) {
        // Si aucun compte n'a cette adresse email
        $error = 1;
    } else {
        $userId = $data['id'];
    }

    switch(Users::login($_POST['email'], $_POST['passwordToVerify'])) {
        case 0:
            // User now logged
            header("HTTP/1.1 200 OK");
            header("X-Message: Connecte");
            echo '[{"userToken":"'.$_SESSION["user"]["token"].',"id":'.$userId.'}]';
            break;
        case 1:
            // Error with the SQL
            header("HTTP/1.1 500 Internal Server Error");
            header("X-Error-Message: Une erreur s'est produite [Lo_1eIn]");
            break;
        case 2:
            //Error with the SQL
            header("HTTP/1.1 500 Internal Server Error");
            header("X-Error-Message: Une erreur s'est produite [Lo_2eIn]");
            break;
        case 3:
            //Bad credentials or account not created
            header("HTTP/1.1 401 Unauthorized");
            header("X-Error-Message: Adresse email ou mot de passe incorrect");
            break;
        default:
            // error handler, something weird had occurred
            header("HTTP/1.1 500 Internal Server Error");
            header("X-Error-Message: Une erreur s'est produite [Lo_?Def]");
            break;
    }

    if ($error == 0 && (isset($userId) && !empty($userId))) {
        $table = 'login_attempts';
        $into = '(user_id, ip_address, attempt_result)';
        $values = '(:user_id, :ip_address, :attempt_result)';
        $params = array(
            ':user_id' => $userId,
            ':ip_address' => $_SERVER['REMOTE_ADDR'],
            ':attempt_result' => $result
        );
        // Met à jour la date de dernière connexion dans la base de données
        if (!SQLManager::insertInto($table, $into, $values, $params)) {
            error_log("[controller/login.php] - SQL login_attempts error : table($table), into($into), values($values), params{:user_id($userId), :ip_address(".$_SERVER['REMOTE_ADDR']."), :attempt_result($result)}", 0);
        }
    }
} else {
    // missings data
    header("HTTP/1.1 400 Bad Request");
    header("X-Error-Message: Des champs sont manquants");
}