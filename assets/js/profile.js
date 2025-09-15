(function() {
    // Vérifier si le script a déjà été initialisé
    if (window.profileScriptInitialized) {
        return;
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (window.profileScriptInitialized) {
            return;
        }

        // Vérifier si nous sommes sur la page de profil
        const editButton = document.getElementById('edit_button');
        const updateForm = document.getElementById('update_form');
        
        // Si les éléments n'existent pas, on n'est pas sur la page de profil
        if (!editButton || !updateForm) {
            return;
        }

        window.profileScriptInitialized = true;
        
        const fieldSelect = document.getElementById('field');
        const newValueInput = document.getElementById('new_value');
        const passwordFields = document.getElementById('password_fields');
        const successMessage = document.getElementById('success_message');
        const errorMessage = document.getElementById('error_message');

        // Afficher le formulaire de modification
        editButton.addEventListener('click', function(e) {
            e.preventDefault(); // Empêcher le comportement par défaut
            
            if (updateForm.style.display === 'none' || updateForm.style.display === '') {
                updateForm.style.display = 'block';
            } else {
                updateForm.style.display = 'none';
            }
        });

        // Afficher les champs de mot de passe si nécessaire
        fieldSelect.addEventListener('change', function() {
            if (this.value === 'user_password') {
                passwordFields.style.display = 'block';
                newValueInput.type = 'password';
                newValueInput.placeholder = 'Nouveau mot de passe';
            } else {
                passwordFields.style.display = 'none';
                newValueInput.type = 'text';
                newValueInput.placeholder = '';
            }
        });

        // Soumission du formulaire de modification
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const field = fieldSelect.value;
            const newValue = newValueInput.value.trim();
            const confirmPassword = document.getElementById('confirm_password').value;

            // Validation simple côté client
            if (!newValue) {
                errorMessage.textContent = "La valeur ne peut pas être vide.";
                errorMessage.style.display = 'block';
                successMessage.style.display = 'none';
                return;
            }

            // Validation supplémentaire pour l'email
            if (field === 'user_email' && !isValidEmail(newValue)) {
                errorMessage.textContent = "Format d'email invalide.";
                errorMessage.style.display = 'block';
                successMessage.style.display = 'none';
                return;
            }

            // Validation du mot de passe
            if (field === 'user_password') {
                if (newValue.length < 8) {
                    errorMessage.textContent = "Le mot de passe doit contenir au moins 8 caractères.";
                    errorMessage.style.display = 'block';
                    successMessage.style.display = 'none';
                    return;
                }
                
                if (newValue !== confirmPassword) {
                    errorMessage.textContent = "Les mots de passe ne correspondent pas.";
                    errorMessage.style.display = 'block';
                    successMessage.style.display = 'none';
                    return;
                }
            }

            // Requête AJAX pour mise à jour
            const formData = new FormData();
            formData.append('ajax_request', true);
            formData.append('field', field);
            formData.append('new_value', newValue);
            if (confirmPassword) {
                formData.append('confirm_password', confirmPassword);
            }

            // Ajout d'un token CSRF si disponible
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                formData.append('csrf_token', csrfToken.getAttribute('content'));
            }

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin' // Important pour la sécurité
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau ou serveur');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Mise à jour réussie
                    successMessage.textContent = data.message;
                    successMessage.style.display = 'block';
                    errorMessage.style.display = 'none';
                    
                    // Mettre à jour l'affichage si nécessaire
                    if (field === 'user_firstname') {
                        document.getElementById('user_firstname').textContent = newValue;
                    } else if (field === 'user_lastname') {
                        document.getElementById('user_lastname').textContent = newValue;
                    } else if (field === 'user_email') {
                        document.getElementById('user_email').textContent = newValue;
                    }
                    
                    // Masquer le formulaire après quelques secondes
                    setTimeout(() => {
                        updateForm.style.display = 'none';
                        successMessage.style.display = 'none';
                    }, 3000);
                } else {
                    // Erreur lors de la mise à jour
                    errorMessage.textContent = data.message || "Une erreur est survenue.";
                    errorMessage.style.display = 'block';
                    successMessage.style.display = 'none';
                }
            })
            .catch(error => {
                errorMessage.textContent = 'Une erreur est survenue. Veuillez réessayer.';
                errorMessage.style.display = 'block';
                successMessage.style.display = 'none';
            });
        });

        // Gestion de la suppression du compte
        const deleteButton = document.getElementById('delete_account_button');
        const confirmationMessage = document.getElementById('confirmation_message');
        const confirmDeleteButton = document.getElementById('confirm_delete');
        const cancelDeleteButton = document.getElementById('cancel_delete');
        const cancelMessage = document.getElementById('cancel_message');

        if (deleteButton && confirmationMessage) {
            // Afficher la confirmation
            deleteButton.addEventListener('click', function() {
                confirmationMessage.style.display = 'block';
                if (cancelMessage) cancelMessage.style.display = 'none';
            });

            // Annuler la suppression
            if (cancelDeleteButton) {
                cancelDeleteButton.addEventListener('click', function() {
                    confirmationMessage.style.display = 'none';
                    if (cancelMessage) {
                        cancelMessage.textContent = 'Suppression du compte annulée.';
                        cancelMessage.style.display = 'block';
                        
                        setTimeout(() => {
                            cancelMessage.style.display = 'none';
                        }, 3000);
                    }
                });
            }

            // Confirmer la suppression
            if (confirmDeleteButton) {
                confirmDeleteButton.addEventListener('click', function() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = window.location.href;
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_account';
                    input.value = '1';
                    
                    // Ajout d'un token CSRF si disponible
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = 'csrf_token';
                        csrfInput.value = csrfToken.getAttribute('content');
                        form.appendChild(csrfInput);
                    }
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                });
            }
        }
    });

    // Fonction de validation d'email
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
})();
