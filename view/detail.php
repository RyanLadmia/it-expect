<?php
// ===== TRAITEMENT CENTRALISÉ - AUCUNE LOGIQUE DANS LA VUE =====
$pageController = new PageController();
$pageData = $pageController->handleDetailPage();

// Variables pour la vue (extraction simple)
$itemId = $pageData['itemId'];
$itemType = $pageData['itemType'];
$userId = $pageData['userId'];
$isLoggedIn = $pageData['isLoggedIn'];
$comments = $pageData['comments'];
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
                        <?php if ($userId && $userId == $comment['user_id']): ?>
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
                                            <?php if ($userId && $userId == $reply['user_id']): ?>
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


