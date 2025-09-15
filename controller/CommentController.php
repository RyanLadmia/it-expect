<?php
class CommentController
{
    private $ModelComment;

    public function __construct()
    {
        $this->ModelComment = new ModelComment();
    }

    // Ajouter un commentaire
    public function addComment($userId, $itemId, $type, $content)
    {
        return $this->ModelComment->addComment($userId, $itemId, $type, $content);
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
}
?>
