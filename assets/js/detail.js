// Fonction pour afficher un message d'erreur
function showError(container, message) {
    if (container) {
        container.innerHTML = `<div class="error-message">
            <p>${message}</p>
        </div>`;
    }
}

// Fonction pour modifier le format des dates en JJ/MM/AAAA
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Mois est zéro-indexé
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
}

// Dictionnaire de correspondance des codes pays en français
const countryTranslations = {
    "US": "États-Unis",
    "FR": "France",
    "GB": "Royaume-Uni",
    "DE": "Allemagne",
    "IT": "Italie",
    "ES": "Espagne",
    "CA": "Canada",
    "JP": "Japon",
    "KR": "Corée du Sud",
    "IN": "Inde",
    // Ajoutez d'autres pays selon vos besoins
};

// Récupérer l'ID et le type (film ou série) du paramètre d'URL
const urlParams = new URLSearchParams(window.location.search);
const id = urlParams.get('id');  // Cela extrait l'ID du paramètre 'id' dans l'URL
const type = urlParams.get('type'); // Cela extrait le type (movie ou tv) du paramètre 'type'

// Vérification si l'utilisateur est connecté (injecté depuis PHP dans un attribut data)
const detailsElement = document.getElementById("details");
const isLoggedIn = detailsElement ? detailsElement.dataset.isLoggedIn === 'true' : false;

