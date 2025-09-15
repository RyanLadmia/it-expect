<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Gestion de la déconnexion
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Refresh:0");
    exit;
}

// Vérifiez si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user']);

// Récupération des données GET pour afficher les commentaires
$itemId = $_GET['id'] ?? null;
$itemType = $_GET['type'] ?? null;

if (!$itemId || !$itemType) {
    die('Paramètres manquants.');
}

if (!in_array($itemType, ['movie', 'tv'])) {
    die('Type invalide.');
}

// Initialisation du contrôleur des commentaires
$commentController = new CommentController();
$comments = $commentController->getComments($itemId, $itemType);

// Gestion des requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification de l'utilisateur connecté
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour effectuer cette action.']);
        exit;
    }

    $userId = $_SESSION['user'];

    // Traitement des requêtes JSON (AJAX) pour ajout de commentaire ou favori
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        if (!isset($input['id'], $input['type'])) {
            echo json_encode(['success' => false, 'message' => 'Paramètres manquants.']);
            exit;
        }

        $itemId = intval($input['id']);
        $itemType = $input['type'];

        if (isset($input['content'])) {
            // Ajout de commentaire
            $content = trim($input['content']);
            if (empty($content)) {
                echo json_encode(['success' => false, 'message' => 'Le commentaire ne peut pas être vide.']);
                exit;
            }

            $success = $commentController->addComment($userId, $itemId, $itemType, $content);
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'firstname' => $_SESSION['user_firstname'] ?? 'Anonyme',
                    'lastname' => $_SESSION['user_lastname'] ?? 'Anonyme',
                    'content' => $content,
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Impossible d\'ajouter le commentaire.']);
            }
        } elseif (isset($input['reply_content']) && isset($input['comment_id'])) {
            // Ajout d'une réponse à un commentaire
            $content = trim($input['reply_content']);
            $commentId = intval($input['comment_id']);
            
            if (empty($content)) {
                echo json_encode(['success' => false, 'message' => 'La réponse ne peut pas être vide.']);
                exit;
            }

            $success = $commentController->addReply($userId, $commentId, $content);
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
        } elseif (isset($input['comment_id']) && $input['action'] === 'delete_comment') {
            // Suppression de commentaire via AJAX
            $commentId = intval($input['comment_id']);
            
            // Vérifie si le commentaire appartient bien à l'utilisateur
            $success = $commentController->deleteComment($commentId, $userId);
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Commentaire supprimé avec succès.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Impossible de supprimer le commentaire.']);
            }
        } elseif (isset($input['reply_id']) && $input['action'] === 'delete_reply') {
            // Suppression d'une réponse via AJAX
            $replyId = intval($input['reply_id']);
            
            // Vérifie si la réponse appartient bien à l'utilisateur
            $success = $commentController->deleteReply($replyId, $userId);
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Réponse supprimée avec succès.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Impossible de supprimer la réponse.']);
            }
        } else {
            // Ajout aux favoris
            $favoritesModel = new ModelFavorite();
            $success = $favoritesModel->addFavorite($userId, $itemId, $itemType);

            echo json_encode(['success' => $success]);
        }
        exit;
    }

    // Gestion de la suppression de commentaire via formulaire classique (si nécessaire)
    if (isset($_POST['delete_comment'])) {
        $commentId = intval($_POST['comment_id']);
        
        // Vérifiez que le commentaire appartient bien à l'utilisateur
        $success = $commentController->deleteComment($commentId, $userId);
        if ($success) {
            echo 'Commentaire supprimé avec succès.';
        } else {
            echo 'Impossible de supprimer le commentaire.';
        }

        // Redirection pour éviter la soumission multiple du formulaire
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    
    // Gestion de la suppression d'une réponse via formulaire classique (si nécessaire)
    if (isset($_POST['delete_reply'])) {
        $replyId = intval($_POST['reply_id']);
        
        // Vérifiez que la réponse appartient bien à l'utilisateur
        $success = $commentController->deleteReply($replyId, $userId);
        if ($success) {
            echo 'Réponse supprimée avec succès.';
        } else {
            echo 'Impossible de supprimer la réponse.';
        }

        // Redirection pour éviter la soumission multiple du formulaire
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

?>
<h1>Détails :</h1>

<!-- Conteneur principal pour le layout responsive -->
<div class="detail-page-container">
    <!-- Colonne de gauche - Détails du film/série -->
    <div class="detail-left-column">
        <div id="details" data-is-logged-in="<?= $isLoggedIn ? 'true' : 'false'; ?>" class="detail-card"></div>
    </div>
    
    <!-- Colonne de droite - Commentaires -->
    <div class="detail-right-column">
        <!-- Section des commentaires -->
        <div id="comments-section">
            <h3>Commentaires</h3>
            
            <div id="comments-list">
                <!-- Affichage des commentaires -->
                <?php for($i = 0; $i < count($comments); $i++): 
                    $comment = $comments[$i];
                    $comment_id = $comment['comment_id']; 
                ?>
                    <div class="comment" data-comment-id="<?= htmlspecialchars($comment_id) ?>" id="comment-<?= $comment_id ?>">
                        <strong><?= htmlspecialchars($comment['user_firstname'] . ' ' . $comment['user_lastname']) ?></strong>
                        <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                        <small>Posté le <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></small>
                        
                        <!-- Bouton de suppression du commentaire -->
                        <?php if (isset($_SESSION['user']) && $_SESSION['user'] == $comment['user_id']): ?>
                            <button class="delete-comment-btn" data-comment-id="<?= $comment_id ?>">Supprimer mon commentaire</button>
                        <?php endif; ?>
                        
                        <!-- Réponses au commentaire -->
                        <div class="replies-section" id="replies-section-<?= $comment_id ?>">
                            <?php if (!empty($comment['replies'])): ?>
                                <div class="replies-count" data-count="<?= count($comment['replies']) ?>">
                                    <?= count($comment['replies']) ?> réponse<?= count($comment['replies']) > 1 ? 's' : '' ?>
                                </div>
                                
                                <div class="replies-list">
                                    <?php foreach ($comment['replies'] as $reply): ?>
                                        <div class="reply" data-reply-id="<?= $reply['reply_id'] ?>" id="reply-<?= $reply['reply_id'] ?>">
                                            <strong><?= htmlspecialchars($reply['user_firstname'] . ' ' . $reply['user_lastname']) ?></strong>
                                            <p><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                                            <small>Posté le <?= date('d/m/Y H:i', strtotime($reply['created_at'])) ?></small>
                                            
                                            <!-- Bouton de suppression de réponse -->
                                            <?php if (isset($_SESSION['user']) && $_SESSION['user'] == $reply['user_id']): ?>
                                                <button class="delete-reply-btn" data-reply-id="<?= $reply['reply_id'] ?>">Supprimer ma réponse</button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Bouton et formulaire de réponse -->
                            <?php if ($isLoggedIn): ?>
                                <button class="reply-btn" data-comment-id="<?= $comment_id ?>">Répondre</button>
                                <div class="reply-form-container" id="reply-form-<?= $comment_id ?>" style="display: none;">
                                    <form class="reply-form" data-comment-id="<?= $comment_id ?>">
                                        <textarea class="reply-content" placeholder="Écrivez votre réponse..." required></textarea>
                                        <div class="reply-actions">
                                            <button type="submit" class="submit-reply-btn">Envoyer</button>
                                            <button type="button" class="cancel-reply-btn">Annuler</button>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <?php if ($isLoggedIn): ?>
                <form id="comment-form" data-item-id="<?= htmlspecialchars($itemId) ?>" data-item-type="<?= htmlspecialchars($itemType) ?>" method="POST">
                    <textarea id="comment-content" name="content" placeholder="Ajoutez un commentaire..." required></textarea>
                    <button type="submit">Ajouter un commentaire</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Section du bas - Crédits et éléments similaires -->
    <div class="detail-bottom-section">
        <div id="credits" class="detail-card"></div>
        <div id="similar" class="detail-card"></div>
    </div>
</div>


