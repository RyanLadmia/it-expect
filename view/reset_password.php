<?php
// ===== TRAITEMENT CENTRALISÉ - AUCUNE LOGIQUE DANS LA VUE =====
$pageController = new PageController();
$pageData = $pageController->handleResetPasswordPage();

// Variables pour la vue (extraction simple)
$successMessage = $pageData['successMessage'];
$msgError = $pageData['msgError'];
$showForm = $pageData['showForm'];
?>

<div class="reset_wrapper">
    <?php if ($showForm): ?>
        <!-- Formulaire de réinitialisation de mot de passe -->
        <form id="reset-password-form" method="POST" action="">
            <h2>Réinitialisation du mot de passe</h2>
            
            <label for="new_password">Nouveau mot de passe</label>
            <input type="password" name="new_password" id="new_password" required minlength="8" placeholder="8 caractères minimum">
            
            <label for="confirm_password">Confirmer le mot de passe</label>
            <input type="password" name="confirm_password" id="confirm_password" required placeholder="Confirmer votre mot de passe">
            
            <input type="submit" value="Réinitialiser mon mot de passe">
        </form>
    <?php endif; ?>

    <!-- Affichage du message d'erreur -->
    <?php if (!empty($msgError)): ?>
    <div class="reset_error">
        <p class="msgError"><?= $msgError ?></p>
    </div>
    <?php endif; ?>

    <!-- Affichage du message de succès -->
    <?php if (!empty($successMessage)): ?>
    <div class="message_confirm">
        <?= $successMessage ?>
        <p><a href="login" class="login-from-success">Se connecter</a></p>
    </div>
    <?php endif; ?>
</div>