if (id && type) {
    // Fetch API configuration from secure endpoint
    fetch('/it-expect/api-config')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération de la configuration API');
            }
            return response.json();
        })
        .then(config => {
            if (!config.apiKey) {
                throw new Error('Clé API non trouvée');
            }
            
            const apiKey = config.apiKey;
            const imageBaseUrl = config.imageBaseUrl;
            const movieBaseUrl = config.movieBaseUrl;
            const tvBaseUrl = config.tvBaseUrl;
            
            let urlDetails = '';
            let urlCredits = '';
            let urlSimilar = '';

            if (type === 'movie') {
                // Si c'est un film
                urlDetails = `${movieBaseUrl}/${id}?api_key=${apiKey}&language=fr-FR`;
                urlCredits = `${movieBaseUrl}/${id}/credits?api_key=${apiKey}&language=fr-FR`;
                urlSimilar = `${movieBaseUrl}/${id}/similar?api_key=${apiKey}&language=fr-FR&page=1`;
            } else if (type === 'tv') {
                // Si c'est une série
                urlDetails = `${tvBaseUrl}/${id}?api_key=${apiKey}&language=fr-FR`;
                urlCredits = `${tvBaseUrl}/${id}/credits?api_key=${apiKey}&language=fr-FR`;
                urlSimilar = `${tvBaseUrl}/${id}/similar?api_key=${apiKey}&language=fr-FR&page=1`;
            }

            // Récupérer les détails du film ou de la série
            fetch(urlDetails)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur lors de la récupération des détails');
                    }
                    return response.json();
                })
                .then(data => {
                    const movieContainer = document.getElementById("details");

                    // Vérifier si l'élément (film ou série) a une affiche
                    if (data.poster_path) {
                        // Créer un élément pour afficher les détails du film ou de la série
                        movieContainer.innerHTML = `
                            <h2>${data.title || data.name}</h2>
                            <img src="${imageBaseUrl}${data.poster_path}" alt="${data.title || data.name}">
                            <p>Date de sortie : ${formatDate(data.release_date || data.first_air_date)}</p>
                            <p>Note : ${data.vote_average}</p>
                            <p>Origine : ${data.production_countries.map(country => countryTranslations[country.iso_3166_1] || country.name).join(', ')}</p>
                            <p>Genres : ${data.genres.map(genre => genre.name).join(', ')}</p>
                            <p>${data.overview}</p>
                        `;

                        // Ajouter le bouton "Ajouter aux favoris" uniquement si l'utilisateur est connecté
                        if (isLoggedIn) {
                            const addToFavoritesButton = document.createElement('button');
                            addToFavoritesButton.id = "favorite-button";  // Ajoutez un id ici
                            addToFavoritesButton.textContent = "Ajouter aux favoris";
                            addToFavoritesButton.style.marginTop = "10px";

                            const feedbackMessage = document.createElement('p');
                            feedbackMessage.id = "feedbackMessage";
                            feedbackMessage.style.marginTop = "10px";

                            addToFavoritesButton.addEventListener('click', () => {
                                fetch(window.location.href, {
                                    method: 'POST',
                                    action: "add_favorite", // Action pour ajouter aux favoris
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({ id: id, type: type }),
                                })
                                    .then(response => response.json())
                                    .then(result => {
                                        if (result.success) {
                                            feedbackMessage.textContent = "Ajouté aux favoris avec succès !";
                                            feedbackMessage.style.color = "green";
                                        } else {
                                            feedbackMessage.textContent = `Erreur : ${result.message}`;
                                            feedbackMessage.style.color = "red";
                                        }
                                    })
                                    .catch(error => {
                                        feedbackMessage.textContent = "Cet élément existe déjà dans vos favoris.";
                                        feedbackMessage.style.color = "red";
                                    });
                            });

                            movieContainer.appendChild(addToFavoritesButton);
                            movieContainer.appendChild(feedbackMessage);
                        }

                        // Récupérer les genres du film ou de la série (sous forme d'ID)
                        const genresPrincipal = data.genres.map(genre => genre.id);

                        // Récupérer les crédits et les éléments similaires
                        fetch(urlCredits)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Erreur lors de la récupération des crédits');
                                }
                                return response.json();
                            })
                            .then(data => {
                                const creditsContainer = document.getElementById("credits");
                                if (creditsContainer) {
                                    creditsContainer.innerHTML = `<h3>Acteurs et Réalisateur :</h3>`;

                                    // Afficher les acteurs (les 5 premiers)
                                    creditsContainer.innerHTML += `<h4>Acteurs :</h4><ul>`;
                                    data.cast.slice(0, 5).forEach(actor => {
                                        const actorImage = actor.profile_path ? `${imageBaseUrl}${actor.profile_path}` : 'assets/medias/no-profile.jpg';
                                        creditsContainer.innerHTML += `
                                            <li>
                                                <img src="${actorImage}" alt="${actor.name}">
                                                <span>${actor.name}</span>
                                                <small>${actor.character}</small>
                                            </li>
                                        `;
                                    });
                                    creditsContainer.innerHTML += `</ul>`;

                                    // Afficher le réalisateur
                                    const directors = data.crew.filter(member => member.job === 'Director');
                                    if (directors && directors.length > 0) {
                                        creditsContainer.innerHTML += `<h4>Réalisateur${directors.length > 1 ? 's' : ''} :</h4><div class="directors-section">`;
                                        
                                        directors.forEach(director => {
                                            const directorImage = director.profile_path ? `${imageBaseUrl}${director.profile_path}` : 'assets/medias/no-profile.jpg';
                                        creditsContainer.innerHTML += `
                                                <div class="director-container">
                                                    <img src="${directorImage}" alt="${director.name}">
                                                    <span>${director.name}</span>
                                                    <small>Réalisateur</small>
                                            </div>
                                        `;
                                        });
                                        
                                        creditsContainer.innerHTML += `</div>`;
                                    } else {
                                        creditsContainer.innerHTML += `
                                            <h4>Réalisateur :</h4>
                                            <div class="directors-section">
                                                <div class="director-container">
                                                    <img src="assets/medias/no-profile.jpg" alt="Non disponible">
                                                    <span>Information non disponible</span>
                                                    <small>Réalisateur</small>
                                                </div>
                                            </div>
                                        `;
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Erreur :', error);
                                showError(document.getElementById("credits"), 'Impossible de charger les crédits');
                            });


                        fetch(urlSimilar)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Erreur lors de la récupération des éléments similaires');
                                }
                                return response.json();
                            })
                            .then(data => {
                                const similarContainer = document.getElementById("similar");
                                if (similarContainer) {
                                    similarContainer.innerHTML = `<h3>${type === 'movie' ? 'Films similaires' : 'Séries similaires'} :</h3><ul>`;

                                    // Appliquer un filtre de popularité et de genres
                                    const MIN_POPULARITY = 25;  // Minimum de popularité
                                    const MIN_VOTES = 50;      // Minimum de votes

                                    // Filtrer les éléments similaires pour ne garder que ceux avec une affiche et des critères de popularité et de votes
                                    const elementsAvecAfficheEtPopulaires = data.results.filter(element => 
                                        element.poster_path && 
                                        element.popularity >= MIN_POPULARITY && 
                                        element.vote_count >= MIN_VOTES
                                    );

                                    // Filtrer les éléments qui partagent au moins un genre avec l'élément principal
                                    const elementsAvecGenresCommuns = elementsAvecAfficheEtPopulaires.filter(element => {
                                        const genresElement = element.genre_ids || []; // Genres de l'élément similaire
                                        return genresElement.some(genreId => genresPrincipal.includes(genreId)); // Vérification des genres communs
                                    });

                                    // Afficher uniquement les éléments avec un genre commun et qui respectent les autres critères (limité à 3)
                                    elementsAvecGenresCommuns.slice(0, 3).forEach(element => {
                                        similarContainer.innerHTML += `
                                            <li>
                                                <h4>${element.title || element.name}</h4>
                                                <img src="${imageBaseUrl}${element.poster_path}" alt="${element.title || element.name}">
                                                <a href="detail?id=${element.id}&type=${type}">
                                                    <button class="similar-detail-btn" style="background-color: #3498db; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-top: 10px; transition: background-color 0.3s;">Voir les détails</button>
                                                </a>
                                            </li>
                                        `;
                                    });

                                    similarContainer.innerHTML += `</ul>`;

                                    // Vérifier si des éléments similaires ont été ajoutés
                                    const listItems = similarContainer.querySelectorAll('ul li');
                                    if (listItems.length === 0) {
                                        // Si aucun élément similaire n'a été trouvé, masquer la section
                                        similarContainer.style.display = 'none';
                                    } else {
                                        // Ajouter un effet hover sur les boutons
                                        setTimeout(() => {
                                            document.querySelectorAll('.similar-detail-btn').forEach(btn => {
                                                btn.addEventListener('mouseover', () => {
                                                    btn.style.backgroundColor = '#2980b9';
                                                });
                                                btn.addEventListener('mouseout', () => {
                                                    btn.style.backgroundColor = '#3498db';
                                                });
                                            });
                                        }, 100);
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Erreur :', error);
                                showError(document.getElementById("similar"), 'Impossible de charger les éléments similaires');
                            });
                    } else {
                        showError(movieContainer, 'Aucune image disponible pour cet élément');
                    }
                })
                .catch(error => {
                    console.error('Erreur :', error);
                    showError(document.getElementById("details"), 'Impossible de charger les détails');
                });
        })
        .catch(error => {
            console.error('Erreur de configuration:', error);
            showError(document.getElementById("details"), 'Erreur de configuration API');
        });
} else {
    showError(document.getElementById("details"), 'Identifiant ou type manquant');
}

