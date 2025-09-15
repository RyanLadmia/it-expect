<?php

class FavoriteController {

    private $modelFavorite;
    private $userId;

    public function __construct($modelFavorite, $userId) {
        $this->modelFavorite = $modelFavorite;
        $this->userId = $userId;
    }

    // Ajouter un favori
    public function addFavorite($contentId, $contentType) {
        return $this->modelFavorite->addFavorite($this->userId, $contentId, $contentType);
    }

    // Supprimer un favori
    public function removeFavorite($contentId, $contentType) {
        return $this->modelFavorite->removeFavorite($this->userId, $contentId, $contentType);
    }

    // Afficher les favoris d'un utilisateur
    public function showFavorites() {
        return $this->modelFavorite->getFavorites($this->userId);
    }
}

?>
