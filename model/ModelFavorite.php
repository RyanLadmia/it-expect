<?php

class ModelFavorite {

    private $connexion;

    public function __construct()
    {
        $conn = new Connection();
        $this->connexion = $conn->connectionDB();
        $this->connexion->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Vérifier si un favori existe déjà
     */
    public function favoriteExists($userId, $contentId, $contentType) {
        try {
            $query = "SELECT COUNT(*) FROM favorites WHERE user_id = :user_id AND content_id = :content_id AND content_type = :content_type";
            $stmt = $this->connexion->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':content_id', $contentId, PDO::PARAM_INT);
            $stmt->bindParam(':content_type', $contentType, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la vérification du favori : " . $e->getMessage());
        }
    }

    /**
     * Ajouter un film ou une série aux favoris
     * @throws Exception Si le favori existe déjà ou en cas d'erreur
     */
    public function addFavorite($userId, $contentId, $contentType) {
        try {
            // Validation des paramètres
            if (!$userId || !$contentId || !in_array($contentType, ['movie', 'tv'])) {
                throw new InvalidArgumentException("Paramètres invalides pour l'ajout aux favoris");
            }

            // Vérifier si le favori existe déjà
            if ($this->favoriteExists($userId, $contentId, $contentType)) {
                throw new Exception("Cet élément existe déjà dans vos favoris");
            }

            $query = "INSERT INTO favorites (user_id, content_id, content_type) VALUES (:user_id, :content_id, :content_type)";
            $stmt = $this->connexion->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':content_id', $contentId, PDO::PARAM_INT);
            $stmt->bindParam(':content_type', $contentType, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                throw new Exception("Échec de l'ajout aux favoris");
            }
            
            return true;
        } catch (PDOException $e) {
            // Gestion spécifique des erreurs de contrainte UNIQUE
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                throw new Exception("Cet élément existe déjà dans vos favoris");
            }
            throw new Exception("Erreur lors de l'ajout aux favoris : " . $e->getMessage());
        }
    }

    /**
     * Supprimer un film ou une série des favoris
     * @throws Exception En cas d'erreur
     */
    public function removeFavorite($userId, $contentId, $contentType) {
        try {
            // Validation des paramètres
            if (!$userId || !$contentId || !in_array($contentType, ['movie', 'tv'])) {
                throw new InvalidArgumentException("Paramètres invalides pour la suppression");
            }

            // Vérifier si le favori existe
            if (!$this->favoriteExists($userId, $contentId, $contentType)) {
                throw new Exception("Ce favori n'existe pas");
            }

            $query = "DELETE FROM favorites WHERE user_id = :user_id AND content_id = :content_id AND content_type = :content_type";
            $stmt = $this->connexion->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':content_id', $contentId, PDO::PARAM_INT);
            $stmt->bindParam(':content_type', $contentType, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                throw new Exception("Échec de la suppression du favori");
            }
            
            return true;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression du favori : " . $e->getMessage());
        }
    }

    /**
     * Récupérer les favoris d'un utilisateur
     * @throws Exception En cas d'erreur
     */
    public function getFavorites($userId) {
        try {
            if (!$userId) {
                throw new InvalidArgumentException("ID utilisateur invalide");
            }

            $query = "SELECT * FROM favorites WHERE user_id = :user_id ORDER BY favorite_id DESC";
            $stmt = $this->connexion->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des favoris : " . $e->getMessage());
        }
    }

    /**
     * Compter le nombre de favoris d'un utilisateur
     */
    public function countFavorites($userId) {
        try {
            if (!$userId) {
                throw new InvalidArgumentException("ID utilisateur invalide");
            }

            $query = "SELECT COUNT(*) FROM favorites WHERE user_id = :user_id";
            $stmt = $this->connexion->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du comptage des favoris : " . $e->getMessage());
        }
    }
}
?>