// Ajout aux favoris
document.addEventListener("DOMContentLoaded", () => {
    const favoriteButton = document.getElementById("favorite-button");

    // Vérification si l'utilisateur est connecté (par exemple, via une variable JavaScript globale ou un token)
    const isUserLoggedIn = localStorage.getItem("userLoggedIn") === "true"; // Exemple avec localStorage

    // Masquer le bouton si l'utilisateur n'est pas connecté
    if (favoriteButton && !isUserLoggedIn) {
        favoriteButton.style.display = "none";
    }

    // Gestion des favoris
    if (favoriteButton) {
        favoriteButton.addEventListener("click", async () => {
            const itemId = favoriteButton.getAttribute("data-item-id");
            const itemType = favoriteButton.getAttribute("data-item-type");

            // Vérification des données nécessaires
            if (!itemId || !itemType) {
                alert("Données manquantes pour ajouter aux favoris.");
                return;
            }

            try {
                const response = await fetch(window.location.href, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        action: "add_favorite", // Action pour ajouter aux favoris
                        id: itemId,
                        type: itemType,
                    }),
                });

                const result = await response.json();

                if (result.success) {
                    alert("Favori ajouté avec succès !");
                } else {
                    alert(result.message || "Erreur lors de la mise à jour des favoris.");
                }
            } catch (error) {
                // Supprimer la journalisation des erreurs
                // console.error("Erreur:", error);
                alert("Une erreur s'est produite. Veuillez réessayer.");
            }
        });
    }
});

