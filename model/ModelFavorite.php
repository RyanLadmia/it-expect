<?php

class ModelFavorite {

    private $connexion;

    public function __construct()
    {
        $conn = new Connection();
        $this->connexion = $conn->connectionDB();
        $this->connexion->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    // Ajouter un film ou une série aux favoris
    public function addFavorite($userId, $contentId, $contentType) {
        $query = "INSERT INTO favorites (user_id, content_id, content_type) VALUES (:user_id, :content_id, :content_type)";
        $stmt = $this->connexion->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':content_id', $contentId);
        $stmt->bindParam(':content_type', $contentType);
        return $stmt->execute();
    }

    // Supprimer un film ou une série des favoris
    public function removeFavorite($userId, $contentId, $contentType) {
        $query = "DELETE FROM favorites WHERE user_id = :user_id AND content_id = :content_id AND content_type = :content_type";
        $stmt = $this->connexion->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':content_id', $contentId);
        $stmt->bindParam(':content_type', $contentType);
        return $stmt->execute();
    }

    // Récupérer les favoris d'un utilisateur
    public function getFavorites($userId) {
        $query = "SELECT * FROM favorites WHERE user_id = :user_id";
        $stmt = $this->connexion->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
