<?php
namespace Database;
use \PDO;
use \Exception;

include $_SERVER['DOCUMENT_ROOT'].'/v1/src/Database/SQLManager.php';
use \Database\SQLManager;

/**
 * La classe Adverts est utilisée pour toutes les interactions entre
 * les annonces et la BDD (ex : les créations).
 */
class Adverts {
    /**
     * Crée une nouvelle annonce avec les informations spécifiées.
     *
     * @param int $user_id L'identifiant de l'utilisateur créant l'annonce.
     * @param string $title Le titre de l'annonce.
     * @param string $description La description de l'annonce.
     * @param bool $is_premium Indique si l'annonce est de type premium.
     * @param bool $is_google_ads Indique si l'annonce est une publicité Google.
     * @param float $latitude La latitude de l'emplacement de l'annonce.
     * @param float $longitude La longitude de l'emplacement de l'annonce.
     * @param string $city La ville de l'emplacement de l'annonce.
     * @param array $images Les images associées à l'annonce.
     * @return int Le code de statut :
     * - 0 si l'annonce a été créée avec succès
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors de l'exécution de la requête SQL ou de la base de données
     * - 3 si le titre de l'annonce n'est pas conforme
     * - 4 si la description de l'annonce n'est pas conforme
     * - 5 si une erreur s'est produite lors de l'enregistrement d'une image associée à l'annonce
     */
    public static function create(
        int $user_id,
        string $title,
        string $description,
        bool $is_premium,
        bool $is_google_ads,
        float $latitude,
        float $longitude,
        string $city,
        array $images
    ) {
        try {
            // Vérifie la conformité du titre de l'annonce
            if (!(strlen($title) >= 8 && strlen($title) <= 32)) {
                return 3; // Le titre n'est pas conforme
            }
            // Vérifie la conformité de la description de l'annonce
            if (!(strlen($description) >= 10 && strlen($description) <= 800)) {
                return 4; // La description n'est pas conforme
            }
        
            $end_date = strtotime("+7 days");
            $params = array(
                ':user_id' => $user_id,
                ':title' => $title,
                ':description' => $description,
                ':end_date' => $end_date,
                ':is_premium' => $is_premium,
                ':is_google_ads' => $is_google_ads,
                ':latitude' => $latitude,
                ':longitude' => $longitude,
                ':city' => $city,
            );
            $table = "adverts";
            $into = "(user_id, title, description, end_date, is_premium, is_google_ads, latitude, longitude, city)";
            $values = "(:user_id,:title,:description,:end_date,:is_premium,:is_google_ads,:latitude,:longitude,:city)";
            
            // Insère l'annonce dans la base de données
            if (SQLManager::insertInto($table, $into, $values, $params)) {
                $advertId = SQLManager::getLastInsertedId($table); // Récupère l'ID de l'annonce nouvellement créée
        
                // Parcourt chaque image pour les enregistrer
                foreach ($images['tmp_name'] as $index => $tmpName) {
                    $imageNumber = $index + 1;
                    $filename = $imageNumber . '.jpg';
                    $folderName = 'adverts_images/' . $advertId;
                    $destination = $_SERVER['DOCUMENT_ROOT'] . '/storage/adverts_images/' . $folderName . '/' . $filename;
        
                    if (move_uploaded_file($tmpName, $destination)) {
                        // Enregistrement de l'image réussi, vous pouvez faire ce que vous souhaitez avec l'image ici
                        // Par exemple, vous pouvez enregistrer le chemin de l'image dans la base de données pour référence future
                        $link = '/storage/' . $folderName . '/' . $filename;
                        $isMain = ($index === 0) ? 1 : 0; // Détermine si l'image est l'image principale
        
                        // Enregistrement des informations de l'image dans la table "images"
                        $imageParams = array(
                            ':folder_name' => $folderName,
                            ':file_name' => $filename,
                            ':link' => $link,
                            ':is_main' => $isMain,
                        );
                        $imageTable = "images";
                        $imageInto = "(folder_name, file_name, link, is_main)";
                        $imageValues = "(:folder_name, :file_name, :link, :is_main)";
                        SQLManager::insertInto($imageTable, $imageInto, $imageValues, $imageParams);
                    } else {
                        // Erreur lors de l'enregistrement de l'image
                        return 5; // Code d'erreur pour l'échec de l'enregistrement de l'image
                    }
                }
        
                return 0; // L'annonce a été créée avec succès
            } else {
                return 2; // Erreur de la base de données ou de la requête SQL
            }
        } catch(Exception $e) {
            // Journalise l'exception
            error_log("[Adverts.php] - Adverts::create Exception : $e", 0);
            return 1; // Une erreur s'est produite
        }
    }

    /**
     * Supprime une annonce existant dans la table adverts avec l'identifiant spécifié.
     *
     * @param int $id L'identifiant de l'annonce à supprimer.
     * @return int Le code de statut :
     * - 0 si l'annonce a été supprimée avec succès
     * - 1 si une erreur s'est produite lors de l'exécution de la fonction
     * - 2 si une erreur s'est produite lors de l'exécution de la requête SQL
     * - 3 si l'annonce n'existe pas
     */
    public static function delete (
        $id
    ) {
        try {
            // Vérifie si l'annonce existe
            $data = SQLManager::findBy('id, title', 'adverts', 'id = :id', array(':id' => $id));
            if (!isset($data['title'])) {
                return 3; // Article non existant
            }
            // Supprime l'article de la table
            $table = "adverts";
            $set = "is_deleted = :is_deleted";
            $where = "id = :id";
            $params = array(
                ':is_deleted' => 1,
                ':id' => $id
            );
            if (!SQLManager::update($table, $set, $where, $params)) {
                return 2; // Erreur SQL
            }
            return 0; // Article supprimé avec succès
        } catch(Exception $e) {
            // Journalise l'exception
            error_log("[Adverts.php] - Adverts::delete Exception : $e", 0);
            return 1; // Une erreur s'est produite
        }        
    }
}