// Gestion des commentaires
document.addEventListener("DOMContentLoaded", () => {
    const commentForm = document.getElementById("comment-form");

    // Lorsque le formulaire est soumis pour ajouter un commentaire
    if (commentForm) {
        commentForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            // Désactiver le défilement
            document.body.style.overflow = 'hidden';

            // Sauvegarder la position de défilement avant la soumission
            const scrollPosition = window.scrollY;
            sessionStorage.setItem('scrollPosition', scrollPosition); // Enregistrer dans sessionStorage

            const itemId = commentForm.getAttribute("data-item-id");
            const itemType = commentForm.getAttribute("data-item-type");
            const commentContent = document.getElementById("comment-content").value.trim();

            if (!commentContent) {
                alert("Le commentaire ne peut pas être vide.");
                return;
            }

            try {
                const response = await fetch(window.location.href, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        action: "add_comment",
                        id: itemId,
                        type: itemType,
                        content: commentContent,
                    }),
                });

                const result = await response.json();

                if (result.success) {
                    // Recharger la page pour afficher le nouveau commentaire
                    location.reload();
                } else {
                    alert(result.message || "Erreur lors de l'ajout du commentaire.");
                }
            } catch (error) {
                // Supprimer la journalisation des erreurs
                // console.error("Erreur attrapée :", error);
                alert("Une erreur s'est produite. Veuillez réessayer.");
            }
        });
    }

    // Gestion des boutons de réponse aux commentaires
    const replyButtons = document.querySelectorAll('.reply-btn');
    replyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const commentId = this.getAttribute('data-comment-id');
            const replyForm = document.getElementById(`reply-form-${commentId}`);
            
            // Masquer tous les autres formulaires de réponse
            document.querySelectorAll('.reply-form-container').forEach(form => {
                if (form.id !== `reply-form-${commentId}`) {
                    form.style.display = 'none';
                }
            });
            
            // Afficher/masquer le formulaire de réponse
            if (replyForm.style.display === 'none' || replyForm.style.display === '') {
                replyForm.style.display = 'block';
                // Focus sur le textarea
                replyForm.querySelector('.reply-content').focus();
            } else {
                replyForm.style.display = 'none';
            }
        });
    });
    
    // Gestion des boutons d'annulation de réponse
    const cancelReplyButtons = document.querySelectorAll('.cancel-reply-btn');
    cancelReplyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const replyForm = this.closest('.reply-form-container');
            replyForm.style.display = 'none';
            // Réinitialiser le contenu du textarea
            replyForm.querySelector('.reply-content').value = '';
        });
    });
    
    // Gestion de la soumission des formulaires de réponse
    const replyForms = document.querySelectorAll('.reply-form');
    replyForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const commentId = this.getAttribute('data-comment-id');
            const replyContent = this.querySelector('.reply-content').value.trim();
            
            if (!replyContent) {
                alert("La réponse ne peut pas être vide.");
                return;
            }
            
            try {
                const response = await fetch(window.location.href, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        id: new URLSearchParams(window.location.search).get('id'),
                        type: new URLSearchParams(window.location.search).get('type'),
                        comment_id: commentId,
                        reply_content: replyContent
                    }),
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Stocker la position de défilement
                    const scrollPosition = window.scrollY;
                    sessionStorage.setItem('scrollPosition', scrollPosition);
                    
                    // Recharger la page pour afficher la nouvelle réponse
                    location.reload();
                } else {
                    alert(result.message || "Erreur lors de l'ajout de la réponse.");
                }
            } catch (error) {
                alert("Une erreur s'est produite. Veuillez réessayer.");
            }
        });
    });
    
    // Gestion de la suppression des réponses
    const deleteReplyButtons = document.querySelectorAll('.delete-reply-btn');
    deleteReplyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const replyId = this.getAttribute('data-reply-id');
            const replyElement = document.getElementById(`reply-${replyId}`);
            
            // Vérifier si un message de confirmation existe déjà
            const existingConfirmation = document.getElementById(`confirmation-reply-${replyId}`);
            if (existingConfirmation) {
                return; // Si un message existe déjà, ne rien faire
            }
            
            // Désactiver le défilement
            document.body.style.overflow = 'hidden';
            
            // Créer un message de confirmation
            const confirmationMessage = `
                <div id="confirmation-reply-${replyId}" class="confirmation-message">
                    <p>Voulez-vous vraiment supprimer cette réponse ?</p>
                    <button class="confirm-btn" data-reply-id="${replyId}">Oui</button>
                    <button class="cancel-btn" data-reply-id="${replyId}">Non</button>
                </div>
            `;
            
            // Ajouter le message de confirmation
            replyElement.insertAdjacentHTML('beforeend', confirmationMessage);
            
            // Gestion du bouton de confirmation
            const confirmButton = document.querySelector(`#confirmation-reply-${replyId} .confirm-btn`);
            confirmButton.addEventListener('click', async function(e) {
                e.preventDefault();
                
                try {
                    const response = await fetch(window.location.href, {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            action: "delete_reply",
                            reply_id: replyId,
                            id: new URLSearchParams(window.location.search).get('id'),
                            type: new URLSearchParams(window.location.search).get('type')
                        }),
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Afficher un message de succès
                        const successMessage = document.createElement('p');
                        successMessage.textContent = result.message || 'Réponse supprimée avec succès.';
                        successMessage.style.color = 'green';
                        replyElement.appendChild(successMessage);
                        
                        // Supprimer le message de confirmation
                        document.getElementById(`confirmation-reply-${replyId}`).remove();
                        
                        // Recharger la page après un court délai
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        // Afficher un message d'erreur
                        const errorMessage = document.createElement('p');
                        errorMessage.textContent = result.message || 'Erreur lors de la suppression de la réponse.';
                        errorMessage.style.color = 'red';
                        replyElement.appendChild(errorMessage);
                        
                        // Supprimer le message de confirmation
                        document.getElementById(`confirmation-reply-${replyId}`).remove();
                    }
                } catch (error) {
                    const errorMessage = document.createElement('p');
                    errorMessage.textContent = 'Erreur lors de la suppression de la réponse.';
                    errorMessage.style.color = 'red';
                    replyElement.appendChild(errorMessage);
                    document.getElementById(`confirmation-reply-${replyId}`).remove();
                }
            });
            
            // Gestion du bouton d'annulation
            const cancelButton = document.querySelector(`#confirmation-reply-${replyId} .cancel-btn`);
            cancelButton.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById(`confirmation-reply-${replyId}`).remove();
            });
        });
    });

    // Après le rechargement, restaurer la position de défilement
    if (sessionStorage.getItem('scrollPosition')) {
        window.scrollTo(0, sessionStorage.getItem('scrollPosition'));
        sessionStorage.removeItem('scrollPosition'); // Supprimer après utilisation
    }

    // Réactiver le défilement après le rechargement
    window.addEventListener('load', () => {
        document.body.style.overflow = ''; // Réinitialise l'overflow à sa valeur par défaut
    });
});

