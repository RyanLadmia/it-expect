<?php

class ModelComment
{
    private $connexion;

    public function __construct()
    {
        $conn = new Connection();
        $this->connexion = $conn->connectionDB();
        $this->connexion->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    // Ajouter un commentaire
    public function addComment($userId, $itemId, $type, $content)
    {
        $stmt = $this->connexion->prepare("
            INSERT INTO comments (user_id, item_id, type, content) 
            VALUES (:user_id, :item_id, :type, :content)
        ");
        return $stmt->execute([
            ':user_id' => $userId,
            ':item_id' => $itemId,
            ':type' => $type,
            ':content' => $content
        ]);
    }

    // Récupérer les commentaires
    public function getComments($itemId, $type)
    {
        try {
            // Requête simple pour récupérer les commentaires
            $stmt = $this->connexion->prepare("
                SELECT comment_id, content, created_at, user_id
                FROM comments
                WHERE item_id = :item_id AND type = :type
                ORDER BY created_at ASC
            ");
            
            $stmt->execute([
                ':item_id' => $itemId,
                ':type' => $type
            ]);
            
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Pour chaque commentaire, obtenir les informations utilisateur
            $processed = [];
            foreach ($comments as $comment) {
                // Récupérer les infos utilisateur
                $userStmt = $this->connexion->prepare("
                    SELECT user_firstname, user_lastname
                    FROM Users
                    WHERE user_id = :user_id
                ");
                $userStmt->execute([':user_id' => $comment['user_id']]);
                $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
                
                // Fusionner les données
                $comment['user_firstname'] = $userData['user_firstname'] ?? 'Anonyme';
                $comment['user_lastname'] = $userData['user_lastname'] ?? '';
                
                $processed[] = $comment;
            }
            
            return $processed;
        } catch (Exception $e) {
            // En cas d'erreur, retourner un tableau vide
            return [];
        }
    }

    // Supprimer un commentaire
    public function deleteComment($commentId, $userId)
    {
        // D'abord supprimer les réponses associées à ce commentaire
        $this->deleteRepliesForComment($commentId);
        
        // Ensuite supprimer le commentaire lui-même
        $stmt = $this->connexion->prepare("
            DELETE FROM comments 
            WHERE comment_id = :comment_id AND user_id = :user_id
        ");
        return $stmt->execute([
            ':comment_id' => $commentId,
            ':user_id' => $userId
        ]);
    }

    // Ajouter une réponse à un commentaire
    public function addReply($userId, $commentId, $content)
    {
        $stmt = $this->connexion->prepare("
            INSERT INTO replies (user_id, comment_id, content) 
            VALUES (:user_id, :comment_id, :content)
        ");
        return $stmt->execute([
            ':user_id' => $userId,
            ':comment_id' => $commentId,
            ':content' => $content
        ]);
    }

    // Récupérer les réponses pour un commentaire
    public function getReplies($commentId)
    {
        $stmt = $this->connexion->prepare("
            SELECT r.reply_id, r.content, r.created_at, u.user_firstname, u.user_lastname, u.user_id
            FROM replies r
            JOIN Users u ON r.user_id = u.user_id
            WHERE r.comment_id = :comment_id
            ORDER BY r.created_at ASC
        ");
        $stmt->execute([
            ':comment_id' => $commentId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Supprimer une réponse
    public function deleteReply($replyId, $userId)
    {
        $stmt = $this->connexion->prepare("
            DELETE FROM replies 
            WHERE reply_id = :reply_id AND user_id = :user_id
        ");
        return $stmt->execute([
            ':reply_id' => $replyId,
            ':user_id' => $userId
        ]);
    }
    
    // Supprimer toutes les réponses pour un commentaire spécifique
    private function deleteRepliesForComment($commentId)
    {
        $stmt = $this->connexion->prepare("
            DELETE FROM replies 
            WHERE comment_id = :comment_id
        ");
        return $stmt->execute([
            ':comment_id' => $commentId
        ]);
    }
}
?>
