<?php
namespace Database;
use \PDO;
use \Exception;

include $_SERVER['DOCUMENT_ROOT'].'/v1/src/Database/SQLManager.php';
use \Database\SQLManager;

include $_SERVER['DOCUMENT_ROOT'].'/v1/src/Mail/Mail.php';
use \Mail\Mail;

/**
 * La classe Users est utilisée pour toutes les interactions entre
 * l'utilisateur et la BDD (ex : les connexions).
 */
class Users {


    
    // ██       ██████   ██████  ██ ███    ██ 
    // ██      ██    ██ ██       ██ ████   ██ 
    // ██      ██    ██ ██   ███ ██ ██ ██  ██ 
    // ██      ██    ██ ██    ██ ██ ██  ██ ██ 
    // ███████  ██████   ██████  ██ ██   ████ 
    /**
     * Fonction qui permet de vérifier les informations de connexion d'un utilisateur.
     * 
     * @param string $email L'adresse email de l'utilisateur.
     * @param string $passwordToVerify Le mot de passe de l'utilisateur à vérifier.
     * 
     * @return int Retourne un entier qui indique l'état de la connexion :
     * - 0 si les informations de connexion sont correctes et que l'utilisateur est connecté avec un token mis à jour
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors des requêtes SQL
     * - 3 si les informations de connexion sont incorrectes
     */
    public static function login(
        string $email,
        string $passwordToVerify
    ) {
        try {
            // Vérifie si l'adresse email est valide
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $columns = 'password';
                $from = 'users';
                $where = 'email = :email';
                $params = array(
                    ':email' => $email
                );
                // Recherche le mot de passe correspondant à l'adresse email dans la base de données
                $data = SQLManager::findBy($columns,$from,$where,$params);
                // Vérifie si le mot de passe est correct
                if(isset($data['password']) && password_verify($passwordToVerify, $data['password'])) {
                    $table = 'users';
                    $set = 'last_login_date = :last_login_date';
                    $where = 'email = :email';
                    $params = array(
                        ':last_login_date' => date('Y-m-d H:i:s'),
                        ':email' => $email
                    );
                    // Met à jour la date de dernière connexion dans la base de données
                    if (!SQLManager::update($table, $set, $where, $params)) {
                        return 2; // Erreur SQL
                    }
                    // Génère un nouveau token pour l'utilisateur
                    Users::createToken($email);
                    
                    return 0; // Informations de connexion correctes, utilisateur connecté avec un token mis à jour
                } else {
                    return 3; // Informations de connexion incorrectes
                }
            } else {
                return 3; // Informations de connexion incorrectes
            }
        } catch(Exception $e) {
            error_log("[Users.php] - Users::login Exception : $e", 0);
            return 1; // Une erreur s'est produite
        }
    }



    //  ██████ ██████  ███████  █████  ████████ ███████     ████████  ██████  ██   ██ ███████ ███    ██ 
    // ██      ██   ██ ██      ██   ██    ██    ██             ██    ██    ██ ██  ██  ██      ████   ██ 
    // ██      ██████  █████   ███████    ██    █████          ██    ██    ██ █████   █████   ██ ██  ██ 
    // ██      ██   ██ ██      ██   ██    ██    ██             ██    ██    ██ ██  ██  ██      ██  ██ ██ 
    //  ██████ ██   ██ ███████ ██   ██    ██    ███████        ██     ██████  ██   ██ ███████ ██   ████ 
    /**
     * Fonction qui permet de générer un token pour l'utilisateur et de l'associer à son compte.
     * 
     * @param string $email L'adresse email de l'utilisateur.
     * 
     * @return int Retourne un entier qui indique l'état de la création du token :
     * - 0 si le token a été créé et associé à l'utilisateur avec succès
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors des requêtes SQL
     */
    public static function createToken(
        string $email
    ) {
        try {
            // Génère un token aléatoire de 60*2 caractères (lettres et chiffres uniquement)
            $token = bin2hex(random_bytes(60));

            $table = 'users';
            $set = 'token = :token';
            $where = 'email = :email';
            $params = array(
                ':token' => $token,
                ':email' => $email
            );
            // Associe le token à l'utilisateur dans la base de données
            if (!SQLManager::update($table, $set, $where, $params)) {
                return 2; // Erreur SQL
            }
            
            // Associe le compte de l'utilisateur avec le token dans la session
            $_SESSION['user'] = array('email' => $email);
            $_SESSION['user']['token'] = $token;
            
            // Récupère le nom du groupe de l'utilisateur à partir du token
            $columns = 'user_username,group_name';
            $from = 'user_group_view';
            $where = 'user_token = :user_token';
            $params = array(
                ':user_token' => $token
            );
            $data = SQLManager::findBy($columns,$from,$where,$params);
            if ($data == false) {
                return 2; // Erreur SQL
            }

            $_SESSION['user']['group'] = $data['group_name'];
            $_SESSION['user']['username'] = $data['user_username'];
            return 0; // Token créé et associé à l'utilisateur avec succès

        } catch(Exception $e) {
            error_log("[Users.php] - Users::createToken Exception : $e", 0);
            return 1; // Une erreur s'est produite
        }
    }



    // ██    ██ ███████ ██████  ██ ███████ ██    ██     ████████  ██████  ██   ██ ███████ ███    ██ 
    // ██    ██ ██      ██   ██ ██ ██       ██  ██         ██    ██    ██ ██  ██  ██      ████   ██ 
    // ██    ██ █████   ██████  ██ █████     ████          ██    ██    ██ █████   █████   ██ ██  ██ 
    //  ██  ██  ██      ██   ██ ██ ██         ██           ██    ██    ██ ██  ██  ██      ██  ██ ██ 
    //   ████   ███████ ██   ██ ██ ██         ██           ██     ██████  ██   ██ ███████ ██   ████ 
    /**
     * Fonction qui permet de vérifier la validité d'un token pour un utilisateur donné.
     * 
     * @param string $email L'adresse email de l'utilisateur.
     * @param string $token Le token à vérifier.
     * 
     * @return int Retourne un entier qui indique l'état de la vérification :
     * - 0 si le token est valide et qu'un nouveau token a été généré
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors des requêtes SQL
     * - 3 si le token est invalide et que la session de l'utilisateur a été supprimée
     */
    public static function verifyToken(
        string $email,
        string $token
    ) {
        try {
            $columns = 'token';
            $from = 'users';
            $where = 'email = :email';
            $params = array(
                ':email' => $email
            );
            // Recherche le token associé à l'adresse email dans la base de données
            $data = SQLManager::findBy($columns, $from, $where, $params);

            if ($data == false) {
                error_log("[Users.php] - Users::verifyToken SQL Error", 0);
                return 2; // Erreur SQL
            }
            // Si le token de l'utilisateur est associé au token dans la base de données
            if ((isset($data['token']) && !empty($data['token'])) && ($token == $data['token'])) { 
                // Créer un nouveau token pour l'utilisateur
                Users::createToken($email);
                return 0; // Token valide

            // Sinon, supprime la session de l'utilisateur
            } else {
                Users::disconnect("Déconnecté","Votre session n'est pas reconnu par le serveur, merci de vous reconnecter.");
                return 3; // Token invalide
            }
            
        } catch(Exception $e) {
            error_log("[Users.php] - Users::verifyToken Exception : $e", 0);
            return 1; // Une erreur s'est produite
        }
    }

    /**
     * Fonction qui permet de déconnecter l'utilisateur en supprimant sa session et en redirigeant vers la page de connexion.
     * 
     * @param string $title Le titre du message à afficher à l'utilisateur après sa déconnexion.
     * @param string $message Le message à afficher à l'utilisateur après sa déconnexion.
     * @param bool $isError Si le message de déconnexion est lié à une erreur ou non. Vrai de base (Optionnel)
     * 
     * @return void
     */
    public static function disconnect(
        string $title,
        string $message,
        bool $isError = true
    ) {
        // Parcours de tous les cookies existants
        foreach ($_COOKIE as $cookieName => $cookieValue) {
            // Supprime le cookie en lui assignant une date d'expiration passée
            setcookie($cookieName, '', time() - 3600, '/');
        }
        
        session_destroy();
        session_write_close();
        if(session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['disconnect'] = array();
        if ($isError == true) {
            $_SESSION['disconnect']['code'] = 1;
        } else {
            $_SESSION['disconnect']['code'] = 0;
        }
        $_SESSION['disconnect']['title'] = $title;
        $_SESSION['disconnect']['message'] = $message;
        header('location: \connexion');
    }



    // ██    ██ ██████  ██████   █████  ████████ ███████     ██████   █████  ███████ ███████ ██     ██  ██████  ██████  ██████  
    // ██    ██ ██   ██ ██   ██ ██   ██    ██    ██          ██   ██ ██   ██ ██      ██      ██     ██ ██    ██ ██   ██ ██   ██ 
    // ██    ██ ██████  ██   ██ ███████    ██    █████       ██████  ███████ ███████ ███████ ██  █  ██ ██    ██ ██████  ██   ██ 
    // ██    ██ ██      ██   ██ ██   ██    ██    ██          ██      ██   ██      ██      ██ ██ ███ ██ ██    ██ ██   ██ ██   ██ 
    //  ██████  ██      ██████  ██   ██    ██    ███████     ██      ██   ██ ███████ ███████  ███ ███   ██████  ██   ██ ██████  
    /**
     * Fonction qui permet de mettre à jour le mot de passe d'un utilisateur.
     *
     * @param string $email L'adresse email de l'utilisateur.
     * @param string $token Le token de l'utilisateur.
     * @param string $hashedPassword Le dernier mot de passe de l'utilisateur.
     *
     * @return int Retourne un entier qui indique l'état de la mise à jour du mot de passe :
     * - 0 si la mise à jour du mot de passe a été effectuée avec succès
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors des requêtes SQL
     * - 3 si le token ne correspond pas à celui de l'utilisateur
     */
    public static function updatePassword(
        string $email,
        string $token,
        string $hashedPassword
    ) {
        try {
            // Vérifie si le token de l'utilisateur est valide
            if(Users::verifyToken($email,$token) == 0) {

                $table = 'users';
                $set = 'password = :password';
                $where = 'email = :email';
                $params = array (
                    ':password' => $hashedPassword,
                    ':email' => $email
                );
                // Met à jour le mot de passe dans la base de données
                if (!SQLManager::update($table, $set, $where, $params)) {
                    return 2; // Erreur SQL
                }
                return 0; // Mot de passe mis à jour avec succès
            } else {
                return 3; // Le token ne correspond pas à celui de l'utilisateur
            }
        } catch(Exception $e) {
            error_log("[Users.php] - Users::updatePassword Exception : $e", 0);
            return 1; // Une erreur s'est produite
        }
    }



    // ██    ██ ██████  ██████   █████  ████████ ███████     ███████ ███    ███  █████  ██ ██      
    // ██    ██ ██   ██ ██   ██ ██   ██    ██    ██          ██      ████  ████ ██   ██ ██ ██      
    // ██    ██ ██████  ██   ██ ███████    ██    █████       █████   ██ ████ ██ ███████ ██ ██      
    // ██    ██ ██      ██   ██ ██   ██    ██    ██          ██      ██  ██  ██ ██   ██ ██ ██      
    //  ██████  ██      ██████  ██   ██    ██    ███████     ███████ ██      ██ ██   ██ ██ ███████ 
    /**
     * Fonction qui permet de mettre à jour l'adresse email d'un utilisateur.
     * 
     * @param string $email L'adresse email actuelle de l'utilisateur.
     * @param string $token Le token de l'utilisateur pour vérifier son identité.
     * @param string $newEmail La nouvelle adresse email à associer à l'utilisateur.
     * 
     * @return int Retourne un entier qui indique l'état de la mise à jour :
     * - 0 si la mise à jour est effectuée avec succès
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors de la mise à jour de la base de données
     * - 3 si le token ne correspond pas à celui de l'utilisateur
     */
    public static function updateEmail(
        string $email,
        string $token,
        string $newEmail
    ) {
        try {
            // Vérifie si le token de l'utilisateur est valide
            if(Users::verifyToken($email,$token) == 0) {

                $table = 'users';
                $set = 'email = :newEmail';
                $where = 'email = :email';
                $params = array (
                    ':newEmail' => $newEmail,
                    ':email' => $email
                );
                // Met à jour l'adresse email dans la base de données
                if (!SQLManager::update($table, $set, $where, $params)) {
                    return 2; // Erreur SQL
                }
                // Met à jour l'adresse email de l'utilisateur dans la session
                $_SESSION['user']['email'] = $newEmail;
                return 0; // Adresse email mise à jour avec succès
            } else {
                return 3; // Le token ne correspond pas à celui de l'utilisateur
            }
        } catch(Exception $e) {
            error_log("[Users.php] - Users::updateEmail Exception : $e", 0);
            return 1; // Une erreur s'est produite
        }
    }



    // ██████  ███████  ██████  ██ ███████ ████████ ███████ ██████  
    // ██   ██ ██      ██       ██ ██         ██    ██      ██   ██ 
    // ██████  █████   ██   ███ ██ ███████    ██    █████   ██████  
    // ██   ██ ██      ██    ██ ██      ██    ██    ██      ██   ██ 
    // ██   ██ ███████  ██████  ██ ███████    ██    ███████ ██   ██                         
    /**
     * Fonction qui permet d'enregistrer un nouvel utilisateur.
     * 
     * @param string $username Le nom d'utilisateur choisi par l'utilisateur.
     * @param string $password Le mot de passe choisi par l'utilisateur.
     * @param string $passwordToVerify La confirmation du mot de passe choisi par l'utilisateur.
     * @param string $email L'adresse email choisie par l'utilisateur.
     * 
     * @return int Retourne un entier qui indique l'état de l'enregistrement :
     * - 0 si l'utilisateur est enregistré avec succès
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors des requêtes SQL
     * - 3 si le nom d'utilisateur ne correspond pas au format requis
     * - 4 si le mot de passe ne correspond pas au format requis
     * - 5 si le mot de passe et la confirmation de mot de passe ne correspondent pas
     * - 6 si l'adresse email n'est pas au format valide
     * - 7 si l'adresse email a déjà un compte vérifié associé
     * - 8 si le nom d'utilisateur est déjà utilisé
     */
    public static function register(
        string $username,
        string $password,
        string $passwordToVerify,
        string $email
    ) {
        try {
            $usernameRegex = '/^[a-zA-Z0-9_]{3,16}$/';
            $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[À-ÿ \p{P}\p{S}]).{8,}$/';
    
            // Vérifie si le nom d'utilisateur correspond au format requis
            if(!(strlen($username) >= 3 && strlen($username) <= 16 && preg_match($usernameRegex, $username))) {
                return 3; // Le nom d'utilisateur ne correspond pas au format requis
            }
            // Vérifie si le mot de passe correspond au format requis
            if(!preg_match($passwordRegex, $password)) {
                return 4; // Le mot de passe ne correspond pas au format requis
            }
            // Vérifie si le mot de passe et la confirmation de mot de passe correspondent
            if($password != $passwordToVerify) {
                return 5; // Le mot de passe et la confirmation de mot de passe ne correspondent pas
            }
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            // Vérifie si l'adresse email est au format valide
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return 6; // L'adresse email n'est pas au format valide
            }
    
            $columns = 'username,is_verified';
            $from = 'users';
            $where = 'email = :email';
            $params = array(
                ':email' => $email
            );
            $data = SQLManager::findBy($columns, $from, $where, $params);
            // Vérifie si une erreur s'est produite lors des requêtes SQL
            if ($data == false) {
                return 2; // Erreur de la base de données
            }
    
            // Vérifie si l'adresse email a déjà un compte vérifié associé
            if (isset($data['is_verified']) && $data['is_verified'] == 1) {
                return 7; // L'adresse email a déjà un compte vérifié associé
            }
            if (isset($data['is_verified'])) {
                $is_verified = $data['is_verified'];
            }

            // Vérifie si le nom d'utilisateur est déjà utilisé
            $columns = 'username';
            $from = 'users';
            $where = 'username = :username';
            $params = array(
                ':username' => $username
            );
            $data = SQLManager::findBy($columns, $from, $where, $params);
            // Vérifie si une erreur s'est produite lors des requêtes SQL
            if ($data == false) {
                return 2; // Erreur de la base de données
            }
            isset($data["username"]) ? $usernameDatabase = $data["username"] : $usernameDatabase = "";
            if (((isset($usernameDatabase) && !empty($usernameDatabase)) || strtolower($username) == strtolower($usernameDatabase)) && (isset($is_verified) && $is_verified == 1)) {
                return 8; // Le nom d'utilisateur est déjà utilisé
            }

            // Génère un code de vérification pour l'utilisateur
            $verification_code_email = rand(1,999999);
            $verification_code_email = str_pad($verification_code_email,6, "0", STR_PAD_LEFT);

            // Prépare et envoie un email de vérification
            $emailTemplate = "register";
            $emailParams = array(
                'verification_code_email' => $verification_code_email
            );
            Mail::sendEmail($email, "", $emailTemplate, $emailParams);

            if (isset($is_verified)) { // Si une adresse email est déjà associée à un compte mais que le compte n'est pas vérifié
                // Modifie le compte avec les nouvelles données pour le "re-créer"
                $table = 'users';
                $set = 'username = :username, password = :password, verification_code_email = :verification_code_email';
                $where = 'email = :email';
                $params = array(
                    ':username' => $username,
                    ':email' => $email,
                    ':password' => $hashedPassword,
                    ':verification_code_email' => $verification_code_email,
                );
                if(SQLManager::update($table,$set,$where,$params)) {
                    return 0; // L'utilisateur a été modifié (même numéro de retour que pour la création)
                } else {
                    return 2; // Erreur de la base de données ou de la requête SQL
                }

            } else { // Si aucune adresse email n'est associée à un compte, on crée un nouveau compte
                $params = array(
                    ':username' => $username,
                    ':email' => $email,
                    ':password' => $hashedPassword,
                    ':verification_code_email' => $verification_code_email,
                );
                $table = "users";
                $into = "(username, email, password, verification_code_email)";
                $values = "(:username,:email,:password,:verification_code_email)";
                if(SQLManager::insertInto($table, $into, $values, $params)) {
                    return 0; // L'utilisateur a été créé (même numéro de retour que pour la modification)
                } else {
                    return 2; // Erreur de la base de données ou de la requête SQL
                }
            }
        } catch(Exception $e) {
            error_log("[Users.php] - Users::register Exception : $e", 0);
            return 1; // Une erreur s'est produite
        }
    }



    // ██    ██ ███████ ██████  ██ ███████ ██    ██     ███████ ███    ███  █████  ██ ██           ██████  ██████  ██████  ███████     ██████  ███████  ██████  ██ ███████ ████████ ███████ ██████  
    // ██    ██ ██      ██   ██ ██ ██       ██  ██      ██      ████  ████ ██   ██ ██ ██          ██      ██    ██ ██   ██ ██          ██   ██ ██      ██       ██ ██         ██    ██      ██   ██ 
    // ██    ██ █████   ██████  ██ █████     ████       █████   ██ ████ ██ ███████ ██ ██          ██      ██    ██ ██   ██ █████       ██████  █████   ██   ███ ██ ███████    ██    █████   ██████  
    //  ██  ██  ██      ██   ██ ██ ██         ██        ██      ██  ██  ██ ██   ██ ██ ██          ██      ██    ██ ██   ██ ██          ██   ██ ██      ██    ██ ██      ██    ██    ██      ██   ██ 
    //   ████   ███████ ██   ██ ██ ██         ██        ███████ ██      ██ ██   ██ ██ ███████      ██████  ██████  ██████  ███████     ██   ██ ███████  ██████  ██ ███████    ██    ███████ ██   ██ 
    /**
     * Fonction qui permet de vérifier le code de vérification envoyé par email lors de l'inscription.
     * 
     * @param string $email L'adresse email de l'utilisateur.
     * @param string $code Le code de vérification envoyé par email.
     * 
     * @return int Retourne un entier qui indique l'état de la vérification :
     * - 0 si la vérification a été effectuée avec succès et que le compte de l'utilisateur a été créé dans la base de données
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors des requêtes SQL
     * - 3 si l'utilisateur a déjà été vérifié
     * - 4 si l'adresse email n'a pas été trouvée dans la base de données
     */
    public static function verifyEmailCodeRegister(
        string $email,
        string $code
    ) {
        try {
            $columns = 'is_verified, verification_code_email';
            $from = 'users';
            $where = 'email = :email';
            $params = array(
                ':email' => $email
            );
            // Recherche dans la base de données pour récupérer l'état de vérification de l'utilisateur et le code de vérification
            $data = SQLManager::findBy($columns, $from, $where, $params);

            if ($data == false) {
                return 2; // Erreur SQL
            }
            $is_verified = $data['is_verified'];
            $verification_code_email = $data['verification_code_email'];

            if (isset($is_verified) && $is_verified == 1) {
                return 3; // Utilisateur déjà vérifié
            }

            if (isset($is_verified) && ($verification_code_email == $code || "$verification_code_email" == "$code")) {
                $params = array (
                    ":is_verified" => 1,
                    ":email" => $email
                );
                $table = "users";
                $set = "is_verified=:is_verified";
                $where = "email=:email";
                // Met à jour l'état de vérification de l'utilisateur dans la base de données
                if (SQLManager::update($table, $set, $where, $params)) {
                    // Récupère l'ID de l'utilisateur vérifié
                    $columns = "id";
                    $from = "users";
                    $where = "email = :email";
                    $params = array(
                        ':email' => $email
                    );
                    $data = SQLManager::findBy($columns, $from, $where, $params);
                    if ($data == false) {
                        return 2; // Erreur SQL
                    }
                    
                    $userId = $data['id'];

                    // Crée une section de compte pour l'utilisateur dans la table des comptes
                    $params = array (":user_id" => $userId);
                    $table = "accounts";
                    $into = "(user_id)";
                    $values = "(:user_id)";
                    if (SQLManager::insertInto($table, $into, $values, $params)) {
                        return 0; // Utilisateur vérifié avec succès et compte créé dans la base de données
                    } else {
                        return 2; // Erreur SQL
                    }
                } else {
                    return 2; // Erreur SQL
                }
            } else {
                return 4; // Aucun utilisateur avec cette email dans la base de données
            }
        } catch(Exception $e) {
            error_log("[Users.php] - Users::verifyEmailCode Exception : $e", 0);
            return 1; // Une erreur s'est produite
        }
    }



    // ██    ██ ███████ ██████  ██ ███████ ██    ██     ███████ ███    ███  █████  ██ ██           ██████  ██████  ██████  ███████ 
    // ██    ██ ██      ██   ██ ██ ██       ██  ██      ██      ████  ████ ██   ██ ██ ██          ██      ██    ██ ██   ██ ██      
    // ██    ██ █████   ██████  ██ █████     ████       █████   ██ ████ ██ ███████ ██ ██          ██      ██    ██ ██   ██ █████   
    //  ██  ██  ██      ██   ██ ██ ██         ██        ██      ██  ██  ██ ██   ██ ██ ██          ██      ██    ██ ██   ██ ██      
    //   ████   ███████ ██   ██ ██ ██         ██        ███████ ██      ██ ██   ██ ██ ███████      ██████  ██████  ██████  ███████ 
    /**
     * Fonction qui permet de vérifier le code de vérification envoyé par email.
     * 
     * @param string $email L'adresse email de l'utilisateur.
     * @param string $code Le code de vérification envoyé par email.
     * 
     * @return int Retourne un entier qui indique l'état de la vérification :
     * - 0 si la vérification a été effectuée avec succès
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors des requêtes SQL
     * - 3 si l'adresse email n'a pas été trouvée dans la base de données
     * - 4 si le token ne correspond pas à celui de l'utilisateur
     */
    public static function verifyEmailCode(
        string $email,
        string $token,
        string $code
    ) {
        try {
            if (Users::verifyToken($email, $token) == 0) {
                $columns = 'verification_code_email';
                $from = 'users';
                $where = 'email = :email';
                $params = array(
                    ':email' => $email
                );
                // Recherche dans la base de données pour récupérer l'état de vérification de l'utilisateur et le code de vérification
                $data = SQLManager::findBy($columns, $from, $where, $params);

                if ($data == false) {
                    return 2; // Erreur SQL
                }
                $verification_code_email = $data['verification_code_email'];

                if ($verification_code_email == $code || "$verification_code_email" == "$code") {
                    return 0; // Code vérifié avec succès
                } else {
                    return 3; // Aucun utilisateur avec cette email dans la base de données
                }
            } else {
                return 3; // Le token ne correspond pas à celui de l'utilisateur
            }
        } catch(Exception $e) {
            error_log("[Users.php] - Users::verifyEmailCode Exception : $e", 0);
            return 1; // Une erreur s'est produite
        }
    }



    // ██    ██ ██████  ██████   █████  ████████ ███████     ███████ ███    ███  █████  ██ ██           ██████  ██████  ██████  ███████ 
    // ██    ██ ██   ██ ██   ██ ██   ██    ██    ██          ██      ████  ████ ██   ██ ██ ██          ██      ██    ██ ██   ██ ██      
    // ██    ██ ██████  ██   ██ ███████    ██    █████       █████   ██ ████ ██ ███████ ██ ██          ██      ██    ██ ██   ██ █████   
    // ██    ██ ██      ██   ██ ██   ██    ██    ██          ██      ██  ██  ██ ██   ██ ██ ██          ██      ██    ██ ██   ██ ██      
    //  ██████  ██      ██████  ██   ██    ██    ███████     ███████ ██      ██ ██   ██ ██ ███████      ██████  ██████  ██████  ███████ 
    /**
     * Fonction qui permet de générer et d'envoyer un code de vérification à l'adresse email de l'utilisateur.
     * 
     * @param string $email L'adresse email qui va recevoir le mail.
     * @param string $emailTemplate Nom du modèle de mail src/Mail/template/
     * @param string $emailUser Adresse email où le code sera modifié. (optionnel)
     * 
     * @return int Retourne un entier qui indique l'état de la fonction :
     * - 0 si le code de vérification a été généré et envoyé avec succès
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors des requêtes SQL
     * - 3 si l'adresse email fournie n'est pas correcte
     */
    public static function updateEmailCode(
        string $email,
        string $emailTemplate,
        string $emailUser = null
    ) {
        try {
            if ($emailUser === null) {
                $emailUser = $email;
            }
            // Vérifie si l'adresse email est valide
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return 3; // Adresse email incorrecte
            }
            // Génère un code de vérification aléatoire à 6 chiffres
            $verification_code_email = rand(1,999999);
            $verification_code_email = str_pad($verification_code_email,6, "0", STR_PAD_LEFT);

            $table = "users";
            $set = "verification_code_email = :verification_code_email";
            $where = "email = :email";
            $params = array(
                ':verification_code_email' => $verification_code_email,
                ':email' => $emailUser
            );
            // Met à jour la base de données avec le nouveau code de vérification
            if(SQLManager::update($table,$set,$where,$params)) {                    
                // Prépare et envoie un email avec le code de vérification à l'utilisateur
                $emailParams = array(
                    'verification_code_email' => $verification_code_email
                );
                Mail::sendEmail($email, "", $emailTemplate, $emailParams);
                return 0; // Code de vérification généré et envoyé avec succès
            } else {
                return 2; // Erreur de SQLManager ou de la requête SQL
            }            
        } catch(Exception $e) {
            error_log("[Users.php] - Users::updateEmailCode Exception : $e", 0);
            return 1; // Une erreur s'est produite
        }
    }


    
    // ██    ██ ███████ ██████  ██ ███████ ██    ██     ███████ ███    ███  █████  ██ ██           █████  ██    ██  █████  ██ ██       █████  ██████  ██      ██ ████████ ██    ██ 
    // ██    ██ ██      ██   ██ ██ ██       ██  ██      ██      ████  ████ ██   ██ ██ ██          ██   ██ ██    ██ ██   ██ ██ ██      ██   ██ ██   ██ ██      ██    ██     ██  ██  
    // ██    ██ █████   ██████  ██ █████     ████       █████   ██ ████ ██ ███████ ██ ██          ███████ ██    ██ ███████ ██ ██      ███████ ██████  ██      ██    ██      ████   
    //  ██  ██  ██      ██   ██ ██ ██         ██        ██      ██  ██  ██ ██   ██ ██ ██          ██   ██  ██  ██  ██   ██ ██ ██      ██   ██ ██   ██ ██      ██    ██       ██    
    //   ████   ███████ ██   ██ ██ ██         ██        ███████ ██      ██ ██   ██ ██ ███████     ██   ██   ████   ██   ██ ██ ███████ ██   ██ ██████  ███████ ██    ██       ██    
    /**
     * Fonction qui permet de vérifier si une adresse email est disponible.
     * 
     * @param string $email L'adresse email à vérifier.
     * 
     * @return int Retourne un entier qui indique si l'adresse email est disponible ou non :
     * - 0 si l'adresse email est disponible
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors de l'exécution de la requête SQL
     * - 3 si l'adresse email n'est pas au format valide
     * - 4 si l'adresse email est déjà associée à un compte
     */
    public static function verifyEmailAvailablity(
        string $email
    ) {
        try {
            // Vérifie si l'adresse email est au format valide
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return 3; // L'adresse email n'est pas au format valide
            }

            $columns = 'username';
            $from = 'users';
            $where = 'email = :email';
            $params = array(
                ':email' => $email
            );
            // Recherche les informations associées à l'adresse email dans la base de données
            $data = SQLManager::findBy($columns, $from, $where, $params);
            // Vérifie si une erreur s'est produite lors des requêtes SQL
            if ($data == false) {
                return 2; // Erreur de la base de données
            }
            // Vérifie si l'adresse email est déjà associée à un compte
            if (isset($data['username']) && !empty($data['username'])) {
                return 4; // Email déjà associé à un compte
            }
            return 0; // Email disponible
        } catch(Exception $e) {
            error_log("[Users.php] - Users::verifyEmail Exception : $e", 0);
            return 1; // Une erreur s'est produite
        }
    }



    // ██████  ███████ ██      ███████ ████████ ███████ 
    // ██   ██ ██      ██      ██         ██    ██      
    // ██   ██ █████   ██      █████      ██    █████   
    // ██   ██ ██      ██      ██         ██    ██      
    // ██████  ███████ ███████ ███████    ██    ███████
    /**
     * Fonction qui permet de supprimer un compte utilisateur.
     * 
     * @param string $email L'adresse email de l'utilisateur.
     * @param string $userToken Le token de l'utilisateur pour vérifier son identité.
     * 
     * @return int Retourne un entier qui indique l'état de la suppression du compte :
     * - 0 si le compte a été supprimé avec succès
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors de la suppression dans la base de données
     * - 3 si le token ne correspond pas à l'utilisateur
     */
    public static function delete(
        string $email,
        string $userToken
    ) {
        try {
            // Vérifie si l'adresse email et le token sont valides
            if(Users::verifyToken($email,$userToken) == 0) {

                $table = 'accounts';
                $where = 'user_id IN (SELECT id FROM users WHERE email = :email)';
                $params = array(
                    ':email' => $email
                );
                // Supprime le compte utilisateur correspondant à l'adresse email de la base de données
                if(!SQLManager::deleteFrom($table,$where,$params)) {
                    return 2; // Erreur SQL
                }

                $table = 'users';
                $where = 'email = :email';
                $params = array(
                    ':email' => $email
                );
                // Supprime le compte utilisateur correspondant à l'adresse email de la base de données
                if(!SQLManager::deleteFrom($table,$where,$params)) {
                    return 2; // Erreur SQL
                }

                // Parcours de tous les cookies existants
                foreach ($_COOKIE as $cookieName => $cookieValue) {
                    // Supprime le cookie en lui assignant une date d'expiration passée
                    setcookie($cookieName, '', time() - 3600, '/');
                }
                session_destroy();
                session_write_close();
                return 0; // Le compte a bien été supprimé
            } else {
                return 3; // Le token ne correspond pas à l'utilisateur
            }
        } catch(Exception $e) {
            error_log("[Users.php] - Users::delete Exception : $e", 0);
            return 1; // Une erreur s'est produite
        }
    }
}
?>