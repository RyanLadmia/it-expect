<?php
// ===== TRAITEMENT CENTRALISÉ - AUCUNE LOGIQUE DANS LA VUE =====
$pageController = new PageController();
$pageData = $pageController->handleLoginPage();

// Variables pour la vue (extraction simple)
$errors = $pageData['errors'];
$successMessage = $pageData['successMessage'];
$formType = $pageData['formType'];
?>

<h1>Connexion :</h1>

<div id="form-wrapper">
    <?php if (isset($successMessage)): ?>
        <!-- Message de succès : pleine largeur -->
        <div id="success-message-wrapper">
            <p class="success"><?= $successMessage ?></p>
            <?php if ($formType === 'register' || $formType === 'forgot_password'): ?>
                <a href="javascript:void(0)" id="show-login-from-success" class="login-from-success">Se connecter</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Conteneur de formulaires : masqué pour inscription réussie -->
    <div id="form-container-wrapper" <?= ($formType === 'register' && isset($successMessage)) ? 'style="display: none;"' : '' ?>>
        <!-- Formulaire d'inscription -->
        <div id="register-form" class="form-container <?= ($formType === 'register' && !isset($successMessage)) ? 'active' : '' ?>">
            <h2>Inscription</h2>
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="register">
                
                <div>
                    <label for="firstname">Prénom</label>
                    <input type="text" name="firstname" id="firstname" value="<?= $_POST['firstname'] ?? '' ?>" placeholder="Votre prénom">
                    <?php if (isset($errors['firstname'])): ?><span class="error-msg"><?= $errors['firstname'] ?></span><?php endif; ?>
                </div>
                
                <div>
                    <label for="lastname">Nom</label>
                    <input type="text" name="lastname" id="lastname" value="<?= $_POST['lastname'] ?? '' ?>" placeholder="Votre nom">
                    <?php if (isset($errors['lastname'])): ?><span class="error-msg"><?= $errors['lastname'] ?></span><?php endif; ?>
                </div>
                
                <div>
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="<?= $_POST['email'] ?? '' ?>" placeholder="Votre adresse email">
                    <?php if (isset($errors['email'])): ?><span class="error-msg"><?= $errors['email'] ?></span><?php endif; ?>
                </div>
                
                <div>
                    <label for="password">Mot de passe</label>
                    <input type="password" name="password" id="password" placeholder="Minimum 8 caractères">
                    <?php if (isset($errors['password'])): ?><span class="error-msg"><?= $errors['password'] ?></span><?php endif; ?>
                </div>
                
                <div>
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirm" id="password_confirm" placeholder="Confirmer votre mot de passe">
                    <?php if (isset($errors['password_confirm'])): ?><span class="error-msg"><?= $errors['password_confirm'] ?></span><?php endif; ?>
                </div>
                
                <div>
                    <input type="submit" value="S'inscrire">
                </div>
            </form>

            <p class="account-link">Vous avez déjà un compte ?<br class="mobile-break"> <a href="javascript:void(0)" id="show-login">Se Connecter</a></p>
        </div>

        <!-- Formulaire de connexion -->
        <div id="login-form" class="form-container <?= $formType === 'login' && !isset($successMessage) ? 'active' : '' ?>">
            <h2>Connexion</h2>
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="login">
                
                <div>
                    <label for="email_login">Email</label>
                    <input type="email" name="email" id="email_login" value="<?= $_POST['email'] ?? '' ?>" placeholder="Votre adresse email">
                    <?php if (isset($errors['email_login'])): ?><span class="error-msg"><?= $errors['email_login'] ?></span><?php endif; ?>
                </div>
                
                <div>
                    <label for="password_login">Mot de passe</label>
                    <input type="password" name="password" id="password_login" placeholder="Votre mot de passe">
                    <?php if (isset($errors['password_login'])): ?><span class="error-msg"><?= $errors['password_login'] ?></span><?php endif; ?>
                </div>
                
                <div class="forgot-link">
                    <a href="javascript:void(0)" id="show-forgot-password">Mot de passe oublié ?</a>
                </div>
                
                <?php if (isset($errors['login'])): ?>
                    <span class="error-msg"><?= $errors['login'] ?></span>
                <?php endif; ?>
                
                <div>
                    <input type="submit" value="Se connecter">
                </div>
            </form>

            <p class="account-link">Vous n'avez pas de compte ?<br class="mobile-break"> <a href="javascript:void(0)" id="show-register">S'inscrire</a></p>
        </div>

        <!-- Formulaire de réinitialisation de mot de passe -->
        <div id="forgot-password-form" class="form-container <?= $formType === 'forgot_password' && !isset($successMessage) ? 'active' : '' ?>">
            <h2>Réinitialisation</h2>
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="forgot_password">
                
                <div>
                    <label for="email_forgot">Email</label>
                    <input type="email" name="email_forgot" id="email_forgot" placeholder="Adresse email de votre compte">
                    <?php if (isset($errors['email_forgot'])): ?>
                        <span class="error-msg"><?= $errors['email_forgot'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div>
                    <input type="submit" value="Envoyer le lien">
                </div>
            </form>
            
            <p>Retour à la <a href="javascript:void(0)" id="show-login-from-forgot">connexion</a></p>
        </div>
    </div>
</div>

<script>
    // Scripts pour la navigation entre les formulaires
    document.addEventListener('DOMContentLoaded', function() {
        // Basculer vers le formulaire de connexion
        document.getElementById('show-login').addEventListener('click', function() {
            hideAllForms();
            document.getElementById('login-form').classList.add('active');
        });
        
        // Basculer vers le formulaire d'inscription
        document.getElementById('show-register').addEventListener('click', function() {
            hideAllForms();
            document.getElementById('register-form').classList.add('active');
        });
        
        // Basculer vers le formulaire de récupération de mot de passe
        document.getElementById('show-forgot-password').addEventListener('click', function() {
            hideAllForms();
            document.getElementById('forgot-password-form').classList.add('active');
        });
        
        // Retour à la connexion depuis le formulaire de récupération
        document.getElementById('show-login-from-forgot').addEventListener('click', function() {
            hideAllForms();
            document.getElementById('login-form').classList.add('active');
        });
        
        // Si le bouton existe (dans le message de succès)
        const loginFromSuccessBtn = document.getElementById('show-login-from-success');
        if (loginFromSuccessBtn) {
            loginFromSuccessBtn.addEventListener('click', function() {
                document.getElementById('success-message-wrapper').style.display = 'none';
                hideAllForms();
                document.getElementById('login-form').classList.add('active');
            });
        }
        
        // Fonction pour cacher tous les formulaires
        function hideAllForms() {
            document.querySelectorAll('.form-container').forEach(form => {
                form.classList.remove('active');
            });
        }
        
        // Vérifier s'il y a un message de succès d'inscription et s'assurer que le formulaire est masqué
        const successMessage = document.getElementById('success-message-wrapper');
        if (successMessage) {
            // Assurez-vous que le formulaire d'inscription est caché si l'inscription est réussie
            const registerForm = document.getElementById('register-form');
            if (registerForm) {
                registerForm.classList.remove('active');
            }
        }
    });
</script>
