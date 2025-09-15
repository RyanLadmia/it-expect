document.addEventListener('DOMContentLoaded', function () {
    // Vérifier si nous sommes sur la page login en cherchant un élément spécifique
    const formWrapper = document.getElementById('form-wrapper');
    
    // Si l'élément form-wrapper n'existe pas, nous ne sommes pas sur la page login
    // donc arrêter l'exécution du script
    if (!formWrapper) {
        return;
    }
    
    // Récupérer les références des éléments du DOM
    const registerForm = document.getElementById('register-form');
    const loginForm = document.getElementById('login-form');
    const forgotPasswordForm = document.getElementById('forgot-password-form');
    const showLoginBtn = document.getElementById('show-login');
    const showRegisterBtn = document.getElementById('show-register');
    const showForgotPasswordBtn = document.getElementById('show-forgot-password');
    const showLoginFromForgotBtn = document.getElementById('show-login-from-forgot');
    const successMessage = document.getElementById('successful-message');

    // Vérifier que les éléments nécessaires existent
    if (!registerForm || !loginForm) {
        console.warn("Certains éléments essentiels n'ont pas été trouvés dans le DOM");
        return; // Sortir de la fonction si les éléments essentiels n'existent pas
    }

    // Restaurer l'état depuis le localStorage
    const activeForm = localStorage.getItem('activeForm') || 'register';
    if (activeForm === 'register' && registerForm && loginForm && forgotPasswordForm) {
        registerForm.classList.add('active');
        loginForm.classList.remove('active');
        forgotPasswordForm.classList.remove('active');
    } else if (activeForm === 'login' && registerForm && loginForm && forgotPasswordForm) {
        loginForm.classList.add('active');
        registerForm.classList.remove('active');
        forgotPasswordForm.classList.remove('active');
    } else if (activeForm === 'forgot_password' && registerForm && loginForm && forgotPasswordForm) {
        forgotPasswordForm.classList.add('active');
        registerForm.classList.remove('active');
        loginForm.classList.remove('active');
    }

    // Ne pas afficher le formulaire de connexion après l'envoi de l'email de réinitialisation
    if (successMessage && forgotPasswordForm && registerForm) {
        forgotPasswordForm.classList.remove('active'); // Garder le formulaire de réinitialisation actif si succès
        // Ne pas forcer la réapparition du formulaire de connexion
        setTimeout(() => {
            forgotPasswordForm.classList.remove('active'); // Enlève le formulaire de réinitialisation si succès
            registerForm.classList.remove('active');
            localStorage.setItem('activeForm', 'register');
        }, 3000); // Ferme après 3 secondes (ajustable)
    }

    // Switch entre les formulaires - vérifier que chaque élément existe avant d'y accéder
    if (showLoginBtn && registerForm && loginForm && forgotPasswordForm) {
        showLoginBtn.addEventListener('click', () => {
            registerForm.classList.remove('active');
            loginForm.classList.add('active');
            forgotPasswordForm.classList.remove('active');
            localStorage.setItem('activeForm', 'login');
        });
    }

    if (showRegisterBtn && registerForm && loginForm && forgotPasswordForm) {
        showRegisterBtn.addEventListener('click', () => {
            loginForm.classList.remove('active');
            registerForm.classList.add('active');
            forgotPasswordForm.classList.remove('active');
            localStorage.setItem('activeForm', 'register');
        });
    }

    // Gestion du retour à la connexion après l'envoi de l'email de réinitialisation
    if (showLoginFromForgotBtn && loginForm && forgotPasswordForm) {
        showLoginFromForgotBtn.addEventListener('click', function() {
            loginForm.classList.add('active');
            forgotPasswordForm.classList.remove('active');
            localStorage.setItem('activeForm', 'login');
        });
    }

    // Passer au formulaire de réinitialisation de mot de passe
    if (showForgotPasswordBtn && loginForm && forgotPasswordForm) {
        showForgotPasswordBtn.addEventListener('click', function() {
            loginForm.classList.remove('active');
            forgotPasswordForm.classList.add('active');
            localStorage.setItem('activeForm', 'forgot_password');
        });
    }
});