// Pour la suppression de commentaire
document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('delete-comment-btn')) {
        e.preventDefault();
        
        // Vérifier si un message de confirmation existe déjà
        const commentId = e.target.getAttribute('data-comment-id');
        const existingConfirmation = document.getElementById('confirmation-' + commentId);
        
        if (existingConfirmation) {
            return; // Si un message existe déjà, ne pas en créer un nouveau
        }
        
        // Créer le container de confirmation
        const confirmContainer = document.createElement('div');
        confirmContainer.id = 'confirmation-' + commentId;
        confirmContainer.className = 'delete-confirm-container';
        confirmContainer.innerHTML = `
            <p>Voulez-vous vraiment supprimer ce commentaire ?</p>
            <button class="confirm-btn confirm-delete-comment" data-comment-id="${commentId}">Oui</button>
            <button class="cancel-btn cancel-delete">Non</button>
        `;
        
        // Trouver où insérer le message
        const deleteButton = e.target;
        const commentContainer = deleteButton.closest('.comment');
        
        // Insérer après le bouton de suppression
        deleteButton.insertAdjacentElement('afterend', confirmContainer);
    }
});

// Pour la suppression de réponse
document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('delete-reply-btn')) {
        e.preventDefault();
        
        // Vérifier si un message de confirmation existe déjà
        const replyId = e.target.getAttribute('data-reply-id');
        const existingConfirmation = document.getElementById('confirmation-reply-' + replyId);
        
        if (existingConfirmation) {
            return; // Si un message existe déjà, ne pas en créer un nouveau
        }
        
        // Créer le container de confirmation avec style inline pour forcer le positionnement
        const confirmContainer = document.createElement('div');
        confirmContainer.id = 'confirmation-reply-' + replyId;
        confirmContainer.className = 'delete-confirm-container';
        
        // Appliquer des styles directs pour forcer le positionnement
        confirmContainer.style.width = '100%';
        confirmContainer.style.maxWidth = '280px';
        confirmContainer.style.margin = '10px 0';
        confirmContainer.style.display = 'block';
        confirmContainer.style.textAlign = 'center';
        confirmContainer.style.clear = 'both';
        confirmContainer.style.position = 'static';
        confirmContainer.style.left = '0';
        confirmContainer.style.transform = 'none';
        
        confirmContainer.innerHTML = `
            <p>Voulez-vous vraiment supprimer cette réponse ?</p>
            <button class="confirm-btn confirm-delete-reply" data-reply-id="${replyId}">Oui</button>
            <button class="cancel-btn cancel-delete">Non</button>
        `;
        
        // Trouver où insérer le message
        const deleteButton = e.target;
        const replyContainer = deleteButton.closest('.reply');
        
        // Créer un wrapper pour un contrôle total du positionnement
        const wrapper = document.createElement('div');
        wrapper.style.width = '100%';
        wrapper.style.display = 'block';
        wrapper.style.margin = '0';
        wrapper.style.padding = '0';
        wrapper.style.textAlign = 'left';
        wrapper.style.clear = 'both';
        wrapper.appendChild(confirmContainer);
        
        // Insérer au début de la réponse, juste après le bouton
        deleteButton.insertAdjacentElement('afterend', wrapper);
    }
});

