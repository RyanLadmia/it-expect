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
    const tendTvsContainer = document.getElementById("tendTvs");
    const topTvsContainer = document.getElementById("topTvs");
    
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
            const tvBaseUrl = config.tvBaseUrl;
            const trendingTvUrl = config.trendingTvUrl;
            
            // Séries en tendance cette semaine
            const urlTendTv = `${trendingTvUrl}?api_key=${apiKey}&language=fr-FR`;

            // Fonction pour afficher les séries en tendance cette semaine
            if (tendTvsContainer) {
                fetch(urlTendTv)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors de la récupération des séries en tendance');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const tvs = data.results;
                        
                        if (!tvs || tvs.length === 0) {
                            showError(tendTvsContainer, 'Aucune série en tendance trouvée');
                            return;
                        }
                        
                        tvs.forEach(tv => {
                            const tvElement = document.createElement("section");
                            tvElement.classList.add("tv");

                            tvElement.innerHTML = `
                                <h2>${tv.name}</h2>
                                <img src="${imageBaseUrl}${tv.poster_path}" alt="${tv.name}">
                                <p>Date de première diffusion : ${formatDate(tv.first_air_date)}</p>
                                <p>Note : ${tv.vote_average}</p>
                                <!-- Bouton Voir les détails -->
                                <a href="detail?id=${tv.id}&type=tv">
                                    <button>Voir les détails</button>
                                </a>
                            `;

                            tendTvsContainer.appendChild(tvElement);
                        });
                    })
                    .catch(error => {
                        console.error('Erreur :', error);
                        showError(tendTvsContainer, 'Impossible de charger les séries en tendance');
                    });
            }

            // Séries les plus populaires
            const urlTopTv = `${tvBaseUrl}/popular?api_key=${apiKey}&language=fr-FR`;

            // Fonction pour afficher les séries populaires
            if (topTvsContainer) {
                fetch(urlTopTv)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors de la récupération des séries populaires');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const tvs = data.results;
                        
                        if (!tvs || tvs.length === 0) {
                            showError(topTvsContainer, 'Aucune série populaire trouvée');
                            return;
                        }

                        tvs.forEach(tv => {
                            const tvElement = document.createElement("section");
                            tvElement.classList.add("tv");

                            tvElement.innerHTML = `
                                <h2>${tv.name}</h2>
                                <img src="${imageBaseUrl}${tv.poster_path}" alt="${tv.name}">
                                <p>Date de première diffusion : ${formatDate(tv.first_air_date)}</p>
                                <p>Note : ${tv.vote_average}</p>
                                <!-- Bouton Voir les détails -->
                                <a href="detail?id=${tv.id}&type=tv">
                                    <button>Voir les détails</button>
                                </a>
                            `;

                            topTvsContainer.appendChild(tvElement);
                        });
                    })
                    .catch(error => {
                        console.error('Erreur :', error);
                        showError(topTvsContainer, 'Impossible de charger les séries populaires');
                    });
            }
        })
        .catch(error => {
            console.error('Erreur de configuration:', error);
            showError(tendTvsContainer, 'Erreur de configuration API');
            showError(topTvsContainer, 'Erreur de configuration API');
        });
});
