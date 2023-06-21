<?php
namespace Database;

use \PDO;

/**
 * Classe pour gérer une connexion unique à la base de données en utilisant le design pattern Singleton.
 */
class DatabaseHandler {
    private static $instance = null;
    private $pdo;

    /**
     * Constructeur privé pour empêcher l'instanciation directe de la classe.
     * La connexion à la base de données est établie lors de la création de l'instance.
     */
    private function __construct()
    {
        // Si on lance les tests unitaires la variable $_SERVEUR n'existe pas
        if (!$_SERVER['DOCUMENT_ROOT']) {
            include 'v1/config/settings.php';
        } else {
            include $_SERVER['DOCUMENT_ROOT'].'/v1/config/settings.php';
        }
        $this->pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Fonction statique pour récupérer l'instance unique de la classe.
     * Si aucune instance n'existe, une nouvelle est créée.
     * 
     * @return DatabaseHandler L'instance unique de la classe DatabaseHandler.
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new DatabaseHandler();
        }
        return self::$instance;
    }

    /**
     * Fonction pour récupérer l'objet PDO associé à la connexion à la base de données.
     * 
     * @return PDO L'objet PDO associé à la connexion à la base de données.
     */
    public function getPDO()
    {
        return $this->pdo; 
    }

    /**
     * Fonction pour fermer la connexion à la base de données et réinitialiser l'instance unique de la classe.
     */
    public function close()
    {
        $this->pdo = null;
        self::$instance = null;
    }
}
?>