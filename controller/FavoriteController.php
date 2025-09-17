<?php

class FavoriteController {

    private $modelFavorite;
    private $userId;

    public function __construct($modelFavorite = null, $userId = null) {
        $this->modelFavorite = $modelFavorite ?: new ModelFavorite();
        $this->userId = $userId;
    }

    /**
     * Ajouter un favori avec gestion d'erreurs
     * @return array Résultat avec success et message
     */
    public function addFavorite($contentId, $contentType) {
        try {
            // Validation côté contrôleur
            if (!$this->userId) {
                return [
                    'success' => false,
                    'message' => 'Vous devez être connecté pour ajouter aux favoris'
                ];
            }

            if (!$contentId || !is_numeric($contentId)) {
                return [
                    'success' => false,
                    'message' => 'ID de contenu invalide'
                ];
            }

            if (!in_array($contentType, ['movie', 'tv'])) {
                return [
                    'success' => false,
                    'message' => 'Type de contenu invalide'
                ];
            }

            // Appel du modèle
            $this->modelFavorite->addFavorite($this->userId, $contentId, $contentType);
            
            return [
                'success' => true,
                'message' => 'Ajouté aux favoris avec succès !'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un favori avec gestion d'erreurs
     * @return array Résultat avec success et message
     */
    public function removeFavorite($contentId, $contentType) {
        try {
            // Validation côté contrôleur
            if (!$this->userId) {
                return [
                    'success' => false,
                    'message' => 'Vous devez être connecté pour supprimer des favoris'
                ];
            }

            if (!$contentId || !is_numeric($contentId)) {
                return [
                    'success' => false,
                    'message' => 'ID de contenu invalide'
                ];
            }

            if (!in_array($contentType, ['movie', 'tv'])) {
                return [
                    'success' => false,
                    'message' => 'Type de contenu invalide'
                ];
            }

            // Appel du modèle
            $this->modelFavorite->removeFavorite($this->userId, $contentId, $contentType);
            
            return [
                'success' => true,
                'message' => 'Supprimé des favoris avec succès !'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les favoris avec gestion d'erreurs
     * @return array Résultat avec success, message et data
     */
    public function showFavorites() {
        try {
            if (!$this->userId) {
                return [
                    'success' => false,
                    'message' => 'Vous devez être connecté pour voir vos favoris',
                    'data' => []
                ];
            }

            $favorites = $this->modelFavorite->getFavorites($this->userId);
            
            return [
                'success' => true,
                'message' => 'Favoris récupérés avec succès',
                'data' => $favorites,
                'count' => count($favorites)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Vérifier si un favori existe
     * @return array Résultat avec success, message et exists
     */
    public function checkFavoriteExists($contentId, $contentType) {
        try {
            if (!$this->userId) {
                return [
                    'success' => false,
                    'message' => 'Vous devez être connecté',
                    'exists' => false
                ];
            }

            $exists = $this->modelFavorite->favoriteExists($this->userId, $contentId, $contentType);
            
            return [
                'success' => true,
                'message' => 'Vérification effectuée',
                'exists' => $exists
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'exists' => false
            ];
        }
    }

    /**
     * Compter les favoris d'un utilisateur
     * @return array Résultat avec success, message et count
     */
    public function countUserFavorites() {
        try {
            if (!$this->userId) {
                return [
                    'success' => false,
                    'message' => 'Vous devez être connecté',
                    'count' => 0
                ];
            }

            $count = $this->modelFavorite->countFavorites($this->userId);
            
            return [
                'success' => true,
                'message' => 'Comptage effectué',
                'count' => $count
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'count' => 0
            ];
        }
    }

    // ===== MÉTHODES POUR LES PAGES (GESTION LOGIQUE VUE) =====

    /**
     * Récupérer les données pour la page des favoris
     */
    public function getFavoritesPageData() {
        $result = $this->showFavorites();
        
        return [
            'success' => $result['success'],
            'favorites' => $result['data'] ?? [],
            'count' => $result['count'] ?? 0,
            'message' => $result['message']
        ];
    }

    /**
     * Traiter la suppression d'un favori via AJAX depuis favorite.php
     */
    public function handleRemoveFavoriteAjax($postData) {
        // Validation des données
        if (!isset($postData['content_id']) || !isset($postData['content_type'])) {
            return [
                'success' => false,
                'message' => 'Paramètres manquants'
            ];
        }

        $contentId = intval($postData['content_id']);
        $contentType = $postData['content_type'];

        // Validation des paramètres
        if (!$contentId || !in_array($contentType, ['movie', 'tv'])) {
            return [
                'success' => false,
                'message' => 'Paramètres invalides'
            ];
        }

        return $this->removeFavorite($contentId, $contentType);
    }

    /**
     * Traiter les requêtes AJAX POST pour favorite.php
     */
    public function handleFavoritePageAjax($postData) {
        // Toujours définir le header JSON en premier
        header('Content-Type: application/json');
        
        try {
            // Vérifier que c'est une requête AJAX pour supprimer un favori
            if (isset($postData['action']) && $postData['action'] === 'remove_favorite') {
                $result = $this->handleRemoveFavoriteAjax($postData);
                echo json_encode($result);
                exit;
            }

            // Action non reconnue
            echo json_encode([
                'success' => false,
                'message' => 'Action non reconnue: ' . ($postData['action'] ?? 'aucune'),
                'debug' => $postData
            ]);
            exit;
            
        } catch (Exception $e) {
            // Capturer toute exception inattendue
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
            exit;
        }
    }

    /**
     * Vérifier l'authentification pour favorite.php
     */
    public function requireAuthForFavorites($redirectTo = 'login') {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user'])) {
            header("Location: $redirectTo");
            exit;
        }
        
        // Mettre à jour l'userId si pas encore fait
        if (!$this->userId) {
            $this->userId = $_SESSION['user'];
        }
        
        return $_SESSION['user'];
    }
}

?>
