<?php
namespace Database;
use \PDO;

include $_SERVER['DOCUMENT_ROOT'].'/v1/src/Database/DatabaseHandler.php';
use \Database\DatabaseHandler;
use Exception;

date_default_timezone_set("Europe/Paris");

class SQLManager {
    /**
     * Récupère toutes les données d'une table de la base de données
     *
     * @param string $columns Les colonnes à sélectionner dans la requête SQL
     * @param string $from Le nom de la table dans laquelle récupérer les données
     * @param array $params Les paramètres nommés à lier à la requête SQL (facultatif)
     * @return array Les données récupérées de la table sous forme de tableau associatif
     */
    public static function findAll($columns, $from, $params = array()) {
        try {
            $db = DatabaseHandler::getInstance();
            $pdo = $db->getPDO();
            $sql = "SELECT $columns FROM $from";
            $stmt= $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $db->close();
            return $data != false ? $data : array('empty' => 0);
        } catch(Exception $e) {
            error_log("[SQLManager.php] - SQLManager::update Exception : $e", 0);
            return false;
        }
    }

    /**
     * Récupère une ligne d'une table de la base de données en fonction d'une condition
     *
     * @param string $columns Les colonnes à sélectionner dans la requête SQL
     * @param string $from Le nom de la table dans laquelle récupérer les données
     * @param string $where La condition à utiliser pour sélectionner la ligne
     * @param array $params Les paramètres nommés à lier à la requête SQL (facultatif)
     * @return array Les données de la ligne récupérée sous forme de tableau associatif
     */
    public static function findBy($columns, $from, $where, $params = array()) {
        try {
            $db = DatabaseHandler::getInstance();
            $pdo = $db->getPDO();
            $sql = "SELECT $columns FROM $from WHERE $where";
            $stmt= $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $db->close();
            return $data != false ? $data : array('empty' => 0);
        } catch(Exception $e) {
            error_log("[SQLManager.php] - SQLManager::findBy Exception : $e", 0);
            return false;
        }
    }

    /**
     * Met à jour les données d'une table de la base de données en fonction d'une condition donnée
     *
     * @param string $table Le nom de la table à mettre à jour
     * @param string $set Les colonnes et valeurs à mettre à jour dans la table
     * @param string $where La condition à utiliser pour sélectionner les lignes à mettre à jour
     * @param array $params Les paramètres nommés à lier à la requête SQL (facultatif)
     * @return bool Vrai si la mise à jour a réussi, faux sinon
     */
    public static function update($table, $set, $where, $params = array()) {
        try  {
            $db = DatabaseHandler::getInstance();
            $pdo = $db->getPDO();
            $sql = "UPDATE $table SET $set WHERE $where";
            $stmt= $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $db->close();
        } catch(Exception $e) {
            error_log("[SQLManager.php] - SQLManager::update Exception : $e", 0);
            return false;
        }
        return true;
    }

    /**
     * Fonction pour insérer une nouvelle ligne dans une table de la base de données.
     * 
     * @param string $table Le nom de la table dans laquelle insérer la ligne.
     * @param string $into Les colonnes dans lesquelles insérer les valeurs.
     * @param string $values Les valeurs à insérer.
     * @param array $params Les paramètres éventuels à lier aux requêtes préparées.
     * 
     * @return bool Retourne true si l'insertion a réussi, false sinon.
     */
    public static function insertInto($table, $into, $values, $params = array()) {
        try {
            $db = DatabaseHandler::getInstance();
            $pdo = $db->getPDO();
            $sql = "INSERT INTO $table $into VALUES $values";
            $stmt= $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $db->close();
        } catch(Exception $e) {
            error_log("[SQLManager.php] - SQLManager::deleteFrom Exception : $e", 0);
            return false;
        }
        return true;
    }

    /**
     * Supprime des lignes d'une table de la base de données en fonction d'une condition donnée
     *
     * @param string $table Le nom de la table dans laquelle supprimer les lignes
     * @param string $where La condition à utiliser pour sélectionner les lignes à supprimer
     * @param array $params Les paramètres nommés à lier à la requête SQL (facultatif)
     * @return bool Vrai si la suppression a réussi, faux sinon
     */
    public static function deleteFrom($table, $where, $params = array()) {
        try {
            $db = DatabaseHandler::getInstance();
            $pdo = $db->getPDO();
            $sql = "DELETE FROM $table WHERE $where";
            $stmt= $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $db->close();
        } catch(Exception $e) {
            error_log("[SQLManager.php] - SQLManager::deleteFrom Exception : $e", 0);
            return false;
        }
        return true;
    }

    /**
     * Exécute une requête SQL fournie et récupère les données d'une ligne de la table de la base de données
     *
     * @param string $sqlScript La requête SQL à exécuter
     * @param array $params Les paramètres nommés à lier à la requête SQL (facultatif)
     * @return array Les données de la ligne récupérée sous forme de tableau associatif
     */
    public static function sqlScript($sqlScript, $params = array()) {
        $db = DatabaseHandler::getInstance();
        $pdo = $db->getPDO();
        $stmt= $pdo->prepare($sqlScript);
        $stmt->execute($params);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $db->close();
        return($data);
    }

}