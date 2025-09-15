// Fonction pour afficher un message d'erreur
function showError(container, message) {
    if (container) {
        container.innerHTML = `<div class="error-message">
            <p>${message}</p>
        </div>`;
    }
}

// Fetch API configuration at the beginning
let apiConfig = null;

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
        apiConfig = config;
    })
    .catch(error => {
        console.error('Erreur de configuration API:', error);
        const searchContainer = document.getElementById('search').parentNode;
        showError(searchContainer, 'Erreur de configuration API. La recherche peut ne pas fonctionner correctement.');
    });

document.getElementById('search').addEventListener('input', function() {
    const query = this.value;
    const suggestionsContainer = document.getElementById('suggestion');
    
    if (query.length > 2) {
        // Vérifier si la configuration API est disponible
        if (!apiConfig) {
            suggestionsContainer.innerHTML = '<div class="error-message">Configuration API non disponible</div>';
            return;
        }
        
        const apiKey = apiConfig.apiKey;
        const searchBaseUrl = apiConfig.searchBaseUrl;
        
        const movieUrl = `${searchBaseUrl}/movie?api_key=${apiKey}&language=fr-FR&query=${query}`;
        const tvUrl = `${searchBaseUrl}/tv?api_key=${apiKey}&language=fr-FR&query=${query}`;

        Promise.all([
            fetch(movieUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur lors de la recherche des films');
                    }
                    return response.json();
                }),
            fetch(tvUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur lors de la recherche des séries');
                    }
                    return response.json();
                })
        ])
        .then(([movieData, tvData]) => {
            suggestionsContainer.innerHTML = '';

            const suggestions = [];

            if (movieData.results && movieData.results.length > 0) {
                movieData.results.forEach(movie => {
                    suggestions.push({ id: movie.id, name: movie.title, type: 'movie' });
                });
            }

            if (tvData.results && tvData.results.length > 0) {
                tvData.results.forEach(tv => {
                    suggestions.push({ id: tv.id, name: tv.name, type: 'tv' });
                });
            }

            if (suggestions.length === 0) {
                suggestionsContainer.innerHTML = '<div class="no-results">Aucun résultat trouvé</div>';
                return;
            }

            suggestions.forEach(suggestion => {
                const suggestionElement = document.createElement('div');
                suggestionElement.classList.add('suggestion-item');
                suggestionElement.textContent = suggestion.name;

                suggestionElement.addEventListener('click', () => {
                    window.location.href = `${window.location.origin}/it-expect/detail?id=${suggestion.id}&type=${suggestion.type}`;
                });

                suggestionsContainer.appendChild(suggestionElement);
            });
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des suggestions:', error);
            suggestionsContainer.innerHTML = '<div class="error-message">Erreur lors de la recherche</div>';
        });
    } else {
        suggestionsContainer.innerHTML = '';
    }
});
