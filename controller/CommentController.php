<?php
class CommentController
{
    private $ModelComment;

    public function __construct()
    {
        $this->ModelComment = new ModelComment();
    }

    /**
     * Ajouter un commentaire avec validation et gestion d'erreurs
     * @return array Résultat avec success et message
     */
    public function addComment($userId, $itemId, $type, $content)
    {
        try {
            // Validation côté contrôleur
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Vous devez être connecté pour commenter'
                ];
            }

            if (empty(trim($content))) {
                return [
                    'success' => false,
                    'message' => 'Le commentaire ne peut pas être vide'
                ];
            }

            if (strlen($content) > 1000) {
                return [
                    'success' => false,
                    'message' => 'Le commentaire est trop long (maximum 1000 caractères)'
                ];
            }

            // Appel du modèle
            $this->ModelComment->addComment($userId, $itemId, $type, $content);
            
            return [
                'success' => true,
                'message' => 'Commentaire ajouté avec succès !'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Récupérer les commentaires
    public function getComments($itemId, $type)
    {
        // Récupérer les commentaires depuis le modèle
        $comments = $this->ModelComment->getComments($itemId, $type);
        
        // Récupérer les réponses pour chaque commentaire
        foreach($comments as $i => $comment) {
            // Ajouter les réponses au commentaire
            $comments[$i]['replies'] = $this->getReplies($comment['comment_id']);
        }
        
        return $comments;
    }

    // Supprimer un commentaire
    public function deleteComment($commentId, $userId)
    {
        return $this->ModelComment->deleteComment($commentId, $userId);
    }

    // Ajouter une réponse à un commentaire
    public function addReply($userId, $commentId, $content)
    {
        return $this->ModelComment->addReply($userId, $commentId, $content);
    }

    // Récupérer les réponses d'un commentaire
    public function getReplies($commentId)
    {
        return $this->ModelComment->getReplies($commentId);
    }

    // Supprimer une réponse
    public function deleteReply($replyId, $userId)
    {
        return $this->ModelComment->deleteReply($replyId, $userId);
    }

    // Gérer les requêtes liées aux commentaires
    public function handleRequest()
    {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'], $_POST['item_id'], $_POST['type'])) {
            // Ajouter un commentaire
            if (!isset($_SESSION['user'])) {
                echo 'Vous devez être connecté pour ajouter un commentaire.';
                exit;
            }

            $userId = $_SESSION['user'];
            $itemId = intval($_POST['item_id']);
            $type = $_POST['type'];
            $content = trim($_POST['content']);

            if (empty($content)) {
                echo 'Le commentaire ne peut pas être vide.';
                exit;
            }

            $this->addComment($userId, $itemId, $type, $content);
            header("Location: {$_SERVER['PHP_SELF']}?id=$itemId&type=$type");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'], $_POST['comment_id'])) {
            // Supprimer un commentaire
            if (!isset($_SESSION['user'])) {
                echo 'Vous devez être connecté pour supprimer un commentaire.';
                exit;
            }

            $userId = $_SESSION['user'];
            $commentId = intval($_POST['comment_id']);

            $success = $this->deleteComment($commentId, $userId);

            if ($success) {
                echo 'Commentaire supprimé avec succès.';
            } else {
                echo 'Impossible de supprimer le commentaire.';
            }

            // Rediriger après suppression pour éviter la soumission multiple du formulaire
            header("Location: {$_SERVER['PHP_SELF']}?id={$_POST['item_id']}&type={$_POST['type']}");
            exit;
        }
    }

    // ===== MÉTHODES POUR LES PAGES (GESTION LOGIQUE VUE) =====

    /**
     * Vérifier l'authentification et récupérer l'userId
     */
    public function requireAuth() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user'])) {
            return null;
        }
        
        return $_SESSION['user'];
    }

    /**
     * Traiter les requêtes AJAX pour detail.php
     */
    public function handleDetailPageAjax($input, $userId) {
        header('Content-Type: application/json');
        
        // Validation de base
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour effectuer cette action.']);
            exit;
        }

        if (!isset($input['id'], $input['type'])) {
            echo json_encode(['success' => false, 'message' => 'Paramètres manquants.']);
            exit;
        }

        $itemId = intval($input['id']);
        $itemType = $input['type'];

        // Ajout de commentaire
        if (isset($input['content'])) {
            $content = trim($input['content']);
            if (empty($content)) {
                echo json_encode(['success' => false, 'message' => 'Le commentaire ne peut pas être vide.']);
                exit;
            }

            $result = $this->addComment($userId, $itemId, $itemType, $content);
            if ($result['success']) {
                $result['firstname'] = $_SESSION['user_firstname'] ?? 'Anonyme';
                $result['lastname'] = $_SESSION['user_lastname'] ?? 'Anonyme';
                $result['content'] = $content;
            }
            echo json_encode($result);
            exit;
        }

        // Ajout de réponse
        if (isset($input['reply_content']) && isset($input['comment_id'])) {
            $content = trim($input['reply_content']);
            $commentId = intval($input['comment_id']);
            
            if (empty($content)) {
                echo json_encode(['success' => false, 'message' => 'La réponse ne peut pas être vide.']);
                exit;
            }

            $success = $this->addReply($userId, $commentId, $content);
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'firstname' => $_SESSION['user_firstname'] ?? 'Anonyme',
                    'lastname' => $_SESSION['user_lastname'] ?? 'Anonyme',
                    'content' => $content,
                    'comment_id' => $commentId
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Impossible d\'ajouter la réponse.']);
            }
            exit;
        }

        // Suppression de commentaire
        if (isset($input['comment_id']) && $input['action'] === 'delete_comment') {
            $commentId = intval($input['comment_id']);
            
            $success = $this->deleteComment($commentId, $userId);
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Commentaire supprimé avec succès.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Impossible de supprimer le commentaire.']);
            }
            exit;
        }

        // Suppression de réponse
        if (isset($input['reply_id']) && $input['action'] === 'delete_reply') {
            $replyId = intval($input['reply_id']);
            
            $success = $this->deleteReply($replyId, $userId);
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Réponse supprimée avec succès.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Impossible de supprimer la réponse.']);
            }
            exit;
        }

        // Si aucune action reconnue, c'est probablement un ajout aux favoris
        echo json_encode(['success' => false, 'message' => 'Action non reconnue pour CommentController']);
        exit;
    }

    /**
     * Traiter les requêtes POST classiques (formulaires)
     */
    public function handleDetailPagePost($postData, $userId) {
        // Suppression de commentaire via formulaire
        if (isset($postData['delete_comment'])) {
            $commentId = intval($postData['comment_id']);
            
            $success = $this->deleteComment($commentId, $userId);
            if ($success) {
                echo 'Commentaire supprimé avec succès.';
            } else {
                echo 'Impossible de supprimer le commentaire.';
            }

            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
        
        // Suppression de réponse via formulaire
        if (isset($postData['delete_reply'])) {
            $replyId = intval($postData['reply_id']);
            
            $success = $this->deleteReply($replyId, $userId);
            if ($success) {
                echo 'Réponse supprimée avec succès.';
            } else {
                echo 'Impossible de supprimer la réponse.';
            }

            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }


    /**
     * Valider les paramètres de la page detail
     */
    public function validateDetailParams($itemId, $itemType) {
        if (!$itemId || !$itemType) {
            die('Paramètres manquants.');
        }

        if (!in_array($itemType, ['movie', 'tv'])) {
            die('Type invalide.');
        }
    }
}
?>
