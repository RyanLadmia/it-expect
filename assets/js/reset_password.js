document.addEventListener("DOMContentLoaded", function() {
    // Vérifier si le message de succès est présent dans le DOM
    const successMessage = document.querySelector(".success");

    // Si le message de succès existe, masquer le formulaire
    if (successMessage) {
        const form = document.getElementById("reset-password-form");
        if (form) {
            form.style.display = "none"; // Masque le formulaire
        }
    }
});
