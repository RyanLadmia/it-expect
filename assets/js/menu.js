function toggleMenu() 
{
    const navbar = document.querySelector('.navbar');
    navbar.classList.toggle('active'); // Ajoute ou enlève la classe active
}

// Assurer que la suggestion disparaît quand on clique ailleurs
document.addEventListener('click', function(event) {
    const searchInput = document.getElementById('search');
    const suggestionContainer = document.getElementById('suggestion');
    
    // Vérifier que les éléments existent avant de les utiliser
    if (searchInput && suggestionContainer) {
        // Si on clique ailleurs que dans la barre de recherche ou dans les suggestions
        if (event.target !== searchInput && !suggestionContainer.contains(event.target)) {
            suggestionContainer.innerHTML = '';
        }
    }
});