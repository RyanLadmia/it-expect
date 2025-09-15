window.addEventListener('scroll', function () {
    const ancre = document.querySelector('.anchor');
    if (window.scrollY > 100) { // Si la page atteint 1500 pixels de défilement
        ancre.style.opacity = '1'; // Le bouton devient visible
        ancre.style.pointerEvents = 'auto';  // Pour activer le clic
    } else {
        ancre.style.opacity = '0'; // Le bouton est invisible
        ancre.style.pointerEvents = 'none';  // Désactive le clic quand l'ancre est invisible
    }
});

document.querySelector('.anchor').addEventListener('click', function (e) {
    e.preventDefault(); // Empêche le comportement par défaut du lien

    const targetId = this.getAttribute('data-target'); // Récupère l'ID cible depuis data-target
    const targetElement = document.querySelector(targetId); // Sélectionne l'élément cible

    if (targetElement) {
        targetElement.scrollIntoView({
            behavior: 'smooth'
        });
    }
});