// Gérer les confirmations ou annulations de suppression
document.addEventListener('click', function(e) {
    // Gestionnaire pour la confirmation de suppression de commentaire
    if (e.target && e.target.classList.contains('confirm-delete-comment')) {
        const commentId = e.target.getAttribute('data-comment-id');
        
        // Envoyer la demande de suppression au serveur
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'delete_comment',
                comment_id: commentId,
                id: new URLSearchParams(window.location.search).get('id'),
                type: new URLSearchParams(window.location.search).get('type')
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Sauvegarder la position de défilement
                const scrollPosition = window.scrollY;
                
                // Stocker la position dans sessionStorage
                sessionStorage.setItem('scrollPosition', scrollPosition);
                
                // Rediriger vers la même page (rechargement)
                window.location.reload();
            } else {
                // Afficher un message d'erreur
                alert(result.message || 'Erreur lors de la suppression du commentaire.');
                // Supprimer le message de confirmation
                document.getElementById('confirmation-' + commentId).remove();
            }
        })
        .catch(error => {
            alert('Une erreur s\'est produite lors de la suppression.');
            document.getElementById('confirmation-' + commentId).remove();
        });
    }
    
    // Gestionnaire pour la confirmation de suppression de réponse
    if (e.target && e.target.classList.contains('confirm-delete-reply')) {
        const replyId = e.target.getAttribute('data-reply-id');
        
        // Envoyer la demande de suppression au serveur
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'delete_reply',
                reply_id: replyId,
                id: new URLSearchParams(window.location.search).get('id'),
                type: new URLSearchParams(window.location.search).get('type')
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Sauvegarder la position de défilement
                const scrollPosition = window.scrollY;
                
                // Stocker la position dans sessionStorage
                sessionStorage.setItem('scrollPosition', scrollPosition);
                
                // Rediriger vers la même page (rechargement)
                window.location.reload();
            } else {
                // Afficher un message d'erreur
                alert(result.message || 'Erreur lors de la suppression de la réponse.');
                // Supprimer le message de confirmation
                document.getElementById('confirmation-reply-' + replyId).remove();
            }
        })
        .catch(error => {
            alert('Une erreur s\'est produite lors de la suppression.');
            document.getElementById('confirmation-reply-' + replyId).remove();
        });
    }
    
    // Gestionnaire pour l'annulation de suppression
    if (e.target && e.target.classList.contains('cancel-delete')) {
        // Trouver le message de confirmation parent et le supprimer
        const confirmationMessage = e.target.closest('.delete-confirm-container');
        if (confirmationMessage) {
            confirmationMessage.remove();
        }
    }
});

// Restaurer la position de défilement après rechargement
window.addEventListener('DOMContentLoaded', function() {
    const scrollPosition = sessionStorage.getItem('scrollPosition');
    if (scrollPosition) {
        window.scrollTo(0, parseInt(scrollPosition));
        sessionStorage.removeItem('scrollPosition');
    }
});

// Fonction pour vérifier si un conteneur est vide et le masquer si nécessaire
function hideIfEmpty(container) {
    if (container && container.children.length === 0) {
        container.style.display = 'none';
    } else if (container && container.innerHTML.trim() === '') {
        container.style.display = 'none';
    }
}
