document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour afficher un message d'erreur
    function showError(container, message) {
        if (container) {
            container.innerHTML = `<div class="error-message">
                <p>${message}</p>
            </div>`;
        }
    }
    
    // Appliquer les couleurs aux boutons de confirmation
    document.querySelectorAll('.confirm-remove').forEach(function(btn) {
        btn.classList.add('green-btn');
        btn.style.backgroundColor = '#4CAF50'; // Vert
        btn.style.color = 'white';
        btn.style.border = 'none';
        btn.style.padding = '5px 10px';
        btn.style.cursor = 'pointer';
        btn.style.borderRadius = '3px';
        btn.style.margin = '5px';
    });
    
    document.querySelectorAll('.cancel-remove').forEach(function(btn) {
        btn.classList.add('red-btn');
        btn.style.backgroundColor = '#f44336'; // Rouge
        btn.style.color = 'white';
        btn.style.border = 'none';
        btn.style.padding = '5px 10px';
        btn.style.cursor = 'pointer';
        btn.style.borderRadius = '3px';
        btn.style.margin = '5px';
    });
    
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
            
            // Fonction pour récupérer les détails du film/serie
            function fetchDetails(contentId, contentType) {
                let url = '';
                if (contentType === 'movie') {
                    url = `${movieBaseUrl}/${contentId}?api_key=${apiKey}&language=fr`;
                } else if (contentType === 'tv') {
                    url = `${tvBaseUrl}/${contentId}?api_key=${apiKey}&language=fr`;
                }

                return fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Erreur lors de la récupération des détails pour ${contentType} ${contentId}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        return {
                            title: data.title || data.name,  // Le titre de l'élément
                            imageUrl: `${imageBaseUrl}${data.poster_path}`,  // L'URL de l'image
                            detailPage: contentType === 'movie' ? `detail?id=${contentId}&type=movie` : `detail?id=${contentId}&type=tv`  // Lien vers la page de détail
                        };
                    })
                    .catch(error => {
                        console.error('Erreur lors de la récupération des détails :', error);
                        return { 
                            title: 'Inconnu', 
                            imageUrl: '', 
                            detailPage: '#',
                            error: true
                        };
                    });
            }

            // Pour chaque favori, récupérer les informations depuis l'API et afficher
            document.querySelectorAll('.favorite-item').forEach(function(item) {
                const contentId = item.getAttribute('data-content-id');
                const contentType = item.getAttribute('data-content-type');

                fetchDetails(contentId, contentType).then(details => {
                    const imageElement = item.querySelector('.favorite-image');
                    const titleElement = item.querySelector('.favorite-title');
                    const detailButton = item.querySelector('.view-detail');

                    if (details.error) {
                        // Si erreur dans la récupération des détails
                        item.querySelector('.favorite-content').innerHTML += 
                            `<div class="error-message">Impossible de charger les informations</div>`;
                    } else {
                        imageElement.src = details.imageUrl || '';
                        titleElement.textContent = details.title || 'Titre non disponible';
                        
                        // Mise à jour de l'attribut href du bouton pour le lien vers la page de détail
                        detailButton.setAttribute('href', details.detailPage); // Lien vers la page de détail
                    }
                });

                // Écouteur de clic pour le bouton "Retirer de mes favoris"
                item.querySelector('.remove-favorite-btn').addEventListener('click', function() {
                    const confirmationMessage = item.querySelector('.confirmation-message');
                    confirmationMessage.style.display = 'block'; // Afficher la confirmation

                    // Si "Non" est cliqué, cacher la confirmation
                    item.querySelector('.cancel-remove').addEventListener('click', function() {
                        confirmationMessage.style.display = 'none';
                    });

                    // Si "Oui" est cliqué, supprimer le favori
                    item.querySelector('.confirm-remove').addEventListener('click', function() {
                        // Envoi de la requête AJAX pour supprimer le favori
                        const contentId = item.getAttribute('data-content-id');
                        const contentType = item.getAttribute('data-content-type');

                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', '', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4) {
                                if (xhr.status === 200) {
                                    try {
                                        const response = JSON.parse(xhr.responseText);
                                        if (response.success) {
                                            confirmationMessage.querySelector('.success-message').style.display = 'block';
                                            setTimeout(() => {
                                                item.style.display = 'none';
                                            }, 2000);
                                        } else {
                                            console.error('Erreur serveur:', response);
                                            alert(`Erreur: ${response.message || 'Une erreur est survenue.'}`);
                                        }
                                    } catch (e) {
                                        console.error('Erreur parsing JSON:', e, 'Response:', xhr.responseText);
                                        alert("Erreur de communication avec le serveur.");
                                    }
                                } else {
                                    console.error('Erreur HTTP:', xhr.status, xhr.statusText, xhr.responseText);
                                    alert(`Erreur HTTP ${xhr.status}: ${xhr.statusText}`);
                                }
                            }
                        };
                        xhr.send(`action=remove_favorite&content_id=${contentId}&content_type=${contentType}`);
                    });
                });
            });
        })
        .catch(error => {
            console.error('Erreur de configuration API:', error);
            document.querySelector('.favorites-container').innerHTML = 
                `<div class="error-message">Erreur de configuration API. Impossible de charger les favoris.</div>`;
        });
});