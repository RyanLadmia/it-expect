// Fonction pour modifier le format des dates en JJ/MM/AAAA
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Mois est zéro-indexé
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
}

// Fonction pour afficher un message d'erreur
function showError(container, message) {
    if (container) {
        container.innerHTML = `<div class="error-message">
            <p>${message}</p>
        </div>`;
    }
}

// Attendre que le DOM soit complètement chargé
document.addEventListener('DOMContentLoaded', function() {
    const movieContainer = document.getElementById("movies");
    const tvContainer = document.getElementById("tvs");
    
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
            
            const urlMovie = `${movieBaseUrl}/top_rated?api_key=${apiKey}&language=fr-FR`;
            const urlTv = `${tvBaseUrl}/top_rated?api_key=${apiKey}&language=fr-FR`;

            // Fonction pour obtenir les films les mieux notés
            if (movieContainer) {
                fetch(urlMovie)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors de la récupération des films');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const movies = data.results;
                        
                        if (!movies || movies.length === 0) {
                            showError(movieContainer, 'Aucun film trouvé');
                            return;
                        }

                        // Boucle pour afficher chaque film
                        movies.forEach(movie => {
                            // Créer un élément section pour chaque film
                            const movieElement = document.createElement("section");
                            movieElement.classList.add("movie");

                            // Ajouter le titre, l'image, la date de sortie, la note et la description
                            movieElement.innerHTML = `
                                <h2>${movie.title}</h2>
                                <img src="${imageBaseUrl}${movie.poster_path}" alt="${movie.title}">
                                <p>Date de sortie : ${formatDate(movie.release_date)}</p>
                                <p>Note : ${movie.vote_average}</p>
                                <!-- Ajout du bouton "Voir les détails" -->
                                <a href="detail?id=${movie.id}&type=movie"><button>Voir les détails</button></a>
                            `;

                            // Ajouter chaque film dans le conteneur principal
                            movieContainer.appendChild(movieElement);
                        });
                    })
                    .catch(error => {
                        console.error('Erreur :', error);
                        showError(movieContainer, 'Impossible de charger les films');
                    });
            }

            // Fonction pour obtenir les séries les mieux notées
            if (tvContainer) {
                fetch(urlTv)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors de la récupération des séries');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const tvs = data.results;
                        
                        if (!tvs || tvs.length === 0) {
                            showError(tvContainer, 'Aucune série trouvée');
                            return;
                        }

                        // Boucle pour afficher chaque série
                        tvs.forEach(tv => {
                            // Créer un élément section pour chaque série
                            const tvElement = document.createElement("section");
                            tvElement.classList.add("tv");

                            // Ajouter le titre, l'image, la date de première diffusion, la note et la description
                            tvElement.innerHTML = `
                                <h2>${tv.name}</h2>
                                <img src="${imageBaseUrl}${tv.poster_path}" alt="${tv.name}">
                                <p>Date de première diffusion : ${formatDate(tv.first_air_date)}</p>
                                <p>Note : ${tv.vote_average}</p>
                                <!-- Ajout du bouton "Voir les détails" -->
                                <a href="detail?id=${tv.id}&type=tv"><button>Voir les détails</button></a>
                            `;

                            // Ajouter chaque série dans le conteneur principal
                            tvContainer.appendChild(tvElement);
                        });
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        showError(tvContainer, 'Impossible de charger les séries');
                    });
            }
        })
        .catch(error => {
            console.error('Erreur de configuration:', error);
            showError(movieContainer, 'Erreur de configuration API');
            showError(tvContainer, 'Erreur de configuration API');
        });
}); 