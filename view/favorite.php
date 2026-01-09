<?php
// ===== TRAITEMENT CENTRALISÉ - AUCUNE LOGIQUE DANS LA VUE =====
$pageController = new PageController();
$pageData = $pageController->handleFavoritePage();

// Variables pour la vue (extraction simple)
$favorites = $pageData['favorites'];
$success = $pageData['success'];
$message = $pageData['message'];
?>

<h1>Mes Favoris</h1>

<?php if (empty($favorites)): ?>
    <p class="no_favorite">Vous n'avez pas encore de favoris.</p>
<?php else: ?>
    <ul id="favoritesList">
    <?php foreach ($favorites as $favorite): ?>
        <li id="favorite_<?= $favorite['content_id'] ?>" class="favorite-item" data-content-id="<?= $favorite['content_id'] ?>" data-content-type="<?= $favorite['content_type'] ?>">
            <div class="favorite-content">
                <h3 class="favorite-title"></h3>
                <img class="favorite-image" src="" alt="Image du favori" />
                
                <div class="favorite-action-buttons">
                    <a href="" class="view-detail" data-content-id="<?= $favorite['content_id'] ?>">Voir les détails</a>
                    <button class="remove-favorite-btn">Retirer de mes favoris</button>
                </div>
                
                <div class="confirmation-message" style="display: none;">
                    <p>Voulez-vous vraiment retirer ce favori de votre liste ?</p>
                    <button class="confirm-remove green-btn">Oui</button>
                    <button class="cancel-remove red-btn">Non</button>
                    <p class="success-message" style="display: none;">Favori retiré avec succès.</p>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gérer l'affichage des messages de confirmation
        const removeBtns = document.querySelectorAll('.remove-favorite-btn');
        
        removeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const favItem = this.closest('.favorite-item');
                const confirmation = favItem.querySelector('.confirmation-message');
                confirmation.style.display = 'block';
            });
        });
        
        // Gérer l'annulation
        const cancelBtns = document.querySelectorAll('.cancel-remove');
        
        cancelBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const confirmation = this.closest('.confirmation-message');
                confirmation.style.display = 'none';
            });
        });
        
        // Gérer la confirmation de suppression
        const confirmBtns = document.querySelectorAll('.confirm-remove');
        
        confirmBtns.forEach(btn => {
            let isProcessing = false;
            btn.addEventListener('click', function() {
                if (isProcessing) {
                    return; // Ignorer si déjà en cours de traitement
                }
                isProcessing = true;
                this.disabled = true;
                
                const favItem = this.closest('.favorite-item');
                const contentId = favItem.dataset.contentId;
                const contentType = favItem.dataset.contentType;
                const confirmationMsg = this.closest('.confirmation-message');
                const successMsg = confirmationMsg.querySelector('.success-message');
                
                // Requête AJAX pour supprimer le favori
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove_favorite&content_id=${contentId}&content_type=${contentType}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Afficher le message de succès
                        if (successMsg) {
                            successMsg.style.display = 'block';
                        }
                        
                        // Masquer les boutons
                        this.style.display = 'none';
                        const cancelBtn = this.nextElementSibling;
                        if (cancelBtn && cancelBtn.classList.contains('cancel-remove')) {
                            cancelBtn.style.display = 'none';
                        }
                        
                        // Retirer l'élément après un délai
                        setTimeout(() => {
                            favItem.style.opacity = '0';
                            favItem.style.transform = 'scale(0.8)';
                            favItem.style.transition = 'all 0.5s ease';
                            
                            setTimeout(() => {
                                favItem.remove();
                                
                                // Si plus aucun favori, afficher le message
                                if (document.querySelectorAll('.favorite-item').length === 0) {
                                    const favoritesList = document.querySelector('#favoritesList');
                                    if (favoritesList) {
                                        const noFavorite = document.createElement('p');
                                        noFavorite.className = 'no_favorite';
                                        noFavorite.textContent = 'Vous n\'avez pas encore de favoris.';
                                        favoritesList.replaceWith(noFavorite);
                                    }
                                }
                            }, 500);
                        }, 1500);
                    } else {
                        // Afficher l'erreur dans le message de confirmation au lieu d'une modale
                        const errorMsg = document.createElement('p');
                        errorMsg.className = 'error-message';
                        errorMsg.style.color = '#f44336';
                        errorMsg.textContent = data.message || 'Une erreur est survenue.';
                        confirmationMsg.appendChild(errorMsg);
                        
                        // Réactiver le bouton après 3 secondes
                        setTimeout(() => {
                            isProcessing = false;
                            this.disabled = false;
                            if (errorMsg.parentNode) {
                                errorMsg.remove();
                            }
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    const errorMsg = document.createElement('p');
                    errorMsg.className = 'error-message';
                    errorMsg.style.color = '#f44336';
                    errorMsg.textContent = 'Erreur de communication avec le serveur.';
                    confirmationMsg.appendChild(errorMsg);
                    
                    setTimeout(() => {
                        isProcessing = false;
                        this.disabled = false;
                        if (errorMsg.parentNode) {
                            errorMsg.remove();
                        }
                    }, 3000);
                });
            });
        });
    });
</script>
