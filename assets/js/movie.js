// Fonction pour modifier le format des dates en JJ/MM/AAAA
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
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
    const tendMoviesContainer = document.getElementById("tendMovies");
    const topMoviesContainer = document.getElementById("topMovies");
    
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
            const trendingMovieUrl = config.trendingMovieUrl;
            
            // Films en tendance cette semaine
            const urlTendMovie = `${trendingMovieUrl}?api_key=${apiKey}&language=fr-FR`;

            // Fonction pour afficher les films en tendance
            if (tendMoviesContainer) {
                fetch(urlTendMovie)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors de la récupération des films en tendance');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const movies = data.results;
                        
                        if (!movies || movies.length === 0) {
                            showError(tendMoviesContainer, 'Aucun film en tendance trouvé');
                            return;
                        }
                        
                        movies.forEach(movie => {
                            const movieElement = document.createElement("section");
                            movieElement.classList.add("movie");

                            movieElement.innerHTML = `
                                <h2>${movie.title}</h2>
                                <img src="${imageBaseUrl}${movie.poster_path}" alt="${movie.title}">
                                <p>Date de sortie : ${formatDate(movie.release_date)}</p>
                                <p>Note : ${movie.vote_average}</p>
                                <!-- Bouton Voir les détails -->
                                <a href="detail?id=${movie.id}&type=movie">
                                    <button>Voir les détails</button>
                                </a>
                            `;

                            tendMoviesContainer.appendChild(movieElement);
                        });
                    })
                    .catch(error => {
                        console.error('Erreur :', error);
                        showError(tendMoviesContainer, 'Impossible de charger les films en tendance');
                    });
            }

            // Films les plus populaires
            const urlTopMovie = `${movieBaseUrl}/popular?api_key=${apiKey}&language=fr-FR`;

            // Fonction pour afficher les films populaires
            if (topMoviesContainer) {
                fetch(urlTopMovie)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors de la récupération des films populaires');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const movies = data.results;
                        
                        if (!movies || movies.length === 0) {
                            showError(topMoviesContainer, 'Aucun film populaire trouvé');
                            return;
                        }

                        movies.forEach(movie => {
                            const movieElement = document.createElement("section");
                            movieElement.classList.add("movie");

                            movieElement.innerHTML = `
                                <h2>${movie.title}</h2>
                                <img src="${imageBaseUrl}${movie.poster_path}" alt="${movie.title}">
                                <p>Date de sortie : ${formatDate(movie.release_date)}</p>
                                <p>Note : ${movie.vote_average}</p>
                                <!-- Bouton Voir les détails -->
                                <a href="detail?id=${movie.id}&type=movie">
                                    <button>Voir les détails</button>
                                </a>
                            `;

                            topMoviesContainer.appendChild(movieElement);
                        });
                    })
                    .catch(error => {
                        console.error('Erreur :', error);
                        showError(topMoviesContainer, 'Impossible de charger les films populaires');
                    });
            }
        })
        .catch(error => {
            console.error('Erreur de configuration:', error);
            showError(tendMoviesContainer, 'Erreur de configuration API');
            showError(topMoviesContainer, 'Erreur de configuration API');
        });
});
